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

        // If they are not logged in render page with login prompt
        if (!$loggedIn) {

            return $this->render(
                'AcaShopBundle:Profile:profile.html.twig',
                array(
                    'loggedIn' => $loggedIn
                )
            );
        }

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

            // Check address from from against address in database, update accordingly in db, and return message
            $shippingMsg = $profile->checkShippingAddress($req->get('shippingStreet'), $req->get('shippingCity'), $req->get('shippingState'), $req->get('shippingZip'));

        }

        // Now check for if updateBilling button was pressed
        if ($req->getMethod() == 'POST' && !empty($req->get('updateBilling'))) {

            // Check address from from against address in database, update accordingly in db, and return message
            $billingMsg = $profile->checkBillingAddress($req->get('billingStreet'), $req->get('billingCity'), $req->get('billingState'), $req->get('billingZip'));

        }


        // Now check for if updateName button was pressed
        if ($req->getMethod() == 'POST' && !empty($req->get('updateName'))) {

            // Check name from form against name in database, updated accordingly in db, and return message
            $nameMsg = $profile->checkName($req->get('name'));

        }


        // Now check for if updateUsername button was pressed
        if ($req->getMethod() == 'POST' && !empty($req->get('updateUsername'))) {

            // Check username from form against name in database, update accordingly in db, and return message
            $usernameMsg = $profile->checkUsername($req->get('username'));

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

        }


        // Set all render variables
        $name = $profile->getName();
        $username = $profile->getUsername();

        // If there is a shipping address, set variables accordingly
        $data = $profile->getShippingAddress();
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

        // If there is a billing address, set variables accordingly
        $data = $profile->getBillingAddress();
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

        $email = $profile->getEmail();



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

    }
}