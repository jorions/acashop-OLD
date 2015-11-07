<?php

namespace Aca\Bundle\ShopBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CartController extends Controller
{
    public function showCartAction()
    {
        return $this->render(
            'AcaShopBundle:Cart:show.cart.html.twig'
        );
    }

    public function addCartAction(Request $req)
    {
        $cart = $this->get('cart');

        $productId = $req->get('product_id');

        $quantity = $req->get('quantity');

        $cart->addProduct($productId, $quantity);

        return $this->redirect('/');
    }

}