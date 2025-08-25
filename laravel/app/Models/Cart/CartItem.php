<?php

namespace App\Models\Cart;

use App\Models\Base\BaseUuidModel;
use App\Models\Product\Product;
use App\Traits\HasMoneyAttributes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CartItem Model representing individual items within shopping carts.
 *
 * Manages cart item relationships, pricing calculations, and item-specific
 * functionality within the e-commerce cart system. Each cart item represents
 * a specific product quantity and pricing snapshot within a user's cart.
 *
 * @property string $uuid Cart item unique identifier
 * @property string $cart_uuid Associated cart UUID
 * @property string $product_uuid Associated product UUID
 * @property int $quantity Number of product units in cart
 * @property int $unit_price Product unit price in cents at time of addition
 * @property Carbon $created_at Item creation timestamp
 * @property Carbon $updated_at Last update timestamp
 * @property Carbon|null $deleted_at Soft deletion timestamp
 * @property int $total_price Calculated total price (quantity * unit_price) - accessor
 *
 * @package App\Models\Cart
 */
class CartItem extends BaseUuidModel
{
    use HasMoneyAttributes;

    protected $fillable = [
        'cart_uuid',
        'product_uuid',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'integer',
    ];

    /**
     * Get the cart that owns this cart item.
     *
     * @return BelongsTo<Cart>
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'cart_uuid', 'uuid');
    }

    /**
     * Get the product associated with this cart item.
     *
     * @return BelongsTo<Product>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    /**
     * Calculate the total price for this cart item.
     *
     * @return int Total price in cents (quantity * unit_price)
     */
    public function getTotalPriceAttribute(): int
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Define which attributes should be treated as money values.
     *
     * @return array<string> Array of money attribute names
     */
    protected function getMoneyAttributes(): array
    {
        return ['unit_price'];
    }
}
