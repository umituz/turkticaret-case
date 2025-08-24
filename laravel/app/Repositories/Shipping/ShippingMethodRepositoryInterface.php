<?php

namespace App\Repositories\Shipping;

use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface ShippingMethodRepositoryInterface extends BaseRepositoryInterface
{
    public function getActiveMethods(): Collection;

    public function getAllMethods(): Collection;
}
