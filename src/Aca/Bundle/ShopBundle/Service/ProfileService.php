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


    public function getSession()
    {

        return $this->session;
    }

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

        return array();
    }


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

        return array();
    }

    public function updateName($newName)
    {
        $this->db->update('aca_user', array('id' => $this->session->get('user_id')), array('name' => $newName));

        $this->session->set('name', $newName);
    }

    public function updateUsername($newName)
    {
        $this->db->update('aca_user', array('id' => $this->session->get('user_id')), array('username' => $newName));

        $this->session->set('username', $newName);
    }

    public function updatePassword($newPass)
    {
        $this->db->update('aca_user', array('id' => $this->session->get('user_id')), array('password' => $newPass));

        $this->session->set('password', $newPass);
    }

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

        return array();
    }
}