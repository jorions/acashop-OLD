<?php

namespace Aca\Bundle\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// Use this for the processLoginAction
use Symfony\Component\HttpFoundation\Request;

// Use this for DB Homework
use Aca\Bundle\ShopBundle\Db\Database;

class LoginController extends Controller {

    public function loginFormAction(Request $req)
    {

        $session = $this->get('session');

        $msg = null;

        $username = $req->get('username');
        $password = $req->get('password');



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

        // Invalid login (check for empty username and password because
        if(empty($data) && !empty($username) && !empty($password)) {

            $msg = 'Please check your credentials';
            $session->set('isLoggedIn', false);
            $session->save();
        } else {

            // Take the last user of the
            $row = array_pop($data);

            // User's name
            $name = $row['name'];

            $session->set('isLoggedIn', true);
            $session->set('name', $name);
            $session->save();
        }

        $loggedIn = $session->get('isLoggedIn');
        $name = $session->get('name');

        return $this->render(
            'AcaShopBundle:LoginForm2:login.html.twig',
            array(
                'loggedIn' => $loggedIn,
                'name' => $name,
                'msg' => $msg,
                'username' => $username,
                'password' => $password
            )
        );
    }

    public function logout()
    {
        $session = $this->get('session');

        $session->set('isLoggedIn', false);
        $session->remove('name');

        return $this->render('AcaShopBundle:LoginForm2:login.html.twig');
    }
}