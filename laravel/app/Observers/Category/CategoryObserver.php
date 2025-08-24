<?php

namespace App\Observers\Category;

use App\Models\Category\Category;
use App\Observers\Base\BaseObserver;
use Illuminate\Database\Eloquent\Model;

/**
 * Observer for Category model events.
 * 
 * This observer handles Category model lifecycle events including
 * automatic slug generation when creating and updating categories.
 *
 * @package App\Observers\Category
 */
class CategoryObserver extends BaseObserver
{
    /**
     * Handle the Category "creating" event.
     * Automatically generates a unique slug if none is provided.
     *
     * @param Model $model The Category model instance
     * @return void
     */
    public function creating(Model $model): void
    {
        if (empty($model->slug)) {
            $model->slug = $model->generateUniqueSlug($model->name);
        }
    }

    /**
     * Handle the Category "updating" event.
     * Regenerates slug if name changed and slug is empty.
     *
     * @param Category $model The Category model instance
     * @return void
     */
    public function updating(Category $model): void
    {
        if ($model->isDirty('name') && empty($model->slug)) {
            $model->slug = $model->generateUniqueSlug($model->name, $model->getOriginal('slug'));
        }
    }
}