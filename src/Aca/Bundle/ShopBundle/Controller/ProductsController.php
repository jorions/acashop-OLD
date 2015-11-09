<?php

namespace Aca\Bundle\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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

        // OLD METHOD WITHOUT SERVICE
        //$db = new Database();

        // NEW METHOD USING SERVICE
        $db = $this->get('acadb');

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

        // NEW METHOD USING SERVICE
        $db = $this->get('acadb');
        $cart = $this->get('cart');

        $alreadyInCart = false;

        $query = "
            SELECT
              *
            FROM
              aca_product
            WHERE
              slug = :myslug";

        // The new acadb object has a method called fetchRow which takes a query as the first parameter and then an associative array
        // where you define the variable terms in the given $query
        $data = $db->fetchRow($query, array('myslug' => $slug));

        // Make sure item exists
        if(count($data) > 0) {

            // Set form function based on whether the item is already in the user's cart
            // Setup query to check for product in user's cart
            $query = "
                SELECT
                  product_id
                FROM
                  aca_cart_product
                WHERE
                  cart_id = :cartID";

            // Query DB
            $cartData = $db->fetchRowMany($query, array('cartID' => $cart->getCartId()));

            if(count($cartData) > 0) {
                foreach ($cartData as $id) {
                    if ($id['product_id'] == $data['id']) {
                        $alreadyInCart = true;
                    }
                }
            }

            // Product page
            return $this->render(
                'AcaShopBundle:Products:product.page.html.twig',
                array(
                    'product' => $data,
                    'error' => false,
                    'alreadyInCart' => $alreadyInCart
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