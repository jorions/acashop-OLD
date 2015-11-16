<?php

namespace Aca\Bundle\ShopBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CartController extends Controller
{
    /**
     * Show the cart
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showCartAction()
    {

        $loggedIn = $this->get('login')->loggedInCheck();

        // If logged in show cart
        if($loggedIn) {
            $cart = $this->get('cart');

            // Get array of cart contents
            $data = $cart->getCart();

            return $this->render(
                'AcaShopBundle:Cart:show.cart.html.twig',
                array(
                    'cart' => $data,
                    'loggedIn' => $loggedIn
                )
            );

        // Otherwise render page without cart
        } else {
            return $this->render(
                'AcaShopBundle:Cart:show.cart.html.twig',
                array(
                    'loggedIn' => $loggedIn
                )
            );
        }
    }

    /**
     * Add a product to cart
     * @param Request $req
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addCartAction(Request $req)
    {
        $cart = $this->get('cart');

        // Get order details from request
        $productId = $req->get('product_id');
        $quantity = $req->get('quantity');

        $cart->addProduct($productId, $quantity);

        // Redirect to /cart, which as a GET will route to showCartAction()
        return $this->redirect('/cart');
    }

    /**
     * Update a product in the cart
     * @param Request $req
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateCartAction(Request $req)
    {
        $cart = $this->get('cart');

        // Get updated order details from request
        // Get cart id
        $productId = $req->get('product_id');
        $quantity = $req->get('quantity');
        $cartId = $cart->getCartId();

        $cart->updateProduct($productId, $quantity, $cartId);

        // Redirect to /cart, which as a GET will route to showCartAction()
        return $this->redirect('/cart');
    }

    /**
     * Remove a product from the cart
     * @param Request $req
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeCartAction(Request $req)
    {
        $cart = $this->get('cart');

        // Get product to remove from request
        // Get cart id
        $productId = $req->get('product_id');
        $cartId = $cart->getCartId();

        $cart->removeProduct($productId, $cartId);

        // Redirect to /cart, which as a GET will route to showCartAction()
        return $this->redirect('/cart');
    }
}

