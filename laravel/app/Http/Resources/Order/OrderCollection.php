<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Base\BaseCollection;

/**
 * API Resource Collection for transforming Order data.
 * 
 * Handles the transformation of multiple Order model instances into standardized
 * JSON API response collections. Provides consistent pagination, filtering,
 * and collection metadata for order listings.
 *
 * @package App\Http\Resources\Order
 */
class OrderCollection extends BaseCollection
{
    public $collects = OrderResource::class;
}