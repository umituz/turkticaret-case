<?php

namespace App\Models\Cart;

use App\Models\Base\BaseUuidModel;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Cart Model representing user shopping carts in the e-commerce system.
 * 
 * Handles shopping cart functionality including item management, total calculations,
 * and cart state operations. Provides methods for cart manipulation and business
 * logic for e-commerce cart operations.
 *
 * @property string $uuid Cart unique identifier
 * @property string $user_uuid Associated user UUID
 * @property \Carbon\Carbon $created_at Cart creation timestamp
 * @property \Carbon\Carbon $updated_at Last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft deletion timestamp
 * 
 * @package App\Models\Cart
 */
class Cart extends BaseUuidModel
{
    protected $fillable = [
        'user_uuid',
    ];

    /**
     * Get the user who owns this cart.
     * 
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    /**
     * Get all items in this cart.
     * 
     * @return HasMany<CartItem>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'cart_uuid', 'uuid');
    }


    /**
     * Check if the cart is empty (contains no items).
     * 
     * @return bool True if cart has no items, false otherwise
     */
    public function isEmpty(): bool
    {
        return $this->cartItems()->count() === 0;
    }
}
