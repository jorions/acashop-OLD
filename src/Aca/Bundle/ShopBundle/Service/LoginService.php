<?php

namespace Aca\Bundle\ShopBundle\Service;

use Simplon\Mysql\Mysql;

use Aca\Bundle\Shopbundle\Controller;

use Symfony\Component\HttpFoundation\Session\Session;

class LoginService {
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
     * Cart class
     * @var CartService
     */
    protected $cart;


    public function __construct(Mysql $db, Session $session)
    {
        $this->db = $db;

        $this->session = $session;

        if(!$this->session->isStarted()) {
            $this->session->start();
        }
    }

    public function getSession() {
        return $this->session;
    }

    public function setSession($loggedIn, $name, $username, $password, $userId)
    {

        // Set all session variables
        $this->session->set('loggedIn', $loggedIn);
        $this->session->set('name', $name);
        $this->session->set('username', $username);
        $this->session->set('password', $password);
        $this->session->set('user_id', $userId);

        // Save session
        $this->session->save();
    }

    public function checkLogin($username, $password)
    {
        $query = "
            SELECT
                *
            FROM
                aca_user
            WHERE
                username= :username
                and password= :password";

        $data = $this->db->fetchRow($query, array('username' => $username, 'password' => $password));

        // Valid login
        if (!empty($data)) {

            // Get user's name from returned query
            $name = $data['name'];
            $id = $data['id'];

            // Set session variables
            $this->session->set('loggedIn', true);
            $this->session->set('name', $name);
            $this->session->set('username', $username);
            $this->session->set('password', $password);
            $this->session->set('user_id', $id);

            // Before you can run any operations on $session you have to save it
            $this->session->save();

            return true;

        // Invalid login
        } else {
            $this->session->set('loggedIn', false);

            // Before you can run any operations on $session you have to save it
            $this->session->save();

            return false;
        }
    }

    public function checkRegistration($username)
    {

        $query = "
            SELECT
                *
            FROM
                aca_user
            WHERE
                username= :username";

        $data = $this->db->fetchRow($query, array('username' => $username));

        // Username doesn't exist yet
        if(count($data) == 0) {
            return true;
        }

        // Username already exists
        return false;

    }

    public function logout()
    {

        $this->session->remove('loggedIn');
        $this->session->remove('name');
        $this->session->remove('username');
        $this->session->remove('password');
        $this->session->remove('user_id');

        $this->session->save();
    }

    public function loggedInCheck()
    {
        if($this->session->get('loggedIn') != true) {
            return false;
        }

        return true;
    }
}