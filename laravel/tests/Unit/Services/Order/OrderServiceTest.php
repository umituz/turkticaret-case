<?php

namespace Tests\Unit\Services\Order;

use App\Services\Order\OrderService;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Services\Cart\CartService;
use App\DTOs\Order\OrderCreateDTO;
use App\Models\Order\Order;
use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Models\Product\Product;
use App\Exceptions\Order\EmptyCartException;
use App\Enums\Order\OrderStatus;
use Tests\Base\BaseServiceUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Mockery;

/**
 * Unit tests for OrderService
 * Tests business logic for order creation, validation, and cart conversion
 */
#[CoversClass(OrderService::class)]
#[Group('unit')]
#[Group('services')]
#[Small]
class OrderServiceTest extends BaseServiceUnitTest
{
    private OrderRepositoryInterface $orderRepositoryMock;
    private CartService $cartServiceMock;

    protected function getServiceClass(): string
    {
        return OrderService::class;
    }

    protected function getServiceDependencies(): array
    {
        $this->orderRepositoryMock = $this->mockRepository(OrderRepositoryInterface::class);
        $this->cartServiceMock = $this->mockService(CartService::class);

        return [
            $this->orderRepositoryMock,
            $this->cartServiceMock
        ];
    }

    #[Test]
    public function service_has_required_constructor_dependencies(): void
    {
        $this->assertHasConstructorDependencies([
            OrderRepositoryInterface::class,
            CartService::class
        ]);
    }

    #[Test]
    public function service_has_required_methods(): void
    {
        $this->assertServiceHasMethod('getUserOrders');
        $this->assertServiceHasMethod('createOrderFromCart');
    }

