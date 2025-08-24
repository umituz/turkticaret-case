<?php

namespace App\Repositories\Cart;

use App\Models\Cart\Cart;
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
}