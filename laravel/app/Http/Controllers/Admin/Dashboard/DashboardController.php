<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Dashboard\DashboardResource;
use App\Services\Dashboard\DashboardService;

class DashboardController extends BaseController
{
    public function __construct(protected DashboardService $dashboardService) {}

    public function index(): DashboardResource
    {
        $dashboardData = $this->dashboardService->getDashboardData();

        return new DashboardResource($dashboardData);
    }
}
