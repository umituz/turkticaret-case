<?php

namespace App\Models\Order;

use App\Enums\Order\OrderStatusEnum;
use App\Models\Base\BaseUuidModel;
use App\Models\User\User;
use App\Traits\HasMoneyAttributes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_uuid', 'uuid');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_uuid', 'uuid')->orderBy('created_at', 'desc');
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->orderItems->sum('quantity');
    }

    protected function getMoneyAttributes(): array
    {
        return ['total_amount'];
    }
}
