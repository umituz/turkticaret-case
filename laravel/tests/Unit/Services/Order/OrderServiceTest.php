<?php

namespace Tests\Unit\Services\Order;

use App\DTOs\Order\OrderCreateDTO;
use App\Enums\Order\OrderStatusEnum;
use App\Exceptions\Order\EmptyCartException;
use App\Exceptions\Product\InsufficientStockException;
use App\Jobs\Order\SendOrderConfirmationJob;
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
use Tests\Base\BaseServiceUnitTest;

#[CoversClass(OrderService::class)]
class OrderServiceTest extends BaseServiceUnitTest
{
    private MockInterface $orderRepository;
    private MockInterface $cartService;
    private MockInterface $productRepository;
    private MockInterface $productService;

    protected function getServiceClass(): string
    {
        return OrderService::class;
    }

    protected function getServiceDependencies(): array
    {
        $this->orderRepository = $this->mock(OrderRepositoryInterface::class);
        $this->cartService = $this->mock(CartService::class);
        $this->productRepository = $this->mock(ProductRepositoryInterface::class);
        $this->productService = $this->mock(ProductService::class);

        return [
            $this->orderRepository,
            $this->cartService,
            $this->productRepository,
            $this->productService,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        
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
        $userUuid = $this->getTestUserUuid();
        $paginator = $this->mockPaginator([]);

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
        $userUuid = $this->getTestUserUuid();
        $productUuid = $this->getTestEntityUuid();
        
        $orderData = new OrderCreateDTO(
            shipping_address: '123 Test Street, Test City',
            notes: 'Test order notes'
        );

        // Mock cart with items
        $cartItem = $this->mockTypedModel(CartItem::class, [
            'product_uuid' => $productUuid,
            'quantity' => 2,
            'unit_price' => 1000,
            'total_price' => 2000
        ]);

        $product = $this->mockTypedModel(Product::class, [
            'uuid' => $productUuid,
            'name' => 'Test Product'
        ]);

        $cartItem->shouldReceive('getAttribute')->with('product')->andReturn($product);

        $cartItems = $this->mockCollection([$cartItem]);
        $cartItems->shouldReceive('sum')->with('total_price')->andReturn(2000);

        $cart = $this->mockTypedModel(Cart::class, [
            'user_uuid' => $userUuid
        ]);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn($cartItems);

        // Mock order
        $order = $this->mockTypedModel(Order::class, [
            'uuid' => 'test-order-uuid',
            'user_uuid' => $userUuid,
            'total_amount' => 2000
        ]);

        $orderItemsRelation = Mockery::mock();
        $order->shouldReceive('orderItems')->andReturn($orderItemsRelation);
        $order->shouldReceive('load')
            ->with(['orderItems.product'])
            ->andReturn($order);

        // Set up expectations
        $this->cartService->shouldReceive('getOrCreateCart')
            ->with($userUuid)
            ->andReturn($cart);

        $this->productRepository->shouldReceive('findByUuid')
            ->with($productUuid)
            ->andReturn($product);

        $this->productService->shouldReceive('validateStock')
            ->with($product, 2)
            ->andReturnTrue();

        $this->orderRepository->shouldReceive('create')
            ->with([
                'user_uuid' => $userUuid,
                'status' => OrderStatusEnum::PENDING->value,
                'total_amount' => 2000,
                'shipping_address' => '123 Test Street, Test City',
                'notes' => 'Test order notes',
            ])
            ->andReturn($order);

        $product->shouldReceive('decreaseStock')
            ->with(2)
            ->andReturn(true);

        $orderItemsRelation->shouldReceive('create')
            ->with([
                'product_uuid' => $productUuid,
                'product_name' => 'Test Product',
                'quantity' => 2,
                'unit_price' => 1000,
                'total_price' => 2000,
            ])
            ->andReturn($this->mockTypedModel(\App\Models\Order\OrderItem::class));

        $this->cartService->shouldReceive('clearCart')
            ->with($userUuid)
            ->andReturnNull();

        $result = $this->service->createOrderFromCart($userUuid, $orderData);

        $this->assertSame($order, $result);
        
        // Verify job was dispatched
        Queue::assertPushed(SendOrderConfirmationJob::class, function ($job) use ($order) {
            return $job->order === $order;
        });
    }

    #[Test]
    public function it_throws_empty_cart_exception_when_cart_is_empty(): void
    {
        $userUuid = $this->getTestUserUuid();
        
        $orderData = new OrderCreateDTO(
            shipping_address: '123 Test Street, Test City'
        );

        $emptyCartItems = $this->mockCollection([]);
        $cart = $this->mockTypedModel(Cart::class, [
            'user_uuid' => $userUuid
        ]);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn($emptyCartItems);

        $this->cartService->shouldReceive('getOrCreateCart')
            ->with($userUuid)
            ->andReturn($cart);

        $this->expectException(EmptyCartException::class);

        $this->service->createOrderFromCart($userUuid, $orderData);
    }

    #[Test]
    public function it_validates_stock_availability_before_creating_order(): void
    {
        $userUuid = $this->getTestUserUuid();
        $productUuid = $this->getTestEntityUuid();
        
        $orderData = new OrderCreateDTO(
            shipping_address: '123 Test Street, Test City'
        );

        $cartItem = $this->mockTypedModel(CartItem::class, [
            'product_uuid' => $productUuid,
            'quantity' => 5,
            'total_price' => 5000
        ]);

        $product = $this->mockTypedModel(Product::class, [
            'uuid' => $productUuid,
            'name' => 'Test Product'
        ]);

        $cartItems = $this->mockCollection([$cartItem]);
        $cart = $this->mockTypedModel(Cart::class, [
            'user_uuid' => $userUuid
        ]);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn($cartItems);

        $this->cartService->shouldReceive('getOrCreateCart')
            ->with($userUuid)
            ->andReturn($cart);

        $this->productRepository->shouldReceive('findByUuid')
            ->with($productUuid)
            ->andReturn($product);

        $this->productService->shouldReceive('validateStock')
            ->with($product, 5)
            ->andThrow(new InsufficientStockException('Test Product', 5, 3));

        $this->expectException(InsufficientStockException::class);

        $this->service->createOrderFromCart($userUuid, $orderData);
    }

    #[Test]
    public function it_throws_insufficient_stock_exception_when_decreasing_stock_fails(): void
    {
        $userUuid = $this->getTestUserUuid();
        $productUuid = $this->getTestEntityUuid();
        
        $orderData = new OrderCreateDTO(
            shipping_address: '123 Test Street, Test City'
        );

        $cartItem = $this->mockTypedModel(CartItem::class, [
            'product_uuid' => $productUuid,
            'quantity' => 2,
            'unit_price' => 1000,
            'total_price' => 2000
        ]);

        $product = $this->mockTypedModel(Product::class, [
            'uuid' => $productUuid,
            'name' => 'Test Product',
            'stock_quantity' => 1
        ]);

        $cartItem->shouldReceive('getAttribute')->with('product')->andReturn($product);

        $cartItems = $this->mockCollection([$cartItem]);
        $cartItems->shouldReceive('sum')->with('total_price')->andReturn(2000);

        $cart = $this->mockTypedModel(Cart::class, [
            'user_uuid' => $userUuid
        ]);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn($cartItems);

        $order = $this->mockTypedModel(Order::class, [
            'uuid' => 'test-order-uuid',
            'user_uuid' => $userUuid
        ]);

        $orderItemsRelation = Mockery::mock();
        $order->shouldReceive('orderItems')->andReturn($orderItemsRelation);

        $this->cartService->shouldReceive('getOrCreateCart')
            ->with($userUuid)
            ->andReturn($cart);

        $this->productRepository->shouldReceive('findByUuid')
            ->with($productUuid)
            ->andReturn($product);

        $this->productService->shouldReceive('validateStock')
            ->with($product, 2)
            ->andReturnTrue();

        $this->orderRepository->shouldReceive('create')
            ->andReturn($order);

        $product->shouldReceive('decreaseStock')
            ->with(2)
            ->andReturn(false); // Simulate stock decrease failure

        $this->expectException(InsufficientStockException::class);

        $this->service->createOrderFromCart($userUuid, $orderData);
    }

    #[Test]
    public function it_handles_multiple_cart_items_in_order_creation(): void
    {
        $userUuid = $this->getTestUserUuid();
        $product1Uuid = $this->getTestEntityUuid();
        $product2Uuid = 'test-product-uuid-2';
        
        $orderData = new OrderCreateDTO(
            shipping_address: '123 Test Street, Test City'
        );

        // First cart item
        $cartItem1 = $this->mockTypedModel(CartItem::class, [
            'product_uuid' => $product1Uuid,
            'quantity' => 2,
            'unit_price' => 1000,
            'total_price' => 2000
        ]);

        $product1 = $this->mockTypedModel(Product::class, [
            'uuid' => $product1Uuid,
            'name' => 'Test Product 1'
        ]);

        $cartItem1->shouldReceive('getAttribute')->with('product')->andReturn($product1);

        // Second cart item
        $cartItem2 = $this->mockTypedModel(CartItem::class, [
            'product_uuid' => $product2Uuid,
            'quantity' => 1,
            'unit_price' => 500,
            'total_price' => 500
        ]);

        $product2 = $this->mockTypedModel(Product::class, [
            'uuid' => $product2Uuid,
            'name' => 'Test Product 2'
        ]);

        $cartItem2->shouldReceive('getAttribute')->with('product')->andReturn($product2);

        $cartItems = $this->mockCollection([$cartItem1, $cartItem2]);
        $cartItems->shouldReceive('sum')->with('total_price')->andReturn(2500);

        $cart = $this->mockTypedModel(Cart::class, [
            'user_uuid' => $userUuid
        ]);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn($cartItems);

        $order = $this->mockTypedModel(Order::class, [
            'uuid' => 'test-order-uuid',
            'user_uuid' => $userUuid
        ]);

        $orderItemsRelation = Mockery::mock();
        $order->shouldReceive('orderItems')->andReturn($orderItemsRelation);
        $order->shouldReceive('load')
            ->with(['orderItems.product'])
            ->andReturn($order);

        $this->cartService->shouldReceive('getOrCreateCart')
            ->with($userUuid)
            ->andReturn($cart);

        $this->productRepository->shouldReceive('findByUuid')
            ->with($product1Uuid)
            ->andReturn($product1);
        
        $this->productRepository->shouldReceive('findByUuid')
            ->with($product2Uuid)
            ->andReturn($product2);

        $this->productService->shouldReceive('validateStock')
            ->with($product1, 2)
            ->andReturnTrue();
        
        $this->productService->shouldReceive('validateStock')
            ->with($product2, 1)
            ->andReturnTrue();

        $this->orderRepository->shouldReceive('create')
            ->with([
                'user_uuid' => $userUuid,
                'status' => OrderStatusEnum::PENDING->value,
                'total_amount' => 2500,
                'shipping_address' => '123 Test Street, Test City',
                'notes' => null,
            ])
            ->andReturn($order);

        $product1->shouldReceive('decreaseStock')
            ->with(2)
            ->andReturn(true);
        
        $product2->shouldReceive('decreaseStock')
            ->with(1)
            ->andReturn(true);

        $orderItemsRelation->shouldReceive('create')
            ->with([
                'product_uuid' => $product1Uuid,
                'product_name' => 'Test Product 1',
                'quantity' => 2,
                'unit_price' => 1000,
                'total_price' => 2000,
            ])
            ->andReturn($this->mockTypedModel(\App\Models\Order\OrderItem::class));

        $orderItemsRelation->shouldReceive('create')
            ->with([
                'product_uuid' => $product2Uuid,
                'product_name' => 'Test Product 2',
                'quantity' => 1,
                'unit_price' => 500,
                'total_price' => 500,
            ])
            ->andReturn($this->mockTypedModel(\App\Models\Order\OrderItem::class));

        $this->cartService->shouldReceive('clearCart')
            ->with($userUuid)
            ->andReturnNull();

        $result = $this->service->createOrderFromCart($userUuid, $orderData);

        $this->assertSame($order, $result);
    }

    #[Test]
    public function it_clears_cart_after_successful_order_creation(): void
    {
        $userUuid = $this->getTestUserUuid();
        $productUuid = $this->getTestEntityUuid();
        
        $orderData = new OrderCreateDTO(
            shipping_address: '123 Test Street, Test City'
        );

        $cartItem = $this->mockTypedModel(CartItem::class, [
            'product_uuid' => $productUuid,
            'quantity' => 1,
            'unit_price' => 1000,
            'total_price' => 1000
        ]);

        $product = $this->mockTypedModel(Product::class, [
            'uuid' => $productUuid,
            'name' => 'Test Product'
        ]);

        $cartItem->shouldReceive('getAttribute')->with('product')->andReturn($product);

        $cartItems = $this->mockCollection([$cartItem]);
        $cartItems->shouldReceive('sum')->with('total_price')->andReturn(1000);

        $cart = $this->mockTypedModel(Cart::class, [
            'user_uuid' => $userUuid
        ]);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn($cartItems);

        $order = $this->mockTypedModel(Order::class, [
            'uuid' => 'test-order-uuid',
            'user_uuid' => $userUuid
        ]);

        $orderItemsRelation = Mockery::mock();
        $order->shouldReceive('orderItems')->andReturn($orderItemsRelation);
        $order->shouldReceive('load')
            ->with(['orderItems.product'])
            ->andReturn($order);

        $this->cartService->shouldReceive('getOrCreateCart')
            ->with($userUuid)
            ->andReturn($cart);

        $this->productRepository->shouldReceive('findByUuid')
            ->with($productUuid)
            ->andReturn($product);

        $this->productService->shouldReceive('validateStock')
            ->with($product, 1)
            ->andReturnTrue();

        $this->orderRepository->shouldReceive('create')
            ->andReturn($order);

        $product->shouldReceive('decreaseStock')
            ->with(1)
            ->andReturn(true);

        $orderItemsRelation->shouldReceive('create')
            ->andReturn($this->mockTypedModel(\App\Models\Order\OrderItem::class));

        $this->cartService->shouldReceive('clearCart')
            ->with($userUuid)
            ->once()
            ->andReturnNull();

        $this->service->createOrderFromCart($userUuid, $orderData);

        // Test passes if clearCart is called once
        $this->assertTrue(true);
    }

    #[Test]
    public function it_dispatches_order_confirmation_job_after_successful_creation(): void
    {
        $userUuid = $this->getTestUserUuid();
        $productUuid = $this->getTestEntityUuid();
        
        $orderData = new OrderCreateDTO(
            shipping_address: '123 Test Street, Test City'
        );

        $cartItem = $this->mockTypedModel(CartItem::class, [
            'product_uuid' => $productUuid,
            'quantity' => 1,
            'unit_price' => 1000,
            'total_price' => 1000
        ]);

        $product = $this->mockTypedModel(Product::class, [
            'uuid' => $productUuid,
            'name' => 'Test Product'
        ]);

        $cartItem->shouldReceive('getAttribute')->with('product')->andReturn($product);

        $cartItems = $this->mockCollection([$cartItem]);
        $cartItems->shouldReceive('sum')->with('total_price')->andReturn(1000);

        $cart = $this->mockTypedModel(Cart::class, [
            'user_uuid' => $userUuid
        ]);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn($cartItems);

        $order = $this->mockTypedModel(Order::class, [
            'uuid' => 'test-order-uuid',
            'user_uuid' => $userUuid
        ]);

        $orderItemsRelation = Mockery::mock();
        $order->shouldReceive('orderItems')->andReturn($orderItemsRelation);
        $order->shouldReceive('load')
            ->with(['orderItems.product'])
            ->andReturn($order);

        $this->cartService->shouldReceive('getOrCreateCart')
            ->with($userUuid)
            ->andReturn($cart);

        $this->productRepository->shouldReceive('findByUuid')
            ->with($productUuid)
            ->andReturn($product);

        $this->productService->shouldReceive('validateStock')
            ->with($product, 1)
            ->andReturnTrue();

        $this->orderRepository->shouldReceive('create')
            ->andReturn($order);

        $product->shouldReceive('decreaseStock')
            ->with(1)
            ->andReturn(true);

        $orderItemsRelation->shouldReceive('create')
            ->andReturn($this->mockTypedModel(\App\Models\Order\OrderItem::class));

        $this->cartService->shouldReceive('clearCart')
            ->with($userUuid)
            ->andReturnNull();

        $this->service->createOrderFromCart($userUuid, $orderData);

        Queue::assertPushed(SendOrderConfirmationJob::class, 1);
        Queue::assertPushed(SendOrderConfirmationJob::class, function ($job) use ($order) {
            return $job->order === $order;
        });
    }

    #[Test]
    public function it_creates_order_with_minimal_data(): void
    {
        $userUuid = $this->getTestUserUuid();
        $productUuid = $this->getTestEntityUuid();
        
        $orderData = new OrderCreateDTO(
            shipping_address: '123 Test Street, Test City'
            // No notes provided
        );

        $cartItem = $this->mockTypedModel(CartItem::class, [
            'product_uuid' => $productUuid,
            'quantity' => 1,
            'unit_price' => 1000,
            'total_price' => 1000
        ]);

        $product = $this->mockTypedModel(Product::class, [
            'uuid' => $productUuid,
            'name' => 'Test Product'
        ]);

        $cartItem->shouldReceive('getAttribute')->with('product')->andReturn($product);

        $cartItems = $this->mockCollection([$cartItem]);
        $cartItems->shouldReceive('sum')->with('total_price')->andReturn(1000);

        $cart = $this->mockTypedModel(Cart::class, [
            'user_uuid' => $userUuid
        ]);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn($cartItems);

        $order = $this->mockTypedModel(Order::class, [
            'uuid' => 'test-order-uuid',
            'user_uuid' => $userUuid
        ]);

        $orderItemsRelation = Mockery::mock();
        $order->shouldReceive('orderItems')->andReturn($orderItemsRelation);
        $order->shouldReceive('load')
            ->with(['orderItems.product'])
            ->andReturn($order);

        $this->cartService->shouldReceive('getOrCreateCart')
            ->with($userUuid)
            ->andReturn($cart);

        $this->productRepository->shouldReceive('findByUuid')
            ->with($productUuid)
            ->andReturn($product);

        $this->productService->shouldReceive('validateStock')
            ->with($product, 1)
            ->andReturnTrue();

        $this->orderRepository->shouldReceive('create')
            ->with([
                'user_uuid' => $userUuid,
                'status' => OrderStatusEnum::PENDING->value,
                'total_amount' => 1000,
                'shipping_address' => '123 Test Street, Test City',
                'notes' => null, // Should be null when not provided
            ])
            ->andReturn($order);

        $product->shouldReceive('decreaseStock')
            ->with(1)
            ->andReturn(true);

        $orderItemsRelation->shouldReceive('create')
            ->andReturn($this->mockTypedModel(\App\Models\Order\OrderItem::class));

        $this->cartService->shouldReceive('clearCart')
            ->with($userUuid)
            ->andReturnNull();

        $result = $this->service->createOrderFromCart($userUuid, $orderData);

        $this->assertSame($order, $result);
    }
}