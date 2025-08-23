<?php

namespace App\Services\Order;

use App\DTOs\Order\OrderCreateDTO;
use App\Jobs\Order\SendOrderConfirmationJob;
use App\Models\Order\Order;
use App\Models\Cart\Cart;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Services\Cart\CartService;
use App\Services\Product\ProductService;
use App\Exceptions\Order\EmptyCartException;
use App\Exceptions\Product\InsufficientStockException;
use App\Enums\Order\OrderStatusEnum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected CartService $cartService,
        protected ProductRepositoryInterface $productRepository,
        protected ProductService $productService
    ) {}

    public function getUserOrders(string $userUuid): LengthAwarePaginator
    {
        return $this->orderRepository->findByUserUuid($userUuid);
    }

    /**
     * Create order from cart using DTO
     */
    public function createOrderFromCart(string $userUuid, OrderCreateDTO $orderData): Order
    {
        return DB::transaction(function () use ($userUuid, $orderData) {
            $cart = $this->validateCartForOrder($userUuid);
            $this->validateCartStockAvailability($cart);
            $order = $this->createOrderFromCartData($userUuid, $cart, $orderData);
            $this->transferCartItemsToOrderAndReduceStock($order, $cart);
            $this->cartService->clearCart($userUuid);

            SendOrderConfirmationJob::dispatch($order);

            return $order->load(['orderItems.product']);
        });
    }

    /**
     * @throws EmptyCartException
     */
    private function validateCartForOrder(string $userUuid): Cart
    {
        $cart = $this->cartService->getOrCreateCart($userUuid);

        if ($cart->cartItems->isEmpty()) {
            throw new EmptyCartException();
        }

        return $cart;
    }

    private function createOrderFromCartData(string $userUuid, Cart $cart, OrderCreateDTO $orderData): Order
    {
        $totalAmount = $cart->cartItems->sum('total_price');

        return $this->orderRepository->create([
            'user_uuid' => $userUuid,
            'status' => OrderStatusEnum::PENDING->value,
            'total_amount' => $totalAmount,
            'shipping_address' => $orderData->shipping_address,
            'notes' => $orderData->notes,
        ]);
    }

    private function validateCartStockAvailability(Cart $cart): void
    {
        foreach ($cart->cartItems as $cartItem) {
            $product = $this->productRepository->findByUuid($cartItem->product_uuid);
            $this->productService->validateStock($product, $cartItem->quantity);
        }
    }

    /**
     * @throws InsufficientStockException
     */
    private function transferCartItemsToOrderAndReduceStock(Order $order, Cart $cart): void
    {
        foreach ($cart->cartItems as $cartItem) {
            $product = $this->productRepository->findByUuid($cartItem->product_uuid);

            if (!$product->decreaseStock($cartItem->quantity)) {
                throw new InsufficientStockException(
                    $product->name,
                    $cartItem->quantity,
                    $product->stock_quantity
                );
            }

            $order->orderItems()->create([
                'product_uuid' => $cartItem->product_uuid,
                'product_name' => $cartItem->product->name,
                'quantity' => $cartItem->quantity,
                'unit_price' => $cartItem->unit_price,
                'total_price' => $cartItem->total_price,
            ]);
        }
    }

}
