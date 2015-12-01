<?php

namespace Aca\Bundle\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;

// Use this for loginFormAction
use Symfony\Component\HttpFoundation\Request;

// Custom-added namespace
use PHPMailer;

class OrderController extends Controller {

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

        // Set up render variables
        $loggedIn = $this->get('login')->loggedInCheck();
        $shippingMsg = null;
        $billingMsg = null;
        $shippingGood = FALSE;
        $billingGood = FALSE;
        $shippingStreet = null;
        $shippingCity = null;
        $shippingState = null;
        $shippingZip = null;
        $billingStreet = null;
        $billingCity = null;
        $billingState = null;
        $billingZip = null;

        // Get shipping address
        $address = $profile->getShippingAddress();

        // If shipping address is not empty then prepare render variables accordingly
        if(!empty($address)) {
            $shippingStreet = $address['street'];
            $shippingCity = $address['city'];
            $shippingState = $address['state'];
            $shippingZip = $address['zip'];
        }

        // Now that shipping address may have been set, check if checkout button was pressed. If it was, populate render variables
        // with request values. This way form fills are consistent between page submissions
        if(!empty($checkoutCheck)) {
            $shippingStreet = $req->get('shippingStreet');
            $shippingCity = $req->get('shippingCity');
            $shippingState = $req->get('shippingState');
            $shippingZip = $req->get('shippingZip');
        }

        // Perform validation checks on shipping address
        // Make sure all fields have content
        if (!empty($shippingStreet) && !empty($shippingCity) && !empty($shippingState) && !empty($shippingZip) && !empty($checkoutCheck)) {

            // If zip is invalid set $shippingMsg to error
            if (!preg_match("#^[0-9]+$#", $req->get('shippingZip'))) {

                $shippingMsg = 'Please enter only numbers for zip';

            // If zip is valid set check variable accordingly
            } else {
                $shippingGood = TRUE;
            }

        // If all fields don't have content but review button was pressed set $shippingMsg to error
        } else if(!empty($checkoutCheck)) {
            $shippingMsg = 'Please enter a street, city, state, and zip';
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

        // Now that billing address may have been set, check if checkout button was pressed. If it was, populate render variables
        // with request values. This way form fills are consistent between page submissions
        if(!empty($checkoutCheck)) {
            $billingStreet = $req->get('billingStreet');
            $billingCity = $req->get('billingCity');
            $billingState = $req->get('billingState');
            $billingZip = $req->get('billingZip');
        }

        // Perform validation checks on billing address
        // Make sure all fields have content
        if (!empty($billingStreet) && !empty($billingCity) && !empty($billingState) && !empty($billingZip) && !empty($checkoutCheck)) {

            // If zip is invalid set $billingMsg to error
            if (!preg_match("#^[0-9]+$#", $req->get('billingZip'))) {

                $billingMsg = 'Please enter only numbers for zip';

            // If zip is valid set check variable accordingly
            } else {
                $billingGood = TRUE;
            }

            // If all fields don't have content but review button was pressed set $billingMsg to error
        } else if(!empty($req->get('checkout_check'))) {
            $billingMsg = 'Please enter a street, city, state, and zip';
        }


        // If check variables are true and submit button was pressed direct to thank you page
        if($shippingGood && $billingGood && !empty($checkoutCheck)) {

            return new RedirectResponse('place_order');

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
                'billingZip' => $billingZip
            )
        );

    }

    public function placeOrderAction()
    {

        // If not logged in redirect to cart, which takes care of login check
        if(!$this->get('login')->loggedInCheck()) {

            return new RedirectResponse('/cart');
        }


        $order = $this->get('order');

        $order->placeOrder();

        // Send email



        return new RedirectResponse('thank_you');
    }

    public function thankYouAction()
    {

        // If not logged in redirect to cart, which takes care of login check
        if(!$this->get('login')->loggedInCheck()) {

            return new RedirectResponse('/cart');
        }

        // Get order details to show on page as receipt
        $order = $this->get('order')->getSessionOrder();

        // Send email??


        // Show thank you
        return $this->render(
            'AcaShopBundle:Order:thank.you.html.twig',
            array(
                'loggedIn' => $this->get('login')->loggedInCheck(),
                'order' => $order
            )
        );
    }


    public function testAction() {

        $mail = new PhpMailer;

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'acashopemail@gmail.com';
        $mail->Password = 'acashopemailpassword';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('acashopemail@gmail.com', 'Acashop');
        $mail->addAddress('jared.orion.selcoe@gmail.com', 'Jared Selcoe');
        $mail->isHTML(true);

        $mail->Subject = 'Test Subject';
        $mail->Body = '<h2>This is a test</h2>And<br />...This is another test';
        $mail->AltBody = '<h2>This is a test</h2>And<br />...This is another test';


        if(!$mail->send()) {
            $msg = "Message could not be sent.<br />Mailer Error: $mail->ErrorInfo";
        } else {
            $msg = "Message has been sent";
        }

        return $this->render(
            'AcaShopBundle:Test:test.html.twig',
            array(
                'loggedIn' => true,
                'msg' => $msg
            )
        );
    }
}