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
    protected $cartId;

    public function __construct(Mysql $db, Session $session)
    {
        $this->db = $db;

        $this->session = $session;

        $this->cartId = $this->getCartId();
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
     * Provide array of cart items
     * @return array|null
     */
    public function getCart()
    {

        $query = "
            SELECT
              *
            FROM
              aca_cart_product
            WHERE
              cart_id = '$this->cartId'";

        return $this->db->fetchRowMany($query);
    }

    /**
     * Add a product to the cart
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function addProduct($productId, $quantity)
    {

        // Set query to get product info
        $query = "
            SELECT
              *
            FROM
              aca_product
            WHERE
              id = '$productId'";

        // Set variables for insert statement
        $data = $this->db->fetchRow($query);
        $name = $data['name'];
        $price = $data['price'];

        // Insert item into aca_cart_product table
        $this->db->insert('aca_cart_product', array('cart_id' => $this->getCartId(), 'product_id' => $productId, 'product_name' => $name, 'price' => $price, 'quantity' => $quantity));

    }

    /**
     * Remove a product from the cart
     * @param $productId
     * @param $cartId
     * @return bool
     */
    public function removeProduct($productId, $cartId)
    {
        $this->db->delete('aca_cart_product', array('product_id' => $productId, 'cart_id' => $cartId));
    }


    /**
     * Update a product in the cart
     * @param int $productId
     * @param int $quantity
     * @param int $cartId
     * @return bool
     */
    public function updateProduct($productId, $quantity, $cartId)
    {
        $this->db->update('aca_cart_product', array('product_id' => $productId, 'cart_id' => $cartId), array('quantity' => $quantity));
    }
}