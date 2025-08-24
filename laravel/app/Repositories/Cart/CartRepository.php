<?php

namespace App\Repositories\Cart;

use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Queries\Cart\CartClearQuery;
use App\Queries\Cart\CartItemRemoveQuery;
use App\Queries\Cart\CartItemUpdateQuery;
use App\Queries\Cart\CartItemUpsertQuery;
use App\Repositories\Base\BaseRepository;

/**
 * Cart Repository for cart-specific database operations.
 * 
 * Handles shopping cart data operations including user-specific cart queries
 * and cart management functionality. Extends BaseRepository to provide
 * cart-specific functionality.
 *
 * @package App\Repositories\Cart
 */
class CartRepository extends BaseRepository implements CartRepositoryInterface
{
    /**
     * Create a new CartRepository instance.
     *
     * @param Cart $model The Cart model instance for this repository
     */
    public function __construct(Cart $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a cart by user UUID.
     *
     * @param string $userUuid The UUID of the user to find cart for
     * @return Cart|null The found cart or null if not found
     */
    public function findByUserUuid(string $userUuid): ?Cart
    {
        return $this->model->where('user_uuid', $userUuid)->first();
    }

    /**
     * Add or update cart item with atomic operation.
     *
     * @param string $cartUuid The cart UUID
     * @param string $productUuid The product UUID
     * @param int $quantity The quantity to add
     * @param int $unitPrice The unit price in cents
     * @return void
     */
    public function upsertCartItem(string $cartUuid, string $productUuid, int $quantity, int $unitPrice): void
    {
        app(CartItemUpsertQuery::class)->execute($cartUuid, $productUuid, $quantity, $unitPrice);
    }

    /**
     * Update cart item quantity.
     *
     * @param string $cartUuid The cart UUID
     * @param string $productUuid The product UUID
     * @param int $quantity The new quantity
     * @return void
     */
    public function updateCartItemQuantity(string $cartUuid, string $productUuid, int $quantity): void
    {
        app(CartItemUpdateQuery::class)->execute($cartUuid, $productUuid, $quantity);
    }

    /**
     * Remove item from cart.
     *
     * @param string $cartUuid The cart UUID
     * @param string $productUuid The product UUID
     * @return void
     */
    public function removeCartItem(string $cartUuid, string $productUuid): void
    {
        app(CartItemRemoveQuery::class)->execute($cartUuid, $productUuid);
    }

    /**
     * Clear all cart items.
     *
     * @param string $cartUuid The cart UUID
     * @return void
     */
    public function clearCartItems(string $cartUuid): void
    {
        app(CartClearQuery::class)->execute($cartUuid);
    }

    /**
     * Find cart item by cart and product UUID.
     *
     * @param string $cartUuid The cart UUID
     * @param string $productUuid The product UUID
     * @return CartItem|null The cart item or null if not found
     */
    public function findCartItem(string $cartUuid, string $productUuid): ?CartItem
    {
        return CartItem::where('cart_uuid', $cartUuid)
            ->where('product_uuid', $productUuid)
            ->whereNull('deleted_at')
            ->first();
    }
}