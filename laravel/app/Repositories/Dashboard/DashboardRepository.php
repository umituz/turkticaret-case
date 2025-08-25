<?php

declare(strict_types=1);

namespace App\Repositories\Dashboard;

use App\Models\Order\Order;
use App\Models\Order\OrderStatusHistory;
use App\Models\Product\Product;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository class for managing dashboard data operations.
 * 
 * This repository provides methods for retrieving dashboard statistics
 * including user counts, order metrics, product statistics, revenue
 * calculations, and system status information.
 * 
 * @package App\Repositories\Dashboard
 */
class DashboardRepository implements DashboardRepositoryInterface
{
    /**
     * Get the total number of users in the system.
     *
     * @return int The total count of all users
     */
    public function getTotalUsers(): int
    {
        return User::count();
    }





    /**
     * Get the total number of orders in the system.
     *
     * @return int The total count of all orders
     */
    public function getTotalOrders(): int
    {
        return Order::count();
    }

    /**
     * Get the total number of products in the system.
     *
     * @return int The total count of all products
     */
    public function getTotalProducts(): int
    {
        return Product::count();
    }




    /**
     * Get the most recent order status change activities.
     *
     * @param int $limit The maximum number of activities to retrieve (default: 10)
     * @return Collection Collection of recent order status history records with loaded relationships
     */
    public function getRecentOrderActivities(int $limit = 10): Collection
    {
        return OrderStatusHistory::with(['order.user'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get the most recently registered users.
     *
     * @param int $limit The maximum number of users to retrieve (default: 3)
     * @return Collection Collection of recently registered users
     */
    public function getRecentUserRegistrations(int $limit = 3): Collection
    {
        return User::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }



}
