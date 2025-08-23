<?php

namespace App\Models\Order;

use App\Enums\Order\OrderStatusEnum;
use App\Models\Base\BaseUuidModel;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_uuid', 'uuid');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_uuid', 'uuid');
    }
}
