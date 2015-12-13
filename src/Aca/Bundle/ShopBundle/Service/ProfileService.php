<?php

namespace Aca\Bundle\ShopBundle\Service;

use Simplon\Mysql\Mysql;

use Aca\Bundle\Shopbundle\Controller;

use Symfony\Component\HttpFoundation\Session\Session;

class ProfileService {

    /**
     * Database class
     * @var Mysql
     */
    protected $db;

    /**
     * User session
     * @var Session
     */
    protected $session;

    /**
     * Login features
     * @var LoginService
     */
    protected $login;

    public function __construct(Mysql $db, Session $session, LoginService $login)
    {
        $this->db = $db;

        $this->session = $session;

        $this->login = $login;

        if(!$this->session->isStarted()) {
            $this->session->start();
        }
    }


    /**
     * Return the current session
     * @return Session
     */
    public function getSession()
    {

        return $this->session;
    }

    /**
     * Add shipping address to user profile
     * @param string $street
     * @param string $city
     * @param string $state
     * @param int $zip
     * @throws \Simplon\Mysql\MysqlException
     */
    public function setShippingAddress($street, $city, $state, $zip)
    {

        // Get original shipping address id so that if we update the address, we can check for if the address is still being used elsewhere after updating
        $data = $this->getUserInfo();

        $originalAddressId = $data['shipping_address_id'];


        // If shipping address already exists, pair user id to it
        if($addressId = $this->getAddressId($street, $city, $state, $zip)){

            $this->db->update('aca_user', array('id' => $this->session->get('user_id')), array('shipping_address_id' => $addressId));

        // If shipping address doesn't already exist add it to database then pair user ID to it
        } else {

            // Insert statement returns id of newly-inserted row
            $addressId = $this->db->insert('aca_address', array('street' => $street, 'city' => $city, 'state' => $state, 'zip' => $zip));

            $this->db->update('aca_user', array('id' => $this->session->get('user_id')), array('shipping_address_id' => $addressId));
        }


        // Now that address has been updated, make sure original address isn't used elsewhere. If it isn't remove it from database
        // If original address ID existed, check for it elsewhere
        $this->checkIfAddressUsed($originalAddressId);
    }

    /**
     * Add billing address to user profile
     * @param string $street
     * @param string $city
     * @param string $state
     * @param int $zip
     * @throws \Simplon\Mysql\MysqlException
     */
    public function setBillingAddress($street, $city, $state, $zip)
    {
        // Get original billing address id so that if we update the address, we can check for if the address is still being used elsewhere after updating
        $data = $this->getUserInfo();

        $originalAddressId = $data['billing_address_id'];


        // If billing address already exists, pair user id to it
        if($addressId = $this->getAddressId($street, $city, $state, $zip)){

            $this->db->update('aca_user', array('id' => $this->session->get('user_id')), array('billing_address_id' => $addressId));

            // If billing address doesn't already exist add it to database then pair user ID to it
        } else {

            // Insert statement returns id of newly-inserted row
            $addressId = $this->db->insert('aca_address', array('street' => $street, 'city' => $city, 'state' => $state, 'zip' => $zip));

            $this->db->update('aca_user', array('id' => $this->session->get('user_id')), array('billing_address_id' => $addressId));
        }


        // Now that address has been updated, make sure original address isn't used elsewhere. If it isn't remove it from database
        $this->checkIfAddressUsed($originalAddressId);
    }

    /**
     * Determine if a given address is used elsewhere and remove it if it isn't
     * @param int $addressId
     */
    public function checkIfAddressUsed($addressId)
    {
        // If original address ID existed, check for it being used elsewhere
        if($addressId != 0) {

            $query = '
                SELECT
                  id
                FROM
                  aca_user
                WHERE
                  shipping_address_id= :addressId
                  OR billing_address_id= :addressId';

            $data = $this->db->fetchRowMany($query, array('addressId' => $addressId));

            // If the address isn't used elsewhere, remove it
            if(empty($data)) {

                $this->db->delete('aca_address', array('id' => $addressId));
            }
        }
    }

    /**
     * Determine if a new proposed address can be updated, provide status message, and if address is valid conduct update
     * @param string $street
     * @param string $city
     * @param string $state
     * @param int $zip
     * @return null|string
     */
    public function checkShippingAddress($street, $city, $state, $zip)
    {

        $msg = null;

        // Make sure all fields have content
        if (!empty($street) && !empty($city) && !empty($state) && !empty($zip)) {

            // Make sure zip is valid
            if (preg_match("#^[0-9]+$#", $zip)) {

                // Make sure the new address is different from the old address
                $data = $this->getShippingAddress();
                if ($street != $data['street'] || $city != $data['city'] || $state != $data['state'] || $zip != $data['zip']) {

                    // With all checks set, update shipping
                    $this->setShippingAddress($street, $city, $state, $zip);
                    $msg = 'Shipping address updated!';

                // If new address is same as current one tell user
                } else {

                    $msg = 'That\'s your current address!';
                }

            // If zip is invalid set error message
            } else {

                $msg = 'Please enter only numbers for zip';
            }

        // If all fields don't have content set error message
        } else {

            $msg = 'Please enter a street, city, state, and zip';
        }

        return $msg;
    }


