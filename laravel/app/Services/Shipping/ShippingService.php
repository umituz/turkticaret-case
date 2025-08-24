<?php

namespace App\Services\Shipping;

use App\Repositories\Shipping\ShippingMethodRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ShippingService
{
    public function __construct(protected ShippingMethodRepositoryInterface $shippingMethodRepository) {}

    public function getAvailableMethods(): Collection
    {
        return $this->shippingMethodRepository->getActiveMethods();
    }

    public function getAllMethods(): Collection
    {
        return $this->shippingMethodRepository->getAllMethods();
    }

    public function getMethodByUuid(string $uuid): ?\App\Models\Shipping\ShippingMethod
    {
        return $this->shippingMethodRepository->findByUuid($uuid);
    }
}