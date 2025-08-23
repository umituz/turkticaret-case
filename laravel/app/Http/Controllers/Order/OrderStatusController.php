<?php

namespace App\Http\Controllers\Order;

use App\Enums\Order\OrderStatusEnum;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Order\OrderStatusUpdateRequest;
use App\Http\Resources\Order\OrderResource;
use App\Http\Resources\Order\OrderStatusHistoryCollection;
use App\Models\Order\Order;
use App\Services\Order\OrderStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class OrderStatusController extends BaseController
{
    public function __construct(protected OrderStatusService $orderStatusService) {}

    public function update(Order $order, OrderStatusUpdateRequest $request): JsonResponse
    {
        Gate::authorize('updateStatus', $order);

        try {
            $newStatus = OrderStatusEnum::from($request->status);
            
            $updatedOrder = $this->orderStatusService->updateStatus($order, $newStatus);

            return $this->ok(new OrderResource($updatedOrder));
        } catch (\InvalidArgumentException $e) {
            return $this->error([$e->getMessage()], 'Failed to update order status', 422);
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Failed to update order status');
        }
    }


    public function getStatusHistory(Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        $order->load(['statusHistories.changedBy']);

        return $this->ok([
            'order_uuid' => $order->uuid,
            'current_status' => $order->status->value,
            'history' => new OrderStatusHistoryCollection($order->statusHistories)
        ]);
    }
}