<?php

declare(strict_types=1);

namespace App\Repositories\Dashboard;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Contract for Dashboard repository implementations.
 * 
 * Defines the required methods for Dashboard data access layer operations
 * including statistical data retrieval, analytics queries, performance metrics,
 * and system status monitoring functionality.
 *
 * @package App\Repositories\Dashboard
 */
interface DashboardRepositoryInterface
{
    /**
     * Get the total number of users in the system.
     *
     * @return int Total user count
     */
    public function getTotalUsers(): int;
    
    /**
     * Get the number of users registered in the current month.
     *
     * @param Carbon $currentMonth The current month reference date
     * @return int Current month user registration count
     */
    public function getCurrentMonthUsers(Carbon $currentMonth): int;
    
    /**
     * Get the number of users registered in the previous month.
     *
     * @param Carbon $previousMonth The previous month start date
     * @param Carbon $currentMonth The current month start date  
     * @return int Previous month user registration count
     */
    public function getPreviousMonthUsers(Carbon $previousMonth, Carbon $currentMonth): int;
    
    /**
     * Get the number of orders placed in the current month.
     *
     * @param Carbon $currentMonth The current month reference date
     * @return int Current month order count
     */
    public function getCurrentMonthOrders(Carbon $currentMonth): int;
    
    /**
     * Get the number of orders placed in the previous month.
     *
     * @param Carbon $previousMonth The previous month start date
     * @param Carbon $currentMonth The current month start date
     * @return int Previous month order count
     */
    public function getPreviousMonthOrders(Carbon $previousMonth, Carbon $currentMonth): int;
    
    /**
     * Get the total number of products in the system.
     *
     * @return int Total product count
     */
    public function getTotalProducts(): int;
    
    /**
     * Get the number of products added in the current month.
     *
     * @param Carbon $currentMonth The current month reference date
     * @return int Current month product count
     */
    public function getCurrentMonthProducts(Carbon $currentMonth): int;
    
    /**
     * Get the total revenue generated in the current month.
     *
     * @param Carbon $currentMonth The current month reference date
     * @return float Current month revenue amount
     */
    public function getCurrentMonthRevenue(Carbon $currentMonth): float;
    
    /**
     * Get the total revenue generated in the previous month.
     *
     * @param Carbon $previousMonth The previous month start date
     * @param Carbon $currentMonth The current month start date
     * @return float Previous month revenue amount
     */
    public function getPreviousMonthRevenue(Carbon $previousMonth, Carbon $currentMonth): float;
    
    /**
     * Get recent order activities for dashboard display.
     *
     * @param int $limit Maximum number of activities to retrieve
     * @return Collection<int, mixed> Collection of recent order activities
     */
    public function getRecentOrderActivities(int $limit = 10): Collection;
    
    /**
     * Get recent user registrations for dashboard display.
     *
     * @param int $limit Maximum number of registrations to retrieve
     * @return Collection<int, mixed> Collection of recent user registrations
     */
    public function getRecentUserRegistrations(int $limit = 3): Collection;
    
    /**
     * Get recent product updates for dashboard display.
     *
     * @param int $limit Maximum number of product updates to retrieve
     * @return Collection<int, mixed> Collection of recent product updates
     */
    public function getRecentProductUpdates(int $limit = 2): Collection;
    
    /**
     * Check the current database connection status.
     *
     * @return string Database status indicator
     */
    public function checkDatabaseStatus(): string;
}