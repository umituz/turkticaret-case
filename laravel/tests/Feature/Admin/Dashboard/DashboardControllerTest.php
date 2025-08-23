<?php

namespace Tests\Feature\Admin\Dashboard;

use Tests\Base\BaseFeatureTest;
use PHPUnit\Framework\Attributes\Test;

class DashboardControllerTest extends BaseFeatureTest
{
    #[Test]
    public function it_can_get_dashboard_data()
    {
        $adminUser = $this->createAdminUser();
        
        $response = $this->actingAs($adminUser, 'sanctum')->getJson('/api/admin/dashboard');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'stats',
                'recent_activity',
                'system_status'
            ]
        ]);
    }

    #[Test]
    public function it_requires_authentication_to_access_dashboard()
    {
        $response = $this->getJson('/api/admin/dashboard');

        $this->assertUnauthorizedResponse($response);
    }

    #[Test]
    public function it_allows_authenticated_users_to_access_dashboard()
    {
        $response = $this->actingAs($this->testUser, 'sanctum')->getJson('/api/admin/dashboard');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_returns_correct_dashboard_statistics()
    {
        $adminUser = $this->createAdminUser();
        
        // Create test data
        $this->createOrderWithItems($this->testUser, 2);
        $this->createOrderWithItems($this->testUser, 1);
        
        $response = $this->actingAs($adminUser, 'sanctum')->getJson('/api/admin/dashboard');

        $response->assertStatus(200);
        
        // Verify that dashboard contains statistical data
        $response->assertJsonStructure([
            'data' => [
                'stats' => [
                    '*' => [
                        'title',
                        'value',
                        'change',
                        'description'
                    ]
                ]
            ]
        ]);
        
        // Check that stats array contains data
        $stats = $response->json('data.stats');
        $this->assertIsArray($stats);
        $this->assertNotEmpty($stats);
    }

    #[Test]
    public function it_includes_recent_activity_in_dashboard()
    {
        $adminUser = $this->createAdminUser();
        
        // Create some recent orders
        $order1 = $this->createOrderWithItems($this->testUser, 1);
        $order2 = $this->createOrderWithItems($this->testUser, 2);
        
        $response = $this->actingAs($adminUser, 'sanctum')->getJson('/api/admin/dashboard');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'recent_activity' => [
                    '*' => [
                        'uuid',
                        'type',
                        'message',
                        'timestamp',
                        'status'
                    ]
                ]
            ]
        ]);
        
        // Check that recent_activity contains data
        $recentActivity = $response->json('data.recent_activity');
        $this->assertIsArray($recentActivity);
    }

    #[Test]
    public function it_includes_system_status_in_dashboard()
    {
        $adminUser = $this->createAdminUser();
        
        $response = $this->actingAs($adminUser, 'sanctum')->getJson('/api/admin/dashboard');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'system_status' => [
                    '*' => [
                        'id',
                        'label',
                        'status'
                    ]
                ]
            ]
        ]);
    }

    #[Test]
    public function it_handles_data_gracefully()
    {
        $adminUser = $this->createAdminUser();
        
        $response = $this->actingAs($adminUser, 'sanctum')->getJson('/api/admin/dashboard');

        $response->assertStatus(200);
        
        // Should return the main structure
        $data = $response->json('data');
        $this->assertArrayHasKey('stats', $data);
        $this->assertArrayHasKey('recent_activity', $data);
        $this->assertArrayHasKey('system_status', $data);
    }

    #[Test]
    public function it_returns_stats_information()
    {
        $adminUser = $this->createAdminUser();
        
        $response = $this->actingAs($adminUser, 'sanctum')->getJson('/api/admin/dashboard');

        $response->assertStatus(200);
        
        $stats = $response->json('data.stats');
        $this->assertIsArray($stats);
        $this->assertNotEmpty($stats);
    }

    #[Test]
    public function it_returns_recent_activity()
    {
        $adminUser = $this->createAdminUser();
        
        $response = $this->actingAs($adminUser, 'sanctum')->getJson('/api/admin/dashboard');

        $response->assertStatus(200);
        
        $recentActivity = $response->json('data.recent_activity');
        $this->assertIsArray($recentActivity);
    }

    #[Test]
    public function it_returns_system_status()
    {
        $adminUser = $this->createAdminUser();
        
        $response = $this->actingAs($adminUser, 'sanctum')->getJson('/api/admin/dashboard');

        $response->assertStatus(200);
        
        $systemStatus = $response->json('data.system_status');
        $this->assertIsArray($systemStatus);
        $this->assertNotEmpty($systemStatus);
    }
}