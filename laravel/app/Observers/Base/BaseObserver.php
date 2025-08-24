<?php

namespace App\Observers\Base;

use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Base Observer providing common model event handling.
 * 
 * This abstract observer class provides foundational functionality for all
 * model observers in the application. It handles UUID generation during
 * model creation and activity logging for various model events including
 * create, update, delete, and restore operations.
 *
 * @package App\Observers\Base
 */
abstract class BaseObserver
{
    use ActivityLoggable;

    /**
     * Handle the model "creating" event.
     * 
     * Automatically generates a UUID for the model if one is not already set.
     * This ensures all models have a UUID before being persisted to the database.
     *
     * @param Model $model The model being created
     * @return void
     */
    public function creating(Model $model): void
    {
        if (!$model->uuid) {
            $model->uuid = (string) Str::uuid();
        }
    }

    /**
     * Handle the model "created" event.
     * 
     * Logs the creation activity when a model is successfully created.
     * This provides an audit trail for new model instances.
     *
     * @param Model $model The model that was created
     * @return void
     */
    public function created(Model $model): void
    {
        $this->logActivity($model, 'created');
    }

    /**
     * Handle the model "updated" event.
     * 
     * Logs the update activity when a model is modified, including
     * the specific changes that were made to the model attributes.
     *
     * @param Model $model The model that was updated
     * @return void
     */
    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        $this->logActivity($model, 'updated', $changes);
    }

    /**
     * Handle the model "deleted" event.
     * 
     * Logs the deletion activity when a model is deleted (soft or hard delete).
     * This maintains an audit trail for deleted models.
     *
     * @param Model $model The model that was deleted
     * @return void
     */
    public function deleted(Model $model): void
    {
        $this->logActivity($model, 'deleted');
    }

    /**
     * Handle the model "restored" event.
     * 
     * Logs the restoration activity when a soft-deleted model is restored.
     * This tracks when previously deleted models are brought back.
     *
     * @param Model $model The model that was restored
     * @return void
     */
    public function restored(Model $model): void
    {
        $this->logActivity($model, 'restored');
    }
}