<?php

namespace App\Queries\Order;

use App\Enums\Order\OrderStatusEnum;
use App\Models\Order\Order;
use Illuminate\Database\Eloquent\Builder;

/**
 * Order Statistics Query class for order statistics database queries.
 *
 * Contains specific database query logic for order statistics that can be reused
 * across different parts of the application.
 *
 * @package App\Queries\Order
 */
class OrderStatisticsQuery
{
    /**
     * Get order statistics by status.
     *
     * @return array Array containing order counts by status
     */
    public function getOrderStatistics(): array
    {
        $statusCounts = Order::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalOrders = $statusCounts->sum();

        return [
            'total' => $totalOrders,
            'pending' => $statusCounts->get(OrderStatusEnum::PENDING->value, 0),
            'processing' => $statusCounts->get(OrderStatusEnum::PROCESSING->value, 0),
            'shipped' => $statusCounts->get(OrderStatusEnum::SHIPPED->value, 0),
            'delivered' => $statusCounts->get(OrderStatusEnum::DELIVERED->value, 0),
            'cancelled' => $statusCounts->get(OrderStatusEnum::CANCELLED->value, 0),
        ];
    }
}