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
        //echo 'Username entered: ' . $username . '<br />';

        $password = $req->get('password');
        //echo 'Password entered: ' . $password . '<br /><br />';

        // Error handling for if the username or password is blank
        if($username == "") {

            // CAN WE ROUTE INFORMATION FROM THIS FILE INTO THE TWIG SO THAT OUR TWIG CAN CONTEXTUALLY KNOW IF THE ISSUE IS A USERNAME OR PASSWORD PROBLEM?
            return $this->render('AcaShopBundle:LoginForm:loginfailure.html.twig');
            //echo "<h3 style='color:red'>Please enter a username</h3>";
        }
        if($password == "") {
            return $this->render('AcaShopBundle:LoginForm:loginfailure.html.twig');
            //echo "<h3 style='color:red'>Please enter a password</h3>";
        }

        // Prevent MYSQL injection - if the username or password use illegal characters, exit
        if(!preg_match("#^[a-zA-Z0-9]+$#", $username) || !preg_match("#^[a-zA-Z0-9]+$#", $password)) {

            return $this->render('AcaShopBundle:LoginForm:loginfailure.html.twig');

            //echo "<h3 style='color:red'>Your username or password contains invalid characters</h3>";
            //exit();
        }

        // Set up a query to check against the DB
        $query = "
        SELECT
            *
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
            // Clear old login
            session_destroy();

            // Start new login
            session_start();
            $_SESSION['username'] = $username;
            $_SESSION['password'] = $password;

            // DO WE ALWAYS HAVE TO RETURN A RENDER ('THE CONTROLLER MUST RETURN A RESPONSE')
            return $this->render('AcaShopBundle:LoginForm:loginsuccess.html.twig');

        } else {

            return $this->render('AcaShopBundle:LoginForm:loginfailure.html.twig');
        }
    }
}