    /**
     * Determine if a new proposed address can be updated, provide status message, and if address is valid conduct update
     * @param string $street
     * @param string $city
     * @param string $state
     * @param int $zip
     * @return null|string
     */
    public function checkBillingAddress($street, $city, $state, $zip)
    {

        $msg = null;

        // Make sure all fields have content
        if (!empty($street) && !empty($city) && !empty($state) && !empty($zip)) {

            // Make sure zip is valid
            if (preg_match("#^[0-9]+$#", $zip)) {

                // Make sure the new address is different from the old address
                $data = $this->getBillingAddress();
                if ($street != $data['street'] || $city != $data['city'] || $state != $data['state'] || $zip != $data['zip']) {

                    // With all checks set, update shipping
                    $this->setBillingAddress($street, $city, $state, $zip);
                    $msg = 'Billing address updated!';

                    // If new address is same as current one tell user
                } else {

                    $msg = 'That\'s your current address!';
                }

                // If zip is invalid set error message
            } else {

                $msg = 'Please enter only numbers for zip';
            }

            // If all fields don't have content set error message
        } else {

            $msg = 'Please enter a street, city, state, and zip';
        }

        return $msg;
    }


    /**
     * Determine if a new proposed name can be updated, provide status message, and if name is valid conduct update
     * @param string $name
     * @return null|string
     */
    public function checkName($name)
    {

        $msg = null;

        // Make sure info was entered
        if (!empty($name)) {

            // Prevent MySQL injection - make sure all characters are legal
            if (preg_match("#^[a-zA-Z0-9]+$#", $name)) {

                // Make sure new name is different than current name
                if ($this->getName() != $name) {

                    // Set new name
                    $this->updateName($name);
                    $msg = 'Name updated!';

                // If new name is same as current name tell user
                } else {

                    $msg = 'That\'s your current name!';
                }

            // If any illegal characters tell user
            } else {
                $msg = 'Make sure your name contains only letters and numbers';
            }

        // If name empty set error message
        } else {

            $msg = 'No new name entered';
        }

        return $msg;
    }


    /**
     * Determine if a new proposed username can be updated, provide status message, and if username is valid conduct update
     * @param string $username
     * @return null|string
     */
    public function checkUsername($username)
    {

        $msg = null;

        // Make sure info was entered
        if (!empty($username)) {

            // Prevent MySQL injection - make sure all characters are legal
            if (preg_match("#^[a-zA-Z0-9]+$#", $username)) {

                // Make sure username isn't already used
                if ($this->login->checkRegistration($username)) {

                    // Set new username
                    $this->updateUsername($username);
                    $msg = 'Username updated!';

                // If username is same as original tell user (instead of giving error message)
                } else if ($this->getUsername() == $username) {

                    $msg = 'That\'s your current username!';

                // If username already exists tell user
                } else {

                    $msg = 'That username is already taken - sorry!';
                }

            // If any illegal characters tell user
            } else {
                $msg = 'Make sure your username contains only letters and numbers';
            }

        // If username empty set error message
        } else {

            $msg = 'No new username entered';
        }

        return $msg;
    }

    /**
     * Determine if a new proposed password can be updated, provide status message, and if password is valid conduct update
     * @param string $password - proposed new user password
     * @param string $passwordCheck - retyped user password to compare against first typed password
     * @return null|string
     */
    public function checkPassword($password, $passwordCheck)
    {

        $msg = null;

        // Make sure info was entered
        if (!empty($password) && !empty($passwordCheck)) {

            // Prevent MySQL injection - make sure all characters are legal
            if (preg_match("#^[a-zA-Z0-9]+$#", $password) && preg_match("#^[a-zA-Z0-9]+$#", $passwordCheck)) {

                // Make sure passwords match
                if ($password == $passwordCheck) {

                    // Set new password
                    $this->updatePassword($password);
                    $msg = 'Password updated!';

                // If passwords don't match tell user
                } else {

                    $msg = 'Make sure your new password matches in both boxes';
                }

            // If any illegal characters tell user
            } else {

                $msg = 'Make sure your password contains only letters and numbers';
            }

        // If one of the password fields was empty set error message
        } else {

            $msg = 'Enter your new password in both boxes';
        }

        return $msg;
    }


