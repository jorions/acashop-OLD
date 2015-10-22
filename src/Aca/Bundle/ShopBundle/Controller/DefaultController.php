<?php

namespace Aca\Bundle\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// Use this for the processLoginAction
use Symfony\Component\HttpFoundation\Request;

// Use this for DB Homework
use Aca\Bundle\ShopBundle\Db\Database;

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
        $username = $req->get('username');
        echo 'Username=' . $username . '<br />';

        $password = $req->get('password');
        echo 'Password=' . $password . '<br />';

        // Error handling for if the username or password is blank
        if($username == "") {
            echo "<h3 style='color:red'>Please enter a username</h3>";
        }
        if($password == "") {
            echo "<h3 style='color:red'>Please enter a password</h3>";
        }
        if($username == "" || $password == "") {
            exit();
        }

        // Prevent MYSQL injection - if the username or password use illegal characters, exit
        if(!preg_match("#^[a-zA-Z0-9]+$#", $username) || !preg_match("#^[a-zA-Z0-9]+$#", $password)) {

            echo "<h3 style='color:red'>Your username or password contains invalid characters</h3>";
            exit();
        }

        // Set up a query to check against the DB
        $query = "
        SELECT
            user_id
        FROM
            aca_user
        WHERE
            username='$username'
            and password='$password'";

        // Check for the record that exists
        $db = new Database();
        $result = $db->fetchRows($query);

        // If you find a record, the login is valid, otherwise it is not
        if($result->num_rows > 0) {

            // If they are valid, set to SESSION and make the login boxes go away
            echo "Welcome!";

            session_start();
            $_SESSION['username'] = "";
            $_SESSION['password'] = "";

        } else {

            echo "Invalid login!";
        }
    }
}
