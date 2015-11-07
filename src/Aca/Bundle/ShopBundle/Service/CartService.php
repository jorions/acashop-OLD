<?php

namespace Aca\Bundle\ShopBundle\Service;

use Simplon\Mysql\Mysql;

use Symfony\Component\HttpFoundation\Session\Session;

class CartService
{
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
     * Unique cart id
     * @var int
     */
    protected $cart_id;

    public function __construct(Mysql $db, Session $session)
    {
        $this->db = $db;

        $this->session = $session;
    }

    /**
     * Add a product to the cart
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function addProduct($productId, $quantity)
    {

        // Insert item into aca_cart item table
        $this->db->insert('aca_cart_item', array('cart_id' => $this->getCartId(), 'product_id' => $productId, 'quantity' => $quantity));

    }

    /**
     * Create a cart record, and return the id if it doesn't exist
     * If it does exist, just return the id
     * @return int id
     */
    public function getCartId()
    {
        // If the cart_id doesn't exist, create one
        if($this->session->get('user_id') == null) {

            // Get current user ID
            $userId = $this->session->get('user_id');

            // Insert into table
            $response = $this->db->insert('aca_cart', array('user_id' => $userId));

            // Get new cart_id
            $query = "
                SELECT
                  id
                FROM
                  aca_cart
                WHERE
                  user_id = '$user_id'";

            //
            $data = $this->db->fetchRow($query);
            $cart_id = $data['id'];

            // Set session variable to cart id
            $this->session->set('cart_id', $cart_id);

            return $cart_id;
        }

        return $this->session->get('cart_id');
    }
}