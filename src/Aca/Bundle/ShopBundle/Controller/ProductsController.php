<?php

namespace Aca\Bundle\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// Use this for Database object
use Aca\Bundle\ShopBundle\Db\Database;

// Use this for Login objects
use Aca\Bundle\ShopBundle\Controller\LoginController;


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

        // Product page headline and opening div for page
        $output =
            '<!-- Jumbotron Header -->
            <header class="jumbotron hero-spacer-centered">
                <h1>All Products</h1>
            </header>
            <div class="row text-center">';

        $query = "SELECT * FROM aca_product";

        // Query DB to get all items from aca_product
        $data = $db->fetchRowMany($query);

        // Iterate through returned array and add HTML-formatted output to array for return
        foreach($data as $item) {
            $allItems[] =
                '<div class="col-md-3 col-sm-6 hero-feature product-custom">
                    <div class="thumbnail thumbnail-custom">
                        <div class="caption">
                            <h4>' . $item['name'] . '</h4>
                        </div>
                        <img src="' . $item['image'] . '" alt="">
                        <div class="caption caption-custom">
                            <h3>$' . $item['price'] . '</h3>
                            <p class="">
                                <a href="#" class="btn btn-primary">Buy Now!</a> <a href="/products/' . $item['product_id'] . '" class="btn btn-default">More Info</a>
                            </p>
                        </div>
                    </div>
                </div>';
        }

        // product_id
        // name
        // description
        // image
        // category
        // price
        // date_added

        // Iterate through new array of all HTML-formatted products and add to output string
        // ???WHY CAN'T I JUST DO THIS ABOVE???
        foreach($allItems as $item) {
            $output .= $item;
        }

        // Close opening div
        $output .= "</div>";

        return $this->render(
            'AcaShopBundle:Products:products.html.twig',
            array(
                'output' => $output
            )
        );
    }

    /**
     * Show individual product page
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewProductAction($id) {

        $db = new Database();

        $query = "SELECT * FROM aca_product WHERE product_id = $id";

        $data = $db->fetchRowMany($query);

        // Make sure item exists
        if(count($data) > 0) {

            // HTML-formatted product page
            $output =
                '<div class="row">
                    <div class="col-md-9">
                        <div class="thumbnail">
                            <img class="img-responsive" src="' . $data[0]['image'] . '" alt="" />
                            <div class="caption-full">
                                <h4 class="pull-right">' . $data[0]['price'] . '</h4>
                                <h4><a href="#">' . $data[0]['name'] . '</a></h4>
                                <p>' . $data[0]['description'] . '</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';

        } else {

            // HTML-formatted error
            $output = '<!-- Jumbotron Header -->
            <header class="jumbotron hero-spacer-centered">
                <h1>Oh no! That product doesn\'t exist!</h1>
            </header>';
        }

        return $this->render(
            'AcaShopBundle:Products:products.html.twig',
            array(
                'output' => $output
            )
        );

    }
}