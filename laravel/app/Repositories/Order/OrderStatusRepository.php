<?php

namespace App\Repositories\Order;

use App\Enums\Order\OrderStatusEnum;
use App\Models\Order\Order;
use App\Models\Order\OrderStatusHistory;

class OrderStatusRepository implements OrderStatusRepositoryInterface
{
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