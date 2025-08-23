<?php

namespace App\Http\Controllers\Order;

use App\Enums\Order\OrderStatusEnum;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Order\OrderStatusUpdateRequest;
use App\Http\Resources\Order\OrderResource;
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

        $order->load(['statusHistories.changedBy:uuid,name,email']);

        $history = $order->statusHistories->map(function ($historyItem) {
            return [
                'uuid' => $historyItem->uuid,
                'old_status' => $historyItem->old_status?->value,
                'new_status' => $historyItem->new_status->value,
                'changed_by' => $historyItem->changedBy ? [
                    'uuid' => $historyItem->changedBy->uuid,
                    'name' => $historyItem->changedBy->name,
                    'email' => $historyItem->changedBy->email,
                ] : null,
                'notes' => $historyItem->notes,
                'changed_at' => $historyItem->created_at,
            ];
        });

        return $this->ok([
            'order_uuid' => $order->uuid,
            'current_status' => $order->status->value,
            'history' => $history
        ]);
    }
}