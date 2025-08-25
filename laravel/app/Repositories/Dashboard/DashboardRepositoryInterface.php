<?php

declare(strict_types=1);

namespace App\Repositories\Dashboard;

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
     * Get the total number of orders in the system.
     *
     * @return int Total order count
     */
    public function getTotalOrders(): int;
    
    /**
     * Get the total number of products in the system.
     *
     * @return int Total product count
     */
    public function getTotalProducts(): int;
    
    
    
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
    
    
}