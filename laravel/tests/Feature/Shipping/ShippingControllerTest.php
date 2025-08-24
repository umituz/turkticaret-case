<?php

namespace Tests\Feature\Shipping;

use Tests\Base\BaseFeatureTest;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Shipping\ShippingMethod;

class ShippingControllerTest extends BaseFeatureTest
{
    #[Test]
    public function it_can_get_shipping_methods()
    {
        ShippingMethod::factory()->count(3)->create(['is_active' => true]);
        
        $response = $this->getJson('/api/shipping/methods');

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'uuid',
                    'name',
                    'description',
                    'cost',
                    'estimated_days',
                    'is_active',
                    'created_at',
                    'updated_at',
                ]
            ]
        ]);
    }

    #[Test]
    public function it_allows_public_access_to_shipping_methods()
    {
        ShippingMethod::factory()->count(2)->create(['is_active' => true]);
        
        $response = $this->getJson('/api/shipping/methods');

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonCount(2, 'data');
    }

    #[Test]
    public function it_only_returns_active_shipping_methods()
    {
        ShippingMethod::factory()->count(2)->create(['is_active' => true]);
        ShippingMethod::factory()->count(1)->create(['is_active' => false]);
        
        $response = $this->getJson('/api/shipping/methods');

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonCount(2, 'data');
        
        // Ensure all returned methods are active
        $data = $response->json('data');
        foreach ($data as $method) {
            $this->assertTrue($method['is_active']);
        }
    }

    #[Test]
    public function it_returns_empty_array_when_no_active_shipping_methods_exist()
    {
        ShippingMethod::factory()->count(2)->create(['is_active' => false]);
        
        $response = $this->getJson('/api/shipping/methods');

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonCount(0, 'data');
    }

    #[Test]
    public function it_returns_shipping_methods_with_correct_data_types()
    {
        $method = ShippingMethod::factory()->create([
            'is_active' => true,
            'cost' => 1500, // 15.00 in cents
            'estimated_days' => 3
        ]);
        
        $response = $this->getJson('/api/shipping/methods');

        $this->assertSuccessfulJsonResponse($response);
        $data = $response->json('data.0');
        
        $this->assertIsString($data['uuid']);
        $this->assertIsString($data['name']);
        $this->assertIsString($data['description']);
        $this->assertIsInt($data['cost']);
        $this->assertIsInt($data['estimated_days']);
        $this->assertIsBool($data['is_active']);
        $this->assertTrue($data['is_active']);
    }
}