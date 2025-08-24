<?php

namespace App\Services\Shipping;

use App\Models\Shipping\ShippingMethod;
use App\Repositories\Shipping\ShippingMethodRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Shipping Service for shipping method management.
 *
 * Handles shipping method operations including retrieving available shipping
 * methods, managing shipping options, and providing shipping method information
 * for order fulfillment and checkout processes.
 *
 * @package App\Services\Shipping
 */
class ShippingService
{
    /**
     * Create a new ShippingService instance.
     *
     * @param ShippingMethodRepositoryInterface $shippingMethodRepository The shipping method repository for data operations
     */
    public function __construct(protected ShippingMethodRepositoryInterface $shippingMethodRepository) {}

    /**
     * Get all active/available shipping methods.
     *
     * @return Collection Collection of active shipping methods available for orders
     */
    public function getAvailableMethods(): Collection
    {
        return $this->shippingMethodRepository->getActiveMethods();
    }

    /**
     * Get all shipping methods including inactive ones.
     *
     * @return Collection Collection of all shipping methods in the system
     */
    public function getAllMethods(): Collection
    {
        return $this->shippingMethodRepository->getAllMethods();
    }

    /**
     * Find a shipping method by its UUID.
     *
     * @param string $uuid The UUID of the shipping method to find
     * @return ShippingMethod|null The shipping method instance or null if not found
     */
    public function getMethodByUuid(string $uuid): ?ShippingMethod
    {
        return $this->shippingMethodRepository->findByUuid($uuid);
    }
}
