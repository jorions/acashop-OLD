<?php

namespace Aca\Bundle\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;

// Use this for loginFormAction
use Symfony\Component\HttpFoundation\Request;

class OrderController extends Controller {

    public function reviewOrderAction(Request $req)
    {

        $submitCheck = $req->get('submit_check');

        // Make sure checkout button was pressed
        if(empty($submitCheck) || $submitCheck != 1) {

            // If button was not pressed redirect to the cart, which contains check for logged in as well
            // This also makes sure if someone is logged out that they are redirected to a page that accounts for it
            return new RedirectResponse('/cart');
        }

        // Set up services and render variable
        $cart = $this->get('cart');
        $profile = $this->get('profile');
        $order = $this->get('order');
        $loggedIn = $this->get('login')->loggedInCheck();


        // Make sure all data is set properly


        // Show final order
        return $this->render(
            'AcaShopBundle:Order:review.order.html.twig',
            array(
                'loggedIn' => $loggedIn,
                'cart' => $cart->getCart()
            )
        );

    }

    public function placeOrderAction()
    {
        $order = $this->get('order');

        $order->placeOrder();

        return new RedirectResponse('thank_you');
    }

    public function thankYouAction()
    {

        // Get order details to show on page as receipt


        // Send email??



        // Show thank you
        return $this->render(
            'AcaShopBundle:Order:thank.you.html.twig',
            array(
                'loggedIn' => $this->get('login')->loggedInCheck(),
            )
        );
    }

}