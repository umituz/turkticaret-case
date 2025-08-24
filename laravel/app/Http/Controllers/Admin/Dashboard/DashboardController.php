<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Dashboard\DashboardResource;
use App\Services\Dashboard\DashboardService;

/**
 * REST API Controller for Admin Dashboard.
 * 
 * Provides comprehensive dashboard data and analytics for administrators
 * including system metrics, user statistics, and business intelligence data.
 *
 * @package App\Http\Controllers\Admin\Dashboard
 */
class DashboardController extends BaseController
{
    /**
     * Create a new DashboardController instance.
     *
     * @param DashboardService $dashboardService The dashboard service for analytics operations
     */
    public function __construct(protected DashboardService $dashboardService) {}

    /**
     * Get comprehensive dashboard data for administrators.
     *
     * @return DashboardResource Dashboard resource containing system metrics and analytics
     */
    public function index(): DashboardResource
    {
        $dashboardData = $this->dashboardService->getDashboardData();

        return new DashboardResource($dashboardData);
    }
}
