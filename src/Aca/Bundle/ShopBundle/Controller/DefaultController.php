<?php

namespace Aca\Bundle\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// Use this for the processLoginAction
use Symfony\Component\HttpFoundation\Request;

// Use this for DB Homework
use Aca\Bundle\ShopBundle\Db\Database

class DefaultController extends Controller
{
    public function indexAction($name, $age)
    {
        return $this->render('AcaShopBundle:Default:index.html.twig', array('name' => $name, 'age' => $age));
    }

    public function loginFormAction()
    {
        return $this->render('AcaShopBundle:LoginForm:loginform.html.twig');
    }

    public function processLoginAction(Request $req)
    {
        // Pull the information out of the form (get can take information from different sources, including forms)
        $user = $req->get('username');
        echo '$username=' . $user . '<br />';

        $pass = $req->get('password');
        echo '$password=' . $pass . '<br />';

        // Run a query against the DB
        $query = "
        SELECT
            user_id
        FROM
            aca_user
        WHERE
            username='$user'
            and password='$pass'";

        // CHeck for the record that exists

        // If you find a record, the login is valid, otherwise it is not

        // If they are valid, set to SESSION and make the login boxes go away
    }
}
