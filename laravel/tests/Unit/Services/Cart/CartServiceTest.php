<?php

namespace Tests\Unit\Services\Cart;

use App\Services\Cart\CartService;
use App\Repositories\Cart\CartRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Models\Product\Product;
use Tests\Base\BaseServiceUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Mockery;

/**
 * Unit tests for CartService
 * Tests business logic for cart management, item operations, and cart lifecycle
 */
#[CoversClass(CartService::class)]
#[Group('unit')]
#[Group('services')]
#[Small]
class CartServiceTest extends BaseServiceUnitTest
{
    private CartRepositoryInterface $cartRepositoryMock;
    private ProductRepositoryInterface $productRepositoryMock;

    protected function getServiceClass(): string
    {
        return CartService::class;
    }

    protected function getServiceDependencies(): array
    {
        $this->cartRepositoryMock = $this->mockRepository(CartRepositoryInterface::class);
        $this->productRepositoryMock = $this->mockRepository(ProductRepositoryInterface::class);

        return [
            $this->cartRepositoryMock,
            $this->productRepositoryMock
        ];
    }

    #[Test]
    public function service_has_required_constructor_dependencies(): void
    {
        $this->assertHasConstructorDependencies([
            CartRepositoryInterface::class,
            ProductRepositoryInterface::class
        ]);
    }

    #[Test]
    public function service_has_required_methods(): void
    {
        $this->assertServiceHasMethod('getOrCreateCart');
        $this->assertServiceHasMethod('addToCart');
        $this->assertServiceHasMethod('updateCartItem');
        $this->assertServiceHasMethod('removeFromCart');
        $this->assertServiceHasMethod('clearCart');
    }

    #[Test]
    public function getOrCreateCart_returns_existing_cart_when_found(): void
    {
        // Arrange
        $userUuid = $this->getTestUserUuid();
        $cart = $this->createMockCart($userUuid);

        $this->cartRepositoryMock
            ->shouldReceive('findByUserUuid')
            ->once()
            ->with($userUuid)
            ->andReturn($cart);

        $cart->shouldReceive('load')
            ->once()
            ->with(['cartItems.product', 'cartItems' => Mockery::type('callable')])
            ->andReturnSelf();

        // Act
        $result = $this->service->getOrCreateCart($userUuid);

        // Assert
        $this->assertServiceReturns($result, Cart::class);
        $this->assertServiceUsesRepository($this->cartRepositoryMock, 'findByUserUuid', [$userUuid]);
    }

    #[Test]
    public function getOrCreateCart_creates_new_cart_when_not_found(): void
    {
        // Arrange
        $userUuid = $this->getTestUserUuid();
        $cart = $this->createMockCart($userUuid);

        $this->cartRepositoryMock
            ->shouldReceive('findByUserUuid')
            ->once()
            ->with($userUuid)
            ->andReturn(null);

        $this->cartRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with(['user_uuid' => $userUuid])
            ->andReturn($cart);

        $cart->shouldReceive('load')
            ->once()
            ->with(['cartItems.product', 'cartItems' => Mockery::type('callable')])
            ->andReturnSelf();

        // Act
        $result = $this->service->getOrCreateCart($userUuid);

        // Assert
        $this->assertServiceReturns($result, Cart::class);
        $this->assertServiceUsesRepository($this->cartRepositoryMock, 'create', [['user_uuid' => $userUuid]]);
    }

