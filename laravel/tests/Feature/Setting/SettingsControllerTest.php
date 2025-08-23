<?php

namespace Tests\Feature\Setting;

use Tests\Base\BaseFeatureTest;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Setting\Setting;

class SettingsControllerTest extends BaseFeatureTest
{
    #[Test]
    public function it_can_get_all_active_settings()
    {
        $adminUser = $this->createAdminUser();
        
        Setting::factory()->create(['is_active' => true, 'key' => 'site_name', 'value' => 'TurkTicaret']);
        Setting::factory()->create(['is_active' => true, 'key' => 'maintenance_mode', 'value' => 'false']);
        Setting::factory()->create(['is_active' => false, 'key' => 'inactive_setting', 'value' => 'test']);

        $response = $this->actingAs($adminUser, 'sanctum')->getJson('/api/admin/settings');

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'uuid',
                    'key',
                    'value',
                    'type',
                    'description',
                    'is_active',
                    'created_at',
                    'updated_at'
                ]
            ]
        ]);

        // Should only return active settings
        $settings = $response->json('data');
        foreach ($settings as $setting) {
            $this->assertTrue($setting['is_active']);
        }
    }

    #[Test]
    public function it_can_update_setting()
    {
        $adminUser = $this->createAdminUser();
        
        $setting = Setting::factory()->create([
            'key' => 'site_name',
            'value' => 'Old Name',
            'is_active' => true
        ]);

        $updateData = [
            'key' => 'site_name',
            'value' => 'New Site Name'
        ];

        $response = $this->actingAs($adminUser, 'sanctum')->putJson('/api/admin/settings', $updateData);

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJson([
            'success' => true,
            'message' => 'Setting updated successfully.'
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'site_name',
            'value' => 'New Site Name'
        ]);
    }

    #[Test]
    public function it_validates_required_fields_when_updating_setting()
    {
        $adminUser = $this->createAdminUser();

        $response = $this->actingAs($adminUser, 'sanctum')->putJson('/api/admin/settings', []);

        $this->assertValidationErrorResponse($response, [
            'key',
            'value'
        ]);
    }

    #[Test]
    public function it_validates_setting_key_exists()
    {
        $adminUser = $this->createAdminUser();

        $updateData = [
            'key' => 'non_existent_key',
            'value' => 'some value'
        ];

        $response = $this->actingAs($adminUser, 'sanctum')->putJson('/api/admin/settings', $updateData);

        $this->assertValidationErrorResponse($response, ['key']);
    }

    #[Test]
    public function it_requires_authentication_for_settings_access()
    {
        $response = $this->getJson('/api/admin/settings');
        $this->assertUnauthorizedResponse($response);

        $response = $this->putJson('/api/admin/settings', ['key' => 'test', 'value' => 'test']);
        $this->assertUnauthorizedResponse($response);
    }

    #[Test]
    public function it_prevents_non_admin_users_from_accessing_settings()
    {
        $response = $this->actingAs($this->testUser, 'sanctum')->getJson('/api/admin/settings');
        $response->assertStatus(403);

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson('/api/admin/settings', [
            'key' => 'site_name',
            'value' => 'test'
        ]);
        $response->assertStatus(403);
    }

    #[Test]
    public function it_handles_different_setting_types()
    {
        $adminUser = $this->createAdminUser();
        
        $booleanSetting = Setting::factory()->create([
            'key' => 'maintenance_mode',
            'value' => 'false',
            'type' => 'boolean'
        ]);

        $stringSetting = Setting::factory()->create([
            'key' => 'site_name',
            'value' => 'TurkTicaret',
            'type' => 'string'
        ]);

        $response = $this->actingAs($adminUser, 'sanctum')->getJson('/api/admin/settings');

        $this->assertSuccessfulJsonResponse($response);
        
        $settings = $response->json('data');
        $this->assertCount(2, $settings);
    }

    #[Test]
    public function it_updates_boolean_settings_correctly()
    {
        $adminUser = $this->createAdminUser();
        
        Setting::factory()->create([
            'key' => 'maintenance_mode',
            'value' => 'false',
            'type' => 'boolean'
        ]);

        $updateData = [
            'key' => 'maintenance_mode',
            'value' => 'true'
        ];

        $response = $this->actingAs($adminUser, 'sanctum')->putJson('/api/admin/settings', $updateData);

        $this->assertSuccessfulJsonResponse($response);
        
        $this->assertDatabaseHas('settings', [
            'key' => 'maintenance_mode',
            'value' => 'true'
        ]);
    }

    #[Test]
    public function it_updates_numeric_settings_correctly()
    {
        $adminUser = $this->createAdminUser();
        
        Setting::factory()->create([
            'key' => 'max_upload_size',
            'value' => '10',
            'type' => 'integer'
        ]);

        $updateData = [
            'key' => 'max_upload_size',
            'value' => '25'
        ];

        $response = $this->actingAs($adminUser, 'sanctum')->putJson('/api/admin/settings', $updateData);

        $this->assertSuccessfulJsonResponse($response);
        
        $this->assertDatabaseHas('settings', [
            'key' => 'max_upload_size',
            'value' => '25'
        ]);
    }

    #[Test]
    public function it_validates_setting_value_format()
    {
        $adminUser = $this->createAdminUser();
        
        Setting::factory()->create([
            'key' => 'email_setting',
            'value' => 'admin@turkticaret.test',
            'type' => 'email'
        ]);

        $updateData = [
            'key' => 'email_setting',
            'value' => 'invalid-email'
        ];

        $response = $this->actingAs($adminUser, 'sanctum')->putJson('/api/admin/settings', $updateData);

        $this->assertValidationErrorResponse($response, ['value']);
    }

    #[Test]
    public function it_returns_settings_in_correct_format()
    {
        $adminUser = $this->createAdminUser();
        
        $setting = Setting::factory()->create([
            'key' => 'app_version',
            'value' => '1.0.0',
            'description' => 'Application version',
            'type' => 'string',
            'is_active' => true
        ]);

        $response = $this->actingAs($adminUser, 'sanctum')->getJson('/api/admin/settings');

        $this->assertSuccessfulJsonResponse($response);
        
        $settings = $response->json('data');
        $appVersionSetting = collect($settings)->firstWhere('key', 'app_version');
        
        $this->assertNotNull($appVersionSetting);
        $this->assertEquals('app_version', $appVersionSetting['key']);
        $this->assertEquals('1.0.0', $appVersionSetting['value']);
        $this->assertEquals('Application version', $appVersionSetting['description']);
        $this->assertEquals('string', $appVersionSetting['type']);
    }

    #[Test]
    public function it_handles_empty_settings_gracefully()
    {
        $adminUser = $this->createAdminUser();

        $response = $this->actingAs($adminUser, 'sanctum')->getJson('/api/admin/settings');

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonPath('data', []);
    }
}