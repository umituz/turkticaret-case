<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Repositories\Dashboard\DashboardRepositoryInterface;
use App\Helpers\MoneyHelper;
use Carbon\Carbon;

/**
 * Dashboard Service for administrative dashboard operations.
 * 
 * Handles comprehensive dashboard data aggregation including system metrics,
 * user statistics, order analytics, product information, and system status monitoring.
 * Provides business intelligence and real-time dashboard data for administrators.
 *
 * @package App\Services\Dashboard
 */
class DashboardService
{
    /**
     * Create a new DashboardService instance.
     *
     * @param DashboardRepositoryInterface $dashboardRepository The dashboard repository for analytics operations
     */
    public function __construct(protected DashboardRepositoryInterface $dashboardRepository) {}

    /**
     * Get comprehensive dashboard data including stats, activities, and system status.
     *
     * @return array Array containing dashboard statistics, recent activities, and system status information
     */
    public function getDashboardData(): array
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            'stats' => $this->getStats($currentMonth, $previousMonth),
            'recent_activity' => $this->getRecentActivity(),
            'system_status' => $this->getSystemStatus()
        ];
    }

    /**
     * Generate statistical data comparing current and previous month metrics.
     *
     * @param Carbon $currentMonth The current month start date for metrics calculation
     * @param Carbon $previousMonth The previous month start date for comparison
     * @return array Array containing formatted statistics with percentage changes
     */
    private function getStats(Carbon $currentMonth, Carbon $previousMonth): array
    {
        $currentUsers = $this->dashboardRepository->getCurrentMonthUsers($currentMonth);
        $currentOrders = $this->dashboardRepository->getCurrentMonthOrders($currentMonth);
        $totalProducts = $this->dashboardRepository->getTotalProducts();
        $currentRevenue = $this->dashboardRepository->getCurrentMonthRevenue($currentMonth);

        $previousUsers = $this->dashboardRepository->getPreviousMonthUsers($previousMonth, $currentMonth);
        $previousOrders = $this->dashboardRepository->getPreviousMonthOrders($previousMonth, $currentMonth);
        $previousRevenue = $this->dashboardRepository->getPreviousMonthRevenue($previousMonth, $currentMonth);

        return [
            [
                'title' => 'Total Users',
                'value' => number_format($this->dashboardRepository->getTotalUsers()),
                'change' => $this->calculatePercentageChange($currentUsers, $previousUsers),
                'description' => 'Active users this month'
            ],
            [
                'title' => 'Orders',
                'value' => number_format($currentOrders),
                'change' => $this->calculatePercentageChange($currentOrders, $previousOrders),
                'description' => 'Orders completed this month'
            ],
            [
                'title' => 'Products',
                'value' => number_format($totalProducts),
                'change' => '+' . $this->dashboardRepository->getCurrentMonthProducts($currentMonth),
                'description' => 'Total products in inventory'
            ],
            [
                'title' => 'Revenue',
                'value' => MoneyHelper::formatAmount((int) $currentRevenue),
                'change' => $this->calculatePercentageChange($currentRevenue, $previousRevenue),
                'description' => 'Total revenue this month'
            ]
        ];
    }

    /**
     * Retrieve and format recent system activities including orders, users, and products.
     *
     * @return array Array of recent activities sorted by timestamp, limited to 10 items
     */
    private function getRecentActivity(): array
    {
        $orderActivities = $this->dashboardRepository->getRecentOrderActivities();
        $userRegistrations = $this->dashboardRepository->getRecentUserRegistrations();
        $productUpdates = $this->dashboardRepository->getRecentProductUpdates();

        $activities = collect();

        foreach ($orderActivities as $history) {
            $activityData = $this->formatOrderActivity($history);
            if ($activityData) {
                $activities->push($activityData);
            }
        }

        foreach ($userRegistrations as $user) {
            $activities->push([
                'uuid' => 'user_' . $user->uuid,
                'type' => 'user',
                'message' => $user->name . ' joined TurkTicaret',
                'timestamp' => $user->created_at->diffForHumans(),
                'user' => $user->name,
                'status' => 'info'
            ]);
        }

        foreach ($productUpdates as $product) {
            $activities->push([
                'uuid' => 'product_' . $product->uuid,
                'type' => 'product',
                'message' => 'Product "' . $product->name . '" was updated',
                'timestamp' => $product->updated_at->diffForHumans(),
                'status' => 'info'
            ]);
        }

        return $activities->sortByDesc(function ($item) {
            return strtotime(str_replace(' ago', '', $item['timestamp']));
        })
            ->take(10)
            ->values()
            ->toArray();
    }

    /**
     * Get comprehensive system status information and health metrics.
     *
     * @return array Array containing system component status and health information
     */
    private function getSystemStatus(): array
    {
        return [
            [
                'id' => 'server',
                'label' => 'Server Status',
                'status' => 'online',
                'last_updated' => '1 minute ago'
            ],
            [
                'id' => 'database',
                'label' => 'Database',
                'status' => $this->dashboardRepository->checkDatabaseStatus(),
                'last_updated' => '2 minutes ago'
            ],
            [
                'id' => 'backup',
                'label' => 'Last Backup',
                'status' => 'online',
                'value' => '2 hours ago',
                'last_updated' => '2 hours ago'
            ],
            [
                'id' => 'storage',
                'label' => 'Storage Usage',
                'status' => $this->getStorageStatus(),
                'value' => $this->getStorageUsage(),
                'last_updated' => '5 minutes ago'
            ],
            [
                'id' => 'security',
                'label' => 'Security Scan',
                'status' => 'online',
                'value' => 'No issues',
                'last_updated' => '1 day ago'
            ]
        ];
    }

    /**
     * Format order status history into activity data.
     *
     * @param mixed $history The order status history record to format
     * @return array|null Formatted activity data or null if formatting fails
     */
    private function formatOrderActivity($history): ?array
    {
        $order = $history->order;
        $user = $order->user;
        $changedBy = $history->changedBy;
        
        $oldStatus = $history->old_status?->value ?? null;
        $newStatus = $history->new_status?->value ?? $history->status ?? 'unknown';
        
        $activityData = $this->getOrderActivityData($oldStatus, $newStatus, $order, $user, $changedBy);
        
        if (!$activityData) {
            return null;
        }
        
        return [
            'uuid' => $history->uuid,
            'type' => 'order',
            'message' => $activityData['message'],
            'timestamp' => $history->created_at->diffForHumans(),
            'user' => $user->name ?? 'Unknown User',
            'status' => $activityData['status']
        ];
    }

    /**
     * Generate activity data based on order status transitions.
     *
     * @param string|null $oldStatus The previous order status or null for new orders
     * @param string $newStatus The new order status
     * @param mixed $order The order model instance
     * @param mixed $user The user who owns the order
     * @param mixed $changedBy The user who changed the status
     * @return array|null Activity data with message and status or null if invalid
     */
    private function getOrderActivityData(?string $oldStatus, string $newStatus, $order, $user, $changedBy): ?array
    {
        $orderNumber = $order->order_number;
        $userName = $user->name ?? 'Unknown User';
        $changedByName = $changedBy->name ?? 'System';
        
        return match ($newStatus) {
            'pending' => [
                'message' => $oldStatus ? 
                    "Order #{$orderNumber} status changed to pending by {$changedByName}" :
                    "{$userName} placed a new order #{$orderNumber}",
                'status' => 'info'
            ],
            'confirmed' => [
                'message' => "Order #{$orderNumber} was confirmed by {$changedByName}",
                'status' => 'success'
            ],
            'processing' => [
                'message' => "Order #{$orderNumber} is now being processed by {$changedByName}",
                'status' => 'info'
            ],
            'shipped' => [
                'message' => "Order #{$orderNumber} has been shipped to {$userName}",
                'status' => 'success'
            ],
            'delivered' => [
                'message' => "Order #{$orderNumber} was successfully delivered to {$userName}",
                'status' => 'success'
            ],
            'cancelled' => [
                'message' => "Order #{$orderNumber} was cancelled by {$changedByName}",
                'status' => 'warning'
            ],
            default => [
                'message' => "Order #{$orderNumber} status updated by {$changedByName}",
                'status' => 'info'
            ]
        };
    }

    /**
     * Calculate percentage change between current and previous values.
     *
     * @param int|float $current The current period value
     * @param int|float $previous The previous period value for comparison
     * @return string Formatted percentage change with sign (e.g., '+15.5%' or '-3.2%')
     */
    private function calculatePercentageChange(int|float $current, int|float $previous): string
    {
        if ($previous == 0) {
            return $current > 0 ? '+100%' : '0%';
        }

        $change = (($current - $previous) / $previous) * 100;
        $sign = $change >= 0 ? '+' : '';

        return $sign . number_format($change, 1) . '%';
    }

    /**
     * Determine storage status based on usage percentage.
     *
     * @return string Storage status: 'online', 'warning', or 'offline'
     */
    private function getStorageStatus(): string
    {
        $usage = $this->getStorageUsagePercentage();

        if ($usage >= 90) {
            return 'offline';
        } elseif ($usage >= 75) {
            return 'warning';
        }

        return 'online';
    }

    /**
     * Get formatted storage usage information.
     *
     * @return string Formatted storage usage percentage (e.g., '65% used')
     */
    private function getStorageUsage(): string
    {
        return $this->getStorageUsagePercentage() . '% used';
    }

    /**
     * Calculate storage usage as a percentage.
     *
     * @return int Storage usage percentage (0-100)
     */
    private function getStorageUsagePercentage(): int
    {
        $storageUsed = disk_total_space(storage_path()) - disk_free_space(storage_path());
        $totalSpace = disk_total_space(storage_path());

        if ($totalSpace === false || $storageUsed === false) {
            return 0;
        }

        return (int) round(($storageUsed / $totalSpace) * 100);
    }
}