    #[Test]
    public function addToCart_creates_new_cart_item_when_product_not_in_cart(): void
    {
        // Arrange
        $userUuid = $this->getTestUserUuid();
        $productUuid = $this->getTestEntityUuid();
        $data = ['product_uuid' => $productUuid, 'quantity' => 2];
        
        $cart = $this->createMockCart($userUuid);
        $product = $this->createMockProduct($productUuid);
        $cartItems = $this->createMockCartItemsQuery();

        $this->setupGetOrCreateCartMock($userUuid, $cart);

        $this->productRepositoryMock
            ->shouldReceive('findByUuid')
            ->once()
            ->with($productUuid)
            ->andReturn($product);

        $cart->shouldReceive('cartItems')
            ->twice()
            ->andReturn($cartItems);

        $cartItems->shouldReceive('where')
            ->once()
            ->with('product_uuid', $productUuid)
            ->andReturnSelf();

        $cartItems->shouldReceive('first')
            ->once()
            ->andReturn(null);

        $cartItems->shouldReceive('create')
            ->once()
            ->with([
                'product_uuid' => $productUuid,
                'quantity' => 2,
                'unit_price' => $product->price,
            ])
            ->andReturn(true);

        $cart->shouldReceive('fresh')
            ->once()
            ->with(['cartItems.product'])
            ->andReturnSelf();

        // Act
        $result = $this->service->addToCart($userUuid, $data);

        // Assert
        $this->assertServiceReturns($result, Cart::class);
    }

    #[Test]
    public function addToCart_updates_existing_cart_item_quantity(): void
    {
        // Arrange
        $userUuid = $this->getTestUserUuid();
        $productUuid = $this->getTestEntityUuid();
        $data = ['product_uuid' => $productUuid, 'quantity' => 2];
        
        $cart = $this->createMockCart($userUuid);
        $product = $this->createMockProduct($productUuid);
        $existingCartItem = $this->createMockCartItem($productUuid, 3);
        $cartItems = $this->createMockCartItemsQuery();

        $this->setupGetOrCreateCartMock($userUuid, $cart);

        $this->productRepositoryMock
            ->shouldReceive('findByUuid')
            ->once()
            ->with($productUuid)
            ->andReturn($product);

        $cart->shouldReceive('cartItems')
            ->once()
            ->andReturn($cartItems);

        $cartItems->shouldReceive('where')
            ->once()
            ->with('product_uuid', $productUuid)
            ->andReturnSelf();

        $cartItems->shouldReceive('first')
            ->once()
            ->andReturn($existingCartItem);

        $existingCartItem->shouldReceive('update')
            ->once()
            ->with(['quantity' => 5]) // 3 + 2
            ->andReturn(true);

        $cart->shouldReceive('fresh')
            ->once()
            ->with(['cartItems.product'])
            ->andReturnSelf();

        // Act
        $result = $this->service->addToCart($userUuid, $data);

        // Assert
        $this->assertServiceReturns($result, Cart::class);
    }

    #[Test]
    public function updateCartItem_updates_existing_cart_item(): void
    {
        // Arrange
        $userUuid = $this->getTestUserUuid();
        $productUuid = $this->getTestEntityUuid();
        $data = ['product_uuid' => $productUuid, 'quantity' => 5];
        
        $cart = $this->createMockCart($userUuid);
        $cartItem = $this->createMockCartItem($productUuid, 2);
        $cartItems = $this->createMockCartItemsQuery();

        $this->setupGetOrCreateCartMock($userUuid, $cart);

        $cart->shouldReceive('cartItems')
            ->once()
            ->andReturn($cartItems);

        $cartItems->shouldReceive('where')
            ->once()
            ->with('product_uuid', $productUuid)
            ->andReturnSelf();

        $cartItems->shouldReceive('first')
            ->once()
            ->andReturn($cartItem);

        $cartItem->shouldReceive('update')
            ->once()
            ->with(['quantity' => 5])
            ->andReturn(true);

        $cart->shouldReceive('fresh')
            ->once()
            ->with(['cartItems.product'])
            ->andReturnSelf();

        // Act
        $result = $this->service->updateCartItem($userUuid, $data);

        // Assert
        $this->assertServiceReturns($result, Cart::class);
    }

