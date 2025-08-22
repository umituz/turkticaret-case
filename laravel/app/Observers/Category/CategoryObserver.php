<?php

namespace App\Observers\Category;

use App\Models\Category\Category;
use App\Observers\Base\BaseObserver;
use Illuminate\Database\Eloquent\Model;

class CategoryObserver extends BaseObserver
{
    public function creating(Model $model): void
    {
        if (empty($model->slug)) {
            $model->slug = $model->generateUniqueSlug($model->name);
        }
    }

    public function updating(Category $model): void
    {
        if ($model->isDirty('name') && empty($model->slug)) {
            $model->slug = $model->generateUniqueSlug($model->name, $model->getOriginal('slug'));
        }
    }
}