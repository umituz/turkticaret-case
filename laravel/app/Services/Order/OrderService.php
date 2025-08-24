<?php

namespace App\Services\Order;

use App\DTOs\Order\OrderCreateDTO;
use App\Jobs\Order\SendOrderConfirmedJob;
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

/**
 * Order Service for complex order business logic operations.
 * 
 * Handles order creation from cart, stock validation, inventory management,
 * transaction processing, and order confirmation. Implements comprehensive
 * business rules for order processing including stock validation and cart clearing.
 *
 * @package App\Services\Order
 */
class OrderService
{
    /**
     * Create a new OrderService instance.
     *
     * @param OrderRepositoryInterface $orderRepository The order repository for data operations
     * @param CartService $cartService The cart service for cart operations
     * @param ProductRepositoryInterface $productRepository The product repository for product data
     * @param ProductService $productService The product service for stock validation
     */
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected CartService $cartService,
        protected ProductRepositoryInterface $productRepository,
        protected ProductService $productService
    ) {}

    /**
     * Get paginated orders for a specific user.
     *
     * @param string $userUuid The UUID of the user to get orders for
     * @return LengthAwarePaginator Paginated collection of user orders
     */
    public function getUserOrders(string $userUuid): LengthAwarePaginator
    {
        return $this->orderRepository->findByUserUuid($userUuid);
    }

    /**
     * Get order status history for a specific order.
     *
     * @param Order $order The order to get status history for
     * @return array Order status history data
     */
    public function getOrderStatusHistory(Order $order): array
    {
        $history = [];
        
        // Order placed
        $history[] = [
            'status' => 'pending',
            'date' => $order->created_at,
            'description' => 'Order placed'
        ];
        
        // Order processing (if updated_at is different from created_at and status is not pending)
        if ($order->status->value !== 'pending' && $order->updated_at > $order->created_at) {
            $history[] = [
                'status' => 'processing',
                'date' => $order->updated_at,
                'description' => 'Order confirmed and processing'
            ];
        }
        
        // Order shipped
        if ($order->shipped_at) {
            $history[] = [
                'status' => 'shipped',
                'date' => $order->shipped_at,
                'description' => 'Order shipped'
            ];
        }
        
        // Order delivered
        if ($order->delivered_at) {
            $history[] = [
                'status' => 'delivered',
                'date' => $order->delivered_at,
                'description' => 'Order delivered'
            ];
        }
        
        // Current status (if different from the timeline above)
        $lastHistoryStatus = end($history)['status'] ?? 'pending';
        if ($order->status->value !== $lastHistoryStatus) {
            $history[] = [
                'status' => $order->status->value,
                'date' => $order->updated_at,
                'description' => 'Order status updated to ' . ucfirst($order->status->value)
            ];
        }
        
        return [
            'order_uuid' => $order->uuid,
            'current_status' => $order->status->value,
            'history' => $history
        ];
    }

    /**
     * Create a new order from user's cart with comprehensive validation.
     *
     * @param string $userUuid The UUID of the user creating the order
     * @param OrderCreateDTO $orderData The order data transfer object containing order details
     * @return Order The created order with loaded relationships
     * @throws EmptyCartException When the user's cart is empty
     * @throws InsufficientStockException When requested quantities exceed available stock
     */
    public function createOrderFromCart(string $userUuid, OrderCreateDTO $orderData): Order
    {
        return DB::transaction(function () use ($userUuid, $orderData) {
            $cart = $this->validateCartForOrder($userUuid);
            $this->validateCartStockAvailability($cart);
            $order = $this->createOrderFromCartData($userUuid, $cart, $orderData);
            $this->transferCartItemsToOrderAndReduceStock($order, $cart);
            $this->cartService->clearCart($userUuid);

            SendOrderConfirmedJob::dispatch($order);

            return $order->load(['orderItems.product']);
        });
    }

    /**
     * Validate that the user's cart is not empty before creating an order.
     *
     * @param string $userUuid The UUID of the user whose cart to validate
     * @return Cart The validated cart with items
     * @throws EmptyCartException When the cart is empty and cannot create an order
     */
    private function validateCartForOrder(string $userUuid): Cart
    {
        $cart = $this->cartService->getOrCreateCart($userUuid);

        if ($cart->cartItems->isEmpty()) {
            throw new EmptyCartException();
        }

        return $cart;
    }

    /**
     * Create the order record from cart data and order information.
     *
     * @param string $userUuid The UUID of the user creating the order
     * @param Cart $cart The validated cart containing items
     * @param OrderCreateDTO $orderData The order data transfer object
     * @return Order The created order instance
     */
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

    /**
     * Validate stock availability for all items in the cart.
     *
     * @param Cart $cart The cart to validate stock for
     * @return void
     * @throws OutOfStockException When any product is out of stock
     * @throws InsufficientStockException When any product has insufficient stock
     */
    private function validateCartStockAvailability(Cart $cart): void
    {
        foreach ($cart->cartItems as $cartItem) {
            $product = $this->productRepository->findByUuid($cartItem->product_uuid);
            $this->productService->validateStock($product, $cartItem->quantity);
        }
    }

    /**
     * Transfer cart items to order items and reduce product stock quantities.
     *
     * @param Order $order The order to create items for
     * @param Cart $cart The cart containing items to transfer
     * @return void
     * @throws InsufficientStockException When stock reduction fails due to insufficient inventory
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
