<?php

namespace Tests\Feature;

use Tests\Base\BaseFeatureTest;
use PHPUnit\Framework\Attributes\Test;

class HealthControllerTest extends BaseFeatureTest
{
    #[Test]
    public function it_returns_healthy_status()
    {
        $response = $this->getJson('/api/health');

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJson([
            'success' => true,
            'message' => 'Service is healthy',
            'data' => [
                'status' => 'healthy'
            ]
        ]);
        
        $this->assertValidTimestampInResponse($response, 'data.timestamp');
    }

    #[Test]
    public function it_returns_health_check_without_authentication()
    {
        $response = $this->get('/api/health');

        $this->assertSuccessfulJsonResponse($response);
        $this->assertJsonContainsKeys($response, ['success', 'message', 'data']);
    }

    #[Test]
    public function it_has_correct_response_structure()
    {
        $response = $this->getJson('/api/health');

        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'status',
                'timestamp'
            ]
        ]);
    }

    #[Test]
    public function it_returns_consistent_health_status()
    {
        // Make multiple requests to ensure consistency
        for ($i = 0; $i < 3; $i++) {
            $response = $this->getJson('/api/health');
            
            $this->assertSuccessfulJsonResponse($response);
            $response->assertJsonPath('data.status', 'healthy');
        }
    }

    #[Test]
    public function it_responds_quickly_to_health_check()
    {
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/health');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        $this->assertSuccessfulJsonResponse($response);
        $this->assertLessThan(100, $responseTime, 'Health check should respond within 100ms');
    }
}