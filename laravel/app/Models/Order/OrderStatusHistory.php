<?php

namespace App\Models\Order;

use App\Enums\Order\OrderStatusEnum;
use App\Models\Base\BaseUuidModel;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OrderStatusHistory Model for tracking order status changes.
 * 
 * Maintains an audit trail of order status transitions including timestamps,
 * responsible users, and optional notes. Provides complete order status
 * history for order management and customer service purposes.
 *
 * @property string $uuid Status history unique identifier
 * @property string $order_uuid Associated order UUID
 * @property OrderStatusEnum|null $old_status Previous order status
 * @property OrderStatusEnum $new_status New order status
 * @property string|null $changed_by_uuid UUID of user who changed the status
 * @property string|null $notes Optional notes about the status change
 * @property \Carbon\Carbon $created_at Status change timestamp
 * @property \Carbon\Carbon $updated_at Last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft deletion timestamp
 * 
 * @package App\Models\Order
 */
class OrderStatusHistory extends BaseUuidModel
{
    protected $table = 'order_status_histories';

    protected $fillable = [
        'order_uuid',
        'old_status',
        'new_status',
        'changed_by_uuid',
        'notes',
    ];

    protected $casts = [
        'old_status' => OrderStatusEnum::class,
        'new_status' => OrderStatusEnum::class,
    ];

    /**
     * Get the order associated with this status history record.
     *
     * @return BelongsTo<Order>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_uuid', 'uuid');
    }

    /**
     * Get the user who changed the order status.
     *
     * @return BelongsTo<User>
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_uuid', 'uuid');
    }
}
