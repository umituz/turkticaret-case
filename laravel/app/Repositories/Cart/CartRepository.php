<?php

namespace App\Repositories\Cart;

use App\Models\Cart\Cart;
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
}