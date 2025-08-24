<?php

namespace App\Repositories\Order;

use App\Models\Order\Order;
use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Contract for Order repository implementations.
 * 
 * Defines the required methods for Order data access layer operations
 * including CRUD operations, user-specific queries, order statistics,
 * and relationship management. Ensures consistent repository API
 * across implementations.
 *
 * @package App\Repositories\Order
 */
interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find orders by user UUID with pagination.
     *
     * @param string $userUuid The user UUID to filter by
     * @return LengthAwarePaginator Paginated collection of user orders
     */
    public function findByUserUuid(string $userUuid): LengthAwarePaginator;
    
    /**
     * Find an order by its unique order number.
     *
     * @param string $orderNumber The order number to search for
     * @return Order|null The found order or null if not exists
     */
    public function findByOrderNumber(string $orderNumber): ?Order;
    
    /**
     * Find all orders with optional filtering and pagination.
     *
     * @param array<string, mixed> $filters Optional filter parameters
     * @return LengthAwarePaginator Paginated and filtered order collection
     */
    public function findAllWithFilters(array $filters = []): LengthAwarePaginator;
    
    /**
     * Get order statistics and metrics.
     *
     * @return array<string, mixed> Statistical data about orders
     */
    public function getOrderStatistics(): array;
}