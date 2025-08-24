<?php

namespace App\Repositories\Order;

use App\Enums\ApiEnums;
use App\Enums\Order\OrderStatusEnum;
use App\Models\Order\Order;
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
     * Find all orders with optional filters and pagination.
     *
     * @param array $filters Optional filters including status, user_uuid, order_number, date_from, date_to, per_page
     * @return LengthAwarePaginator Paginated orders with loaded relationships
     */
    public function findAllWithFilters(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model
            ->with(['user:uuid,name,email', 'orderItems.product'])
            ->orderBy('created_at', 'desc');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['user_uuid'])) {
            $query->where('user_uuid', $filters['user_uuid']);
        }

        if (!empty($filters['order_number'])) {
            $query->where('order_number', 'LIKE', '%' . $filters['order_number'] . '%');
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $perPage = !empty($filters['per_page']) ? (int) $filters['per_page'] : ApiEnums::DEFAULT_PAGINATION->value;

        return $query->paginate($perPage);
    }

    /**
     * Get comprehensive order statistics by status.
     *
     * @return array Array containing order counts by status (total, pending, processing, shipped, delivered, cancelled)
     */
    public function getOrderStatistics(): array
    {
        $totalOrders = $this->model->count();
        $pendingOrders = $this->model->where('status', OrderStatusEnum::PENDING)->count();
        $processingOrders = $this->model->where('status', OrderStatusEnum::PROCESSING)->count();
        $shippedOrders = $this->model->where('status', OrderStatusEnum::SHIPPED)->count();
        $deliveredOrders = $this->model->where('status', OrderStatusEnum::DELIVERED)->count();
        $cancelledOrders = $this->model->where('status', OrderStatusEnum::CANCELLED)->count();

        return [
            'total' => $totalOrders,
            'pending' => $pendingOrders,
            'processing' => $processingOrders,
            'shipped' => $shippedOrders,
            'delivered' => $deliveredOrders,
            'cancelled' => $cancelledOrders,
        ];
    }
}
