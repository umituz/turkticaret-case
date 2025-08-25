<?php

namespace App\Repositories\Order;

use App\Enums\ApiEnums;
use App\Models\Order\Order;
use App\Queries\Order\OrderStatisticsQuery;
use App\Repositories\Base\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Order Repository for order-specific database operations.
 *
 * Handles comprehensive order data operations including user-specific queries,
 * order number lookups, filtered searches, and order statistics generation.
 * Extends BaseRepository to provide order-specific functionality.
 *
 * @package App\Repositories\Order
 */
class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    /**
     * Create a new OrderRepository instance.
     *
     * @param Order $model The Order model instance for this repository
     */
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    /**
     * Find orders by user UUID with pagination.
     *
     * @param string $userUuid The UUID of the user to find orders for
     * @return LengthAwarePaginator Paginated orders with loaded relationships
     */
    public function findByUserUuid(string $userUuid): LengthAwarePaginator
    {
        return $this->model
            ->where('user_uuid', $userUuid)
            ->with(['orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(ApiEnums::DEFAULT_PAGINATION->value);
    }

    /**
     * Find an order by its order number.
     *
     * @param string $orderNumber The order number to search for
     * @return Order|null The found order with loaded relationships or null if not found
     */
    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return $this->model
            ->where('order_number', $orderNumber)
            ->with(['orderItems.product'])
            ->first();
    }

    /**
     * Find all orders with pagination.
     *
     * @return LengthAwarePaginator Paginated orders with loaded relationships
     */
    public function findAllWithPagination(): LengthAwarePaginator
    {
        return $this->model
            ->with(['user:uuid,name,email', 'orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(ApiEnums::DEFAULT_PAGINATION->value);
    }

    /**
     * Get comprehensive order statistics by status.
     *
     * @return array Array containing order counts by status (total, pending, processing, shipped, delivered, cancelled)
     */
    public function getOrderStatistics(): array
    {
        return app(OrderStatisticsQuery::class)->getOrderStatistics();
    }

    /**
     * Update order status with additional metadata.
     *
     * @param string $orderUuid The order UUID to update
     * @param string $status The new status
     * @param array $additionalData Additional data to update (shipped_at, delivered_at, etc.)
     * @return bool Whether the update was successful
     */
    public function updateOrderStatus(string $orderUuid, string $status, array $additionalData = []): bool
    {
        $order = $this->findByUuid($orderUuid);
        if (!$order) {
            return false;
        }

        $updateData = array_merge(['status' => $status], $additionalData);

        foreach ($updateData as $key => $value) {
            $order->$key = $value;
        }

        return $order->save();
    }
}
