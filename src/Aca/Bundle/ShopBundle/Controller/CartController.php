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

        $cart = $this->get('cart');

        $data = $cart->getCart();

        return $this->render(
            'AcaShopBundle:Cart:show.cart.html.twig',
            array(
                'cart' => $data
            )
        );
    }

    /**
     * Add a product to cart
     * @param Request $req
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addCartAction(Request $req)
    {
        $cart = $this->get('cart');

        $productId = $req->get('product_id');
        $quantity = $req->get('quantity');

        $cart->addProduct($productId, $quantity);

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

        $productId = $req->get('product_id');
        $quantity = $req->get('quantity');
        $cartId = $cart->getCartId();

        $cart->updateProduct($productId, $quantity, $cartId);

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

        $productId = $req->get('product_id');
        $cartId = $cart->getCartId();

        $cart->removeProduct($productId, $cartId);

        return $this->redirect('/cart');
    }
}

