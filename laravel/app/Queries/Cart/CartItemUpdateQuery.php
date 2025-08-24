<?php

namespace App\Queries\Cart;

use App\Models\Cart\CartItem;

/**
 * Cart Item Update Query Handler
 *
 * Handles updating the quantity of existing cart items.
 * Provides atomic quantity update operations for cart items.
 *
 * @package App\Queries\Cart
 */
class CartItemUpdateQuery
{
    /**
     * Update existing cart item quantity.
     *
     * Updates the quantity of an existing cart item to the specified amount.
     *
     * @param string $cartUuid The cart UUID
     * @param string $productUuid The product UUID
     * @param int $quantity The new quantity
     * @return void
     */
    public function execute(string $cartUuid, string $productUuid, int $quantity): void
    {
        CartItem::where('cart_uuid', $cartUuid)
            ->where('product_uuid', $productUuid)
            ->whereNull('deleted_at')
            ->update([
                'quantity' => $quantity,
                'updated_at' => now()
            ]);
    }
}