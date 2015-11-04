<?php

namespace Aca\Bundle\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// Use this for Database object
use Aca\Bundle\ShopBundle\Db\Database;

// Use this for Login objects
use Aca\Bundle\ShopBundle\Controller\LoginController;

use Symfony\Component\HttpFoundation\Session\Session;


class ProductsController extends Controller {

    /**
     * Show all products on page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAllAction() {


        /*
        $login = new LoginController();

        $session = $login->getSession();

        if($session->get('loggedIn') == true) {
            $loggedIn = $session->get('loggedIn');
            $name = $session->get('name');
            $msg = null;
            $username = $session->get('username');
            $password = $session->get('password');
        } else {
            $loggedIn = false;
            $name = null;
            $msg = null;
            $username = null;
            $password = null;
        }


        return $this->render(
            'AcaShopBundle:Products:products.html.twig',
            array(
                'loggedIn' => $loggedIn,
                'name' => $name,
                'msg' => $msg,
                'username' => $username,
                'password' => $password
            )
        );*/

        $db = new Database();

        // DB query
        $query = "SELECT * FROM aca_product";

        // Query DB to get all items from aca_product
        $data = $db->fetchRowMany($query);

        return $this->render(
            'AcaShopBundle:Products:products.html.twig',
            array(
                'products' => $data
            )
        );
    }

    /**
     * Show individual product page
     * @param $slug
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewProductAction($slug) {

        $db = new Database();

        $query = "SELECT * FROM aca_product WHERE slug = '$slug'";

        $data = $db->fetchRowMany($query);

        // Make sure item exists
        if(count($data) > 0) {

            // Set $data to be the first index of the array so that the properties (name, image, etc) can be directly referenced in the twig
            $data = $data[0];

            // HTML-formatted product page
            return $this->render(
                'AcaShopBundle:Products:product.page.html.twig',
                array(
                    'product' => $data,
                    'error' => false
                )
            );

        } else {

            return $this->render(
                'AcaShopBundle:Products:product.page.html.twig',
                array(
                    'error' => true
                )
            );
        }
    }
}