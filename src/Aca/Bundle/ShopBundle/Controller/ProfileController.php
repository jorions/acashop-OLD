<?php

namespace Aca\Bundle\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// Use this for logoutAction
use Symfony\Component\HttpFoundation\RedirectResponse;

// Use this for loginFormAction
use Symfony\Component\HttpFoundation\Request;

class ProfileController extends Controller {

    public function profilePageAction(Request $req) {

        // Determine whether user is logged in
        $loggedIn = $this->get('login')->loggedInCheck();

        // If they are logged in render page
        if($loggedIn) {

            $profile = $this->get('profile');

            // If profile page is visited via GET it is a link click, not a form submission
            if($req->getMethod() == 'GET') {

            // If profile page is visited via not GET (POST) it is a form submission
            } else {

            }


            $id = $profile->setBillingAddress("456 Kane Street", "Austin", "TX", "73442");

            $address = $profile->getBillingAddress();

            return $this->render(
                'AcaShopBundle:Profile:profile.html.twig',
                array(
                    'id' => $id,
                    'loggedIn' => $loggedIn,
                    'address' => $address
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