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
        $login = $this->get('login');

        $msg = null;

        // Sets the $session variable by getting the session
        $session = $login->getSession();
        $username = $req->get('username');
        $password = $req->get('password');

       if ($req->getMethod() == 'POST') {

           $msg = $login->checkLogin($username, $password);
       }

        $loggedIn = $session->get('loggedIn');

        // If logged in set up new cart (can't put this in checkLogin because the cart service can only be used once logged in)
        if ($loggedIn) {

            $this->get('cart');
        }

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
     * Logout logic
     * @return RedirectResponse
     */
    public function logoutAction()
    {
        $this->get('login')->logout();

        return new RedirectResponse('/');
    }

    /**
     * Account registration
     * @param Request $req
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registrationAction(Request $req) {

        // Get or start a session
        $login = $this->get('login');
        $session = $login->getSession();

        // Set variables to check for form entry
        $name = $req->get('name');
        $msg = null;
        $username = $req->get('username');
        $password = $req->get('password');
        $passwordCheck = $req->get('passwordCheck');

        // If loggedIn is empty set it to false
        if(empty($session->get('loggedIn'))) {

            $session->set('loggedIn', false);

            // Before you can run any operations on $session you have to save it
            $session->save();
        }


        // If POST then submit button was pressed
        if($req->getMethod() == 'POST') {

            $msg = $login->checkRegistration($username, $password, $passwordCheck, $name);

        }


        // If logged in set up new cart (can't put this in checkRegistration because the cart service can only be used once logged in)
        if ($session->get('loggedIn')) {

            $this->get('cart');

        }


        // Return the rendered twig
        return $this->render(
            'AcaShopBundle:LoginForm:registration.html.twig',
            array(
                'loggedIn' => $session->get('loggedIn'),
                'name' => $name,
                'msg' => $msg,
                'username' => $username,
                'password' => $password,
                'passwordCheck' => $passwordCheck
            )
        );
    }
}