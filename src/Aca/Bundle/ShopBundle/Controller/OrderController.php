<?php

namespace Aca\Bundle\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;

// Use this for loginFormAction
use Symfony\Component\HttpFoundation\Request;

class OrderController extends Controller {

    /**
     * Provide page to review order and then submit order
     * @param Request $req
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function reviewOrderAction(Request $req)
    {

        $reviewCheck = $req->get('review_check');
        $checkoutCheck = $req->get('checkout_check');

        // Make sure either the review button from the previous page or the checkout button from the current page was pressed
        if((empty($reviewCheck) || $reviewCheck != 1) && (empty($checkoutCheck) || $checkoutCheck !=1)) {

            // If button was not pressed redirect to the cart, which contains check for logged in as well
            // This also makes sure if someone is logged out that they are redirected to a page that accounts for it
            return new RedirectResponse('/cart');
        }

        // Set up services
        $cart = $this->get('cart');
        $profile = $this->get('profile');
        $order = $this->get('order');

        // Set up render variables
        $loggedIn = $this->get('login')->loggedInCheck();
        $shippingMsg = null;
        $billingMsg = null;
        $emailMsg = null;
        $shippingStreet = null;
        $shippingCity = null;
        $shippingState = null;
        $shippingZip = null;
        $billingStreet = null;
        $billingCity = null;
        $billingState = null;
        $billingZip = null;
        $email = null;

        // Get shipping address
        $address = $profile->getShippingAddress();

        // If shipping address is not empty then prepare render variables accordingly
        if(!empty($address)) {
            $shippingStreet = $address['street'];
            $shippingCity = $address['city'];
            $shippingState = $address['state'];
            $shippingZip = $address['zip'];
        }

        // Get billing address
        $address = $profile->getBillingAddress();

        // If billing address is not empty then prepare render variables accordingly
        if(!empty($address)) {
            $billingStreet = $address['street'];
            $billingCity = $address['city'];
            $billingState = $address['state'];
            $billingZip = $address['zip'];
        }

        // Get email
        $email = $profile->getEmail();

        // Now that shipping/billing/email addresses may have been set, check if checkout button was pressed. If it was, populate render variables
        // with request values. This way form fills are consistent between page submissions even if new address is different than address on file
        if(!empty($checkoutCheck)) {
            $shippingStreet = $req->get('shippingStreet');
            $shippingCity = $req->get('shippingCity');
            $shippingState = $req->get('shippingState');
            $shippingZip = $req->get('shippingZip');
            $billingStreet = $req->get('billingStreet');
            $billingCity = $req->get('billingCity');
            $billingState = $req->get('billingState');
            $billingZip = $req->get('billingZip');
            $email = $req->get('email');

            $shippingMsg = $order->checkAddress($shippingStreet, $shippingCity, $shippingState, $shippingZip);
            $billingMsg = $order->checkAddress($billingStreet, $billingCity, $billingState, $billingZip);
            $emailMsg = $order->checkEmail($email);
        }


        // If no error messages are set and submit button was pressed submit order and redirect to thank you page
        if($shippingMsg == null && $billingMsg == null && $emailMsg == null && !empty($checkoutCheck)) {

            // Place order
            $order = $this->get('order');

            $order->placeOrder($billingStreet, $billingCity, $billingState, $billingZip, $shippingStreet, $shippingCity, $shippingState, $shippingZip, $email);

            return new RedirectResponse('thank_you');

        }

        // Show final order
        return $this->render(
            'AcaShopBundle:Order:review.order.html.twig',
            array(
                'loggedIn' => $loggedIn,
                'cart' => $cart->getCart(),
                'shippingMsg' => $shippingMsg,
                'shippingStreet' => $shippingStreet,
                'shippingCity' => $shippingCity,
                'shippingState' => $shippingState,
                'shippingZip' => $shippingZip,
                'billingMsg' => $billingMsg,
                'billingStreet' => $billingStreet,
                'billingCity' => $billingCity,
                'billingState' => $billingState,
                'billingZip' => $billingZip,
                'emailMsg' => $emailMsg,
                'email' => $email
            )
        );

    }

    /**
     * Generate email to send to user and direct user to thank you page
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function thankYouAction()
    {

        // If not logged in redirect to cart, which takes care of login check
        if(!$this->get('login')->loggedInCheck()) {

            return new RedirectResponse('/cart');
        }

        // Get order products to show on page as receipt
        $orderProducts = $this->get('order')->getSessionOrderProducts();

        // Get order details to show on page for receipt
        $orderDetails = $this->get('order')->getSessionOrderDetails();

        // Send email
        $message = \Swift_Message::newInstance()
            ->setSubject('Receipt For Your ACAShop Order')
            ->setFrom('acashopemail@gmail.com')
            ->setTo($orderDetails['email'])
            ->setBody(
                $this->renderView(
                    'AcaShopBundle:Email:receipt.html.twig',
                    array(
                        'products' => $orderProducts,
                        'details' => $orderDetails
                    )
                ),
                'text/html'
            );

        $this->get('mailer')->send($message);


        // Show thank you
        return $this->render(
            'AcaShopBundle:Order:thank.you.html.twig',
            array(
                'loggedIn' => $this->get('login')->loggedInCheck(),
                'order' => $orderProducts
            )
        );
    }
}