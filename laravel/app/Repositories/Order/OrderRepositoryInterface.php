<?php

namespace App\Repositories\Order;

use App\Models\Order\Order;
use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    public function findByUserUuid(string $userUuid): LengthAwarePaginator;
    
    public function findByOrderNumber(string $orderNumber): ?Order;
    
    public function findAllWithFilters(array $filters = []): LengthAwarePaginator;
    
    public function getOrderStatistics(): array;
}