<?php

namespace App\Services\Order;

use App\DTOs\Order\OrderCreateDTO;
use App\Enums\Order\OrderEnum;
use App\Exceptions\Product\OutOfStockException;
use App\Notifications\Order\OrderConfirmedNotification;
use App\Models\Order\Order;
use App\Models\Cart\Cart;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Services\Cart\CartService;
use App\Services\Product\ProductService;
use App\Exceptions\Order\EmptyCartException;
use App\Exceptions\Order\MinimumOrderAmountException;
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
     * @param OrderStatusHistoryService $historyService The order status history service
     */
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected CartService $cartService,
        protected ProductRepositoryInterface $productRepository,
        protected ProductService $productService,
        protected OrderStatusHistoryService $historyService
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
        return $this->historyService->buildHistory($order);
    }

    /**
     * Create a new order from user's cart with comprehensive validation.
     *
     * @param string $userUuid The UUID of the user creating the order
     * @param OrderCreateDTO $orderData The order data transfer object containing order details
     * @return Order The created order with loaded relationships
     * @throws EmptyCartException When the user's cart is empty
     * @throws MinimumOrderAmountException When cart total is below minimum order amount
     * @throws InsufficientStockException When requested quantities exceed available stock
     */
    public function createOrderFromCart(string $userUuid, OrderCreateDTO $orderData): Order
    {
        return DB::transaction(function () use ($userUuid, $orderData) {
            $cart = $this->validateCartForOrder($userUuid);
            $this->validateMinimumOrderAmount($cart);
            $this->validateCartStockAvailability($cart);
            $order = $this->createOrderFromCartData($userUuid, $cart, $orderData);
            $this->transferCartItemsToOrderAndReduceStock($order, $cart);
            $this->cartService->clearCart($userUuid);

            $order->user->notify(new OrderConfirmedNotification($order));

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
     * Validate that the cart total meets the minimum order amount requirement.
     *
     * @param Cart $cart The cart to validate minimum amount for
     * @return void
     * @throws MinimumOrderAmountException When cart total is below minimum order amount
     */
    private function validateMinimumOrderAmount(Cart $cart): void
    {
        $cartItems = $cart->relationLoaded('cartItems') ? $cart->cartItems : collect([]);
        $totalAmount = $cartItems->sum('total_price');
        $minimumAmount = OrderEnum::getMinimumOrderAmountCents();

        if ($totalAmount < $minimumAmount) {
            throw new MinimumOrderAmountException($totalAmount, $minimumAmount);
        }
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
        $cartItems = $cart->relationLoaded('cartItems') ? $cart->cartItems : collect([]);
        $totalAmount = $cartItems->sum('total_price');

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

    /**
     * Get order with loaded relationships for display.
     *
     * @param Order $order The order to load relationships for
     * @return Order The order with loaded relationships
     */
    public function getOrderWithRelations(Order $order): Order
    {
        return $order->load(['orderItems.product']);
    }

    /**
     * Get order statistics and metrics for dashboard display.
     *
     * @return array Array containing order statistics and metrics
     */
    public function getOrderStatistics(): array
    {
        return $this->orderRepository->getOrderStatistics();
    }
}
