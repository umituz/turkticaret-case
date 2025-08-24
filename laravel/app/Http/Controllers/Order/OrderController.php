<?php

namespace App\Http\Controllers\Order;

use App\DTOs\Order\OrderCreateDTO;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Order\OrderCreateRequest;
use App\Http\Resources\Order\OrderCollection;
use App\Http\Resources\Order\OrderResource;
use App\Models\Order\Order;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

/**
 * REST API Controller for Order management.
 * 
 * Handles order operations including creating orders from cart, viewing user orders,
 * and order details with authorization checks. Manages the complete order lifecycle
 * from cart conversion to order completion.
 *
 * @package App\Http\Controllers\Order
 */
class OrderController extends BaseController
{
    /**
     * Create a new OrderController instance.
     *
     * @param OrderService $orderService The order service for business logic operations
     */
    public function __construct(protected OrderService $orderService) {}

    /**
     * Display a listing of the authenticated user's orders.
     *
     * @return JsonResponse JSON response containing the user's order collection
     */
    public function index(): JsonResponse
    {
        $orders = $this->orderService->getUserOrders(auth()->id());
        
        return $this->ok(new OrderCollection($orders));
    }

    /**
     * Create a new order from the user's cart.
     *
     * @param OrderCreateRequest $request The validated request containing order creation data
     * @return JsonResponse JSON response containing the created order resource with 201 status, or error response if creation fails
     */
    public function store(OrderCreateRequest $request): JsonResponse
    {
        try {
            $orderData = OrderCreateDTO::fromArray($request->validated());
            
            $order = $this->orderService->createOrderFromCart(
                auth()->id(),
                $orderData
            );

            return $this->created(new OrderResource($order));
        } catch (\Exception $e) {
            return $this->error([$e->getMessage()], 'Failed to create order');
        }
    }

    /**
     * Display the specified order with authorization check.
     *
     * @param Order $order The order model instance resolved by route model binding
     * @return JsonResponse JSON response containing the order resource with loaded relationships
     */
    public function show(Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        $order->load(['orderItems.product']);
        
        return $this->ok(new OrderResource($order));
    }
}