    /**
     * Determine if a new proposed email can be updated, provide status message, and if email is valid conduct update
     * @param string $email
     * @return null|string
     */
    public function checkEmail($email)
    {

        $msg = null;

        // Make sure info was entered
        if (!empty($email)) {

            // Make sure email is valid
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {

                // If email is same as original tell user
                if ($this->getEmail() == $email) {

                    $msg = 'That\'s your current email!';

                // With all checks complete update email
                } else {

                    $this->updateEmail($email);
                    $msg = 'Email updated!';

                }

            // If email is invalid set error message
            } else {

                $msg = 'Invalid email entered';
            }

        // If no email entered set error message
        } else {

            $msg = 'No new email entered';
        }

        return $msg;
    }

    /**
     * Get all of the data associated with a specific user
     * @return array|null
     */
    public function getUserInfo()
    {
        $query = '
            SELECT
              *
            FROM
              aca_user
            WHERE
              id= :userId';

        return $this->db->fetchRow($query, array('userId' => $this->session->get('user_id')));
    }

    /**
     * Get a user's shipping address
     * @return array|null
     */
    public function getShippingAddress()
    {
        // Get user info
        $data = $this->getUserInfo();

        // Get address array
        $address = $this->getAddress($data['shipping_address_id']);

        // If address is set, return address
        if(!empty($address)) {

            return $address;
        }

        return null;
    }

    /**
     * Get a user's billing address
     * @return array|null
     */
    public function getBillingAddress()
    {
        // Get user info
        $data = $this->getUserInfo();

        // Get address array
        $address = $this->getAddress($data['billing_address_id']);

        // If address is set, return address
        if(!empty($address)) {

            return $address;
        }

        return null;
    }


    /**
     * Update a user's name
     * @param string $newName
     * @throws \Simplon\Mysql\MysqlException
     */
    public function updateName($newName)
    {
        $this->db->update('aca_user', array('id' => $this->session->get('user_id')), array('name' => $newName));

        $this->session->set('name', $newName);
    }

    /**
     * Update a user's username
     * @param string $newName
     * @throws \Simplon\Mysql\MysqlException
     */
    public function updateUsername($newName)
    {
        $this->db->update('aca_user', array('id' => $this->session->get('user_id')), array('username' => $newName));

        $this->session->set('username', $newName);
    }

    /**
     * Updated a user's password
     * @param string $newPass
     * @throws \Simplon\Mysql\MysqlException
     */
    public function updatePassword($newPass)
    {
        $this->db->update('aca_user', array('id' => $this->session->get('user_id')), array('password' => $newPass));

        $this->session->set('password', $newPass);
    }

    /**
     * Get an address ID based on a given address
     * @param string $street
     * @param string $city
     * @param string $state
     * @param int $zip
     * @return int
     */
    public function getAddressId($street, $city, $state, $zip)
    {

        $query = '
            SELECT
              id
            FROM
              aca_address
            WHERE
              street= :street
              and city= :city
              and state= :state
              and zip= :zip';

        $data = $this->db->fetchRow($query, array('street' => $street, 'city' => $city, 'state' => $state, 'zip' => $zip));

        // Address exists
        if(!empty($data)) {
            return $data['id'];
        }

        return 0;
    }

    /**
     * Get an address based on an address ID
     * @param int $addressId
     * @return array|null
     */
    public function getAddress($addressId)
    {
        $query = '
            SELECT
              *
            FROM
              aca_address
            WHERE
              id= :addressId';

        $data = $this->db->fetchRow($query, array('addressId' => $addressId));

        // Address exists
        if(!empty($data)) {
            return $data;
        }

        return null;
    }

    /**
     * Updated a user's email
     * @param string $newEmail
     * @throws \Simplon\Mysql\MysqlException
     */
    public function updateEmail($newEmail)
    {
        $this->db->update('aca_user', array('id' => $this->session->get('user_id')), array('email' => $newEmail));
    }

    /**
     * Return a user's email
     * @return string|null
     */
    public function getEmail()
    {
        // Get user info
        $data = $this->getUserInfo();

        // Get email array
        $email = $data['email'];

        // If email is set, return email
        if(!empty($email)) {

            return $email;
        }

        return null;
    }

    /**
     * Return a user's name
     * @return string|null
     */
    public function getName()
    {
        // Get user info
        $data = $this->getUserInfo();

        // Get email array
        $name = $data['name'];

        // If email is set, return email
        if(!empty($name)) {

            return $name;
        }

        return null;
    }

    /**
     * Return a user's username
     * @return string|null
     */
    public function getUsername()
    {
        // Get user info
        $data = $this->getUserInfo();

        // Get email array
        $username = $data['username'];

        // If email is set, return email
        if(!empty($username)) {

            return $username;
        }

        return null;
    }
}