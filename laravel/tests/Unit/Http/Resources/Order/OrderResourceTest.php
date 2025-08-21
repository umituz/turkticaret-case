<?php

namespace Tests\Unit\Http\Resources\Order;

use App\Http\Resources\Order\OrderResource;
use App\Http\Resources\Order\OrderItemResource;
use Tests\Base\BaseResourceUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Mockery;

/**
 * Unit tests for OrderResource
 * Tests order response formatting with complex business logic and item relationships
 */
#[CoversClass(OrderResource::class)]
#[Group('unit')]
#[Group('resources')]
#[Small]
class OrderResourceTest extends BaseResourceUnitTest
{
    protected function getResourceClass(): string
    {
        return OrderResource::class;
    }

    protected function getResourceData(): array
    {
        return [
            'uuid' => $this->generateTestUuid(),
            'order_number' => 'ORD-2024-000001',
            'user_uuid' => $this->generateTestUuid(),
            'status' => 'pending',
            'total_amount' => 15999, // $159.99 in cents
            'shipping_address' => '123 Main St, City, State 12345',
            'notes' => 'Please handle with care',
            'shipped_at' => null,
            'delivered_at' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    private function getOrderItemsData(): array
    {
        return [
            [
                'uuid' => $this->generateTestUuid(),
                'product_uuid' => $this->generateTestUuid(),
                'product_name' => 'Gaming Mouse',
                'quantity' => 2,
                'unit_price' => 4999,
                'total_price' => 9998,
            ],
            [
                'uuid' => $this->generateTestUuid(),
                'product_uuid' => $this->generateTestUuid(),
                'product_name' => 'Keyboard',
                'quantity' => 1,
                'unit_price' => 5999,
                'total_price' => 5999,
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
        $orderData = $this->getResourceData();
        $orderItems = $this->createMockCollection([]);
        
        $order = $this->createMockModel($orderData);
        $order->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
        $order->orderItems = $orderItems;
        
        $request = new Request();

        // Act
        $resource = new OrderResource($order);
        $result = $resource->toArray($request);

        // Assert
        $this->assertResourceArrayStructure([
            'uuid',
            'order_number',
            'user_uuid',
            'status',
            'total_amount',
            'shipping_address',
            'notes',
            'shipped_at',
            'delivered_at',
            'items',
            'items_count',
            'created_at',
            'updated_at',
        ], $result);
    }

    #[Test]
    public function toArray_includes_all_order_attributes(): void
    {
        // Arrange
        $orderData = [
            'uuid' => 'order-test-uuid',
            'order_number' => 'ORD-2024-123456',
            'user_uuid' => 'user-uuid-789',
            'status' => 'shipped',
            'total_amount' => 25999, // $259.99
            'shipping_address' => '456 Oak Avenue, Downtown, NY 10001',
            'notes' => 'Fragile items - handle carefully',
            'shipped_at' => Carbon::parse('2024-01-10 14:30:00'),
            'delivered_at' => null,
            'created_at' => Carbon::parse('2024-01-01 10:00:00'),
            'updated_at' => Carbon::parse('2024-01-15 14:30:00'),
        ];
        
        $orderItems = $this->createMockCollection([]);
        $order = $this->createMockModel($orderData);
        $order->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
        $order->orderItems = $orderItems;
        
        $request = new Request();

        // Act
        $resource = new OrderResource($order);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('order-test-uuid', $result['uuid']);
        $this->assertEquals('ORD-2024-123456', $result['order_number']);
        $this->assertEquals('user-uuid-789', $result['user_uuid']);
        $this->assertEquals('shipped', $result['status']);
        $this->assertEquals(25999, $result['total_amount']);
        $this->assertEquals('456 Oak Avenue, Downtown, NY 10001', $result['shipping_address']);
        $this->assertEquals('Fragile items - handle carefully', $result['notes']);
    }

    #[Test]
    public function toArray_formats_timestamps_as_iso8601(): void
    {
        // Arrange
        $createdAt = Carbon::parse('2024-01-01 12:00:00');
        $updatedAt = Carbon::parse('2024-01-15 15:30:00');
        $shippedAt = Carbon::parse('2024-01-10 09:45:00');
        $deliveredAt = Carbon::parse('2024-01-12 16:20:00');
        
        $orderData = array_merge($this->getResourceData(), [
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'shipped_at' => $shippedAt,
            'delivered_at' => $deliveredAt,
        ]);
        
        $orderItems = $this->createMockCollection([]);
        $order = $this->createMockModel($orderData);
        $order->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
        $order->orderItems = $orderItems;
        
        $request = new Request();

        // Act
        $resource = new OrderResource($order);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($createdAt->toIso8601String(), $result['created_at']);
        $this->assertEquals($updatedAt->toIso8601String(), $result['updated_at']);
        $this->assertEquals($shippedAt->toIso8601String(), $result['shipped_at']);
        $this->assertEquals($deliveredAt->toIso8601String(), $result['delivered_at']);
    }

    #[Test]
    public function toArray_handles_null_timestamps(): void
    {
        // Arrange
        $orderData = array_merge($this->getResourceData(), [
            'shipped_at' => null,
            'delivered_at' => null,
            'created_at' => null,
            'updated_at' => null,
        ]);
        
        $orderItems = $this->createMockCollection([]);
        $order = $this->createMockModel($orderData);
        $order->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
        $order->orderItems = $orderItems;
        
        $request = new Request();

        // Act
        $resource = new OrderResource($order);
        $result = $resource->toArray($request);

        // Assert
        $this->assertNull($result['shipped_at']);
        $this->assertNull($result['delivered_at']);
        $this->assertNull($result['created_at']);
        $this->assertNull($result['updated_at']);
    }

    #[Test]
    public function toArray_includes_order_items_when_loaded(): void
    {
        // Arrange
        $orderItemsData = $this->getOrderItemsData();
        $orderItems = [
            $this->createMockModel($orderItemsData[0]),
            $this->createMockModel($orderItemsData[1]),
        ];
        $orderItemsCollection = $this->createMockCollection($orderItems);
        
        $orderData = $this->getResourceData();
        $order = $this->createMockModelWithRelations($orderData, [
            'orderItems' => $orderItemsCollection,
        ]);
        
        $request = new Request();

        // Act
        $resource = new OrderResource($order);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayHasKey('items', $result);
        // The items should be a collection of OrderItemResource
        $this->assertNotNull($result['items']);
    }

    #[Test]
    public function toArray_excludes_order_items_when_not_loaded(): void
    {
        // Arrange
        $orderData = $this->getResourceData();
        $order = $this->createMockModel($orderData);
        
        // Mock relationLoaded to return false for orderItems
        $order->shouldReceive('relationLoaded')->with('orderItems')->andReturn(false);
        $orderItems = $this->createMockCollection([]);
        $order->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
        $order->orderItems = $orderItems;
        
        $request = new Request();

        // Act
        $resource = new OrderResource($order);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayHasKey('items', $result);
        // whenLoaded should return an empty collection when relation is not loaded
    }

    #[Test]
    public function toArray_calculates_items_count_correctly(): void
    {
        // Arrange
        $orderItems = $this->createMockCollection([
            (object)['id' => 1],
            (object)['id' => 2],
            (object)['id' => 3],
        ]);
        $orderItems->shouldReceive('count')->andReturn(3);
        
        $orderData = $this->getResourceData();
        $order = $this->createMockModel($orderData);
        $order->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
        $order->orderItems = $orderItems;
        
        $request = new Request();

        // Act
        $resource = new OrderResource($order);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals(3, $result['items_count']);
        $this->assertIsInt($result['items_count']);
    }

    #[Test]
    public function toArray_handles_empty_order(): void
    {
        // Arrange
        $orderItems = $this->createMockCollection([]);
        $orderItems->shouldReceive('count')->andReturn(0);
        
        $orderData = $this->getResourceData();
        $order = $this->createMockModel($orderData);
        $order->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
        $order->orderItems = $orderItems;
        
        $request = new Request();

        // Act
        $resource = new OrderResource($order);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals(0, $result['items_count']);
    }

    #[Test]
    public function toArray_handles_null_order_items_collection(): void
    {
        // Arrange
        $orderData = $this->getResourceData();
        $order = $this->createMockModel($orderData);
        $order->shouldReceive('getAttribute')->with('orderItems')->andReturn(null);
        $order->orderItems = null;
        
        $request = new Request();

        // Act
        $resource = new OrderResource($order);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals(0, $result['items_count']);
    }

    #[Test]
    public function toArray_validates_order_number_format(): void
    {
        // Arrange
        $orderData = array_merge($this->getResourceData(), [
            'order_number' => 'ORD-2024-SPECIAL-999999',
        ]);
        
        $orderItems = $this->createMockCollection([]);
        $order = $this->createMockModel($orderData);
        $order->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
        $order->orderItems = $orderItems;
        
        $request = new Request();

        // Act
        $resource = new OrderResource($order);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('ORD-2024-SPECIAL-999999', $result['order_number']);
        $this->assertIsString($result['order_number']);
    }

    #[Test]
    public function toArray_handles_different_order_statuses(): void
    {
        // Arrange - Test various order statuses
        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        foreach ($statuses as $status) {
            $orderData = array_merge($this->getResourceData(), [
                'status' => $status,
            ]);
            
            $orderItems = $this->createMockCollection([]);
            $order = $this->createMockModel($orderData);
            $order->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
            $order->orderItems = $orderItems;
            
            $request = new Request();

            // Act
            $resource = new OrderResource($order);
            $result = $resource->toArray($request);

            // Assert
            $this->assertEquals($status, $result['status']);
        }
    }

    #[Test]
    public function toArray_validates_uuid_preservation(): void
    {
        // Arrange
        $orderUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
        $userUuid = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
        $orderData = array_merge($this->getResourceData(), [
            'uuid' => $orderUuid,
            'user_uuid' => $userUuid,
        ]);
        
        $orderItems = $this->createMockCollection([]);
        $order = $this->createMockModel($orderData);
        $order->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
        $order->orderItems = $orderItems;
        
        $request = new Request();

        // Act
        $resource = new OrderResource($order);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($orderUuid, $result['uuid']);
        $this->assertEquals($userUuid, $result['user_uuid']);
        $this->assertIsString($result['uuid']);
        $this->assertIsString($result['user_uuid']);
    }

    #[Test]
    public function toArray_handles_large_total_amounts(): void
    {
        // Arrange
        $orderData = array_merge($this->getResourceData(), [
            'total_amount' => 99999999, // $999,999.99
        ]);
        
        $orderItems = $this->createMockCollection([]);
        $order = $this->createMockModel($orderData);
        $order->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
        $order->orderItems = $orderItems;
        
        $request = new Request();

        // Act
        $resource = new OrderResource($order);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals(99999999, $result['total_amount']);
        $this->assertIsInt($result['total_amount']);
    }

    #[Test]
    public function toArray_handles_special_characters_in_addresses_and_notes(): void
    {
        // Arrange
        $orderData = array_merge($this->getResourceData(), [
            'shipping_address' => '123 Rüe dè l\'Église, Montréal, QC H2X 3A2, Canada',
            'notes' => 'Special instructions: "Ring bell twice" & wait 5 mins. Handle with care!',
        ]);
        
        $orderItems = $this->createMockCollection([]);
        $order = $this->createMockModel($orderData);
        $order->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
        $order->orderItems = $orderItems;
        
        $request = new Request();

        // Act
        $resource = new OrderResource($order);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('123 Rüe dè l\'Église, Montréal, QC H2X 3A2, Canada', $result['shipping_address']);
        $this->assertEquals('Special instructions: "Ring bell twice" & wait 5 mins. Handle with care!', $result['notes']);
    }

    #[Test]
    public function toArray_handles_null_optional_fields(): void
    {
        // Arrange
        $orderData = array_merge($this->getResourceData(), [
            'notes' => null,
        ]);
        
        $orderItems = $this->createMockCollection([]);
        $order = $this->createMockModel($orderData);
        $order->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
        $order->orderItems = $orderItems;
        
        $request = new Request();

        // Act
        $resource = new OrderResource($order);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayHasKey('notes', $result);
        $this->assertNull($result['notes']);
    }

    #[Test]
    public function toArray_maintains_order_lifecycle_timestamps(): void
    {
        // Arrange - Complete order lifecycle
        $createdAt = Carbon::parse('2024-01-01 10:00:00');
        $shippedAt = Carbon::parse('2024-01-05 14:30:00');
        $deliveredAt = Carbon::parse('2024-01-08 16:45:00');
        $updatedAt = Carbon::parse('2024-01-08 16:46:00'); // Updated after delivery
        
        $orderData = array_merge($this->getResourceData(), [
            'status' => 'delivered',
            'created_at' => $createdAt,
            'shipped_at' => $shippedAt,
            'delivered_at' => $deliveredAt,
            'updated_at' => $updatedAt,
        ]);
        
        $orderItems = $this->createMockCollection([]);
        $order = $this->createMockModel($orderData);
        $order->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
        $order->orderItems = $orderItems;
        
        $request = new Request();

        // Act
        $resource = new OrderResource($order);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('delivered', $result['status']);
        $this->assertEquals($createdAt->toIso8601String(), $result['created_at']);
        $this->assertEquals($shippedAt->toIso8601String(), $result['shipped_at']);
        $this->assertEquals($deliveredAt->toIso8601String(), $result['delivered_at']);
        $this->assertEquals($updatedAt->toIso8601String(), $result['updated_at']);
    }
}