<?php

namespace App\Repositories\Order;

use App\Enums\Order\OrderStatusEnum;
use App\Models\Order\Order;

/**
 * Order Status Repository Interface
 * 
 * Defines the contract for order status management operations.
 * Handles the updating of order statuses and maintains status history.
 * 
 * @package App\Repositories\Order
 */
interface OrderStatusRepositoryInterface
{
    /**
     * Update order status
     * 
     * Changes the order status and creates a status history record.
     * Validates status transitions and ensures data consistency.
     * 
     * @param Order $order The order to update
     * @param OrderStatusEnum $newStatus The new status to set
     * @return bool True if status was updated successfully
     */
    public function updateStatus(Order $order, OrderStatusEnum $newStatus): bool;
}