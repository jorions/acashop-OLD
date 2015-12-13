<?php

namespace Aca\Bundle\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Session\Session;


class ProductsController extends Controller {

    /**
     * Show all products on page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAllAction() {

        // NEW METHOD USING SERVICE
        $db = $this->get('acadb');

        // DB query
        $query = "SELECT * FROM aca_product";

        // Query DB to get all items from aca_product
        $data = $db->fetchRowMany($query);

        return $this->render(
            'AcaShopBundle:Products:products.html.twig',
            array(
                'products' => $data,
                'loggedIn' => $this->get('login')->loggedInCheck()
            )
        );
    }

    /**
     * Show individual product page
     * @param $slug
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewProductAction($slug) {

        $loggedIn = $this->get('login')->loggedInCheck();

        // NEW METHOD USING SERVICE
        $db = $this->get('acadb');

        // If logged in create cart (avoids getCartId error for when not logged in)
        if($loggedIn) {
            $cart = $this->get('cart');
        }

        // Used to determine if the product on the page is already in the cart
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

            // If user is logged in (avoids errors when using cart)
            if($loggedIn) {
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

                // If there is data in the returned array of ids...
                if (count($cartData) > 0) {

                    // Iterate through the cart array, and if the id of the given product is already in the cart, then set the variable accordingly
                    foreach ($cartData as $id) {
                        if ($id['product_id'] == $data['id']) {
                            $alreadyInCart = true;
                        }
                    }
                }
            }

            // Product page with no error
            return $this->render(
                'AcaShopBundle:Products:product.page.html.twig',
                array(
                    'product' => $data,
                    'error' => false,
                    'alreadyInCart' => $alreadyInCart,
                    'loggedIn' => $loggedIn
                )
            );

        } else {

            // Product page with error ($alreadyInCart not needed in this case)
            return $this->render(
                'AcaShopBundle:Products:product.page.html.twig',
                array(
                    'error' => true,
                    'loggedIn' => $loggedIn
                )
            );
        }
    }
}