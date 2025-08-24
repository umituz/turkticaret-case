<?php

namespace App\Repositories\Shipping;

use App\Models\Shipping\ShippingMethod;
use App\Repositories\Base\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Shipping method repository for handling shipping method database operations.
 * 
 * This repository provides methods for managing shipping method data including
 * retrieving active methods and all methods with proper sorting.
 *
 * @package App\Repositories\Shipping
 */
class ShippingMethodRepository extends BaseRepository implements ShippingMethodRepositoryInterface
{
    /**
     * Create a new ShippingMethod repository instance.
     *
     * @param ShippingMethod $model The ShippingMethod model instance
     */
    public function __construct(ShippingMethod $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all active shipping methods ordered by sort order and price.
     *
     * @return Collection Collection of active shipping methods
     */
    public function getActiveMethods(): Collection
    {
        return $this->model->active()
            ->orderBy('sort_order', 'asc')
            ->orderBy('price', 'asc')
            ->get();
    }

    /**
     * Get all shipping methods ordered by sort order and price.
     *
     * @return Collection Collection of all shipping methods
     */
    public function getAllMethods(): Collection
    {
        return $this->model->orderBy('sort_order', 'asc')
            ->orderBy('price', 'asc')
            ->get();
    }
}