<?php

namespace Aca\Bundle\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// Use this for logoutAction
use Symfony\Component\HttpFoundation\RedirectResponse;

// Use this for loginFormAction
use Symfony\Component\HttpFoundation\Request;

// Use this for database - WHY IS THIS SHOWING AS NOT USED
use Simplon\Mysql\Mysql;

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
                username= :username
                and password= :password";

            $db = $this->get('acadb');

            $data = $db->fetchRow($query, array('username' => $username, 'password' => $password));

            // Invalid login
            if (empty($data)) {

                $msg = 'Please check your credentials';
                $session->set('loggedIn', false);

            // Valid login
            } else {

                // Get user's name from returned query
                $name = $data['name'];
                $id = $data['id'];

                // Set session variables
                $session->set('loggedIn', true);
                $session->set('name', $name);
                $session->set('username', $username);
                $session->set('password', $password);
                $session->set('user_id', $id);

                // Set up shopping cart
                $cart = $this->get('cart');
                $cart->getCartId();
            }

        // If the form isn't fully filled out but has been submitted
        } else if($req->getMethod() == 'POST') {

            $msg = 'Please make sure you enter a username and password';
        }

        // Before you can run any operations on $session you have to save it
        $session->save();

        $loggedIn = $session->get('loggedIn');

        // If you "get" something that doesn't exist then it will be created with a null value (this is for if login was invalid)
        $name = $session->get('name');
        $id = $session->get('id');

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
                'password' => $password,
                'user_id' => $id
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
        $loggedIn = $session->get('loggedIn');
        $name = $req->get('name');
        $msg = null;
        $username = $req->get('username');
        $password = $req->get('password');
        $passwordCheck = $req->get('passwordCheck');

        // If already logged on just render page with "Welcome" message
        if($loggedIn == true) {
            return $this->render(
                'AcaShopBundle:LoginForm:registration.html.twig',
                array(
                    'loggedIn' => true,
                    'name' => $session->get('name'),
                    'msg' => $msg,
                    'username' => $session->get('username'),
                    'password' => $session->get('password'),
                    'user_id' => $session->get('user_id')
                )
            );
        }

        // Check for form entry
        // If values in all fields and submit button was pressed, check for illegal characters
        if (!empty($username) && !empty($password) && !empty($passwordCheck) && !empty($name) && $req->getMethod() == 'POST') {

            // Prevent MySQL injection - if anything uses illegal characters, tell user
            if(!preg_match("#^[a-zA-Z0-9]+$#", $username) || !preg_match("#^[a-zA-Z0-9]+$#", $password) || !preg_match("#^[a-zA-Z0-9]+$#", $passwordCheck) || !preg_match("#^[a-zA-Z0-9]+$#", $name)) {

                // Set error message
                $msg = 'Make sure everything contains only numbers and letters';

                // Set session variable
                $loggedIn = false;
                $session->set('loggedIn', $loggedIn);
                $session->save();

                // Return the rendered twig
                return $this->render(
                    'AcaShopBundle:LoginForm:registration.html.twig',
                    array(
                        'loggedIn' => $loggedIn,
                        'name' => $name,
                        'msg' => $msg,
                        'username' => $username,
                        'password' => $password,
                        'passwordCheck' => $passwordCheck
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
                username= :username";

            $db = $this->get('acadb');

            $data = $db->fetchRow($query, array('username' => $username));

            // Login already exists
            if (count($data) > 0 && $req->getMethod() == 'POST') {

                // Set error message
                $msg = 'That username already exists - please try another';

                // Set session variable
                $loggedIn = false;
                $session->set('loggedIn', $loggedIn);

            // Login does not exist
            } else {

                // Make sure password was entered properly in both fields
                if ($password != $passwordCheck) {

                    $msg = 'Please make sure you properly entered your password in both fields';

                    return $this->render(
                        'AcaShopBundle:LoginForm:registration.html.twig',
                        array(
                            'loggedIn' => $loggedIn,
                            'name' => $name,
                            'msg' => $msg,
                            'username' => $username,
                            'password' => $password,
                            'passwordCheck' => $passwordCheck
                        )
                    );
                }


                // Create new user
                $userId = $db->insert('aca_user', array('name' => $name, 'username' => $username, 'password' => $password));

                // Set render array variable now that user credentials have been created
                $loggedIn = true;

                // Set session values
                $session->set('loggedIn', $loggedIn);
                $session->set('name', $name);
                $session->set('username', $username);
                $session->set('password', $password);
                $session->set('user_id', $userId);

                // Save session
                $session->save();

                // Set up shopping cart
                $cart = $this->get('cart');
                $cart->getCartId();

                // Return the rendered twig
                return $this->render(
                    'AcaShopBundle:LoginForm:login.html.twig',
                    array(
                        'loggedIn' => $loggedIn,
                        'name' => $name,
                        'msg' => $msg,
                        'username' => $username,
                        'password' => $password,
                        'passwordCheck' => $passwordCheck
                    )
                );
            }

        // Form entry error
        } else if($loggedIn != true && $req->getMethod() == 'POST') {

            $msg = 'Please make sure you have entered information in all fields';
            $session->set('loggedIn', false);
        }

        // Before you can run any operations on $session you have to save it
        $session->save();

        // Set render array variables now that user credentials have been created
        $loggedIn = $session->get('loggedIn');

        // Return the rendered twig
        return $this->render(
            'AcaShopBundle:LoginForm:registration.html.twig',
            array(
                'loggedIn' => $loggedIn,
                'name' => $name,
                'msg' => $msg,
                'username' => $username,
                'password' => $password,
                'passwordCheck' => $passwordCheck
            )
        );
    }
}