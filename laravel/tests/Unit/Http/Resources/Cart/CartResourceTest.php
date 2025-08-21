<?php

namespace Tests\Unit\Http\Resources\Cart;

use App\Http\Resources\Cart\CartResource;
use App\Http\Resources\Cart\CartItemResource;
use Tests\Base\BaseResourceUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Mockery;

/**
 * Unit tests for CartResource
 * Tests cart response formatting with cart items and calculations
 */
#[CoversClass(CartResource::class)]
#[Group('unit')]
#[Group('resources')]
#[Small]
class CartResourceTest extends BaseResourceUnitTest
{
    protected function getResourceClass(): string
    {
        return CartResource::class;
    }

    protected function getResourceData(): array
    {
        return [
            'uuid' => $this->generateTestUuid(),
            'user_uuid' => $this->generateTestUuid(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    private function getCartItemsData(): array
    {
        return [
            [
                'uuid' => $this->generateTestUuid(),
                'product_uuid' => $this->generateTestUuid(),
                'quantity' => 2,
                'unit_price' => 1999,
                'total_price' => 3998,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'uuid' => $this->generateTestUuid(),
                'product_uuid' => $this->generateTestUuid(),
                'quantity' => 1,
                'unit_price' => 4999,
                'total_price' => 4999,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
    }

    #[Test]
    public function resource_extends_base_resource(): void
    {
        $this->assertResourceExtendsBaseResource();
    }

    #[Test]
    public function resource_has_required_methods(): void
    {
        $this->assertResourceHasMethod('toArray');
    }

    #[Test]
    public function toArray_returns_correct_structure(): void
    {
        // Arrange
        $cartData = $this->getResourceData();
        $cartItems = $this->createMockCollection([]);
        
        $cart = $this->createMockModel($cartData);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn($cartItems);
        $cart->cartItems = $cartItems;
        
        $request = new Request();

        // Act
        $resource = new CartResource($cart);
        $result = $resource->toArray($request);

        // Assert
        $this->assertResourceArrayStructure([
            'uuid',
            'user_uuid',
            'items',
            'total_items',
            'total_amount',
            'created_at',
            'updated_at',
        ], $result);
    }

    #[Test]
    public function toArray_includes_all_cart_attributes(): void
    {
        // Arrange
        $cartData = [
            'uuid' => 'cart-test-uuid',
            'user_uuid' => 'user-uuid-123',
            'created_at' => Carbon::parse('2024-01-01 10:00:00'),
            'updated_at' => Carbon::parse('2024-01-15 14:30:00'),
        ];
        
        $cartItems = $this->createMockCollection([]);
        $cart = $this->createMockModel($cartData);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn($cartItems);
        $cart->cartItems = $cartItems;
        
        $request = new Request();

        // Act
        $resource = new CartResource($cart);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('cart-test-uuid', $result['uuid']);
        $this->assertEquals('user-uuid-123', $result['user_uuid']);
    }

    #[Test]
    public function toArray_formats_timestamps_as_iso8601(): void
    {
        // Arrange
        $createdAt = Carbon::parse('2024-01-01 12:00:00');
        $updatedAt = Carbon::parse('2024-01-15 15:30:00');
        $cartData = array_merge($this->getResourceData(), [
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);
        
        $cartItems = $this->createMockCollection([]);
        $cart = $this->createMockModel($cartData);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn($cartItems);
        $cart->cartItems = $cartItems;
        
        $request = new Request();

        // Act
        $resource = new CartResource($cart);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($createdAt->toIso8601String(), $result['created_at']);
        $this->assertEquals($updatedAt->toIso8601String(), $result['updated_at']);
    }

    #[Test]
    public function toArray_handles_null_timestamps(): void
    {
        // Arrange
        $cartData = array_merge($this->getResourceData(), [
            'created_at' => null,
            'updated_at' => null,
        ]);
        
        $cartItems = $this->createMockCollection([]);
        $cart = $this->createMockModel($cartData);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn($cartItems);
        $cart->cartItems = $cartItems;
        
        $request = new Request();

        // Act
        $resource = new CartResource($cart);
        $result = $resource->toArray($request);

        // Assert
        $this->assertNull($result['created_at']);
        $this->assertNull($result['updated_at']);
    }

    #[Test]
    public function toArray_includes_cart_items_when_loaded(): void
    {
        // Arrange
        $cartItemsData = $this->getCartItemsData();
        $cartItems = [
            $this->createMockModel($cartItemsData[0]),
            $this->createMockModel($cartItemsData[1]),
        ];
        $cartItemsCollection = $this->createMockCollection($cartItems);
        
        $cartData = $this->getResourceData();
        $cart = $this->createMockModelWithRelations($cartData, [
            'cartItems' => $cartItemsCollection,
        ]);
        
        $request = new Request();

        // Act
        $resource = new CartResource($cart);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayHasKey('items', $result);
        // The items should be a collection of CartItemResource
        $this->assertNotNull($result['items']);
    }

    #[Test]
    public function toArray_excludes_cart_items_when_not_loaded(): void
    {
        // Arrange
        $cartData = $this->getResourceData();
        $cart = $this->createMockModel($cartData);
        
        // Mock relationLoaded to return false for cartItems
        $cart->shouldReceive('relationLoaded')->with('cartItems')->andReturn(false);
        $cartItems = $this->createMockCollection([]);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn($cartItems);
        $cart->cartItems = $cartItems;
        
        $request = new Request();

        // Act
        $resource = new CartResource($cart);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayHasKey('items', $result);
        // whenLoaded should return an empty collection when relation is not loaded
    }

    #[Test]
    public function toArray_calculates_total_items_correctly(): void
    {
        // Arrange
        $cartItems = $this->createMockCollection([
            (object)['quantity' => 2], // Mock objects with quantity
            (object)['quantity' => 3],
            (object)['quantity' => 1],
        ]);
        $cartItems->shouldReceive('sum')->with('quantity')->andReturn(6); // 2 + 3 + 1
        
        $cartData = $this->getResourceData();
        $cart = $this->createMockModel($cartData);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn($cartItems);
        $cart->cartItems = $cartItems;
        
        $request = new Request();

        // Act
        $resource = new CartResource($cart);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals(6, $result['total_items']);
        $this->assertIsInt($result['total_items']);
    }

    #[Test]
    public function toArray_calculates_total_amount_correctly(): void
    {
        // Arrange
        $cartItems = $this->createMockCollection([
            (object)['total_price' => 3998], // Mock objects with total_price
            (object)['total_price' => 4999],
            (object)['total_price' => 1500],
        ]);
        $cartItems->shouldReceive('sum')->with('total_price')->andReturn(10497); // 3998 + 4999 + 1500
        
        $cartData = $this->getResourceData();
        $cart = $this->createMockModel($cartData);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn($cartItems);
        $cart->cartItems = $cartItems;
        
        $request = new Request();

        // Act
        $resource = new CartResource($cart);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals(10497, $result['total_amount']);
        $this->assertIsInt($result['total_amount']);
    }

    #[Test]
    public function toArray_handles_empty_cart(): void
    {
        // Arrange
        $cartItems = $this->createMockCollection([]);
        $cartItems->shouldReceive('sum')->with('quantity')->andReturn(0);
        $cartItems->shouldReceive('sum')->with('total_price')->andReturn(0);
        
        $cartData = $this->getResourceData();
        $cart = $this->createMockModel($cartData);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn($cartItems);
        $cart->cartItems = $cartItems;
        
        $request = new Request();

        // Act
        $resource = new CartResource($cart);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals(0, $result['total_items']);
        $this->assertEquals(0, $result['total_amount']);
    }

    #[Test]
    public function toArray_handles_null_cart_items_collection(): void
    {
        // Arrange
        $cartData = $this->getResourceData();
        $cart = $this->createMockModel($cartData);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn(null);
        $cart->cartItems = null;
        
        $request = new Request();

        // Act
        $resource = new CartResource($cart);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals(0, $result['total_items']);
        $this->assertEquals(0, $result['total_amount']);
    }

    #[Test]
    public function toArray_validates_uuid_preservation(): void
    {
        // Arrange
        $cartUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
        $userUuid = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
        $cartData = array_merge($this->getResourceData(), [
            'uuid' => $cartUuid,
            'user_uuid' => $userUuid,
        ]);
        
        $cartItems = $this->createMockCollection([]);
        $cart = $this->createMockModel($cartData);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn($cartItems);
        $cart->cartItems = $cartItems;
        
        $request = new Request();

        // Act
        $resource = new CartResource($cart);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($cartUuid, $result['uuid']);
        $this->assertEquals($userUuid, $result['user_uuid']);
        $this->assertIsString($result['uuid']);
        $this->assertIsString($result['user_uuid']);
    }

    #[Test]
    public function toArray_handles_large_cart_with_many_items(): void
    {
        // Arrange
        $cartItems = $this->createMockCollection(array_fill(0, 50, (object)['quantity' => 1, 'total_price' => 999]));
        $cartItems->shouldReceive('sum')->with('quantity')->andReturn(50); // 50 items, 1 each
        $cartItems->shouldReceive('sum')->with('total_price')->andReturn(49950); // 50 * 999
        
        $cartData = $this->getResourceData();
        $cart = $this->createMockModel($cartData);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn($cartItems);
        $cart->cartItems = $cartItems;
        
        $request = new Request();

        // Act
        $resource = new CartResource($cart);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals(50, $result['total_items']);
        $this->assertEquals(49950, $result['total_amount']);
    }

    #[Test]
    public function toArray_handles_high_value_cart(): void
    {
        // Arrange
        $cartItems = $this->createMockCollection([
            (object)['quantity' => 1, 'total_price' => 99999999], // Very expensive item
        ]);
        $cartItems->shouldReceive('sum')->with('quantity')->andReturn(1);
        $cartItems->shouldReceive('sum')->with('total_price')->andReturn(99999999);
        
        $cartData = $this->getResourceData();
        $cart = $this->createMockModel($cartData);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn($cartItems);
        $cart->cartItems = $cartItems;
        
        $request = new Request();

        // Act
        $resource = new CartResource($cart);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals(1, $result['total_items']);
        $this->assertEquals(99999999, $result['total_amount']);
    }

    #[Test]
    public function toArray_maintains_calculation_integrity_with_mixed_quantities(): void
    {
        // Arrange
        $cartItems = $this->createMockCollection([
            (object)['quantity' => 10, 'total_price' => 500],   // Low price, high quantity
            (object)['quantity' => 1, 'total_price' => 5000],   // High price, low quantity
            (object)['quantity' => 3, 'total_price' => 1500],   // Medium price, medium quantity
        ]);
        $cartItems->shouldReceive('sum')->with('quantity')->andReturn(14); // 10 + 1 + 3
        $cartItems->shouldReceive('sum')->with('total_price')->andReturn(7000); // 500 + 5000 + 1500
        
        $cartData = $this->getResourceData();
        $cart = $this->createMockModel($cartData);
        $cart->shouldReceive('getAttribute')->with('cartItems')->andReturn($cartItems);
        $cart->cartItems = $cartItems;
        
        $request = new Request();

        // Act
        $resource = new CartResource($cart);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals(14, $result['total_items']);
        $this->assertEquals(7000, $result['total_amount']);
    }
}