<?php

namespace App\Observers\Order;

use App\Models\Order\Order;
use App\Models\Order\OrderStatusHistory;
use App\Observers\Base\BaseObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrderObserver extends BaseObserver
{
    /**
     * Handle the Order "creating" event.
     */
    public function creating(Model $model): void
    {
        parent::creating($model);

        if ($model instanceof Order && empty($model->order_number)) {
            $model->order_number = 'ORD-' . date('Ymd') . '-' . substr(Str::uuid(), 0, 8);
        }
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Model $model): void
    {
        OrderStatusHistory::create([
            'order_uuid' => $model->uuid,
            'old_status' => null,
            'new_status' => $model->status->value,
            'changed_by_uuid' => auth()->id(),
            'notes' => 'Order created',
        ]);
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Model $model): void
    {
        if ($model->isDirty('status')) {
            OrderStatusHistory::create([
                'order_uuid' => $model->uuid,
                'old_status' => $model->getOriginal('status'),
                'new_status' => $model->status->value,
                'changed_by_uuid' => auth()->id(),
                'notes' => 'Status updated',
            ]);
        }
    }
}
