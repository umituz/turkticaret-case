<?php

namespace App\Observers\Product;

use App\Models\Product\Product;
use App\Observers\Base\BaseObserver;
use Illuminate\Database\Eloquent\Model;

class ProductObserver extends BaseObserver
{
    public function creating(Model $model): void
    {
        if (empty($model->slug)) {
            $model->slug = $model->generateUniqueSlug($model->name);
        }
    }

    public function updating(Product $model): void
    {
        if ($model->isDirty('name') && empty($model->slug)) {
            $model->slug = $model->generateUniqueSlug($model->name, $model->getOriginal('slug'));
        }
    }
}
