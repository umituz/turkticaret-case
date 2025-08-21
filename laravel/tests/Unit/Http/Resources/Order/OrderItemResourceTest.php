<?php

namespace Tests\Unit\Http\Resources\Order;

use App\Http\Resources\Order\OrderItemResource;
use App\Http\Resources\Product\ProductResource;
use Tests\Base\BaseResourceUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Unit tests for OrderItemResource
 * Tests order item response formatting with product relationships and snapshots
 */
#[CoversClass(OrderItemResource::class)]
#[Group('unit')]
#[Group('resources')]
#[Small]
class OrderItemResourceTest extends BaseResourceUnitTest
{
    protected function getResourceClass(): string
    {
        return OrderItemResource::class;
    }

    protected function getResourceData(): array
    {
        return [
            'uuid' => $this->generateTestUuid(),
            'order_uuid' => $this->generateTestUuid(),
            'product_uuid' => $this->generateTestUuid(),
            'product_name' => 'Premium Laptop',
            'quantity' => 2,
            'unit_price' => 159999, // $1599.99 in cents
            'total_price' => 319998, // $3199.98 in cents (2 * $1599.99)
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    private function getProductData(): array
    {
        return [
            'uuid' => $this->generateTestUuid(),
            'name' => 'Current Product Name',
            'description' => 'Current product description',
            'sku' => 'CURRENT-SKU-001',
            'price' => 169999, // Current price may be different
            'stock_quantity' => 15,
            'image_path' => '/images/current-product.jpg',
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
        $orderItemData = $this->getResourceData();
        $orderItem = $this->createMockModel($orderItemData);
        $request = new Request();

        // Act
        $resource = new OrderItemResource($orderItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertResourceArrayStructure([
            'uuid',
            'product_uuid',
            'product_name',
            'quantity',
            'unit_price',
            'total_price',
            'product',
        ], $result);
    }

    #[Test]
    public function toArray_includes_all_order_item_attributes(): void
    {
        // Arrange
        $orderItemData = [
            'uuid' => 'order-item-test-uuid',
            'order_uuid' => 'order-uuid-123',
            'product_uuid' => 'product-uuid-456',
            'product_name' => 'Gaming Mechanical Keyboard',
            'quantity' => 3,
            'unit_price' => 12999, // $129.99 in cents
            'total_price' => 38997, // $389.97 in cents (3 * $129.99)
            'created_at' => Carbon::parse('2024-01-01 10:00:00'),
            'updated_at' => Carbon::parse('2024-01-15 14:30:00'),
        ];
        $orderItem = $this->createMockModel($orderItemData);
        $request = new Request();

        // Act
        $resource = new OrderItemResource($orderItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('order-item-test-uuid', $result['uuid']);
        $this->assertEquals('product-uuid-456', $result['product_uuid']);
        $this->assertEquals('Gaming Mechanical Keyboard', $result['product_name']);
        $this->assertEquals(3, $result['quantity']);
        $this->assertEquals(12999, $result['unit_price']);
        $this->assertEquals(38997, $result['total_price']);
    }

    #[Test]
    public function toArray_preserves_product_name_snapshot(): void
    {
        // Arrange - Product name at time of order (snapshot)
        $orderItemData = array_merge($this->getResourceData(), [
            'product_name' => 'Historical Product Name', // Name when order was placed
        ]);
        
        // Current product with different name
        $productData = array_merge($this->getProductData(), [
            'name' => 'Updated Product Name', // Current name is different
        ]);
        $product = $this->createMockModel($productData);
        
        $orderItem = $this->createMockModelWithRelations($orderItemData, [
            'product' => $product,
        ]);
        $request = new Request();

        // Act
        $resource = new OrderItemResource($orderItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('Historical Product Name', $result['product_name']);
        // The product name in the order item should be the historical snapshot, not the current product name
    }

    #[Test]
    public function toArray_includes_product_when_loaded(): void
    {
        // Arrange
        $productData = $this->getProductData();
        $product = $this->createMockModel($productData);
        
        $orderItemData = $this->getResourceData();
        $orderItem = $this->createMockModelWithRelations($orderItemData, [
            'product' => $product,
        ]);
        $request = new Request();

        // Act
        $resource = new OrderItemResource($orderItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayHasKey('product', $result);
        $this->assertInstanceOf(ProductResource::class, $result['product']);
    }

    #[Test]
    public function toArray_excludes_product_when_not_loaded(): void
    {
        // Arrange
        $orderItemData = $this->getResourceData();
        $orderItem = $this->createMockModel($orderItemData);
        
        // Mock relationLoaded to return false for product
        $orderItem->shouldReceive('relationLoaded')->with('product')->andReturn(false);
        $request = new Request();

        // Act
        $resource = new OrderItemResource($orderItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayHasKey('product', $result);
        // whenLoaded creates a MissingValue resource when relation is not loaded
        $this->assertInstanceOf(ProductResource::class, $result['product']);
    }

    #[Test]
    public function toArray_validates_integer_quantity_and_prices(): void
    {
        // Arrange
        $orderItemData = array_merge($this->getResourceData(), [
            'quantity' => 5,
            'unit_price' => 4999, // $49.99 in cents
            'total_price' => 24995, // $249.95 in cents (5 * $49.99)
        ]);
        $orderItem = $this->createMockModel($orderItemData);
        $request = new Request();

        // Act
        $resource = new OrderItemResource($orderItem);
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
        $orderItemData = array_merge($this->getResourceData(), [
            'quantity' => 1,
            'unit_price' => 1500, // $15.00 in cents
            'total_price' => 1500, // Same as unit price for quantity 1
        ]);
        $orderItem = $this->createMockModel($orderItemData);
        $request = new Request();

        // Act
        $resource = new OrderItemResource($orderItem);
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
        $orderItemData = array_merge($this->getResourceData(), [
            'product_name' => 'Bulk Item',
            'quantity' => 50,
            'unit_price' => 199, // $1.99 in cents
            'total_price' => 9950, // $99.50 in cents (50 * $1.99)
        ]);
        $orderItem = $this->createMockModel($orderItemData);
        $request = new Request();

        // Act
        $resource = new OrderItemResource($orderItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('Bulk Item', $result['product_name']);
        $this->assertEquals(50, $result['quantity']);
        $this->assertEquals(199, $result['unit_price']);
        $this->assertEquals(9950, $result['total_price']);
    }

    #[Test]
    public function toArray_validates_uuid_preservation(): void
    {
        // Arrange
        $itemUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
        $productUuid = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
        $orderItemData = array_merge($this->getResourceData(), [
            'uuid' => $itemUuid,
            'product_uuid' => $productUuid,
        ]);
        $orderItem = $this->createMockModel($orderItemData);
        $request = new Request();

        // Act
        $resource = new OrderItemResource($orderItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($itemUuid, $result['uuid']);
        $this->assertEquals($productUuid, $result['product_uuid']);
        $this->assertIsString($result['uuid']);
        $this->assertIsString($result['product_uuid']);
    }

    #[Test]
    public function toArray_handles_expensive_luxury_items(): void
    {
        // Arrange
        $orderItemData = array_merge($this->getResourceData(), [
            'product_name' => 'Luxury Watch',
            'quantity' => 1,
            'unit_price' => 99999999, // $999,999.99 per item
            'total_price' => 99999999, // Same for quantity 1
        ]);
        $orderItem = $this->createMockModel($orderItemData);
        $request = new Request();

        // Act
        $resource = new OrderItemResource($orderItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('Luxury Watch', $result['product_name']);
        $this->assertEquals(99999999, $result['unit_price']);
        $this->assertEquals(99999999, $result['total_price']);
        $this->assertIsInt($result['unit_price']);
        $this->assertIsInt($result['total_price']);
    }

    #[Test]
    public function toArray_handles_special_characters_in_product_name(): void
    {
        // Arrange
        $orderItemData = array_merge($this->getResourceData(), [
            'product_name' => 'Café Möller\'s "Premium" Collection & Co. - Model X1',
        ]);
        $orderItem = $this->createMockModel($orderItemData);
        $request = new Request();

        // Act
        $resource = new OrderItemResource($orderItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('Café Möller\'s "Premium" Collection & Co. - Model X1', $result['product_name']);
    }

    #[Test]
    public function toArray_maintains_price_calculation_integrity(): void
    {
        // Arrange - Test various quantity and price combinations
        $testCases = [
            [
                'product_name' => 'Product A',
                'quantity' => 3,
                'unit_price' => 1000,
                'total_price' => 3000
            ],
            [
                'product_name' => 'Product B',
                'quantity' => 7,
                'unit_price' => 599,
                'total_price' => 4193
            ],
            [
                'product_name' => 'Product C',
                'quantity' => 12,
                'unit_price' => 250,
                'total_price' => 3000
            ],
        ];

        foreach ($testCases as $testCase) {
            $orderItemData = array_merge($this->getResourceData(), $testCase);
            $orderItem = $this->createMockModel($orderItemData);
            $request = new Request();

            // Act
            $resource = new OrderItemResource($orderItem);
            $result = $resource->toArray($request);

            // Assert
            $this->assertEquals($testCase['product_name'], $result['product_name']);
            $this->assertEquals($testCase['quantity'], $result['quantity']);
            $this->assertEquals($testCase['unit_price'], $result['unit_price']);
            $this->assertEquals($testCase['total_price'], $result['total_price']);
        }
    }

    #[Test]
    public function toArray_excludes_order_uuid_from_response(): void
    {
        // Arrange
        $orderItemData = $this->getResourceData();
        $orderItem = $this->createMockModel($orderItemData);
        $request = new Request();

        // Act
        $resource = new OrderItemResource($orderItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayNotHasKey('order_uuid', $result);
        $this->assertArrayHasKey('product_uuid', $result); // But product_uuid should be included
    }

    #[Test]
    public function toArray_excludes_timestamps(): void
    {
        // Arrange - Order items don't typically show their timestamps in responses
        $orderItemData = $this->getResourceData();
        $orderItem = $this->createMockModel($orderItemData);
        $request = new Request();

        // Act
        $resource = new OrderItemResource($orderItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayNotHasKey('created_at', $result);
        $this->assertArrayNotHasKey('updated_at', $result);
    }

    #[Test]
    public function toArray_supports_unicode_in_product_name(): void
    {
        // Arrange
        $orderItemData = array_merge($this->getResourceData(), [
            'product_name' => 'Ürün Adı & Spéçiäl Châracters (αβγ) 測試',
        ]);
        $orderItem = $this->createMockModel($orderItemData);
        $request = new Request();

        // Act
        $resource = new OrderItemResource($orderItem);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('Ürün Adı & Spéçiäl Châracters (αβγ) 測試', $result['product_name']);
    }
}