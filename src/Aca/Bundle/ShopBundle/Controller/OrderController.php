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
        $shippingGood = FALSE;
        $billingGood = FALSE;
        $emailGood = FALSE;
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


        // Get email
        $email = $profile->getEmail();

        // Now that email may have been set, check if checkout button was pressed. If it was, populate render variables
        // with request values. This way form fills are consistent between page submissions
        if(!empty($checkoutCheck)) {

            $email = $req->get('email');
        }

        // Perform validation checks on email
        // Make sure email has content
        if (!empty($email)) {

            // If email is invalid set error accordingly
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

                $emailMsg = 'Invalid email entered';

            // If email is valid set check variable accordingly
            } else {

                $emailGood = TRUE;
            }

        // If no email entered but review button was pressed set $emailMsg error
        } else if(!empty($req->get('checkout_check'))) {

            $emailMsg = 'No email entered';
        }


        // If check variables are true and submit button was pressed submit order and redirect to thank you page
        if($shippingGood && $billingGood && $emailGood && !empty($checkoutCheck)) {


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


        // Gather together html to put in body of email
        $body = '
            <html>
                <body>
                    <h1>Your Order Details</h1>
                    <h3>Shipped To</h3>'
                    . $orderDetails['shipping_street'] . '<br />'
                    . $orderDetails['shipping_city'] . ', ' . $orderDetails['shipping_state'] . ' ' . $orderDetails['shipping_zip'] . '<br /><br />
                    <h3>Billed To</h3>'
                    . $orderDetails['billing_street'] . '<br />'
                    . $orderDetails['billing_city'] . ', ' . $orderDetails['billing_state'] . ' ' . $orderDetails['billing_zip'] . '<br /><br />
                    <hr />
                    <br />
                    <h1>Your Purchased Products</h1>
                    <table>';

        $totalPrice = 0;

        foreach($orderProducts as $item) {
            $itemTotal = $item['price'] * $item['quantity'];
            $body .= '
                    <tr>
                        <td>
                            <div style="width:120px;height:120px;display:table-cell;text-align:center;vertical-align:middle;">
                                <img style="max-height:100px;max-width:120px;" src="' . $item['image'] . '" />
                            </div>
                        </td>
                        <td style="width:400px;font-weight:bold;font-size:16px;">' . $item['name'] . '</td>
                        <td style="height:60px;width:10px;"></td>
                        <td>$' . $item['price'] . '</td>
                        <td style="height:60px;width:10px;"></td>
                        <td><i> x' . $item['quantity'] . '</i></td>
                        <td style="height:60px;width:10px;"></td>
                        <td style="height:60px;width:10px;"></td>
                        <td><b>' . $itemTotal . '</b></td>
                    </tr>';

            $totalPrice += $itemTotal;
        }

        $body .= '
                <tr>
                    <td></td>
                    <td style="width:400px;font-weight:bold;font-size:24px;">Total</td>
                    <td style="height:60px;width:10px;"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="font-weight:bold;font-size:24px;">$' . $totalPrice . '</td>
                </tr>
            </table></body></html>';


        // Send email
        $message = \Swift_Message::newInstance()
            ->setSubject('Receipt For Your ACAShop Order')
            ->setFrom('acashopemail@gmail.com')
            ->setTo($orderDetails['email'])
            ->setBody(
                $body,
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