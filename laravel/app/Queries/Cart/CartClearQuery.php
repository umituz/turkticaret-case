<?php

namespace App\Queries\Cart;

use App\Models\Cart\CartItem;

/**
 * Cart Clear Query Handler
 *
 * Handles clearing all items from a cart through soft deletion.
 * Provides atomic operations for emptying entire shopping carts.
 *
 * @package App\Queries\Cart
 */
class CartClearQuery
{
    /**
     * Clear all cart items.
     *
     * Soft deletes all items in the specified cart by setting deleted_at timestamp.
     *
     * @param string $cartUuid The cart UUID
     * @return void
     */
    public function execute(string $cartUuid): void
    {
        CartItem::where('cart_uuid', $cartUuid)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);
    }
}