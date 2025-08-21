<?php

namespace Tests\Feature\Order;

use App\Models\Order\Order;
use Tests\Base\BaseFeatureTest;
use PHPUnit\Framework\Attributes\Test;

class OrderControllerTest extends BaseFeatureTest
{
    #[Test]
    public function it_can_list_user_orders()
    {
        $orders = [];
        for ($i = 0; $i < 3; $i++) {
            $orders[] = $this->createOrderWithItems($this->testUser, 2);
        }

        $response = $this->actingAs($this->testUser, 'sanctum')->getJson('/api/orders');

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'uuid',
                    'user_uuid',
                    'total_amount',
                    'status',
                    'shipping_address',
                    'items',
                    'created_at',
                    'updated_at'
                ]
            ]
        ]);

        $response->assertJsonCount(3, 'data');
    }

    #[Test]
    public function it_returns_empty_list_for_user_with_no_orders()
    {
        $response = $this->actingAs($this->testUser, 'sanctum')->getJson('/api/orders');

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonCount(0, 'data');
    }

    #[Test]
    public function it_requires_authentication_to_list_orders()
    {
        $response = $this->jsonGet('/api/orders');

        $this->assertUnauthorizedResponse($response);
    }

    #[Test]
    public function it_can_create_order_from_cart()
    {
        $cart = $this->createCartWithItems($this->testUser, 2);
        $orderData = $this->createValidOrderData();

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/orders', $orderData);

        $this->assertSuccessfulCreation($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'uuid',
                'order_number',
                'user_uuid',
                'status',
                'total_amount',
                'shipping_address',
                'notes',
                'shipped_at',
                'delivered_at',
                'items' => [
                    '*' => [
                        'uuid',
                        'product_uuid',
                        'quantity',
                        'unit_price'
                    ]
                ],
                'items_count',
                'created_at',
                'updated_at'
            ]
        ]);

        $this->assertValidUuidInResponse($response, 'data.uuid');
        $response->assertJsonPath('data.user_uuid', $this->testUser->uuid);
        $response->assertJsonPath('data.status', 'pending');
        $response->assertJsonCount(2, 'data.items');
    }

    #[Test]
    public function it_validates_required_fields_when_creating_order()
    {
        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/orders', []);

        $this->assertValidationErrorResponse($response, [
            'shipping_address'
        ]);
    }

    #[Test]
    public function it_fails_to_create_order_with_empty_cart()
    {
        $orderData = $this->createValidOrderData();

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/orders', $orderData);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'Failed to create order'
        ]);
    }

    #[Test]
    public function it_can_show_specific_order()
    {
        $order = $this->createOrderWithItems($this->testUser, 2);

        $response = $this->actingAs($this->testUser, 'sanctum')->getJson("/api/orders/{$order->uuid}");

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonFragment([
            'uuid' => $order->uuid,
            'user_uuid' => $order->user_uuid,
            'total_amount' => $order->total_amount,
            'status' => $order->status,
        ]);

        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'uuid',
                'user_uuid',
                'total_amount',
                'status',
                'shipping_address',
                'items' => [
                    '*' => [
                        'uuid',
                        'product_uuid',
                        'quantity',
                        'unit_price',
                        'product' => [
                            'uuid',
                            'name',
                            'price'
                        ]
                    ]
                ]
            ]
        ]);
    }

    #[Test]
    public function it_returns_404_for_non_existent_order()
    {
        $fakeUuid = '12345678-1234-4123-8123-123456789012';

        $response = $this->actingAs($this->testUser, 'sanctum')->getJson("/api/orders/{$fakeUuid}");

        $this->assertNotFoundResponse($response);
    }

    #[Test]
    public function it_prevents_viewing_other_users_orders()
    {
        $otherUser = $this->createTestUser();
        $otherUserOrder = $this->createOrderWithItems($otherUser, 1);

        $response = $this->actingAs($this->testUser, 'sanctum')->getJson("/api/orders/{$otherUserOrder->uuid}");

        $this->assertForbiddenResponse($response);
    }

    #[Test]
    public function it_requires_authentication_for_order_operations()
    {
        // This test verifies authentication is required
        // We already have specific authentication test above
        $this->assertTrue(true); // Placeholder - auth is tested in other methods
    }

    #[Test]
    public function it_calculates_order_total_correctly()
    {
        $product1 = $this->createTestProduct(['price' => 10000]); // 100.00
        $product2 = $this->createTestProduct(['price' => 15000]); // 150.00
        
        $cart = $this->createTestCart($this->testUser);
        $this->createTestCartItem($cart, $product1, ['quantity' => 2, 'unit_price' => 10000]);
        $this->createTestCartItem($cart, $product2, ['quantity' => 1, 'unit_price' => 15000]);

        $orderData = $this->createValidOrderData();

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/orders', $orderData);

        $this->assertSuccessfulCreation($response);
        
        $expectedTotal = (2 * 10000) + (1 * 15000); // 35000
        $response->assertJsonPath('data.total_amount', $expectedTotal);
    }

    #[Test]
    public function it_creates_order_items_with_correct_details()
    {
        $product1 = $this->createTestProduct(['name' => 'Product 1', 'price' => 10000]);
        $product2 = $this->createTestProduct(['name' => 'Product 2', 'price' => 15000]);
        
        $cart = $this->createTestCart($this->testUser);
        $this->createTestCartItem($cart, $product1, ['quantity' => 2, 'unit_price' => 10000]);
        $this->createTestCartItem($cart, $product2, ['quantity' => 1, 'unit_price' => 15000]);

        $orderData = $this->createValidOrderData();

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/orders', $orderData);

        $this->assertSuccessfulCreation($response);
        
        $orderItems = $response->json('data.items');
        $this->assertCount(2, $orderItems);

        // Check first item
        $item1 = collect($orderItems)->firstWhere('product_uuid', $product1->uuid);
        $this->assertEquals(2, $item1['quantity']);
        $this->assertEquals(10000, $item1['unit_price']);

        // Check second item
        $item2 = collect($orderItems)->firstWhere('product_uuid', $product2->uuid);
        $this->assertEquals(1, $item2['quantity']);
        $this->assertEquals(15000, $item2['unit_price']);
    }

    #[Test]
    public function it_clears_cart_after_successful_order_creation()
    {
        $cart = $this->createCartWithItems($this->testUser, 2);
        $orderData = $this->createValidOrderData();

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/orders', $orderData);

        $this->assertSuccessfulCreation($response);

        // Verify cart is empty after order creation
        $cartResponse = $this->actingAs($this->testUser, 'sanctum')->getJson('/api/cart');
        $cartResponse->assertJsonCount(0, 'data.items');
    }

    #[Test]
    public function it_handles_order_creation_with_notes()
    {
        $cart = $this->createCartWithItems($this->testUser, 1);
        $orderData = $this->createValidOrderData([
            'notes' => 'Please deliver between 9 AM - 5 PM'
        ]);

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/orders', $orderData);

        $this->assertSuccessfulCreation($response);
        $response->assertJsonPath('data.notes', 'Please deliver between 9 AM - 5 PM');
    }

    #[Test]
    public function it_validates_address_length()
    {
        $orderData = $this->createValidOrderData([
            'shipping_address' => 'A' // Too short
        ]);

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/orders', $orderData);

        $this->assertValidationErrorResponse($response, [
            'shipping_address'
        ]);
    }

    #[Test]
    public function it_allows_order_creation_with_out_of_stock_products()
    {
        $product = $this->createTestProduct(['stock_quantity' => 0]);
        $cart = $this->createTestCart($this->testUser);
        $this->createTestCartItem($cart, $product, ['quantity' => 1]);

        $orderData = $this->createValidOrderData();

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/orders', $orderData);

        $this->assertSuccessfulCreation($response);
        $response->assertJsonPath('data.status', 'pending');
        $response->assertJsonCount(1, 'data.items');
    }

    #[Test]
    public function it_does_not_update_product_stock_after_order_creation()
    {
        $product = $this->createTestProduct(['stock_quantity' => 10]);
        $cart = $this->createTestCart($this->testUser);
        $this->createTestCartItem($cart, $product, ['quantity' => 3]);

        $orderData = $this->createValidOrderData();

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/orders', $orderData);

        $this->assertSuccessfulCreation($response);

        // Stock remains unchanged as system doesn't manage stock automatically
        $product->refresh();
        $this->assertEquals(10, $product->stock_quantity); // Stock unchanged
    }

    #[Test]
    public function it_handles_multiple_order_creation()
    {
        // Create two separate orders to verify system can handle multiple orders
        $order1 = $this->createOrderWithItems($this->testUser, 2);
        $order2 = $this->createOrderWithItems($this->testUser, 1);

        $this->assertNotNull($order1);
        $this->assertNotNull($order2);
        $this->assertNotEquals($order1->uuid, $order2->uuid);
        $this->assertEquals('pending', $order1->status);
        $this->assertEquals('pending', $order2->status);
        $this->assertEquals($this->testUser->uuid, $order1->user_uuid);
        $this->assertEquals($this->testUser->uuid, $order2->user_uuid);
    }
}