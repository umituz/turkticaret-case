<?php

namespace App\Queries\Cart;

use App\Models\Cart\CartItem;

/**
 * Cart Item Remove Query Handler
 *
 * Handles the removal (soft deletion) of individual cart items.
 * Provides atomic operations for removing specific items from cart.
 *
 * @package App\Queries\Cart
 */
class CartItemRemoveQuery
{
    /**
     * Remove cart item.
     *
     * Soft deletes a cart item by setting deleted_at timestamp.
     *
     * @param string $cartUuid The cart UUID
     * @param string $productUuid The product UUID
     * @return void
     */
    public function execute(string $cartUuid, string $productUuid): void
    {
        CartItem::where('cart_uuid', $cartUuid)
            ->where('product_uuid', $productUuid)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);
    }
}