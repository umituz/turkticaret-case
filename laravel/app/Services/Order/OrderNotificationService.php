<?php

namespace App\Services\Order;

use App\DTOs\Order\OrderStatusChangeDTO;
use App\Models\Order\Order;
use App\Notifications\Order\OrderStatusUpdatedNotification;
use App\Repositories\Order\OrderStatusHistoryRepositoryInterface;
use App\Traits\ActivityLoggable;

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
    use ActivityLoggable;

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

        // Log activity for dashboard recent activity
        $this->logStatusChangeActivity($order, $dto->getOldStatusValue(), $newStatus);

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
     * Log order status change activity for dashboard display.
     *
     * @param Order $order The order being updated
     * @param string|null $oldStatus The previous status value
     * @param string $newStatus The new status value
     * @return void
     */
    protected function logStatusChangeActivity(Order $order, ?string $oldStatus, string $newStatus): void
    {
        $description = $oldStatus 
            ? "Order #{$order->order_number} status changed from {$oldStatus} to {$newStatus}"
            : "Order #{$order->order_number} status set to {$newStatus}";

        activity('order_status_change')
            ->performedOn($order)
            ->causedBy(auth()->user())
            ->withProperty('order_number', $order->order_number)
            ->withProperty('old_status', $oldStatus)
            ->withProperty('new_status', $newStatus)
            ->withProperty('user_email', $order->user?->email)
            ->withProperty('total_amount', $order->total_amount)
            ->log($description);
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
