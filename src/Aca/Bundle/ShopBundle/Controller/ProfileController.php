<?php

namespace Aca\Bundle\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// Use this for loginFormAction
use Symfony\Component\HttpFoundation\Request;

// WHY DO THESE SHOW AS UNUSED
use Aca\Bundle\ShopBundle\Service\ProfileService;

use Aca\Bundle\ShopBundle\Service\LoginService;

class ProfileController extends Controller
{

    /**
     * Provide all logic for rendering and interacting with profile page
     * @param Request $req
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function profilePageAction(Request $req)
    {

        // Determine whether user is logged in
        $loggedIn = $this->get('login')->loggedInCheck();

        // If they are logged in render page
        if ($loggedIn) {

            // Set initial error messages to null
            $shippingMsg = null;
            $billingMsg = null;
            $nameMsg = null;
            $usernameMsg = null;
            $passwordMsg = null;
            $emailMsg = null;

            $profile = $this->get('profile');

            // If profile page is visited via POST it is a form submission
            // Check for if updateShipping button was pressed
            if ($req->getMethod() == 'POST' && !empty($req->get('updateShipping'))) {

                // Set render variables
                $shippingStreet = $req->get('shippingStreet');
                $shippingCity = $req->get('shippingCity');
                $shippingState = $req->get('shippingState');
                $shippingZip = $req->get('shippingZip');

                // Make sure all fields have content
                if (!empty($shippingStreet) && !empty($shippingCity) && !empty($shippingState) && !empty($shippingZip)) {

                    // Make sure zip is valid
                    if (preg_match("#^[0-9]+$#", $req->get('shippingZip'))) {

                        // With all checks set, update shipping
                        $profile->setShippingAddress($shippingStreet, $shippingCity, $shippingState, $shippingZip);
                        $shippingMsg = 'Shipping address updated!';

                        // If zip is invalid set $msg to error
                    } else {
                        $shippingMsg = 'Please enter only numbers for zip';
                    }

                    // If all fields don't have content set $msg to error
                } else {
                    $shippingMsg = 'Please enter a street, city, state, and zip';
                }

                // If profile page is visited not via POST (GET) it is a normal page visit, so set render variables accordingly
            } else {
                $data = $profile->getShippingAddress();

                // If there is a shipping address, set variables accordingly
                if (!empty($data)) {
                    $shippingStreet = $data['street'];
                    $shippingCity = $data['city'];
                    $shippingState = $data['state'];
                    $shippingZip = $data['zip'];

                    // If there is no shipping address, set variables to empty
                } else {
                    $shippingStreet = null;
                    $shippingCity = null;
                    $shippingState = null;
                    $shippingZip = null;
                }
            }


            // Now check for if updateBilling button was pressed
            if ($req->getMethod() == 'POST' && !empty($req->get('updateBilling'))) {

                // Set render variables
                $billingStreet = $req->get('billingStreet');
                $billingCity = $req->get('billingCity');
                $billingState = $req->get('billingState');
                $billingZip = $req->get('billingZip');

                // Make sure all fields have content
                if (!empty($billingStreet) && !empty($billingCity) && !empty($billingState) && !empty($billingZip)) {

                    // Make sure zip is valid
                    if (preg_match("#^[0-9]+$#", $req->get('billingZip'))) {

                        // With all checks set, update billing
                        $profile->setBillingAddress($billingStreet, $billingCity, $billingState, $billingZip);
                        $billingMsg = 'Billing address updated!';

                        // If zip is invalid set $msg to error
                    } else {
                        $billingMsg = 'Please enter only numbers for zip';
                    }

                    // If all fields don't have content set $msg to error
                } else {
                    $billingMsg = 'Please enter a street, city, state, and zip';
                }

            // If profile page is visited not via POST (GET) it is a normal page visit, so set render variables accordingly
            } else {
                $data = $profile->getBillingAddress();

                // If there is a billing address, set variables accordingly
                if (!empty($data)) {
                    $billingStreet = $data['street'];
                    $billingCity = $data['city'];
                    $billingState = $data['state'];
                    $billingZip = $data['zip'];

                // If there is no billing address, set variables to empty
                } else {
                    $billingStreet = null;
                    $billingCity = null;
                    $billingState = null;
                    $billingZip = null;
                }
            }


            // Get all username info
            $name = $profile->getName();
            $username = $profile->getUsername();
            $email = $profile->getEmail();

            // Now check for if updateName button was pressed
            if ($req->getMethod() == 'POST' && !empty($req->get('updateName'))) {

                // Check name from form against name in database, updated accordingly in db, and return message
                $nameMsg = $profile->checkName($req->get('name'));
                $name = $profile->getName();

            }


            // Now check for if updateUsername button was pressed
            if ($req->getMethod() == 'POST' && !empty($req->get('updateUsername'))) {

                // Check username from form against name in database, update accordingly in db, and return message
                $usernameMsg = $profile->checkUsername($req->get('username'));
                $username = $profile->getUsername();

            }


            // Now check for if updatePassword button was pressed
            if ($req->getMethod() == 'POST' && !empty($req->get('updatePassword'))) {

                // Check both password entries from form to make sure they matched, update accordingly in db, and return message
                $passwordMsg = $profile->checkPassword($req->get('password'), $req->get('passwordCheck'));
            }


            // Now check if the updateEmail button was pressed
            if ($req->getMethod() == 'POST' && !empty($req->get('updateEmail'))) {

                // Check email from form against email in database, updated accordingly in db, and return message
                $emailMsg = $profile->checkEmail($req->get('email'));
                $email = $profile->getEmail();

            }


            // Render the final page with all variables
            return $this->render(
                'AcaShopBundle:Profile:profile.html.twig',
                array(
                    'loggedIn' => $loggedIn,
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
                    'nameMsg' => $nameMsg,
                    'name' => $name,
                    'usernameMsg' => $usernameMsg,
                    'username' => $username,
                    'passwordMsg' => $passwordMsg,
                    'email' => $email,
                    'emailMsg' => $emailMsg
                )
            );

        // If they are not logged in render page with login prompt
        } else {

            return $this->render(
                'AcaShopBundle:Profile:profile.html.twig',
                array(
                    'loggedIn' => $loggedIn
                )
            );
        }
    }
}