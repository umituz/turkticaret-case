<?php

namespace App\Models\Order;

use App\Models\Base\BaseUuidModel;
use App\Models\Product\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OrderItem Model representing individual items within orders.
 *
 * Manages order item data including product references, pricing, and quantities.
 * Each order item captures a snapshot of product information at the time of purchase
 * to preserve historical data even if the original product is modified or deleted.
 *
 * @property string $uuid Order item unique identifier
 * @property string $order_uuid Associated order UUID
 * @property string $product_uuid Associated product UUID
 * @property string $product_name Product name at time of order
 * @property int $quantity Number of units ordered
 * @property int $unit_price Price per unit in cents
 * @property int $total_price Total item price in cents (quantity * unit_price)
 * @property Carbon $created_at Creation timestamp
 * @property Carbon $updated_at Last update timestamp
 * @property Carbon|null $deleted_at Soft deletion timestamp
 *
 * @package App\Models\Order
 */
class OrderItem extends BaseUuidModel
{
    protected $fillable = [
        'order_uuid',
        'product_uuid',
        'product_name',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'integer',
        'total_price' => 'integer',
    ];

    /**
     * Get the order that owns this order item.
     *
     * @return BelongsTo<Order>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_uuid', 'uuid');
    }

    /**
     * Get the product associated with this order item.
     *
     * @return BelongsTo<Product>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

}
