<?php

namespace App\Http\Resources\Order;


use App\Http\Resources\Base\BaseCollection;

/**
 * API Resource Collection for transforming Order Status History data.
 * 
 * Handles the transformation of multiple OrderStatusHistory model instances into
 * standardized JSON API response collections. Provides audit trail listings
 * with consistent pagination and metadata for order status changes.
 *
 * @package App\Http\Resources\Order
 */
class OrderStatusHistoryCollection extends BaseCollection
{
    public $collects = OrderStatusHistoryResource::class;
}
