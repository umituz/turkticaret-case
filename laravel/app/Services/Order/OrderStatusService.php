<?php

namespace App\Services\Order;

use App\Enums\Order\OrderStatusEnum;
use App\Models\Order\Order;
use App\Repositories\Order\OrderStatusRepositoryInterface;

class OrderStatusService
{
    public function __construct(protected OrderStatusRepositoryInterface $orderStatusRepository) {}

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


    public function canTransitionTo(OrderStatusEnum $currentStatus, OrderStatusEnum $newStatus): bool
    {
        return $currentStatus->canTransitionTo($newStatus);
    }
}
