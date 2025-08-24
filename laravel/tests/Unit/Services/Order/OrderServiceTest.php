<?php

namespace Tests\Unit\Services\Order;

use App\DTOs\Order\OrderCreateDTO;
use App\Enums\Order\OrderStatusEnum;
use App\Exceptions\Order\EmptyCartException;
use App\Exceptions\Product\InsufficientStockException;
use App\Jobs\Order\SendOrderConfirmedJob;
use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Models\Order\Order;
use App\Models\Product\Product;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Services\Cart\CartService;
use App\Services\Order\OrderService;
use App\Services\Product\ProductService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Base\UnitTestCase;

#[CoversClass(OrderService::class)]
class OrderServiceTest extends UnitTestCase
{
    private MockInterface $orderRepository;
    private MockInterface $cartService;
    private MockInterface $productRepository;
    private MockInterface $productService;
    private OrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->orderRepository = Mockery::mock(OrderRepositoryInterface::class);
        $this->cartService = Mockery::mock(CartService::class);
        $this->productRepository = Mockery::mock(ProductRepositoryInterface::class);
        $this->productService = Mockery::mock(ProductService::class);

        $this->service = new OrderService(
            $this->orderRepository,
            $this->cartService,
            $this->productRepository,
            $this->productService
        );
        
        // Mock DB facade transaction method
        DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
            return $callback();
        });
        
        // Mock Queue facade for job dispatching
        Queue::fake();
    }

    #[Test]
    public function it_gets_user_orders(): void
    {
        $userUuid = 'user-uuid-123';
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->orderRepository->shouldReceive('findByUserUuid')
            ->with($userUuid)
            ->once()
            ->andReturn($paginator);

        $result = $this->service->getUserOrders($userUuid);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame($paginator, $result);
    }

    #[Test]
    public function it_creates_order_from_cart_successfully(): void
    {
        $userUuid = 'user-uuid-123';
        $productUuid = 'product-uuid-123';
        
        $orderData = new OrderCreateDTO(
            shipping_address: '123 Test Street, Test City',
            notes: 'Test order notes'
        );

        // Mock cart with items
        $cartItem = Mockery::mock(CartItem::class);
        $cartItem->product_uuid = $productUuid;
        $cartItem->quantity = 2;
        $cartItem->price = 1000;

        $product = Mockery::mock(Product::class);
        $product->shouldReceive('getAttribute')->with('stock_quantity')->andReturn(10);
        $product->shouldReceive('hasStock')->with(2)->andReturn(true);
        $cartItem->shouldReceive('getAttribute')->with('product')->andReturn($product);

        $cart = Mockery::mock(Cart::class);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn(new Collection([$cartItem]));

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('load')->with(['orderItems.product'])->andReturnSelf();

        // Mock service calls
        $this->cartService->shouldReceive('getUserCart')->with($userUuid)->andReturn($cart);
        $this->orderRepository->shouldReceive('create')->andReturn($order);
        $this->orderRepository->shouldReceive('createOrderItem')->andReturn(true);
        $this->productService->shouldReceive('reduceStock')->with($productUuid, 2)->andReturn(true);
        $this->cartService->shouldReceive('clearCart')->with($userUuid)->andReturn(true);

        $result = $this->service->createOrderFromCart($userUuid, $orderData);

        $this->assertInstanceOf(Order::class, $result);
    }

    #[Test]
    public function it_validates_stock_availability(): void
    {
        $userUuid = 'user-uuid-123';
        $productUuid = 'product-uuid-123';
        
        $orderData = new OrderCreateDTO(
            shipping_address: '123 Test Street, Test City',
            notes: 'Test order notes'
        );

        // Mock cart item with insufficient stock
        $cartItem = Mockery::mock(CartItem::class);
        $cartItem->product_uuid = $productUuid;
        $cartItem->quantity = 5;

        $product = Mockery::mock(Product::class);
        $product->shouldReceive('getAttribute')->with('stock_quantity')->andReturn(2);
        $product->shouldReceive('hasStock')->with(5)->andReturn(false);
        $cartItem->shouldReceive('getAttribute')->with('product')->andReturn($product);

        $cart = Mockery::mock(Cart::class);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn(new Collection([$cartItem]));

        $this->cartService->shouldReceive('getUserCart')->with($userUuid)->andReturn($cart);

        $this->expectException(InsufficientStockException::class);
        
        $this->service->createOrderFromCart($userUuid, $orderData);
    }

    #[Test]
    public function it_throws_exception_for_empty_cart(): void
    {
        $userUuid = 'user-uuid-123';
        
        $orderData = new OrderCreateDTO(
            shipping_address: '123 Test Street, Test City',
            notes: 'Test order notes'
        );

        // Mock empty cart
        $cart = Mockery::mock(Cart::class);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn(new Collection([]));

        $this->cartService->shouldReceive('getUserCart')->with($userUuid)->andReturn($cart);

        $this->expectException(EmptyCartException::class);
        
        $this->service->createOrderFromCart($userUuid, $orderData);
    }

    #[Test]
    public function it_gets_order_by_id(): void
    {
        $orderUuid = 'order-uuid-123';
        $order = Mockery::mock(Order::class);

        $this->orderRepository->shouldReceive('findById')
            ->with($orderUuid)
            ->once()
            ->andReturn($order);

        $result = $this->service->getOrderById($orderUuid);

        $this->assertSame($order, $result);
    }

    #[Test]
    public function it_updates_order_status(): void
    {
        $orderUuid = 'order-uuid-123';
        $newStatus = OrderStatusEnum::CONFIRMED;
        $order = Mockery::mock(Order::class);

        $this->orderRepository->shouldReceive('findById')
            ->with($orderUuid)
            ->once()
            ->andReturn($order);

        $this->orderRepository->shouldReceive('update')
            ->with($order, ['status' => $newStatus])
            ->once()
            ->andReturn($order);

        $result = $this->service->updateOrderStatus($orderUuid, $newStatus);

        $this->assertSame($order, $result);
    }

    #[Test]
    public function it_cancels_order_and_restores_stock(): void
    {
        $orderUuid = 'order-uuid-123';
        $productUuid = 'product-uuid-123';

        $orderItem = Mockery::mock();
        $orderItem->product_uuid = $productUuid;
        $orderItem->quantity = 3;

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('status')->andReturn(OrderStatusEnum::PENDING);
        $order->shouldReceive('getAttribute')->with('orderItems')->andReturn(new Collection([$orderItem]));

        $this->orderRepository->shouldReceive('findById')->with($orderUuid)->andReturn($order);
        $this->orderRepository->shouldReceive('update')
            ->with($order, ['status' => OrderStatusEnum::CANCELLED])
            ->andReturn($order);

        $this->productService->shouldReceive('restoreStock')
            ->with($productUuid, 3)
            ->once()
            ->andReturn(true);

        $result = $this->service->cancelOrder($orderUuid);

        $this->assertSame($order, $result);
    }

    #[Test]
    public function it_calculates_order_total_correctly(): void
    {
        $userUuid = 'user-uuid-123';
        $productUuid = 'product-uuid-123';
        
        $orderData = new OrderCreateDTO(
            shipping_address: '123 Test Street, Test City',
            notes: 'Test order notes'
        );

        // Mock cart item
        $cartItem = Mockery::mock(CartItem::class);
        $cartItem->product_uuid = $productUuid;
        $cartItem->quantity = 2;
        $cartItem->price = 1500; // 15.00 in cents

        $product = Mockery::mock(Product::class);
        $product->shouldReceive('getAttribute')->with('stock_quantity')->andReturn(10);
        $product->shouldReceive('hasStock')->with(2)->andReturn(true);
        $cartItem->shouldReceive('getAttribute')->with('product')->andReturn($product);

        $cart = Mockery::mock(Cart::class);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn(new Collection([$cartItem]));

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('load')->with(['orderItems.product'])->andReturnSelf();

        // Mock service calls
        $this->cartService->shouldReceive('getUserCart')->with($userUuid)->andReturn($cart);
        
        // Expect order creation with correct total (1500 * 2 = 3000)
        $this->orderRepository->shouldReceive('create')
            ->with(Mockery::on(function ($data) {
                return $data['total_amount'] === 3000;
            }))
            ->andReturn($order);

        $this->orderRepository->shouldReceive('createOrderItem')->andReturn(true);
        $this->productService->shouldReceive('reduceStock')->with($productUuid, 2)->andReturn(true);
        $this->cartService->shouldReceive('clearCart')->with($userUuid)->andReturn(true);

        $result = $this->service->createOrderFromCart($userUuid, $orderData);

        $this->assertInstanceOf(Order::class, $result);
    }
}