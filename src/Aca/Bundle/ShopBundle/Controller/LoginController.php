<?php

namespace Aca\Bundle\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// Use this for logoutAction
use Symfony\Component\HttpFoundation\RedirectResponse;

// Use this for loginFormAction
use Symfony\Component\HttpFoundation\Request;

// Use this for Database object
use Aca\Bundle\ShopBundle\Db\Database;

class LoginController extends Controller {

    /**
     * Login form action
     * @param Request $req
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginFormAction(Request $req)
    {
        $msg = null;

        // Sets the $session variable by getting the session
        $session = $this->getSession();
        $username = $req->get('username');
        $password = $req->get('password');

        if (!empty($username) && !empty($password)) {

            $query = "
            SELECT
                *
            FROM
                aca_user
            WHERE
                username='$username'
                and password='$password'";

            $db = $this->get('acadb');

            $data = $db->fetchRowMany($query);

            // Invalid login (check for empty username and password)
            if (empty($data)) {

                $msg = 'Please check your credentials';
                $session->set('loggedIn', false);

            // Valid login
            } else {

                // Take the last user of the returned array
                $row = array_pop($data);

                // Get user's name
                $name = $row['name'];

                $session->set('loggedIn', true);
                $session->set('name', $name);
            }
        }

        // Before you can run any operations on $session you have to save it
        $session->save();

        $loggedIn = $session->get('loggedIn');

        // If you "get" something that doesn't exist then it will be created with a null value
        $name = $session->get('name');

        // "AcaShopBundle" is the shorthand for the namespace "ACA\Bundle\ShopBundle\Resources\views"
        // This returns a "response" object, which is the only thing that Symfony can display in the browser
        // There are 3 types of response objects: Redirect, JSON, and Response
        return $this->render(
            'AcaShopBundle:LoginForm:login.html.twig',
            array(
                'loggedIn' => $loggedIn,
                'name' => $name,
                'msg' => $msg,
                'username' => $username,
                'password' => $password
            )
        );
    }

    /**
     * Get a started session
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    public function getSession()
    {
        $session = $this->get('session');

        if(!$session->isStarted()) {
            $session->start();
        }

        return $session;
    }

    /**
     * Logout logic
     * @return RedirectResponse
     */
    public function logoutAction()
    {
        $session = $this->getSession();

        $session->remove('loggedIn');
        $session->remove('name');

        $session->save();

        // This is another type of response object
        return new RedirectResponse('/');
    }

    /**
     * Account registration
     * @param Request $req
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registrationAction(Request $req) {

        // Get or start a session
        $session = $this->getSession();

        // Set variables to check for form entry
        // Check for logged in first so we don't reset credentials
        if($session->get('loggedIn') != true) {
            $name = $req->get('name');
            $username = $req->get('username');
            $password = $req->get('password');
            $loggedIn = false;

        // If already logged on just render page
        } else {
            return $this->render(
                'AcaShopBundle:LoginForm:registration.html.twig',
                array(
                    'loggedIn' => $session->get('loggedIn'),
                    'name' => $session->get('name'),
                    'msg' => $session->get('msg'),
                    'username' => $session->get('username'),
                    'password' => $session->get('password')
                )
            );
        }

        // Check for form entry
        if (!empty($username) && !empty($password) && !empty($name) && $req->getMethod() == 'POST' && $loggedIn != true) {

            // Prevent MySQL injection - if anything uses illegal characters, tell user
            if(!preg_match("#^[a-zA-Z0-9]+$#", $username) || !preg_match("#^[a-zA-Z0-9]+$#", $password) || !preg_match("#^[a-zA-Z0-9]+$#", $name)) {

                $msg = 'Make sure everything contains only numbers and letters';
                $session->set('msg', $msg);
                $session->set('loggedIn', false);

                $session->save();

                $loggedIn = $session->get('loggedIn');
                $msg = $session->get('msg');

                // Return the rendered twig
                return $this->render(
                    'AcaShopBundle:LoginForm:registration.html.twig',
                    array(
                        'loggedIn' => $loggedIn,
                        'name' => $name,
                        'msg' => $msg,
                        'username' => $username,
                        'password' => $password
                    )
                );
            }

            // Now that we know there is no MySQL injection, query DB
            $query = "
            SELECT
                *
            FROM
                aca_user
            WHERE
                username='$username'";

            $db = $this->get('acadb');

            $data = $db->fetchRow($query);

            // Login already exists
            if (count($data) > 0 && $req->getMethod() == 'POST') {

                $msg = 'That username already exists - please try another';
                $session->set('msg', $msg);
                $session->set('loggedIn', false);

            // Login does not exist
            } else {

                // Create new user
                //$db->insertNewUser($username, $password, $name);

                $db->insert('aca_user', array('name' => $name, 'username' => $username, 'password' => $password));

                // Set session values
                $session->set('loggedIn', true);
                $session->set('name', $name);
                $session->set('username', $username);
                $session->set('password', $password);

                // Before you can run any operations on $session you have to save it
                $session->save();

                // Set render array variables now that user credentials have been created
                $loggedIn = $session->get('loggedIn');
                $name = $req->get('name');
                $msg = $session->get('msg');
                $username = $req->get('username');
                $password = $req->get('password');

                // Return the rendered twig
                return $this->render(
                    'AcaShopBundle:LoginForm:login.html.twig',
                    array(
                        'loggedIn' => $loggedIn,
                        'name' => $name,
                        'msg' => $msg,
                        'username' => $username,
                        'password' => $password
                    )
                );
            }


        $buttonClicked = $req->get('create');

        // Form entry error
        } else if($loggedIn != true) {
            $msg = 'Please make sure you have entered information in all fields';
            $session->set('msg', $msg);
            $session->set('loggedIn', false);
        }

        // Before you can run any operations on $session you have to save it
        $session->save();

        // Set render array variables now that user credentials have been created
        $loggedIn = $session->get('loggedIn');
        $msg = $session->get('msg');

        // Return the rendered twig
        return $this->render(
            'AcaShopBundle:LoginForm:registration.html.twig',
            array(
                'loggedIn' => $loggedIn,
                'name' => $name,
                'msg' => $msg,
                'username' => $username,
                'password' => $password
            )
        );
    }
}