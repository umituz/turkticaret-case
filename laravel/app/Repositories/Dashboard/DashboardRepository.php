<?php

declare(strict_types=1);

namespace App\Repositories\Dashboard;

use App\Enums\Order\OrderStatusEnum;
use App\Models\Order\Order;
use App\Models\Order\OrderStatusHistory;
use App\Models\Product\Product;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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
     * Get the number of users registered in the current month.
     *
     * @param Carbon $currentMonth The start date of the current month
     * @return int The count of users registered since the current month started
     */
    public function getCurrentMonthUsers(Carbon $currentMonth): int
    {
        return User::where('created_at', '>=', $currentMonth)->count();
    }

    /**
     * Get the number of users registered in the previous month.
     *
     * @param Carbon $previousMonth The start date of the previous month
     * @param Carbon $currentMonth The start date of the current month
     * @return int The count of users registered between the previous month and current month
     */
    public function getPreviousMonthUsers(Carbon $previousMonth, Carbon $currentMonth): int
    {
        return User::whereBetween('created_at', [$previousMonth, $currentMonth])->count();
    }

    /**
     * Get the number of orders created in the current month.
     *
     * @param Carbon $currentMonth The start date of the current month
     * @return int The count of orders created since the current month started
     */
    public function getCurrentMonthOrders(Carbon $currentMonth): int
    {
        return Order::where('created_at', '>=', $currentMonth)->count();
    }

    /**
     * Get the number of orders created in the previous month.
     *
     * @param Carbon $previousMonth The start date of the previous month
     * @param Carbon $currentMonth The start date of the current month
     * @return int The count of orders created between the previous month and current month
     */
    public function getPreviousMonthOrders(Carbon $previousMonth, Carbon $currentMonth): int
    {
        return Order::whereBetween('created_at', [$previousMonth, $currentMonth])->count();
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
     * Get the number of products created in the current month.
     *
     * @param Carbon $currentMonth The start date of the current month
     * @return int The count of products created since the current month started
     */
    public function getCurrentMonthProducts(Carbon $currentMonth): int
    {
        return Product::where('created_at', '>=', $currentMonth)->count();
    }

    /**
     * Get the total revenue from delivered orders in the current month.
     *
     * @param Carbon $currentMonth The start date of the current month
     * @return float The total revenue from delivered orders since the current month started
     */
    public function getCurrentMonthRevenue(Carbon $currentMonth): float
    {
        return (float) Order::where('created_at', '>=', $currentMonth)
            ->where('status', OrderStatusEnum::DELIVERED)
            ->sum('total_amount');
    }

    /**
     * Get the total revenue from delivered orders in the previous month.
     *
     * @param Carbon $previousMonth The start date of the previous month
     * @param Carbon $currentMonth The start date of the current month
     * @return float The total revenue from delivered orders between the previous month and current month
     */
    public function getPreviousMonthRevenue(Carbon $previousMonth, Carbon $currentMonth): float
    {
        return (float) Order::whereBetween('created_at', [$previousMonth, $currentMonth])
            ->where('status', OrderStatusEnum::DELIVERED)
            ->sum('total_amount');
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

    /**
     * Get the most recently updated products.
     *
     * @param int $limit The maximum number of products to retrieve (default: 2)
     * @return Collection Collection of recently updated products
     */
    public function getRecentProductUpdates(int $limit = 2): Collection
    {
        return Product::orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Check the database connection status.
     *
     * @return string Returns 'online' if database connection is successful, 'offline' otherwise
     */
    public function checkDatabaseStatus(): string
    {
        try {
            DB::connection()->getPdo();
            return 'online';
        } catch (\Exception $e) {
            return 'offline';
        }
    }
}
