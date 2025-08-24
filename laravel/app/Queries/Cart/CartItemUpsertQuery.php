<?php

namespace App\Queries\Cart;

use App\Models\Cart\CartItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Cart Item Upsert Query Handler
 *
 * Handles the complex cart item upsert operation with proper race condition handling.
 * Uses database-level atomic operations to ensure data consistency in concurrent scenarios.
 *
 * @package App\Queries\Cart
 */
class CartItemUpsertQuery
{
    /**
     * Perform atomic cart item upsert operation.
     *
     * This method handles adding items to cart or updating existing quantities
     * using PostgreSQL's ON CONFLICT clause for atomic operations.
     * Prevents race conditions in high-concurrency scenarios.
     *
     * @param string $cartUuid The cart UUID
     * @param string $productUuid The product UUID
     * @param int $quantity The quantity to add/set
     * @param int $unitPrice The unit price in cents
     * @return void
     */
    public function execute(string $cartUuid, string $productUuid, int $quantity, int $unitPrice): void
    {
        $cartItemUuid = Str::uuid();
        $now = now();

        DB::statement("
            INSERT INTO cart_items (uuid, cart_uuid, product_uuid, quantity, unit_price, deleted_at, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NULL, ?, ?)
            ON CONFLICT (cart_uuid, product_uuid)
            DO UPDATE SET 
                quantity = cart_items.quantity + EXCLUDED.quantity,
                updated_at = EXCLUDED.updated_at,
                deleted_at = NULL
        ", [
            $cartItemUuid,
            $cartUuid,
            $productUuid,
            $quantity,
            $unitPrice,
            $now,
            $now
        ]);
    }

}