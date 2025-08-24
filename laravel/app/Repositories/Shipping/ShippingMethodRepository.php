<?php

namespace App\Repositories\Shipping;

use App\Models\Shipping\ShippingMethod;
use App\Repositories\Base\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class ShippingMethodRepository extends BaseRepository implements ShippingMethodRepositoryInterface
{
    public function __construct(ShippingMethod $model)
    {
        parent::__construct($model);
    }
    public function getActiveMethods(): Collection
    {
        return $this->model->active()
            ->orderBy('sort_order', 'asc')
            ->orderBy('price', 'asc')
            ->get();
    }

    public function getAllMethods(): Collection
    {
        return $this->model->orderBy('sort_order', 'asc')
            ->orderBy('price', 'asc')
            ->get();
    }
}