<?php

namespace App\Observers\Order;

use App\Enums\Order\OrderEnum;
use App\Models\Order\Order;
use App\Observers\Base\BaseObserver;
use App\Services\Order\OrderNotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrderObserver extends BaseObserver
{
    public function __construct(protected OrderNotificationService $notificationService) {}

    /**
     * Handle the Order "creating" event.
     */
    public function creating(Model $model): void
    {
        parent::creating($model);

        if ($model instanceof Order && empty($model->order_number)) {
            $model->order_number = 'ORD-' . date('Ymd') . '-' . substr(Str::uuid(), 0, OrderEnum::getOrderNumberUuidLength());
        }
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Model $model): void
    {
        if ($model instanceof Order) {
            $this->notificationService->handleOrderCreated($model);
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Model $model): void
    {
        if ($model instanceof Order && $model->isDirty('status')) {
            $oldStatus = $model->getOriginal('status');
            $newStatus = $model->status->value;

            $this->notificationService->handleStatusChange($model, $oldStatus, $newStatus);
        }
    }
}
