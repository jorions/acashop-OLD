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
     * Create a cart record, and return the id if it doesn't exist
     * If it does exist, just return the id
     * @return int id
     */
    public function getCartId()
    {
        // Get current user ID
        $userId = $this->session->get('user_id');

        // Set cart ID query
        $cartId = "
            SELECT
              id
            FROM
              aca_cart
            WHERE
              user_id = '$userId'";

        // Query database
        $data = $this->db->fetchRow($cartId);

        // If the returned query is empty then there is no cart
        if(empty($data)) {

            // Insert method returns the last-inserted ID
            // So this simultaneously inserts a new cart and sets $cartId = to the last-inserted ID
            $cartId = $this->db->insert('aca_cart', array('user_id' => $userId));

        } else {

            $cartId = $data['id'];
        }

        return $cartId;
    }


    /**
     * Add a product to the cart
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function addProduct($productId, $quantity)
    {

        // Set query to get product price
        $query = "
            SELECT
              price
            FROM
              aca_product
            WHERE
              id = '$productId'";

        // Get price
        $data = $this->db->fetchRow($query);
        $price = $data['price'];

        // Insert item into aca_cart_product table
        $this->db->insert('aca_cart_product', array('cart_id' => $this->getCartId(), 'product_id' => $productId, 'price' => $price, 'quantity' => $quantity));

    }

    /**
     * Remove a product from the cart
     * @param $productId
     * @return bool
     */
    public function removeProduct($productId)
    {
        $this->db->delete('aca_cart_product', array('product_id' => $productId));
    }


    /**
     * @param $productId
     */
    public function updateProduct($productId)
    {

    }
}