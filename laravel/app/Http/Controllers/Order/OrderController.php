<?php

namespace App\Http\Controllers\Order;

use App\DTOs\Order\OrderCreateDTO;
use App\Exceptions\Order\EmptyCartException;
use App\Exceptions\Order\MinimumOrderAmountException;
use App\Exceptions\Product\InsufficientStockException;
use App\Helpers\AuthHelper;
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
        $orders = $this->orderService->getUserOrders(AuthHelper::getUserUuid());

        return $this->ok(new OrderCollection($orders));
    }

    /**
     * Create a new order from the user's cart.
     *
     * @param OrderCreateRequest $request The validated request containing order creation data
     * @return JsonResponse JSON response containing the created order resource with 201 status, or error response if creation fails
     * @throws EmptyCartException
     * @throws MinimumOrderAmountException
     * @throws InsufficientStockException
     */
    public function store(OrderCreateRequest $request): JsonResponse
    {
        $orderData = OrderCreateDTO::fromArray($request->validated());
        $order = $this->orderService->createOrderFromCart(AuthHelper::getUserUuid(), $orderData);

        return $this->created(new OrderResource($order));
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
        $order = $this->orderService->getOrderWithRelations($order);

        return $this->ok(new OrderResource($order));
    }

    /**
     * Get order status history for the specified order.
     *
     * @param Order $order The order model instance resolved by route model binding
     * @return JsonResponse JSON response containing order status history
     */
    public function statusHistory(Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        $history = $this->orderService->getOrderStatusHistory($order);

        return $this->ok($history);
    }
}
