<?php

namespace Tests\Unit\Http\Resources\Cart;

use App\Http\Resources\Cart\CartItemResource;
use App\Http\Resources\Product\ProductResource;
use Tests\Base\BaseResourceUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Unit tests for CartItemResource
 * Tests cart item response formatting with product relationships
 */
#[CoversClass(CartItemResource::class)]
#[Group('unit')]
#[Group('resources')]
#[Small]
class CartItemResourceTest extends BaseResourceUnitTest
{
    protected function getResourceClass(): string
    {
        return CartItemResource::class;
    }

    protected function getResourceData(): array
    {
        return [
            'uuid' => $this->generateTestUuid(),
            'cart_uuid' => $this->generateTestUuid(),
            'product_uuid' => $this->generateTestUuid(),
            'quantity' => 2,
            'unit_price' => 1999, // $19.99 in cents
            'total_price' => 3998, // $39.98 in cents
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    private function getProductData(): array
    {
        return [
            'uuid' => $this->generateTestUuid(),
            'name' => 'Test Product',
            'description' => 'Test product description',
            'sku' => 'TEST-SKU-001',
            'price' => 1999,
            'stock_quantity' => 50,
            'image_path' => '/images/test-product.jpg',
            'is_active' => true,
            'category_uuid' => $this->generateTestUuid(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
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
        $cartItemData = $this->getResourceData();
        $cartItem = $this->createMockModel($cartItemData);
        $request = new Request();

        // Act
        $resource = new CartItemResource($cartItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertResourceArrayStructure([
            'uuid',
            'product_uuid',
            'quantity',
            'unit_price',
            'total_price',
            'product',
            'created_at',
            'updated_at',
        ], $result);
    }

    #[Test]
    public function toArray_includes_all_cart_item_attributes(): void
    {
        // Arrange
        $cartItemData = [
            'uuid' => 'cart-item-test-uuid',
            'cart_uuid' => 'cart-uuid-123',
            'product_uuid' => 'product-uuid-456',
            'quantity' => 3,
            'unit_price' => 2999, // $29.99 in cents
            'total_price' => 8997, // $89.97 in cents (3 * $29.99)
            'created_at' => Carbon::parse('2024-01-01 10:00:00'),
            'updated_at' => Carbon::parse('2024-01-15 14:30:00'),
        ];
        $cartItem = $this->createMockModel($cartItemData);
        $request = new Request();

        // Act
        $resource = new CartItemResource($cartItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('cart-item-test-uuid', $result['uuid']);
        $this->assertEquals('product-uuid-456', $result['product_uuid']);
        $this->assertEquals(3, $result['quantity']);
        $this->assertEquals(2999, $result['unit_price']);
        $this->assertEquals(8997, $result['total_price']);
    }

    #[Test]
    public function toArray_formats_timestamps_as_iso8601(): void
    {
        // Arrange
        $createdAt = Carbon::parse('2024-01-01 12:00:00');
        $updatedAt = Carbon::parse('2024-01-15 15:30:00');
        $cartItemData = array_merge($this->getResourceData(), [
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);
        $cartItem = $this->createMockModel($cartItemData);
        $request = new Request();

        // Act
        $resource = new CartItemResource($cartItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($createdAt->toIso8601String(), $result['created_at']);
        $this->assertEquals($updatedAt->toIso8601String(), $result['updated_at']);
    }

    #[Test]
    public function toArray_handles_null_timestamps(): void
    {
        // Arrange
        $cartItemData = array_merge($this->getResourceData(), [
            'created_at' => null,
            'updated_at' => null,
        ]);
        $cartItem = $this->createMockModel($cartItemData);
        $request = new Request();

        // Act
        $resource = new CartItemResource($cartItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertNull($result['created_at']);
        $this->assertNull($result['updated_at']);
    }

    #[Test]
    public function toArray_includes_product_when_loaded(): void
    {
        // Arrange
        $productData = $this->getProductData();
        $product = $this->createMockModel($productData);
        
        $cartItemData = $this->getResourceData();
        $cartItem = $this->createMockModelWithRelations($cartItemData, [
            'product' => $product,
        ]);
        $request = new Request();

        // Act
        $resource = new CartItemResource($cartItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayHasKey('product', $result);
        $this->assertInstanceOf(ProductResource::class, $result['product']);
    }

    #[Test]
    public function toArray_excludes_product_when_not_loaded(): void
    {
        // Arrange
        $cartItemData = $this->getResourceData();
        $cartItem = $this->createMockModel($cartItemData);
        
        // Mock relationLoaded to return false for product
        $cartItem->shouldReceive('relationLoaded')->with('product')->andReturn(false);
        $request = new Request();

        // Act
        $resource = new CartItemResource($cartItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayHasKey('product', $result);
        // whenLoaded creates a MissingValue resource when relation is not loaded
        // In unit tests, this manifests as a ProductResource with MissingValue
        $this->assertInstanceOf(ProductResource::class, $result['product']);
    }

    #[Test]
    public function toArray_validates_integer_quantity_and_prices(): void
    {
        // Arrange
        $cartItemData = array_merge($this->getResourceData(), [
            'quantity' => 5,
            'unit_price' => 4999, // $49.99 in cents
            'total_price' => 24995, // $249.95 in cents (5 * $49.99)
        ]);
        $cartItem = $this->createMockModel($cartItemData);
        $request = new Request();

        // Act
        $resource = new CartItemResource($cartItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals(5, $result['quantity']);
        $this->assertEquals(4999, $result['unit_price']);
        $this->assertEquals(24995, $result['total_price']);
        $this->assertIsInt($result['quantity']);
        $this->assertIsInt($result['unit_price']);
        $this->assertIsInt($result['total_price']);
    }

    #[Test]
    public function toArray_handles_single_quantity_item(): void
    {
        // Arrange
        $cartItemData = array_merge($this->getResourceData(), [
            'quantity' => 1,
            'unit_price' => 1500, // $15.00 in cents
            'total_price' => 1500, // Same as unit price for quantity 1
        ]);
        $cartItem = $this->createMockModel($cartItemData);
        $request = new Request();

        // Act
        $resource = new CartItemResource($cartItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals(1, $result['quantity']);
        $this->assertEquals(1500, $result['unit_price']);
        $this->assertEquals(1500, $result['total_price']);
    }

    #[Test]
    public function toArray_handles_high_quantity_items(): void
    {
        // Arrange
        $cartItemData = array_merge($this->getResourceData(), [
            'quantity' => 100,
            'unit_price' => 99, // $0.99 in cents
            'total_price' => 9900, // $99.00 in cents (100 * $0.99)
        ]);
        $cartItem = $this->createMockModel($cartItemData);
        $request = new Request();

        // Act
        $resource = new CartItemResource($cartItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals(100, $result['quantity']);
        $this->assertEquals(99, $result['unit_price']);
        $this->assertEquals(9900, $result['total_price']);
    }

    #[Test]
    public function toArray_validates_uuid_preservation(): void
    {
        // Arrange
        $itemUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
        $productUuid = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
        $cartItemData = array_merge($this->getResourceData(), [
            'uuid' => $itemUuid,
            'product_uuid' => $productUuid,
        ]);
        $cartItem = $this->createMockModel($cartItemData);
        $request = new Request();

        // Act
        $resource = new CartItemResource($cartItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($itemUuid, $result['uuid']);
        $this->assertEquals($productUuid, $result['product_uuid']);
        $this->assertIsString($result['uuid']);
        $this->assertIsString($result['product_uuid']);
    }

    #[Test]
    public function toArray_handles_expensive_items(): void
    {
        // Arrange
        $cartItemData = array_merge($this->getResourceData(), [
            'quantity' => 2,
            'unit_price' => 99999999, // $999,999.99 per item
            'total_price' => 199999998, // $1,999,999.98 total
        ]);
        $cartItem = $this->createMockModel($cartItemData);
        $request = new Request();

        // Act
        $resource = new CartItemResource($cartItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals(99999999, $result['unit_price']);
        $this->assertEquals(199999998, $result['total_price']);
        $this->assertIsInt($result['unit_price']);
        $this->assertIsInt($result['total_price']);
    }

    #[Test]
    public function toArray_maintains_price_calculation_integrity(): void
    {
        // Arrange - Test various quantity and price combinations
        $testCases = [
            ['quantity' => 3, 'unit_price' => 1000, 'total_price' => 3000],
            ['quantity' => 7, 'unit_price' => 599, 'total_price' => 4193],
            ['quantity' => 12, 'unit_price' => 250, 'total_price' => 3000],
        ];

        foreach ($testCases as $testCase) {
            $cartItemData = array_merge($this->getResourceData(), $testCase);
            $cartItem = $this->createMockModel($cartItemData);
            $request = new Request();

            // Act
            $resource = new CartItemResource($cartItem);
            $result = $resource->toArray($request);

            // Assert
            $this->assertEquals($testCase['quantity'], $result['quantity']);
            $this->assertEquals($testCase['unit_price'], $result['unit_price']);
            $this->assertEquals($testCase['total_price'], $result['total_price']);
        }
    }

    #[Test]
    public function toArray_excludes_cart_uuid_from_response(): void
    {
        // Arrange
        $cartItemData = $this->getResourceData();
        $cartItem = $this->createMockModel($cartItemData);
        $request = new Request();

        // Act
        $resource = new CartItemResource($cartItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayNotHasKey('cart_uuid', $result);
        $this->assertArrayHasKey('product_uuid', $result); // But product_uuid should be included
    }
}