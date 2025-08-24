<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Order;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Order\OrderCollection;
use App\Http\Resources\Order\OrderResource;
use App\Models\Order\Order;
use App\Services\Admin\Order\AdminOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * REST API Controller for Admin Order Management.
 * 
 * Provides comprehensive order management functionality for administrators
 * including viewing all orders, updating order statuses, and order analytics.
 *
 * @package App\Http\Controllers\Admin\Order
 */
class AdminOrderController extends BaseController
{
    /**
     * Create a new AdminOrderController instance.
     *
     * @param AdminOrderService $adminOrderService The admin order service for business logic operations
     */
    public function __construct(protected AdminOrderService $adminOrderService) {}

    /**
     * Display a listing of all orders with optional filters.
     *
     * @param Request $request The HTTP request containing optional filter parameters
     * @return JsonResponse JSON response containing the order collection
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'user_uuid', 'order_number', 'date_from', 'date_to', 'per_page']);
        $orders = $this->adminOrderService->getAllOrders($filters);
        
        return $this->ok(new OrderCollection($orders));
    }

    /**
     * Display the specified order with full details.
     *
     * @param Order $order The order model instance resolved by route model binding
     * @return JsonResponse JSON response containing the order resource with loaded relationships
     */
    public function show(Order $order): JsonResponse
    {
        $order->load(['orderItems.product', 'user:uuid,name,email']);
        
        return $this->ok(new OrderResource($order));
    }

    /**
     * Update the status of the specified order.
     *
     * @param Request $request The HTTP request containing the new status
     * @param Order $order The order model instance resolved by route model binding
     * @return JsonResponse JSON response indicating success or failure
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled,refunded'
        ]);

        $success = $this->adminOrderService->updateOrderStatus($order, $request->status);
        
        if ($success) {
            return $this->ok(['message' => 'Order status updated successfully']);
        }
        
        return $this->error(['Failed to update order status'], 'Update failed');
    }

    /**
     * Get order statistics for admin dashboard.
     *
     * @return JsonResponse JSON response containing order statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->adminOrderService->getOrderStatistics();
        
        return $this->ok($stats);
    }

    /**
     * Get order status history for the specified order.
     *
     * @param Order $order The order model instance resolved by route model binding
     * @return JsonResponse JSON response containing order status history
     */
    public function statusHistory(Order $order): JsonResponse
    {
        $history = $this->adminOrderService->getOrderStatusHistory($order);
        
        return $this->ok($history);
    }
}