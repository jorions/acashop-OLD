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
            $userInfo = $profile->getUserInfo();
            $name = $userInfo['name'];
            $username = $userInfo['username'];

            // Now check for if updateName button was pressed
            if ($req->getMethod() == 'POST' && !empty($req->get('updateName'))) {

                $name = $req->get('name');

                // Make sure info was entered
                if (!empty($name)) {

                    // Prevent MySQL injection - make sure all characters are legal
                    if (preg_match("#^[a-zA-Z0-9]+$#", $name)) {

                        // Make sure new name is different than current name
                        if ($name != $userInfo['name']) {

                            // Set new name
                            $profile->updateName($name);
                            $nameMsg = 'Name updated!';

                            // If new name is same as current name tell user
                        } else {

                            $nameMsg = 'That\'s your current name!';
                        }

                        // If any illegal characters tell user
                    } else {
                        $nameMsg = 'Make sure your name contains only letters and numbers';
                    }

                    // If name empty set error message
                } else {

                    $nameMsg = 'No new name entered';
                }
            }


            // Now check for if updateUsername button was pressed
            if ($req->getMethod() == 'POST' && !empty($req->get('updateUsername'))) {

                $username = $req->get('username');

                // Make sure info was entered
                if (!empty($username)) {

                    // Prevent MySQL injection - make sure all characters are legal
                    if (preg_match("#^[a-zA-Z0-9]+$#", $username)) {

                        // Make sure username isn't already used
                        $login = $this->get('login');
                        if ($login->checkRegistration($username)) {

                            // Set new username
                            $profile->updateUsername($username);
                            $usernameMsg = 'Username updated!';

                            // If username is same as original tell user (instead of giving error message)
                        } else if ($username == $userInfo['username']) {

                            $usernameMsg = 'That\'s your current username!';

                            // If username already exists tell user
                        } else {

                            $usernameMsg = 'That username is already taken - sorry!';
                        }

                        // If any illegal characters tell user
                    } else {
                        $usernameMsg = 'Make sure your username contains only letters and numbers';
                    }

                    // If username empty set error message
                } else {

                    $usernameMsg = 'No new username entered';
                }
            }


            // Now check for if updatePassword button was pressed
            if ($req->getMethod() == 'POST' && !empty($req->get('updatePassword'))) {

                $password = $req->get('password');
                $passwordCheck = $req->get('passwordCheck');

                // Make sure info was entered
                if (!empty($password) && !empty($passwordCheck)) {

                    // Prevent MySQL injection - make sure all characters are legal
                    if (preg_match("#^[a-zA-Z0-9]+$#", $password) && preg_match("#^[a-zA-Z0-9]+$#", $req->get('updatePassword'))) {

                        // Make sure passwords match
                        if ($password == $passwordCheck) {

                            // Set new password
                            $profile->updatePassword($password);
                            $passwordMsg = 'Password updated!';

                            // If passwords don't match tell user
                        } else {
                            $passwordMsg = 'Make sure your new password matches in both boxes';
                        }

                        // If any illegal characters tell user
                    } else {
                        $passwordMsg = 'Make sure your password contains only letters and numbers';
                    }

                // If one of the password fields was empty set error message
                } else {

                    $passwordMsg = 'Enter your new password in both boxes';
                }
            }

            // Now check if the updateEmail button was pressed
            if ($req->getMethod() == 'POST' && !empty($req->get('updateEmail'))) {

                $email = $req->get('email');

                // Make sure info was entered
                if (!empty($email)) {

                    // Make sure email is valid
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {

                        $profile->updateEmail($email);
                        $emailMsg = 'Email updated!';

                    // If email is invalid set error message
                    } else {

                        $emailMsg = 'Invalid email entered';
                    }


                // If no email entered set error message
                } else {

                    $emailMsg = 'No new email entered';
                }

            // If profile page is visited not via POST (GET) it is a normal page visit, so set render variables accordingly
            } else {

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