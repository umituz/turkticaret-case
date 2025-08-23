<?php

declare(strict_types=1);

namespace App\Repositories\Dashboard;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface DashboardRepositoryInterface
{
    public function getTotalUsers(): int;
    
    public function getCurrentMonthUsers(Carbon $currentMonth): int;
    
    public function getPreviousMonthUsers(Carbon $previousMonth, Carbon $currentMonth): int;
    
    public function getCurrentMonthOrders(Carbon $currentMonth): int;
    
    public function getPreviousMonthOrders(Carbon $previousMonth, Carbon $currentMonth): int;
    
    public function getTotalProducts(): int;
    
    public function getCurrentMonthProducts(Carbon $currentMonth): int;
    
    public function getCurrentMonthRevenue(Carbon $currentMonth): float;
    
    public function getPreviousMonthRevenue(Carbon $previousMonth, Carbon $currentMonth): float;
    
    public function getRecentOrderActivities(int $limit = 10): Collection;
    
    public function getRecentUserRegistrations(int $limit = 3): Collection;
    
    public function getRecentProductUpdates(int $limit = 2): Collection;
    
    public function checkDatabaseStatus(): string;
}