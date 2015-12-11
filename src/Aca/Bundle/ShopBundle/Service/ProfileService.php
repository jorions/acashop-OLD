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

    public function __construct(Mysql $db, Session $session)
    {
        $this->db = $db;

        $this->session = $session;

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
}