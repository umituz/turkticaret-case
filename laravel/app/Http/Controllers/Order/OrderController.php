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

class OrderController extends BaseController
{
    public function __construct(protected OrderService $orderService) {}

    public function index(): JsonResponse
    {
        $orders = $this->orderService->getUserOrders(auth()->id());
        
        return $this->ok(new OrderCollection($orders));
    }

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

    public function show(Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        $order->load(['orderItems.product']);
        
        return $this->ok(new OrderResource($order));
    }
}