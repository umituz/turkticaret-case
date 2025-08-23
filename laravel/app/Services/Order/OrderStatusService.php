<?php

namespace App\Services\Order;

use App\Enums\Order\OrderStatusEnum;
use App\Models\Order\Order;
use App\Repositories\Order\OrderStatusRepositoryInterface;
use App\Services\Slack\SlackNotificationService;

class OrderStatusService
{
    public function __construct(
        protected OrderStatusRepositoryInterface $orderStatusRepository,
        protected SlackNotificationService $slackService
    ) {}

    public function updateStatus(Order $order, OrderStatusEnum $newStatus): Order
    {
        if (!$this->canTransitionTo($order->status, $newStatus)) {
            throw new \InvalidArgumentException(
                "Cannot transition from {$order->status->value} to {$newStatus->value}"
            );
        }

        $this->orderStatusRepository->updateStatus($order, $newStatus);
        $updatedOrder = $order->fresh();
        
        $this->slackService->sendOrderNotification($updatedOrder, 'status_changed');

        return $updatedOrder;
    }


    public function canTransitionTo(OrderStatusEnum $currentStatus, OrderStatusEnum $newStatus): bool
    {
        return $currentStatus->canTransitionTo($newStatus);
    }
}