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
     * @throws \Exception
     */
    public function getCartId()
    {
        // Get current user ID
        $userId = $this->session->get('user_id');

        // Make sure user ID exist
        if (empty($userId)) {
            throw new \Exception('You must be logged in');
        }

        // Set cart ID query
        $cartId = "
            SELECT
              id
            FROM
              aca_cart
            WHERE
              user_id = :userId";

        // Query database
        $data = $this->db->fetchRow($cartId, array('userId' => $userId));

        // If the returned query is empty then there is no cart
        if(empty($data)) {

            // Insert method returns the last-inserted ID
            // So this simultaneously inserts a new cart and sets $cartId = to the last-inserted ID
            $cartId = $this->db->insert('aca_cart', array('user_id' => $userId));

            // Set session variable to $cartId
            $this->session->set('cart_id', $cartId);

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
              p.id as p_id,
              p.name as p_name,
              p.image as p_image,
              cp.price as cp_price,
              cp.product_id as cp_product_id,
              cp.id as cp_id,
              cp.cart_id as cp_cart_id,
              cp.quantity as cp_quantity
            FROM
              aca_cart_product as cp
              LEFT JOIN aca_product as p on (p.id = cp.product_id)
              LEFT JOIN aca_cart as ct on (ct.id = cp.cart_id)
            WHERE
              ct.id = :cartId";

        return $this->db->fetchRowMany($query, array('cartId' => $this->cartId));
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
              id = :productId";

        // Set variables for insert statement
        $data = $this->db->fetchRow($query, array('productId' => $productId));
        $price = $data['price'];

        // Insert item into aca_cart_product table
        try {
            $this->db->insert('aca_cart_product', array('cart_id' => $this->getCartId(), 'product_id' => $productId, 'price' => $price, 'quantity' => $quantity));
        } catch (\Exception $e) {
            return $e;
        }
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


    /**
     * Delete a shopping cart
     * @throws \Exception
     */
    public function removeCart()
    {
        // Delete order from cart
        $this->db->delete('aca_cart_product', array('cart_id' => $this->cartId));
        $this->db->delete('aca_cart', array('id' => $this->cartId));
    }
}