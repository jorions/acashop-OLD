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
    public function getSession() {
        return $this->session;
    }

    /**
     * Set all core session variables
     * @param bool $loggedIn
     * @param string $name
     * @param string $username
     * @param string $password
     * @param int $userId
     */
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

    /**
     * Determine if the entered login is valid
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function checkLogin($username, $password)
    {

        $msg = null;


        // If any fields are left empty tell user
        if (empty($username) || empty($password)) {

            $msg = 'Please make sure you enter a username and password';

        // If all fields have data check login
        } else {

            $query = "
                SELECT
                    *
                FROM
                    aca_user
                WHERE
                    username= :username
                    and password= :password";

            $data = $this->db->fetchRow($query, array('username' => $username, 'password' => $password));

            // If invalid login set session variables
            if (empty($data)) {

                $this->session->set('loggedIn', false);
                $this->session->save();

                $msg = 'Please check your credentials';

            // If valid login set session variables
            } else {

                $this->setSession(true, $data['name'], $username, $password, $data['id']);
            }
        }

        return $msg;

    }

    /**
     * Determine if the proposed username is already used
     * @param string $username
     * @return bool
     */
    public function registrationIsNew($username)
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

    /**
     * Determine if a new proposed user profile can be created, provide status message, and if info is valid create new user
     * @param $username
     * @param $password
     * @param $passwordCheck
     * @param $name
     * @return null|string
     * @throws \Simplon\Mysql\MysqlException
     */
    public function checkRegistration($username, $password, $passwordCheck, $name)
    {

        $msg = null;

        // If some fields are left empty tell user
        if (empty($username) || empty($password) || empty($passwordCheck) || empty($name)) {

            $msg = 'Please make sure you have entered information in all fields';

        // Prevent MySQL injection - if anything uses illegal characters, tell user
        } else if (!preg_match("#^[a-zA-Z0-9]+$#", $username) || !preg_match("#^[a-zA-Z0-9]+$#", $password) || !preg_match("#^[a-zA-Z0-9]+$#", $passwordCheck) || !preg_match("#^[a-zA-Z0-9]+$#", $name)) {

            $msg = 'Make sure everything contains only numbers and letters';

        // Now that we know there is no MySQL injection, query DB to make sure login doesn't already exist
        } else if (!$this->registrationIsNew($username)) {

            $msg = 'That username already exists - please try another';

        // Login does not exist but password was entered improperly
        } else if ($password != $passwordCheck) {

            $msg = 'Please make sure you properly entered your password in both fields';

        // Login does not exist and password was entered properly
        } else {

            // Create new user
            $userId = $this->db->insert('aca_user', array('name' => $name, 'username' => $username, 'password' => $password));

            // Set render array variable now that user credentials have been created
            $loggedIn = true;

            // Set and save session values
            $this->setSession($loggedIn, $name, $username, $password, $userId);

        }

        return $msg;
    }

    /**
     * Remove all session variables upon logout
     */
    public function logout()
    {

        $this->session->remove('loggedIn');
        $this->session->remove('name');
        $this->session->remove('username');
        $this->session->remove('password');
        $this->session->remove('user_id');

        $this->session->save();
    }

    /**
     * Determine if user is logged in
     * @return bool
     */
    public function loggedInCheck()
    {
        if($this->session->get('loggedIn') != true) {
            return false;
        }

        return true;
    }
}