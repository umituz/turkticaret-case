<?php

declare(strict_types=1);

namespace App\Services\Admin\Order;

use App\Models\Order\Order;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Enums\Order\OrderStatusEnum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Admin Order Service for complex admin order management operations.
 *
 * Handles admin-specific order operations including viewing all orders,
 * updating order statuses, order analytics, and status history management.
 * Provides comprehensive business logic for order administration.
 *
 * @package App\Services\Admin\Order
 */
class AdminOrderService
{
    /**
     * Create a new AdminOrderService instance.
     *
     * @param OrderRepositoryInterface $orderRepository The order repository for data operations
     */
    public function __construct(protected OrderRepositoryInterface $orderRepository) {}

    /**
     * Get all orders with pagination for admin view.
     *
     * @param array $filters Optional filters for the query (currently not used)
     * @return LengthAwarePaginator Paginated collection of all orders
     */
    public function getAllOrders(array $filters = []): LengthAwarePaginator
    {
        return $this->orderRepository->findAllWithPagination();
    }

    /**
     * Update the status of an order.
     *
     * @param Order $order The order to update
     * @param string $newStatus The new status to set
     * @return bool True if update was successful, false otherwise
     */
    public function updateOrderStatus(Order $order, string $newStatus): bool
    {
        try {
            $additionalData = [];

            switch ($newStatus) {
                case OrderStatusEnum::SHIPPED->value:
                    $additionalData['shipped_at'] = now();
                    break;
                case OrderStatusEnum::DELIVERED->value:
                    $additionalData['delivered_at'] = now();
                    break;
            }

            return $this->orderRepository->updateOrderStatus(
                $order->uuid,
                $newStatus,
                $additionalData
            );
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get comprehensive order statistics for admin dashboard.
     *
     * @return array Array containing various order statistics
     */
    public function getOrderStatistics(): array
    {
        return $this->orderRepository->getOrderStatistics();
    }

    /**
     * Get order status history for a specific order.
     *
     * @param Order $order The order to get status history for
     * @return array Order status history data
     */
    public function getOrderStatusHistory(Order $order): array
    {
        $history = [];

        // Order placed
        $history[] = [
            'status' => 'pending',
            'date' => $order->created_at,
            'description' => 'Order placed'
        ];

        // Order processing (if updated_at is different from created_at and status is not pending)
        if ($order->status->value !== 'pending' && $order->updated_at > $order->created_at) {
            $history[] = [
                'status' => 'processing',
                'date' => $order->updated_at,
                'description' => 'Order confirmed and processing'
            ];
        }

        // Order shipped
        if ($order->shipped_at) {
            $history[] = [
                'status' => 'shipped',
                'date' => $order->shipped_at,
                'description' => 'Order shipped'
            ];
        }

        // Order delivered
        if ($order->delivered_at) {
            $history[] = [
                'status' => 'delivered',
                'date' => $order->delivered_at,
                'description' => 'Order delivered'
            ];
        }

        // Current status (if different from the timeline above)
        $lastHistoryStatus = end($history)['status'] ?? 'pending';
        if ($order->status->value !== $lastHistoryStatus) {
            $history[] = [
                'status' => $order->status->value,
                'date' => $order->updated_at,
                'description' => 'Order status updated to ' . ucfirst($order->status->value)
            ];
        }

        return [
            'order_uuid' => $order->uuid,
            'current_status' => $order->status->value,
            'history' => $history
        ];
    }

    /**
     * Get orders by status for admin filtering.
     *
     * @param string $status The status to filter by
     * @param array $additionalFilters Additional filters to apply
     * @return LengthAwarePaginator Paginated collection of filtered orders
     */
    public function getOrdersByStatus(string $status, array $additionalFilters = []): LengthAwarePaginator
    {
        $filters = array_merge($additionalFilters, ['status' => $status]);
        return $this->getAllOrders($filters);
    }

    /**
     * Get recent orders for admin dashboard.
     *
     * @param int $limit Number of recent orders to retrieve
     * @return LengthAwarePaginator Recent orders collection
     */
    public function getRecentOrders(int $limit = 10): LengthAwarePaginator
    {
        return $this->getAllOrders(['per_page' => $limit]);
    }

    /**
     * Search orders by order number or customer information.
     *
     * @param string $searchTerm The search term
     * @param array $additionalFilters Additional filters to apply
     * @return LengthAwarePaginator Search results
     */
    public function searchOrders(string $searchTerm, array $additionalFilters = []): LengthAwarePaginator
    {
        $filters = array_merge($additionalFilters, ['order_number' => $searchTerm]);
        return $this->getAllOrders($filters);
    }

    /**
     * Get order with full details including relationships.
     *
     * @param Order $order The order to load relationships for
     * @return Order The order with loaded relationships
     */
    public function getOrderWithFullDetails(Order $order): Order
    {
        return $order->load(['orderItems.product', 'user:uuid,name,email']);
    }
}
