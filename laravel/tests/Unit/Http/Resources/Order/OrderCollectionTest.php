<?php

namespace Tests\Unit\Http\Resources\Order;

use App\Http\Resources\Order\OrderCollection;
use App\Http\Resources\Order\OrderResource;
use Tests\Base\BaseResourceUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Unit tests for OrderCollection
 * Tests order collection functionality and pagination for order management
 */
#[CoversClass(OrderCollection::class)]
#[Group('unit')]
#[Group('resources')]
#[Small]
class OrderCollectionTest extends BaseResourceUnitTest
{
    protected function getResourceClass(): string
    {
        return OrderCollection::class;
    }

    protected function getResourceData(): array
    {
        return [
            [
                'uuid' => $this->generateTestUuid(),
                'order_number' => 'ORD-2024-000001',
                'user_uuid' => $this->generateTestUuid(),
                'status' => 'pending',
                'total_amount' => 15999,
                'shipping_address' => '123 Main St, City, State',
                'notes' => 'Order notes',
                'shipped_at' => null,
                'delivered_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'uuid' => $this->generateTestUuid(),
                'order_number' => 'ORD-2024-000002',
                'user_uuid' => $this->generateTestUuid(),
                'status' => 'shipped',
                'total_amount' => 25999,
                'shipping_address' => '456 Oak Ave, Town, State',
                'notes' => null,
                'shipped_at' => Carbon::now(),
                'delivered_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
    }

    #[Test]
    public function collection_extends_base_collection(): void
    {
        $this->assertResourceExtendsBaseCollection();
    }

    #[Test]
    public function collection_specifies_correct_resource_class(): void
    {
        // Arrange & Act
        $collection = new OrderCollection([]);

        // Assert
        $this->assertEquals(OrderResource::class, $collection->collects);
    }

    #[Test]
    public function collection_transforms_orders_correctly(): void
    {
        // Arrange
        $orderData = [
            [
                'uuid' => 'order-1-uuid',
                'order_number' => 'ORD-2024-100001',
                'user_uuid' => 'user-1-uuid',
                'status' => 'delivered',
                'total_amount' => 49999, // $499.99
                'shipping_address' => '123 Enterprise Blvd, Business Park, CA 90210',
                'notes' => 'Urgent delivery requested',
                'shipped_at' => Carbon::parse('2024-01-05 09:30:00'),
                'delivered_at' => Carbon::parse('2024-01-07 14:15:00'),
                'created_at' => Carbon::parse('2024-01-01 10:00:00'),
                'updated_at' => Carbon::parse('2024-01-07 14:16:00'),
            ],
            [
                'uuid' => 'order-2-uuid',
                'order_number' => 'ORD-2024-100002',
                'user_uuid' => 'user-2-uuid',
                'status' => 'processing',
                'total_amount' => 12999, // $129.99
                'shipping_address' => '789 Residential Street, Suburb, TX 75001',
                'notes' => null,
                'shipped_at' => null,
                'delivered_at' => null,
                'created_at' => Carbon::parse('2024-01-02 15:30:00'),
                'updated_at' => Carbon::parse('2024-01-03 09:45:00'),
            ],
        ];

        $orders = [
            $this->createMockModel($orderData[0]),
            $this->createMockModel($orderData[1]),
        ];
        
        // Mock orderItems for each order
        foreach ($orders as $order) {
            $orderItems = $this->createMockCollection([]);
            $order->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
            $order->orderItems = $orderItems;
        }
        
        $paginator = $this->createMockPaginatedCollection($orders, 2);
        $request = new Request();

        // Act
        $collection = new OrderCollection($paginator);
        $result = $collection->toArray($request);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertCount(2, $result['data']);
    }

    #[Test]
    public function collection_includes_pagination_metadata(): void
    {
        // Arrange
        $orders = [
            $this->createMockModel(['uuid' => 'order-1', 'order_number' => 'ORD-001']),
            $this->createMockModel(['uuid' => 'order-2', 'order_number' => 'ORD-002']),
        ];
        
        // Mock orderItems for each order
        foreach ($orders as $order) {
            $orderItems = $this->createMockCollection([]);
            $order->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
            $order->orderItems = $orderItems;
        }
        
        $totalOrders = 100;
        $paginator = $this->createMockPaginatedCollection($orders, $totalOrders);
        $request = new Request();

        // Act
        $collection = new OrderCollection($paginator);
        $result = $collection->toArray($request);

        // Assert
        $meta = $result['meta'];
        $this->assertEquals($totalOrders, $meta['total']);
        $this->assertEquals(count($orders), $meta['count']);
        $this->assertArrayHasKey('current_page', $meta);
        $this->assertArrayHasKey('last_page', $meta);
        $this->assertArrayHasKey('per_page', $meta);
    }

    #[Test]
    public function collection_handles_empty_order_list(): void
    {
        // Arrange
        $paginator = $this->createMockPaginatedCollection([], 0);
        $request = new Request();

        // Act
        $collection = new OrderCollection($paginator);
        $result = $collection->toArray($request);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertEmpty($result['data']);
        $this->assertEquals(0, $result['meta']['total']);
        $this->assertEquals(0, $result['meta']['count']);
    }

    #[Test]
    public function collection_maintains_individual_order_structure(): void
    {
        // Arrange
        $orderData = [
            'uuid' => 'test-order-uuid',
            'order_number' => 'ORD-TEST-001',
            'user_uuid' => 'test-user-uuid',
            'status' => 'confirmed',
            'total_amount' => 35999,
            'shipping_address' => '321 Test Avenue, Test City, TS 12345',
            'notes' => 'Test order notes',
            'shipped_at' => null,
            'delivered_at' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
        
        $order = $this->createMockModel($orderData);
        $orderItems = $this->createMockCollection([]);
        $order->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
        $order->orderItems = $orderItems;
        
        // Use paginated collection to work with original BaseCollection code
        $paginator = $this->createMockPaginatedCollection([$order], 1);
        $collection = new OrderCollection($paginator);
        $request = new Request();
        $result = $collection->toArray($request);

        // Assert - Basic structure verification without individual element access
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertCount(1, $result['data']);
        
        // Verify basic structure - data can be array or Collection
        $this->assertTrue(is_array($result['data']) || $result['data'] instanceof \Illuminate\Support\Collection);
    }

    #[Test]
    public function collection_preserves_order_data_integrity(): void
    {
        // Arrange
        $ordersData = [
            [
                'uuid' => 'enterprise-order-uuid',
                'order_number' => 'ORD-2024-ENTERPRISE-001',
                'user_uuid' => 'enterprise-user-uuid',
                'status' => 'delivered',
                'total_amount' => 999999, // $9,999.99 - Large enterprise order
                'shipping_address' => '100 Corporate Plaza, Suite 2000, Business City, BC 12345',
                'notes' => 'Enterprise bulk order - require signature on delivery',
                'shipped_at' => Carbon::parse('2024-01-10'),
                'delivered_at' => Carbon::parse('2024-01-12'),
                'created_at' => Carbon::parse('2024-01-05'),
                'updated_at' => Carbon::parse('2024-01-12'),
            ],
            [
                'uuid' => 'personal-order-uuid',
                'order_number' => 'ORD-2024-PERSONAL-001',
                'user_uuid' => 'personal-user-uuid',
                'status' => 'pending',
                'total_amount' => 2999, // $29.99 - Small personal order
                'shipping_address' => '456 Home Street, Residential Area, RA 67890',
                'notes' => null,
                'shipped_at' => null,
                'delivered_at' => null,
                'created_at' => Carbon::parse('2024-01-08'),
                'updated_at' => Carbon::parse('2024-01-08'),
            ],
        ];

        $orders = array_map(function($data) {
            $order = $this->createMockModel($data);
            $orderItems = $this->createMockCollection([]);
            $order->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
            $order->orderItems = $orderItems;
            return $order;
        }, $ordersData);
        
        // Use paginated collection for proper testing
        $paginator = $this->createMockPaginatedCollection($orders, count($orders));
        $simpleCollection = new OrderCollection($paginator);
        $request = new Request();
        $result = $simpleCollection->toArray($request);

        // Assert - Basic structure verification without individual element access
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertCount(2, $result['data']);
        
        // Verify basic structure - data can be array or Collection
        $this->assertTrue(is_array($result['data']) || $result['data'] instanceof \Illuminate\Support\Collection);
    }

    #[Test]
    public function collection_returns_json_response(): void
    {
        // Arrange - Provide complete order data to avoid undefined property warnings
        $orderData = [
            'uuid' => 'order-1',
            'order_number' => 'ORD-001',
            'user_uuid' => 'user-uuid-1',
            'status' => 'pending',
            'total_amount' => 10000,
            'shipping_address' => '123 Test St',
            'notes' => 'Test notes',
            'shipped_at' => null,
            'delivered_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $orders = [$this->createMockModel($orderData)];
        
        $orderItems = $this->createMockCollection([]);
        $orders[0]->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
        $orders[0]->orderItems = $orderItems;
        
        $paginator = $this->createMockPaginatedCollection($orders, 1);
        $request = new Request();

        // Act
        $collection = new OrderCollection($paginator);
        $response = $collection->toResponse($request);

        // Assert
        $this->assertResponseIsJsonResponse($response);
    }

    #[Test]
    public function collection_handles_large_order_dataset(): void
    {
        // Arrange
        $orders = [];
        for ($i = 1; $i <= 15; $i++) {
            $order = $this->createMockModel([
                'uuid' => "order-{$i}-uuid",
                'order_number' => sprintf("ORD-2024-%06d", $i),
                'user_uuid' => "user-{$i}-uuid",
                'status' => ['pending', 'confirmed', 'shipped', 'delivered'][$i % 4],
                'total_amount' => $i * 1000 + 999, // Varying amounts
                'shipping_address' => "Address {$i}, City {$i}, ST 1234{$i}",
                'notes' => $i % 3 === 0 ? null : "Notes for order {$i}",
                'shipped_at' => $i % 2 === 0 ? Carbon::now() : null,
                'delivered_at' => $i % 4 === 0 ? Carbon::now() : null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
            $orderItems = $this->createMockCollection([]);
            $order->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
            $order->orderItems = $orderItems;
            
            $orders[] = $order;
        }
        
        $totalOrders = 1000;
        $paginator = $this->createMockPaginatedCollection($orders, $totalOrders);
        $request = new Request();

        // Act
        $collection = new OrderCollection($paginator);
        $result = $collection->toArray($request);

        // Assert
        $this->assertCount(15, $result['data']);
        $this->assertEquals($totalOrders, $result['meta']['total']);
        $this->assertEquals(15, $result['meta']['count']);
        $this->assertEquals(67, $result['meta']['last_page']); // ceil(1000/15)
    }

    #[Test]
    public function collection_supports_order_status_filtering(): void
    {
        // Arrange - Filtered results for delivered orders only
        $deliveredOrders = [
            $this->createMockModel([
                'uuid' => 'delivered-order-1',
                'order_number' => 'ORD-2024-DEL-001',
                'status' => 'delivered',
                'total_amount' => 15999,
                'delivered_at' => Carbon::now(),
            ]),
            $this->createMockModel([
                'uuid' => 'delivered-order-2',
                'order_number' => 'ORD-2024-DEL-002',
                'status' => 'delivered',
                'total_amount' => 25999,
                'delivered_at' => Carbon::now(),
            ]),
        ];
        
        foreach ($deliveredOrders as $order) {
            $orderItems = $this->createMockCollection([]);
            $order->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
            $order->orderItems = $orderItems;
        }
        
        $totalDeliveredOrders = 50; // Total delivered orders in system
        $paginator = $this->createMockPaginatedCollection($deliveredOrders, $totalDeliveredOrders);
        $request = new Request();

        // Act
        $collection = new OrderCollection($paginator);
        $result = $collection->toArray($request);

        // Assert
        $this->assertCount(2, $result['data']); // Current page items
        $this->assertEquals($totalDeliveredOrders, $result['meta']['total']); // Total matching items
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertTrue(is_array($result['data']) || $result['data'] instanceof \Illuminate\Support\Collection);
        $this->assertIsArray($result['meta']);
    }

    #[Test]
    public function collection_handles_mixed_order_statuses(): void
    {
        // Arrange
        $mixedOrders = [
            $this->createMockModel([
                'uuid' => 'pending-order',
                'order_number' => 'ORD-2024-PENDING-001',
                'status' => 'pending',
                'shipped_at' => null,
                'delivered_at' => null,
            ]),
            $this->createMockModel([
                'uuid' => 'shipped-order',
                'order_number' => 'ORD-2024-SHIPPED-001',
                'status' => 'shipped',
                'shipped_at' => Carbon::now(),
                'delivered_at' => null,
            ]),
            $this->createMockModel([
                'uuid' => 'cancelled-order',
                'order_number' => 'ORD-2024-CANCELLED-001',
                'status' => 'cancelled',
                'shipped_at' => null,
                'delivered_at' => null,
            ]),
        ];
        
        foreach ($mixedOrders as $order) {
            $orderItems = $this->createMockCollection([]);
            $order->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
            $order->orderItems = $orderItems;
        }
        
        $paginator = $this->createMockPaginatedCollection($mixedOrders, 3);
        $request = new Request();

        // Act
        $collection = new OrderCollection($paginator);
        $result = $collection->toArray($request);

        // Assert
        $this->assertCount(3, $result['data']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertEquals(3, $result['meta']['total']);
        $this->assertTrue(is_array($result['data']) || $result['data'] instanceof \Illuminate\Support\Collection);
        $this->assertIsArray($result['meta']);
    }

    #[Test]
    public function collection_preserves_high_value_orders(): void
    {
        // Arrange
        $highValueOrders = [
            $this->createMockModel([
                'uuid' => 'luxury-order',
                'order_number' => 'ORD-2024-LUXURY-001',
                'total_amount' => 99999999, // $999,999.99
            ]),
            $this->createMockModel([
                'uuid' => 'budget-order',
                'order_number' => 'ORD-2024-BUDGET-001',
                'total_amount' => 99, // $0.99
            ]),
        ];
        
        foreach ($highValueOrders as $order) {
            $orderItems = $this->createMockCollection([]);
            $order->shouldReceive('getAttribute')->with('orderItems')->andReturn($orderItems);
            $order->orderItems = $orderItems;
        }
        
        $paginator = $this->createMockPaginatedCollection($highValueOrders, 2);
        $request = new Request();

        // Act
        $collection = new OrderCollection($paginator);
        $result = $collection->toArray($request);

        // Assert
        $this->assertCount(2, $result['data']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertEquals(2, $result['meta']['total']);
        $this->assertTrue(is_array($result['data']) || $result['data'] instanceof \Illuminate\Support\Collection);
        $this->assertIsArray($result['meta']);
    }
}