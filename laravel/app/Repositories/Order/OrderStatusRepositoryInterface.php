<?php

namespace App\Repositories\Order;

use App\Enums\Order\OrderStatusEnum;
use App\Models\Order\Order;

interface OrderStatusRepositoryInterface
{
    public function updateStatus(Order $order, OrderStatusEnum $newStatus): bool;
}