    #[Test]
    public function getUserOrders_returns_paginated_orders(): void
    {
        // Arrange
        $userUuid = $this->getTestUserUuid();
        $expectedResult = $this->mockPaginator([]);

        $this->orderRepositoryMock
            ->shouldReceive('findByUserUuid')
            ->once()
            ->with($userUuid)
            ->andReturn($expectedResult);

        // Act
        $result = $this->service->getUserOrders($userUuid);

        // Assert
        $this->assertServiceReturns($result, \Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
        $this->assertServiceUsesRepository($this->orderRepositoryMock, 'findByUserUuid', [$userUuid]);
    }

    #[Test]
    public function createOrderFromCart_creates_order_successfully(): void
    {
        // Arrange
        $userUuid = $this->getTestUserUuid();
        $orderData = $this->createOrderDTO();
        $cart = $this->createMockCartWithItems();
        $order = $this->createMockOrder();

        $this->cartServiceMock
            ->shouldReceive('getOrCreateCart')
            ->once()
            ->with($userUuid)
            ->andReturn($cart);

        $this->orderRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->andReturn($order);

        $this->cartServiceMock
            ->shouldReceive('clearCart')
            ->once()
            ->with($userUuid);

        $order->shouldReceive('orderItems')->andReturnSelf();
        $order->shouldReceive('create')->andReturn(true);
        $order->shouldReceive('load')->with(['orderItems.product'])->andReturnSelf();

        $this->mockDatabaseTransaction();

        // Act
        $result = $this->service->createOrderFromCart($userUuid, $orderData);

        // Assert
        $this->assertServiceReturns($result, Order::class);
    }

    #[Test]
    public function createOrderFromCart_throws_exception_for_empty_cart(): void
    {
        // Arrange
        $userUuid = $this->getTestUserUuid();
        $orderData = $this->createOrderDTO();
        $emptyCart = $this->createMockEmptyCart();

        $this->cartServiceMock
            ->shouldReceive('getOrCreateCart')
            ->once()
            ->with($userUuid)
            ->andReturn($emptyCart);

        $this->mockDatabaseTransaction();

        // Act & Assert
        $this->assertServiceThrowsException(
            fn() => $this->service->createOrderFromCart($userUuid, $orderData),
            EmptyCartException::class
        );
    }

    #[Test]
    public function createOrderFromCart_calculates_total_amount_correctly(): void
    {
        // Arrange
        $userUuid = $this->getTestUserUuid();
        $orderData = $this->createOrderDTO();
        $cart = $this->createMockCartWithItems([
            ['total_price' => 1000], // 10.00
            ['total_price' => 1500], // 15.00
            ['total_price' => 2000], // 20.00
        ]);
        $order = $this->createMockOrder();

        $this->cartServiceMock
            ->shouldReceive('getOrCreateCart')
            ->once()
            ->with($userUuid)
            ->andReturn($cart);

        $this->orderRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['total_amount'] === 4500; // 45.00
            }))
            ->andReturn($order);

        $this->cartServiceMock
            ->shouldReceive('clearCart')
            ->once()
            ->with($userUuid);

        $order->shouldReceive('orderItems')->andReturnSelf();
        $order->shouldReceive('create')->andReturn(true);
        $order->shouldReceive('load')->with(['orderItems.product'])->andReturnSelf();

        $this->mockDatabaseTransaction();

        // Act
        $result = $this->service->createOrderFromCart($userUuid, $orderData);

        // Assert
        $this->assertServiceReturns($result, Order::class);
    }

    #[Test]
    public function createOrderFromCart_sets_correct_order_status(): void
    {
        // Arrange
        $userUuid = $this->getTestUserUuid();
        $orderData = $this->createOrderDTO();
        $cart = $this->createMockCartWithItems();
        $order = $this->createMockOrder();

        $this->cartServiceMock
            ->shouldReceive('getOrCreateCart')
            ->once()
            ->with($userUuid)
            ->andReturn($cart);

        $this->orderRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['status'] === OrderStatus::PENDING->value;
            }))
            ->andReturn($order);

        $this->cartServiceMock
            ->shouldReceive('clearCart')
            ->once()
            ->with($userUuid);

        $order->shouldReceive('orderItems')->andReturnSelf();
        $order->shouldReceive('create')->andReturn(true);
        $order->shouldReceive('load')->with(['orderItems.product'])->andReturnSelf();

        $this->mockDatabaseTransaction();

        // Act
        $result = $this->service->createOrderFromCart($userUuid, $orderData);

        // Assert
        $this->assertServiceReturns($result, Order::class);
    }

    #[Test]
    public function createOrderFromCart_transfers_cart_items_to_order(): void
    {
        // Arrange
        $userUuid = $this->getTestUserUuid();
        $orderData = $this->createOrderDTO();
        $cartItems = [
            $this->createMockCartItem('product-1', 'Test Product 1', 2, 1000, 2000),
            $this->createMockCartItem('product-2', 'Test Product 2', 1, 1500, 1500),
        ];
        $cart = $this->createMockCartWithItems($cartItems);
        $order = $this->createMockOrder();

        $this->cartServiceMock
            ->shouldReceive('getOrCreateCart')
            ->once()
            ->with($userUuid)
            ->andReturn($cart);

        $this->orderRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->andReturn($order);

        $orderItems = Mockery::mock();
        $orderItems->shouldReceive('create')
            ->twice()
            ->andReturn(true);

        $order->shouldReceive('orderItems')
            ->twice()
            ->andReturn($orderItems);

        $order->shouldReceive('load')->with(['orderItems.product'])->andReturnSelf();

        $this->cartServiceMock
            ->shouldReceive('clearCart')
            ->once()
            ->with($userUuid);

        $this->mockDatabaseTransaction();

        // Act
        $result = $this->service->createOrderFromCart($userUuid, $orderData);

        // Assert
        $this->assertServiceReturns($result, Order::class);
    }

    #[Test]
    public function createOrderFromCart_clears_cart_after_order_creation(): void
    {
        // Arrange
        $userUuid = $this->getTestUserUuid();
        $orderData = $this->createOrderDTO();
        $cart = $this->createMockCartWithItems();
        $order = $this->createMockOrder();

        $this->cartServiceMock
            ->shouldReceive('getOrCreateCart')
            ->once()
            ->with($userUuid)
            ->andReturn($cart);

        $this->orderRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->andReturn($order);

        $this->cartServiceMock
            ->shouldReceive('clearCart')
            ->once()
            ->with($userUuid);

        $order->shouldReceive('orderItems')->andReturnSelf();
        $order->shouldReceive('create')->andReturn(true);
        $order->shouldReceive('load')->with(['orderItems.product'])->andReturnSelf();

        $this->mockDatabaseTransaction();

        // Act
        $result = $this->service->createOrderFromCart($userUuid, $orderData);

        // Assert
        $this->assertServiceReturns($result, Order::class);
        $this->assertServiceMethodCalled($this->cartServiceMock, 'clearCart', [$userUuid]);
    }

    /**
     * Create mock OrderCreateDTO
     */
    private function createOrderDTO(): OrderCreateDTO
    {
        return $this->mockDTO(OrderCreateDTO::class, [
            'shipping_address' => 'Test Address',
            'notes' => 'Test Notes'
        ]);
    }

    /**
     * Create mock Order
     */
    private function createMockOrder(): \Mockery\MockInterface
    {
        return $this->mockModel([
            'uuid' => $this->getTestEntityUuid(),
            'user_uuid' => $this->getTestUserUuid(),
            'status' => OrderStatus::PENDING->value,
            'total_amount' => 4500,
            'shipping_address' => 'Test Address',
            'notes' => 'Test Notes'
        ]);
    }

    /**
     * Create mock Cart with items
     */
    private function createMockCartWithItems(array $itemsData = null): \Mockery\MockInterface
    {
        $itemsData = $itemsData ?? [
            ['total_price' => 1000],
            ['total_price' => 1500],
            ['total_price' => 2000]
        ];

        $cartItems = [];
        foreach ($itemsData as $itemData) {
            $cartItems[] = $this->createMockCartItem(
                $itemData['product_uuid'] ?? 'test-product-uuid',
                $itemData['product_name'] ?? 'Test Product',
                $itemData['quantity'] ?? 1,
                $itemData['unit_price'] ?? 1000,
                $itemData['total_price']
            );
        }

        $cart = $this->mockModel(['uuid' => 'test-cart-uuid']);
        $cart->cartItems = $this->mockCollection($cartItems);

        return $cart;
    }

    /**
     * Create mock empty Cart
     */
    private function createMockEmptyCart(): \Mockery\MockInterface
    {
        $cart = $this->mockModel(['uuid' => 'test-cart-uuid']);
        $cart->cartItems = $this->mockCollection([]);

        return $cart;
    }

    /**
     * Create mock CartItem
     */
    private function createMockCartItem(string $productUuid, string $productName, int $quantity, int $unitPrice, int $totalPrice): \Mockery\MockInterface
    {
        $product = $this->mockModel([
            'uuid' => $productUuid,
            'name' => $productName
        ]);

        $cartItem = $this->mockModel([
            'product_uuid' => $productUuid,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice
        ]);

        $cartItem->product = $product;

        return $cartItem;
    }
}