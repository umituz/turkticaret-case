<?php

namespace App\Repositories\Shipping;

use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Contract for Shipping Method repository implementations.
 * 
 * Defines the required methods for Shipping Method data access layer operations
 * including active method retrieval, comprehensive method listings, and
 * shipping option management functionality.
 *
 * @package App\Repositories\Shipping
 */
interface ShippingMethodRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all active shipping methods.
     *
     * @return Collection<int, mixed> Collection of active shipping methods
     */
    public function getActiveMethods(): Collection;

    /**
     * Get all shipping methods regardless of status.
     *
     * @return Collection<int, mixed> Collection of all shipping methods
     */
    public function getAllMethods(): Collection;
}
