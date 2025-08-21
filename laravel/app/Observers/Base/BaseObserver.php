<?php

namespace App\Observers\Base;

use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

abstract class BaseObserver
{
    use ActivityLoggable;

    public function creating(Model $model): void
    {
        if (!$model->uuid) {
            $model->uuid = (string) Str::uuid();
        }
    }

    public function created(Model $model): void
    {
        $this->logActivity($model, 'created');
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        $this->logActivity($model, 'updated', $changes);
    }

    public function deleted(Model $model): void
    {
        $this->logActivity($model, 'deleted');
    }

    public function restored(Model $model): void
    {
        $this->logActivity($model, 'restored');
    }
}