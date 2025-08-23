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

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getTotalUsers(): int
    {
        return User::count();
    }

    public function getCurrentMonthUsers(Carbon $currentMonth): int
    {
        return User::where('created_at', '>=', $currentMonth)->count();
    }

    public function getPreviousMonthUsers(Carbon $previousMonth, Carbon $currentMonth): int
    {
        return User::whereBetween('created_at', [$previousMonth, $currentMonth])->count();
    }

    public function getCurrentMonthOrders(Carbon $currentMonth): int
    {
        return Order::where('created_at', '>=', $currentMonth)->count();
    }

    public function getPreviousMonthOrders(Carbon $previousMonth, Carbon $currentMonth): int
    {
        return Order::whereBetween('created_at', [$previousMonth, $currentMonth])->count();
    }

    public function getTotalProducts(): int
    {
        return Product::count();
    }

    public function getCurrentMonthProducts(Carbon $currentMonth): int
    {
        return Product::where('created_at', '>=', $currentMonth)->count();
    }

    public function getCurrentMonthRevenue(Carbon $currentMonth): float
    {
        return (float) Order::where('created_at', '>=', $currentMonth)
            ->where('status', OrderStatusEnum::DELIVERED)
            ->sum('total_amount');
    }

    public function getPreviousMonthRevenue(Carbon $previousMonth, Carbon $currentMonth): float
    {
        return (float) Order::whereBetween('created_at', [$previousMonth, $currentMonth])
            ->where('status', OrderStatusEnum::DELIVERED)
            ->sum('total_amount');
    }

    public function getRecentOrderActivities(int $limit = 10): Collection
    {
        return OrderStatusHistory::with(['order.user'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getRecentUserRegistrations(int $limit = 3): Collection
    {
        return User::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getRecentProductUpdates(int $limit = 2): Collection
    {
        return Product::orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

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
