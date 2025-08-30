<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Repositories\Dashboard\DashboardRepositoryInterface;
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

        return [
            'stats' => $this->getStats(),
            'recent_activity' => $this->getRecentActivity()
        ];
    }

    /**
     * Generate statistical data comparing current and previous month metrics.
     *
     * @param Carbon $currentMonth The current month start date for metrics calculation
     * @param Carbon $previousMonth The previous month start date for comparison
     * @return array Array containing formatted statistics with percentage changes
     */
    private function getStats(): array
    {
        $totalUsers = $this->dashboardRepository->getTotalUsers();
        $totalOrders = $this->dashboardRepository->getTotalOrders();
        $totalProducts = $this->dashboardRepository->getTotalProducts();

        return [
            [
                'title' => 'Total Users',
                'value' => number_format($totalUsers),
                'description' => 'Registered users'
            ],
            [
                'title' => 'Orders',
                'value' => number_format($totalOrders),
                'description' => 'Total orders'
            ],
            [
                'title' => 'Products',
                'value' => number_format($totalProducts),
                'description' => 'Products in inventory'
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
                'message' => $user->name . ' joined Ecommerce',
                'timestamp' => $user->created_at->diffForHumans(),
                'user' => $user->name,
                'status' => 'info'
            ]);
        }

        return $activities->take(8)->values()->toArray();
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




}
