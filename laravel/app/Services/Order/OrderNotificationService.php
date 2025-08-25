<?php

namespace App\Services\Order;

use App\DTOs\Order\OrderStatusChangeDTO;
use App\Models\Order\Order;
use App\Notifications\Order\OrderStatusUpdatedNotification;
use App\Repositories\Order\OrderStatusHistoryRepositoryInterface;

/**
 * Order Notification Service for handling order-related notifications and status history.
 *
 * Manages order status change notifications, status history tracking, and business logic
 * related to order lifecycle events. Provides centralized notification handling to keep
 * observers clean and focused on their primary responsibilities.
 *
 * @package App\Services\Order
 */
class OrderNotificationService
{
    public function __construct(protected OrderStatusHistoryRepositoryInterface $statusHistoryRepository) {}

    /**
     * Handle order status change notification and history tracking.
     *
     * @param Order $order The order that was updated
     * @param mixed $oldStatus The previous status (enum or string)
     * @param string $newStatus The new status value
     * @return void
     */
    public function handleStatusChange(Order $order, mixed $oldStatus, string $newStatus): void
    {
        $dto = OrderStatusChangeDTO::forStatusUpdate($order, $oldStatus, $newStatus);

        // Create status history record
        $this->createStatusHistoryRecord($dto);

        // Send notification to user
        $this->sendStatusUpdateNotification($dto);
    }

    /**
     * Create order status history record.
     *
     * @param OrderStatusChangeDTO $dto The status change data
     * @return void
     */
    protected function createStatusHistoryRecord(OrderStatusChangeDTO $dto): void
    {
        $this->statusHistoryRepository->create($dto->toArray());
    }

    /**
     * Send status update notification to the order owner.
     *
     * @param OrderStatusChangeDTO $dto The status change data
     * @return void
     */
    protected function sendStatusUpdateNotification(OrderStatusChangeDTO $dto): void
    {
        if ($dto->order->user && $dto->order->user->email) {
            $dto->order->user->notify(new OrderStatusUpdatedNotification(
                $dto->order->load('user', 'orderItems.product'),
                $dto->getOldStatusValue(),
                $dto->newStatus
            ));
        }
    }

    /**
     * Handle order creation notification.
     *
     * @param Order $order The newly created order
     * @return void
     */
    public function handleOrderCreated(Order $order): void
    {
        $dto = OrderStatusChangeDTO::forOrderCreated($order);
        $this->createStatusHistoryRecord($dto);
    }
}
