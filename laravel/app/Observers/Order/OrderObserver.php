<?php

namespace App\Observers\Order;

use App\Jobs\Order\SendOrderStatusUpdateEmail;
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
            $oldStatus = $model->getOriginal('status');
            $newStatus = $model->status->value;

            // Convert enum to string for job serialization
            if ($oldStatus instanceof \App\Enums\Order\OrderStatusEnum) {
                $oldStatus = $oldStatus->value;
            }

            // Create status history record
            OrderStatusHistory::create([
                'order_uuid' => $model->uuid,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by_uuid' => auth()->id(),
                'notes' => 'Status updated',
            ]);

            // Send email notification to order owner when status changes
            if ($model->user && $model->user->email) {
                SendOrderStatusUpdateEmail::dispatch(
                    $model->load('user', 'orderItems.product'),
                    $oldStatus,
                    $newStatus
                );
            }
        }
    }
}
