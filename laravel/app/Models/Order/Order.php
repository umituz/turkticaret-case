<?php

namespace App\Models\Order;

use App\Enums\Order\OrderStatusEnum;
use App\Models\Base\BaseUuidModel;
use App\Models\User\User;
use App\Traits\HasMoneyAttributes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Order Model representing customer orders in the e-commerce system.
 *
 * Handles order processing, status management, customer relationships,
 * order items tracking, shipping information, and financial calculations.
 * Implements money attributes for proper currency handling.
 *
 * @property string $uuid Order unique identifier
 * @property string $order_number Human-readable order number
 * @property string $user_uuid Associated user UUID
 * @property OrderStatusEnum $status Current order status
 * @property int $total_amount Order total amount in cents
 * @property string|null $shipping_address Shipping address information
 * @property string|null $notes Order notes or special instructions
 * @property \Carbon\Carbon|null $shipped_at Order shipment timestamp
 * @property \Carbon\Carbon|null $delivered_at Order delivery timestamp
 * @property \Carbon\Carbon $created_at Order creation timestamp
 * @property \Carbon\Carbon $updated_at Last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft deletion timestamp
 *
 * @package App\Models\Order
 */
class Order extends BaseUuidModel
{
    use HasMoneyAttributes;

    protected $fillable = [
        'order_number',
        'user_uuid',
        'status',
        'total_amount',
        'shipping_address',
        'notes',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'status' => OrderStatusEnum::class,
        'total_amount' => 'integer',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * Get the user who placed this order.
     *
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    /**
     * Get all items in this order.
     *
     * @return HasMany<OrderItem>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_uuid', 'uuid');
    }

    /**
     * Get the status history for this order (ordered by most recent first).
     *
     * @return HasMany<OrderStatusHistory>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_uuid', 'uuid')->orderBy('created_at', 'desc');
    }

    /**
     * Define which attributes should be treated as money values.
     *
     * @return array<string> Array of money attribute names
     */
    protected function getMoneyAttributes(): array
    {
        return ['total_amount'];
    }
}
