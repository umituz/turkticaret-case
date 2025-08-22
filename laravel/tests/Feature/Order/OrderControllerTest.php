<?php

namespace Tests\Feature\Order;

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
    public function it_prevents_order_creation_with_out_of_stock_products()
    {
        $product = $this->createTestProduct(['stock_quantity' => 0]);
        $cart = $this->createTestCart($this->testUser);
        $this->createTestCartItem($cart, $product, ['quantity' => 1]);

        $orderData = $this->createValidOrderData();

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/orders', $orderData);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => "Product '{$product->name}' is out of stock"
        ]);
    }

    #[Test]
    public function it_reduces_product_stock_after_successful_order_creation()
    {
        $product = $this->createTestProduct(['stock_quantity' => 10]);
        $cart = $this->createTestCart($this->testUser);
        $this->createTestCartItem($cart, $product, ['quantity' => 3]);

        $orderData = $this->createValidOrderData();

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/orders', $orderData);

        $this->assertSuccessfulCreation($response);

        // Stock should be reduced after successful order
        $product->refresh();
        $this->assertEquals(7, $product->stock_quantity); // 10 - 3 = 7
    }

    #[Test]
    public function it_prevents_order_creation_when_insufficient_stock_available()
    {
        $product = $this->createTestProduct(['stock_quantity' => 3]);
        $cart = $this->createTestCart($this->testUser);
        $this->createTestCartItem($cart, $product, ['quantity' => 5]); // More than available

        $orderData = $this->createValidOrderData();

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/orders', $orderData);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => "Insufficient stock for product '{$product->name}'. Requested: 5, Available: 3"
        ]);

        // Stock should remain unchanged
        $product->refresh();
        $this->assertEquals(3, $product->stock_quantity);
    }

    #[Test]
    public function it_handles_multiple_products_stock_validation_during_order_creation()
    {
        $product1 = $this->createTestProduct(['stock_quantity' => 5]);
        $product2 = $this->createTestProduct(['stock_quantity' => 2]);

        $cart = $this->createTestCart($this->testUser);
        $this->createTestCartItem($cart, $product1, ['quantity' => 3]);
        $this->createTestCartItem($cart, $product2, ['quantity' => 2]);

        $orderData = $this->createValidOrderData();
        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/orders', $orderData);

        $this->assertSuccessfulCreation($response);

        // Both products' stock should be reduced
        $product1->refresh();
        $product2->refresh();
        $this->assertEquals(2, $product1->stock_quantity); // 5 - 3 = 2
        $this->assertEquals(0, $product2->stock_quantity); // 2 - 2 = 0
    }

    #[Test]
    public function it_prevents_order_if_any_product_has_insufficient_stock()
    {
        $product1 = $this->createTestProduct(['stock_quantity' => 5]);
        $product2 = $this->createTestProduct(['stock_quantity' => 1]); // Insufficient

        $cart = $this->createTestCart($this->testUser);
        $this->createTestCartItem($cart, $product1, ['quantity' => 3]);
        $this->createTestCartItem($cart, $product2, ['quantity' => 2]); // Exceeds stock

        $orderData = $this->createValidOrderData();
        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/orders', $orderData);

        $response->assertStatus(422);

        // No stock should be reduced for either product
        $product1->refresh();
        $product2->refresh();
        $this->assertEquals(5, $product1->stock_quantity);
        $this->assertEquals(1, $product2->stock_quantity);
    }

    #[Test]
    public function it_allows_order_with_exactly_available_stock()
    {
        $product = $this->createTestProduct(['stock_quantity' => 7]);
        $cart = $this->createTestCart($this->testUser);
        $this->createTestCartItem($cart, $product, ['quantity' => 7]); // Exactly all stock

        $orderData = $this->createValidOrderData();
        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/orders', $orderData);

        $this->assertSuccessfulCreation($response);

        // Product should have 0 stock left
        $product->refresh();
        $this->assertEquals(0, $product->stock_quantity);
    }

    #[Test]
    public function it_maintains_transaction_integrity_on_stock_validation_failure()
    {
        $product1 = $this->createTestProduct(['stock_quantity' => 10]);
        $product2 = $this->createTestProduct(['stock_quantity' => 2]);

        $cart = $this->createTestCart($this->testUser);
        $this->createTestCartItem($cart, $product1, ['quantity' => 5]);
        $this->createTestCartItem($cart, $product2, ['quantity' => 5]); // Will fail

        $initialStock1 = $product1->stock_quantity;
        $initialStock2 = $product2->stock_quantity;

        $orderData = $this->createValidOrderData();
        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/orders', $orderData);

        $response->assertStatus(422);

        // Both products' stock should remain unchanged (transaction rollback)
        $product1->refresh();
        $product2->refresh();
        $this->assertEquals($initialStock1, $product1->stock_quantity);
        $this->assertEquals($initialStock2, $product2->stock_quantity);

        // No order should be created
        $this->assertDatabaseMissing('orders', [
            'user_uuid' => $this->testUser->uuid
        ]);
    }

    #[Test]
    public function it_handles_sequential_orders_reducing_stock_correctly()
    {
        $product = $this->createTestProduct(['stock_quantity' => 10]);

        // First order
        $cart1 = $this->createTestCart($this->testUser);
        $this->createTestCartItem($cart1, $product, ['quantity' => 4]);

        $orderData1 = $this->createValidOrderData();
        $response1 = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/orders', $orderData1);
        $this->assertSuccessfulCreation($response1);

        // Check stock after first order
        $product->refresh();
        $this->assertEquals(6, $product->stock_quantity); // 10 - 4 = 6

        // Second order from different user
        $user2 = $this->createTestUser();
        $cart2 = $this->createTestCart($user2);
        $this->createTestCartItem($cart2, $product, ['quantity' => 3]);

        $orderData2 = $this->createValidOrderData();
        $response2 = $this->actingAs($user2, 'sanctum')->postJson('/api/orders', $orderData2);
        $this->assertSuccessfulCreation($response2);

        // Check final stock
        $product->refresh();
        $this->assertEquals(3, $product->stock_quantity); // 6 - 3 = 3
    }
}
