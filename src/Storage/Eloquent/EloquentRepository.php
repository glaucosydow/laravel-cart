<?php

namespace JulioBitencourt\Cart\Storage\Eloquent;

use JulioBitencourt\Cart\Storage\StorageInterface;
use JulioBitencourt\Cart\Storage\Eloquent\Entities\Cart;
use JulioBitencourt\Cart\Storage\Eloquent\Entities\Items;
use Cookie;

/**
 * Stores the Cart data using Laravel's Eloquent
 */
class EloquentRepository implements StorageInterface
{

    /**
     * @var $cart
     */
    protected $cart;

    /**
     * @param Cart $model
     */
    public function __construct()
    {
        $this->cart = $this->getOrCreate();
    }

    /**
     * Get all cart items stored on the model
     * @return array
     */
    public function get()
    {
        return $this->cart->items()->get()->toArray();
    }

    /**
     * Insert the data in the model.
     *
     * @return array
     */
    public function insert($data)
    {
        $data['cart_id'] = $this->cart->id;
        Items::create($data);
    }

    /**
     * Update the item on the model
     * @param  integer $itemKey
     * @param  integer $quantity
     * @return void
     */
    public function update($itemKey, $quantity)
    {
        $items = $this->cart->items()->get();
        $items[$itemKey]->update(['quantity' => $quantity]);
    }

    /**
     * Delete the item from the storage
     * @param  integer $itemKey
     * @return void
     */
    public function delete($itemKey)
    {
        $items = $this->cart->items()->get();
        $items[$itemKey]->delete();
    }

    /**
     * Delete all items
     * @return void
     */
    public function destroy()
    {
        Items::where('cart_id', $this->cart->id)->delete();
    }

    /**
     * Store the email for the Cart
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->cart->update(['email' => $email]);
    }

    /**
     * Get or create the cart
     * @return Cart
     */
    protected function getOrCreate()
    {
        $cartId = Cookie::get('laravel_cart');

        if ($cartId) return $this->getCartByCookie($cartId);

        return $this->createCart();
    }

    /**
     * Get the cart when the cookie id is set
     * @param  integer $id
     * @return Cart
     */
    protected function getCartByCookie($id)
    {
        $cart = Cart::find($id);

        if ($cart) return $cart;

        return $this->createCart();
    }

    /**
     * Create a new cart database record
     * @return Cart
     */
    protected function createCart()
    {
        $cart = new Cart;
        $cart->save();
        $cartId = $cart->id;
        Cookie::queue(Cookie::make('laravel_cart', $cartId, 21600));
        return $cart;
    }

}
