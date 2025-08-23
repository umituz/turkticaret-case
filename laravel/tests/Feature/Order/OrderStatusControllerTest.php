<?php

namespace Tests\Feature\Order;

use Tests\Base\BaseFeatureTest;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Order\Order;
use App\Enums\Order\OrderStatusEnum;

class OrderStatusControllerTest extends BaseFeatureTest
{
    private Order $testOrder;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->testOrder = $this->createOrderWithItems($this->testUser, 1);
    }

    #[Test]
    public function it_can_update_order_status()
    {
        $response = $this->actingAs($this->testUser, 'sanctum')->patchJson(
            "/api/orders/{$this->testOrder->uuid}/status",
            ['status' => OrderStatusEnum::CONFIRMED->value]
        );

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'uuid',
                'status',
                'created_at',
                'updated_at'
            ]
        ]);

        $this->assertDatabaseHas('orders', [
            'uuid' => $this->testOrder->uuid,
            'status' => OrderStatusEnum::CONFIRMED->value,
        ]);
    }

    #[Test]
    public function it_validates_required_status_field()
    {
        $response = $this->actingAs($this->testUser, 'sanctum')->patchJson(
            "/api/orders/{$this->testOrder->uuid}/status",
            []
        );

        $this->assertValidationErrorResponse($response, ['status']);
    }

    #[Test]
    public function it_validates_valid_status_enum()
    {
        $response = $this->actingAs($this->testUser, 'sanctum')->patchJson(
            "/api/orders/{$this->testOrder->uuid}/status",
            ['status' => 'invalid_status']
        );

        $this->assertValidationErrorResponse($response, ['status']);
    }

    #[Test]
    public function it_prevents_unauthorized_users_from_updating_order_status()
    {
        $otherUser = $this->createTestUser();
        
        $response = $this->actingAs($otherUser, 'sanctum')->patchJson(
            "/api/orders/{$this->testOrder->uuid}/status",
            ['status' => OrderStatusEnum::CONFIRMED->value]
        );

        $response->assertStatus(403);
    }

    #[Test]
    public function it_requires_authentication_to_update_order_status()
    {
        $response = $this->patchJson(
            "/api/orders/{$this->testOrder->uuid}/status",
            ['status' => OrderStatusEnum::CONFIRMED->value]
        );

        $this->assertUnauthorizedResponse($response);
    }


    #[Test]
    public function it_can_get_status_history()
    {
        $response = $this->actingAs($this->testUser, 'sanctum')->getJson(
            "/api/orders/{$this->testOrder->uuid}/status/history"
        );

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'order_uuid',
                'current_status',
                'history'
            ]
        ]);

        $response->assertJson([
            'data' => [
                'order_uuid' => $this->testOrder->uuid,
                'current_status' => OrderStatusEnum::PENDING->value,
            ]
        ]);
    }

    #[Test]
    public function it_prevents_unauthorized_users_from_viewing_status_history()
    {
        $otherUser = $this->createTestUser();
        
        $response = $this->actingAs($otherUser, 'sanctum')->getJson(
            "/api/orders/{$this->testOrder->uuid}/status/history"
        );

        $response->assertStatus(403);
    }

    #[Test]
    public function it_requires_authentication_to_view_status_history()
    {
        $response = $this->getJson(
            "/api/orders/{$this->testOrder->uuid}/status/history"
        );

        $this->assertUnauthorizedResponse($response);
    }

    #[Test]
    public function it_returns_404_for_non_existent_order()
    {
        $fakeUuid = fake()->uuid();
        
        $response = $this->actingAs($this->testUser, 'sanctum')->patchJson(
            "/api/orders/{$fakeUuid}/status",
            ['status' => OrderStatusEnum::CONFIRMED->value]
        );

        $response->assertStatus(404);
    }

    #[Test]
    public function it_handles_status_transition_validation()
    {
        $this->testOrder->update(['status' => OrderStatusEnum::DELIVERED]);
        
        $response = $this->actingAs($this->testUser, 'sanctum')->patchJson(
            "/api/orders/{$this->testOrder->uuid}/status",
            ['status' => OrderStatusEnum::PROCESSING->value]
        );

        $response->assertStatus(422);
    }

}