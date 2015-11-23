<?php

namespace Aca\Bundle\ShopBundle\Service;

use Simplon\Mysql\Mysql;

use Aca\Bundle\Shopbundle\Controller;

use Symfony\Component\HttpFoundation\Session\Session;

class OrderService {

    /**
     * Database class
     * @var Mysql
     */
    protected $db;

    /**
     * User session
     * @var Session
     */
    protected $session;


    /**
     * CartService class
     * @var CartService
     */
    protected $cart;


    public function __construct(Mysql $db, Session $session, CartService $cart)
    {
        $this->db = $db;

        $this->session = $session;

        $this->cart = $cart;

    }


    /**
     * Place order and clear cart
     * @throws \Simplon\Mysql\MysqlException
     */
    public function placeOrder()
    {
        $query = '
            SELECT
              *
            FROM
              aca_cart_product
            WHERE
              cart_id= :cartId';

        // Get all user orders that are in the cart
        $data = $this->cart->getCart();

        // If there are products in the order
        if(!empty($data)) {

            // Add order to order table
            $orderId = $this->db->insert('aca_order',
                array(
                    'user_id' => $this->session->get('user_id')
                )
            );

            // Iterate through products and add to order product table
            foreach($data as $product) {

                $this->db->insert('aca_order_product',
                    array(
                        'order_id' => $orderId,
                        'product_id' => $product['cp_product_id'],
                        'quantity' => $product['cp_quantity'],
                        'price' => $product['cp_price']
                    )
                );
            }

            // Delete order from cart
            $this->cart->removeCart();

            // Add orderId to session
            $this->session->set('order_id', $orderId);

        }
    }

    public function getSessionOrder()
    {
        $query = '
            SELECT
              ord.product_id,
              ord.quantity,
              ord.price,
              prod.name,
              prod.image
            FROM
              aca_order_product ord
                LEFT JOIN
              aca_product prod
            ON
              ord.product_id = prod.id
            WHERE
              order_id= :orderId';

        $data = $this->db->fetchRowMany($query, array('orderId' => $this->session->get('order_id')));

        return $data;
    }
}