<?php

namespace App\Repositories\Order;

use App\Enums\ApiEnums;
use App\Enums\Order\OrderStatusEnum;
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

    public function findAllWithFilters(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model
            ->with(['user:uuid,name,email', 'orderItems'])
            ->orderBy('created_at', 'desc');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['user_uuid'])) {
            $query->where('user_uuid', $filters['user_uuid']);
        }

        if (!empty($filters['order_number'])) {
            $query->where('order_number', 'LIKE', '%' . $filters['order_number'] . '%');
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $perPage = !empty($filters['per_page']) ? (int) $filters['per_page'] : ApiEnums::DEFAULT_PAGINATION->value;

        return $query->paginate($perPage);
    }

    public function getOrderStatistics(): array
    {
        $totalOrders = $this->model->count();
        $pendingOrders = $this->model->where('status', OrderStatusEnum::PENDING)->count();
        $processingOrders = $this->model->where('status', OrderStatusEnum::PROCESSING)->count();
        $shippedOrders = $this->model->where('status', OrderStatusEnum::SHIPPED)->count();
        $deliveredOrders = $this->model->where('status', OrderStatusEnum::DELIVERED)->count();
        $cancelledOrders = $this->model->where('status', OrderStatusEnum::CANCELLED)->count();

        return [
            'total' => $totalOrders,
            'pending' => $pendingOrders,
            'processing' => $processingOrders,
            'shipped' => $shippedOrders,
            'delivered' => $deliveredOrders,
            'cancelled' => $cancelledOrders,
        ];
    }
}
