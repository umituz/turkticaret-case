<?php

namespace Tests\Feature;

use Tests\Base\BaseFeatureTest;
use PHPUnit\Framework\Attributes\Test;

class SimpleApiTest extends BaseFeatureTest
{
    #[Test]
    public function it_can_register_a_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/api/register', $userData);
        
        $response->assertStatus(201);
    }

    #[Test]
    public function it_can_access_health_endpoint()
    {
        $response = $this->getJson('/api/health');
        
        $response->assertStatus(200);
    }
}