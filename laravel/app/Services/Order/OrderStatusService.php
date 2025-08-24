<?php

namespace App\Services\Order;

use App\Enums\Order\OrderStatusEnum;
use App\Models\Order\Order;
use App\Repositories\Order\OrderStatusRepositoryInterface;

/**
 * Order Status Service for order status management operations.
 * 
 * Handles order status transitions, validation of status changes,
 * and maintains order status history. Ensures proper order lifecycle
 * management with business rule enforcement.
 *
 * @package App\Services\Order
 */
class OrderStatusService
{
    /**
     * Create a new OrderStatusService instance.
     *
     * @param OrderStatusRepositoryInterface $orderStatusRepository The order status repository for data operations
     */
    public function __construct(protected OrderStatusRepositoryInterface $orderStatusRepository) {}

    /**
     * Update order status with transition validation.
     *
     * @param Order $order The order to update status for
     * @param OrderStatusEnum $newStatus The new status to transition to
     * @return Order The order instance with updated status
     * @throws \InvalidArgumentException When status transition is not allowed
     */
    public function updateStatus(Order $order, OrderStatusEnum $newStatus): Order
    {
        if (!$this->canTransitionTo($order->status, $newStatus)) {
            throw new \InvalidArgumentException(
                "Cannot transition from {$order->status->value} to {$newStatus->value}"
            );
        }

        $this->orderStatusRepository->updateStatus($order, $newStatus);

        return $order->fresh();
    }


    /**
     * Check if order status can transition from current to new status.
     *
     * @param OrderStatusEnum $currentStatus The current order status
     * @param OrderStatusEnum $newStatus The desired new status
     * @return bool True if transition is allowed, false otherwise
     */
    public function canTransitionTo(OrderStatusEnum $currentStatus, OrderStatusEnum $newStatus): bool
    {
        return $currentStatus->canTransitionTo($newStatus);
    }
}
