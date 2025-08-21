<?php

namespace App\Observers\Order;

use App\Models\Order\Order;
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
}
