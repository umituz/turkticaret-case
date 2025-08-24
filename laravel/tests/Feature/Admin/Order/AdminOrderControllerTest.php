<?php

namespace Tests\Feature\Admin\Order;

use Tests\Base\BaseFeatureTest;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Order\Order;
use App\Models\User\User;
use App\Enums\Order\OrderStatusEnum;

class AdminOrderControllerTest extends BaseFeatureTest
{
    #[Test]
    public function it_can_list_all_orders()
    {
        Order::factory()->count(5)->create();
        $adminUser = $this->setupAdminTest();
        
        $response = $this->actingAs($adminUser, 'sanctum')->getJson('/api/admin/orders');

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'data' => [
                    '*' => [
                        'uuid',
                        'order_number',
                        'user_uuid',
                        'status',
                        'total_amount',
                        'currency_code',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'current_page',
                'per_page',
                'total',
            ]
        ]);
    }

    #[Test]
    public function it_requires_admin_role_to_list_orders()
    {
        $user = $this->testUser; // Regular user
        
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/admin/orders');

        $response->assertStatus(403);
    }

    #[Test]
    public function it_requires_authentication_to_list_orders()
    {
        $response = $this->getJson('/api/admin/orders');

        $response->assertStatus(401);
    }

    #[Test]
    public function it_can_filter_orders_by_status()
    {
        Order::factory()->count(3)->create(['status' => OrderStatusEnum::PENDING]);
        Order::factory()->count(2)->create(['status' => OrderStatusEnum::PROCESSING]);
        $adminUser = $this->setupAdminTest();
        
        $response = $this->actingAs($adminUser, 'sanctum')->getJson('/api/admin/orders?status=pending');

        $this->assertSuccessfulJsonResponse($response);
        $data = $response->json('data.data');
        $this->assertCount(3, $data);
        
        foreach ($data as $order) {
            $this->assertEquals('pending', $order['status']);
        }
    }

    #[Test]
    public function it_can_filter_orders_by_user()
    {
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['user_uuid' => $user->uuid]);
        Order::factory()->count(2)->create(); // Different users
        $adminUser = $this->setupAdminTest();
        
        $response = $this->actingAs($adminUser, 'sanctum')->getJson("/api/admin/orders?user_uuid={$user->uuid}");

        $this->assertSuccessfulJsonResponse($response);
        $data = $response->json('data.data');
        $this->assertCount(3, $data);
        
        foreach ($data as $order) {
            $this->assertEquals($user->uuid, $order['user_uuid']);
        }
    }

    #[Test]
    public function it_can_show_specific_order_with_details()
    {
        $order = Order::factory()->withItems()->create();
        $adminUser = $this->setupAdminTest();
        
        $response = $this->actingAs($adminUser, 'sanctum')->getJson("/api/admin/orders/{$order->uuid}");

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'uuid',
                'order_number',
                'user_uuid',
                'status',
                'total_amount',
                'currency_code',
                'order_items' => [
                    '*' => [
                        'uuid',
                        'product_uuid',
                        'quantity',
                        'unit_price',
                        'total_price',
                        'product'
                    ]
                ],
                'user',
                'created_at',
                'updated_at',
            ]
        ]);
        $response->assertJsonFragment([
            'uuid' => $order->uuid,
            'order_number' => $order->order_number,
        ]);
    }

    #[Test]
    public function it_can_update_order_status()
    {
        $order = Order::factory()->create(['status' => OrderStatusEnum::PENDING]);
        $adminUser = $this->setupAdminTest();
        
        $response = $this->actingAs($adminUser, 'sanctum')->patchJson("/api/admin/orders/{$order->uuid}/status", [
            'status' => 'processing'
        ]);

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonFragment([
            'message' => 'Order status updated successfully'
        ]);

        $this->assertDatabaseHas('orders', [
            'uuid' => $order->uuid,
            'status' => OrderStatusEnum::PROCESSING,
        ]);
    }

    #[Test]
    public function it_validates_status_when_updating_order_status()
    {
        $order = Order::factory()->create();
        $adminUser = $this->setupAdminTest();
        
        $response = $this->actingAs($adminUser, 'sanctum')->patchJson("/api/admin/orders/{$order->uuid}/status", [
            'status' => 'invalid_status'
        ]);

        $this->assertValidationErrorResponse($response);
        $response->assertJsonValidationErrors(['status']);
    }

    #[Test]
    public function it_requires_status_field_when_updating_order_status()
    {
        $order = Order::factory()->create();
        $adminUser = $this->setupAdminTest();
        
        $response = $this->actingAs($adminUser, 'sanctum')->patchJson("/api/admin/orders/{$order->uuid}/status", []);

        $this->assertValidationErrorResponse($response);
        $response->assertJsonValidationErrors(['status']);
    }

    #[Test]
    public function it_can_get_order_statistics()
    {
        Order::factory()->count(3)->create(['status' => OrderStatusEnum::PENDING]);
        Order::factory()->count(2)->create(['status' => OrderStatusEnum::PROCESSING]);
        Order::factory()->count(1)->create(['status' => OrderStatusEnum::DELIVERED]);
        $adminUser = $this->setupAdminTest();
        
        $response = $this->actingAs($adminUser, 'sanctum')->getJson('/api/admin/orders/statistics');

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'total_orders',
                'orders_by_status',
                'total_revenue',
                'average_order_value',
            ]
        ]);
    }

    #[Test]
    public function it_can_get_order_status_history()
    {
        $order = Order::factory()->create();
        $adminUser = $this->setupAdminTest();
        
        $response = $this->actingAs($adminUser, 'sanctum')->getJson("/api/admin/orders/{$order->uuid}/status/history");

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'status',
                    'changed_at',
                    'notes'
                ]
            ]
        ]);
    }

    #[Test]
    public function it_requires_admin_role_for_all_admin_operations()
    {
        $user = $this->testUser; // Regular user
        $order = Order::factory()->create();

        $endpoints = [
            ['GET', '/api/admin/orders'],
            ['GET', '/api/admin/orders/statistics'],
            ['GET', "/api/admin/orders/{$order->uuid}"],
            ['PATCH', "/api/admin/orders/{$order->uuid}/status"],
            ['GET', "/api/admin/orders/{$order->uuid}/status/history"],
        ];

        foreach ($endpoints as [$method, $url]) {
            $response = $this->actingAs($user, 'sanctum')->json($method, $url, ['status' => 'processing']);
            $response->assertStatus(403);
        }
    }

    #[Test]
    public function it_requires_authentication_for_all_admin_operations()
    {
        $order = Order::factory()->create();

        $endpoints = [
            ['GET', '/api/admin/orders'],
            ['GET', '/api/admin/orders/statistics'],
            ['GET', "/api/admin/orders/{$order->uuid}"],
            ['PATCH', "/api/admin/orders/{$order->uuid}/status"],
            ['GET', "/api/admin/orders/{$order->uuid}/status/history"],
        ];

        foreach ($endpoints as [$method, $url]) {
            $response = $this->json($method, $url, ['status' => 'processing']);
            $response->assertStatus(401);
        }
    }

    #[Test]
    public function it_returns_404_for_non_existent_order()
    {
        $adminUser = $this->setupAdminTest();
        $nonExistentUuid = '550e8400-e29b-41d4-a716-446655440000';
        
        $response = $this->actingAs($adminUser, 'sanctum')->getJson("/api/admin/orders/{$nonExistentUuid}");

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_filter_orders_by_date_range()
    {
        $yesterday = now()->subDay();
        $tomorrow = now()->addDay();
        
        Order::factory()->count(2)->create(['created_at' => $yesterday]);
        Order::factory()->count(3)->create(['created_at' => now()]);
        Order::factory()->count(1)->create(['created_at' => $tomorrow]);
        
        $adminUser = $this->setupAdminTest();
        
        $response = $this->actingAs($adminUser, 'sanctum')->getJson('/api/admin/orders?' . http_build_query([
            'date_from' => now()->startOfDay()->toISOString(),
            'date_to' => now()->endOfDay()->toISOString(),
        ]));

        $this->assertSuccessfulJsonResponse($response);
        $data = $response->json('data.data');
        $this->assertCount(3, $data);
    }
}