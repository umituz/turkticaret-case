<?php

namespace App\Repositories\Order;

use App\Models\Order\OrderStatusHistory;
use App\Repositories\Base\BaseRepository;

/**
 * Order Status History Repository for order status history database operations.
 * 
 * Handles order status history data operations including creating status change records,
 * tracking order lifecycle events, and maintaining audit trails for order status changes.
 * Extends BaseRepository to provide standard CRUD functionality.
 *
 * @package App\Repositories\Order
 */
class OrderStatusHistoryRepository extends BaseRepository implements OrderStatusHistoryRepositoryInterface
{
    /**
     * Create a new OrderStatusHistoryRepository instance.
     *
     * @param OrderStatusHistory $model The OrderStatusHistory model instance
     */
    public function __construct(OrderStatusHistory $model)
    {
        parent::__construct($model);
    }
}