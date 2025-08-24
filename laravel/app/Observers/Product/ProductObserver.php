<?php

namespace App\Observers\Product;

use App\Models\Product\Product;
use App\Observers\Base\BaseObserver;
use Illuminate\Database\Eloquent\Model;

/**
 * Observer class for Product model events.
 * 
 * Handles product-related model events including automatic slug generation
 * during creation and updates, ensuring consistent URL-friendly identifiers
 * for all products in the e-commerce system.
 *
 * @package App\Observers\Product
 */
class ProductObserver extends BaseObserver
{
    /**
     * Handle the Product "creating" event.
     *
     * @param Model $model The Product model being created
     * @return void
     */
    public function creating(Model $model): void
    {
        if (empty($model->slug)) {
            $model->slug = $model->generateUniqueSlug($model->name);
        }
    }

    /**
     * Handle the Product "updating" event.
     *
     * @param Product $model The Product model being updated
     * @return void
     */
    public function updating(Product $model): void
    {
        if ($model->isDirty('name') && empty($model->slug)) {
            $model->slug = $model->generateUniqueSlug($model->name, $model->getOriginal('slug'));
        }
    }
}