    #[Test]
    public function updateCartItem_does_nothing_when_cart_item_not_found(): void
    {
        // Arrange
        $userUuid = $this->getTestUserUuid();
        $productUuid = $this->getTestEntityUuid();
        $data = ['product_uuid' => $productUuid, 'quantity' => 5];
        
        $cart = $this->createMockCart($userUuid);
        $cartItems = $this->createMockCartItemsQuery();

        $this->setupGetOrCreateCartMock($userUuid, $cart);

        $cart->shouldReceive('cartItems')
            ->once()
            ->andReturn($cartItems);

        $cartItems->shouldReceive('where')
            ->once()
            ->with('product_uuid', $productUuid)
            ->andReturnSelf();

        $cartItems->shouldReceive('first')
            ->once()
            ->andReturn(null);

        $cart->shouldReceive('fresh')
            ->once()
            ->with(['cartItems.product'])
            ->andReturnSelf();

        // Act
        $result = $this->service->updateCartItem($userUuid, $data);

        // Assert
        $this->assertServiceReturns($result, Cart::class);
    }

    #[Test]
    public function removeFromCart_removes_cart_item(): void
    {
        // Arrange
        $userUuid = $this->getTestUserUuid();
        $productUuid = $this->getTestEntityUuid();
        
        $cart = $this->createMockCart($userUuid);
        $cartItems = $this->createMockCartItemsQuery();

        $this->setupGetOrCreateCartMock($userUuid, $cart);

        $cart->shouldReceive('cartItems')
            ->once()
            ->andReturn($cartItems);

        $cartItems->shouldReceive('where')
            ->once()
            ->with('product_uuid', $productUuid)
            ->andReturnSelf();

        $cartItems->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $cart->shouldReceive('fresh')
            ->once()
            ->with(['cartItems.product'])
            ->andReturnSelf();

        // Act
        $result = $this->service->removeFromCart($userUuid, $productUuid);

        // Assert
        $this->assertServiceReturns($result, Cart::class);
    }

    #[Test]
    public function clearCart_removes_all_cart_items(): void
    {
        // Arrange
        $userUuid = $this->getTestUserUuid();
        
        $cart = $this->createMockCart($userUuid);
        $cartItems = $this->createMockCartItemsQuery();

        $this->setupGetOrCreateCartMock($userUuid, $cart);

        $cart->shouldReceive('cartItems')
            ->once()
            ->andReturn($cartItems);

        $cartItems->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        // Act
        $this->service->clearCart($userUuid);

        // Assert - clearCart returns void, so we just verify no exceptions were thrown
        $this->assertTrue(true);
    }

    /**
     * Setup mocks for getOrCreateCart method
     */
    private function setupGetOrCreateCartMock(string $userUuid, $cart): void
    {
        $this->cartRepositoryMock
            ->shouldReceive('findByUserUuid')
            ->with($userUuid)
            ->andReturn($cart);

        $cart->shouldReceive('load')
            ->with(['cartItems.product', 'cartItems' => Mockery::type('callable')])
            ->andReturnSelf();
    }

    /**
     * Create mock Cart
     */
    private function createMockCart(string $userUuid): \Mockery\MockInterface
    {
        return $this->mockModel([
            'uuid' => $this->getTestEntityUuid(),
            'user_uuid' => $userUuid,
        ]);
    }

    /**
     * Create mock Product
     */
    private function createMockProduct(string $productUuid, int $price = 1000): \Mockery\MockInterface
    {
        return $this->mockModel([
            'uuid' => $productUuid,
            'name' => 'Test Product',
            'price' => $price,
        ]);
    }

    /**
     * Create mock CartItem
     */
    private function createMockCartItem(string $productUuid, int $quantity): \Mockery\MockInterface
    {
        $cartItem = $this->mockModel([
            'product_uuid' => $productUuid,
            'quantity' => $quantity,
            'unit_price' => 1000,
        ]);

        return $cartItem;
    }

    /**
     * Create mock CartItems query builder
     */
    private function createMockCartItemsQuery(): \Mockery\MockInterface
    {
        $query = Mockery::mock();
        $query->shouldReceive('where')->andReturnSelf();
        $query->shouldReceive('first')->andReturn(null);
        $query->shouldReceive('create')->andReturn(true);
        $query->shouldReceive('delete')->andReturn(true);
        
        return $query;
    }
}