<?php

namespace App\Repositories\Order;

use App\Enums\Order\OrderStatusEnum;
use App\Models\Order\Order;
use App\Models\Order\OrderStatusHistory;

/**
 * Repository class for managing order status operations.
 * 
 * This repository handles the business logic for updating order statuses
 * and maintaining order status history records.
 * 
 * @package App\Repositories\Order
 */
class OrderStatusRepository implements OrderStatusRepositoryInterface
{
    /**
     * Update the status of an order and create a history record.
     * 
     * This method updates the order's status to the new provided status and
     * creates a corresponding entry in the order status history table to
     * track the change along with the authenticated user who made the change.
     * 
     * @param Order $order The order instance to update
     * @param OrderStatusEnum $newStatus The new status to set for the order
     * @return bool True if the status was successfully updated, false otherwise
     */
    public function updateStatus(Order $order, OrderStatusEnum $newStatus): bool
    {
        $oldStatus = $order->status;
        
        $order->status = $newStatus;
        $result = $order->save();
        
        if ($result) {
            OrderStatusHistory::create([
                'order_uuid' => $order->uuid,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by_uuid' => auth()->id(),
                'notes' => 'Status updated via admin panel',
            ]);
        }
        
        return $result;
    }
}