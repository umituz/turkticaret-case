<?php

namespace App\Repositories\Cart;

use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Repositories\Base\BaseRepositoryInterface;

/**
 * Cart Repository Interface
 * 
 * Defines the contract for cart data access operations.
 * Extends the base repository interface with cart-specific methods
 * for managing user shopping carts and cart items.
 * 
 * @package App\Repositories\Cart
 */
interface CartRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find cart by user UUID
     * 
     * Retrieves the cart belonging to a specific user by their UUID.
     * Returns null if no cart exists for the user.
     * 
     * @param string $userUuid The UUID of the user
     * @return Cart|null The user's cart or null if not found
     */
    public function findByUserUuid(string $userUuid): ?Cart;

    /**
     * Add or update cart item with atomic operation
     * 
     * Performs an upsert operation on cart items using atomic database operations
     * to prevent race conditions in concurrent scenarios.
     * 
     * @param string $cartUuid The cart UUID
     * @param string $productUuid The product UUID
     * @param int $quantity The quantity to add
     * @param int $unitPrice The unit price in cents
     * @return void
     */
    public function upsertCartItem(string $cartUuid, string $productUuid, int $quantity, int $unitPrice): void;

    /**
     * Update cart item quantity
     * 
     * Updates the quantity of an existing cart item.
     * 
     * @param string $cartUuid The cart UUID
     * @param string $productUuid The product UUID
     * @param int $quantity The new quantity
     * @return void
     */
    public function updateCartItemQuantity(string $cartUuid, string $productUuid, int $quantity): void;

    /**
     * Remove item from cart
     * 
     * Removes (soft deletes) a specific item from the cart.
     * 
     * @param string $cartUuid The cart UUID
     * @param string $productUuid The product UUID
     * @return void
     */
    public function removeCartItem(string $cartUuid, string $productUuid): void;

    /**
     * Clear all cart items
     * 
     * Removes (soft deletes) all items from the specified cart.
     * 
     * @param string $cartUuid The cart UUID
     * @return void
     */
    public function clearCartItems(string $cartUuid): void;

    /**
     * Find cart item by cart and product UUID
     * 
     * Retrieves a specific cart item by cart UUID and product UUID.
     * 
     * @param string $cartUuid The cart UUID
     * @param string $productUuid The product UUID
     * @return CartItem|null The cart item or null if not found
     */
    public function findCartItem(string $cartUuid, string $productUuid): ?CartItem;
}