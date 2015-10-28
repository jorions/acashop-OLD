<?php

namespace Aca\Bundle\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// Use this for the processLoginAction
use Symfony\Component\HttpFoundation\Request;

// Use this for DB Homework
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
                user_id
            FROM
                aca_user
            WHERE
                username='$username'
                and password='$password'";

            $db = new Database();

            $data = $db->fetchRowMany($query);

            // Invalid login (check for empty username and password)
            if (empty($data) && $req->getMethod() == 'POST') {

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
        return new RedirectResponse('/login_form');
    }
}