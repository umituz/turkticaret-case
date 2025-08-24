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

/**
 * REST API Controller for Order Status management.
 * 
 * Handles order status updates and status history tracking with proper
 * authorization checks. Manages the complete order status lifecycle
 * including status transitions and history logging.
 *
 * @package App\Http\Controllers\Order
 */
class OrderStatusController extends BaseController
{
    /**
     * Create a new OrderStatusController instance.
     *
     * @param OrderStatusService $orderStatusService The order status service for status operations
     */
    public function __construct(protected OrderStatusService $orderStatusService) {}

    /**
     * Update the status of the specified order.
     *
     * @param Order $order The order model instance resolved by route model binding
     * @param OrderStatusUpdateRequest $request The validated request containing new status
     * @return JsonResponse JSON response containing updated order resource or error message
     */
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


    /**
     * Get the complete status history for the specified order.
     *
     * @param Order $order The order model instance resolved by route model binding
     * @return JsonResponse JSON response containing order status history with change details
     */
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