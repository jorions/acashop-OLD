<?php

namespace Aca\Bundle\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// Use this for Database object
use Aca\Bundle\ShopBundle\Db\Database;

// Use this for Login objects
use Aca\Bundle\ShopBundle\Controller\LoginController;


class ProductsController extends Controller {

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

        $output =
            '<!-- Jumbotron Header -->
            <header class="jumbotron hero-spacer-centered">
                <h1>All Products</h1>
            </header>
            <div class="row text-center">';

        $query = "SELECT * FROM aca_product";

        $data = $db->fetchRowMany($query);

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
                                <a href="#" class="btn btn-primary">Buy Now!</a> <a href="#" class="btn btn-default">More Info</a>
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

        foreach($allItems as $item) {
            $output .= $item;
        }

        $output .= "</div>";

        return $this->render(
            'AcaShopBundle:Products:products.html.twig',
            array(
                'output' => $output
            )
        );


    }
}