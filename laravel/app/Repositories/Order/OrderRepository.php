<?php

namespace App\Repositories\Order;

use App\Enums\ApiEnums;
use App\Models\Order\Order;
use App\Repositories\Base\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function findByUserUuid(string $userUuid): LengthAwarePaginator
    {
        return $this->model
            ->where('user_uuid', $userUuid)
            ->with(['orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(ApiEnums::DEFAULT_PAGINATION->value);
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return $this->model
            ->where('order_number', $orderNumber)
            ->with(['orderItems.product'])
            ->first();
    }
}
