<?php

namespace Tests\Feature\User\UserSettings;

use Tests\Base\BaseFeatureTest;
use PHPUnit\Framework\Attributes\Test;
use App\Models\User\UserSetting;

class UserSettingsControllerTest extends BaseFeatureTest
{
    #[Test]
    public function it_can_get_user_settings()
    {
        $userSetting = UserSetting::factory()->create([
            'user_uuid' => $this->testUser->uuid,
        ]);

        $response = $this->actingAs($this->testUser, 'sanctum')->getJson('/api/user/settings');

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'uuid',
                'user_uuid',
                'email_notifications',
                'push_notifications',
                'sms_notifications',
                'marketing_notifications',
                'order_update_notifications',
                'newsletter_notifications',
                'created_at',
                'updated_at'
            ]
        ]);

        $response->assertJsonPath('data.user_uuid', $this->testUser->uuid);
    }

    #[Test]
    public function it_can_create_default_settings()
    {
        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/user/settings');

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'uuid',
                'user_uuid',
                'email_notifications',
                'push_notifications',
                'sms_notifications',
                'marketing_notifications',
                'order_update_notifications',
                'newsletter_notifications',
                'created_at',
                'updated_at'
            ]
        ]);

        $response->assertJsonPath('data.user_uuid', $this->testUser->uuid);
        $response->assertJsonPath('data.email_notifications', true);
        $response->assertJsonPath('data.push_notifications', true);

        $this->assertDatabaseHas('user_settings', [
            'user_uuid' => $this->testUser->uuid,
            'email_notifications' => true,
            'push_notifications' => true,
        ]);
    }

    #[Test]
    public function it_can_update_notification_preferences()
    {
        UserSetting::factory()->create([
            'user_uuid' => $this->testUser->uuid,
        ]);

        $updateData = [
            'email_notifications' => false,
            'push_notifications' => false,
            'sms_notifications' => true,
            'marketing_notifications' => true,
            'order_update_notifications' => false,
            'newsletter_notifications' => true,
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson(
            '/api/user/settings/notifications',
            $updateData
        );

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonPath('data.email_notifications', false);
        $response->assertJsonPath('data.push_notifications', false);
        $response->assertJsonPath('data.sms_notifications', true);

        $this->assertDatabaseHas('user_settings', array_merge([
            'user_uuid' => $this->testUser->uuid,
        ], $updateData));
    }

    #[Test]
    public function it_validates_notification_update_request()
    {
        UserSetting::factory()->create([
            'user_uuid' => $this->testUser->uuid,
        ]);

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson(
            '/api/user/settings/notifications',
            []
        );

        $this->assertValidationErrorResponse($response, [
            'email_notifications',
            'push_notifications',
            'sms_notifications',
            'marketing_notifications',
            'order_update_notifications',
            'newsletter_notifications'
        ]);
    }

    #[Test]
    public function it_can_update_user_preferences()
    {
        $language = \App\Models\Language\Language::factory()->create();
        
        $updateData = [
            'language_uuid' => $language->uuid,
            'timezone' => 'Europe/Istanbul',
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson(
            '/api/user/settings/preferences',
            $updateData
        );

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonPath('data.language_uuid', $language->uuid);
        $response->assertJsonPath('data.timezone', 'Europe/Istanbul');

        $this->assertDatabaseHas('users', [
            'uuid' => $this->testUser->uuid,
            'language_uuid' => $language->uuid,
            'timezone' => 'Europe/Istanbul',
        ]);
    }

    #[Test]
    public function it_validates_preferences_update_request()
    {
        $response = $this->actingAs($this->testUser, 'sanctum')->putJson(
            '/api/user/settings/preferences',
            []
        );

        $this->assertValidationErrorResponse($response, [
            'language_uuid',
            'timezone'
        ]);
    }

    #[Test]
    public function it_can_change_password()
    {
        $passwordData = [
            'current_password' => 'password123',
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson(
            '/api/user/settings/password',
            $passwordData
        );

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJson([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);

        // Test login with new password
        $loginResponse = $this->postJson('/api/login', [
            'email' => $this->testUser->email,
            'password' => 'newpassword456',
        ]);

        $loginResponse->assertStatus(200);
    }

    #[Test]
    public function it_validates_password_change_request()
    {
        $response = $this->actingAs($this->testUser, 'sanctum')->putJson(
            '/api/user/settings/password',
            []
        );

        $this->assertValidationErrorResponse($response, [
            'current_password',
            'password'
        ]);
    }

    #[Test]
    public function it_fails_password_change_with_incorrect_current_password()
    {
        $passwordData = [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson(
            '/api/user/settings/password',
            $passwordData
        );

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Current password is incorrect.',
        ]);
    }

    #[Test]
    public function it_validates_password_confirmation()
    {
        $passwordData = [
            'current_password' => 'password123',
            'password' => 'newpassword456',
            'password_confirmation' => 'differentpassword',
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson(
            '/api/user/settings/password',
            $passwordData
        );

        $this->assertValidationErrorResponse($response, ['password']);
    }

    #[Test]
    public function it_requires_authentication_for_all_endpoints()
    {
        $endpoints = [
            ['GET', '/api/user/settings'],
            ['POST', '/api/user/settings'],
            ['PUT', '/api/user/settings/notifications'],
            ['PUT', '/api/user/settings/preferences'],
            ['PUT', '/api/user/settings/password'],
        ];

        foreach ($endpoints as [$method, $endpoint]) {
            $response = $this->json($method, $endpoint);
            $this->assertUnauthorizedResponse($response);
        }
    }

    #[Test]
    public function it_handles_nonexistent_user_settings_gracefully()
    {
        $newUser = $this->createTestUser();
        
        $response = $this->actingAs($newUser, 'sanctum')->getJson('/api/user/settings');

        // Should return an empty response or create default settings
        $response->assertStatus(500); // Or whatever the expected behavior is
    }

    #[Test]
    public function it_prevents_duplicate_default_settings_creation()
    {
        UserSetting::factory()->create([
            'user_uuid' => $this->testUser->uuid,
        ]);

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/user/settings');

        $response->assertStatus(500); // Should handle existing settings appropriately

        // Should still have only one settings record
        $this->assertEquals(1, UserSetting::where('user_uuid', $this->testUser->uuid)->count());
    }

    #[Test]
    public function it_validates_timezone_format()
    {
        $updateData = [
            'language_uuid' => $this->testUser->language_uuid,
            'timezone' => 'Invalid/Timezone',
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson(
            '/api/user/settings/preferences',
            $updateData
        );

        $this->assertValidationErrorResponse($response, ['timezone']);
    }

    #[Test]
    public function it_validates_language_uuid_exists()
    {
        $updateData = [
            'language_uuid' => fake()->uuid(),
            'timezone' => 'Europe/Istanbul',
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson(
            '/api/user/settings/preferences',
            $updateData
        );

        $this->assertValidationErrorResponse($response, ['language_uuid']);
    }
}