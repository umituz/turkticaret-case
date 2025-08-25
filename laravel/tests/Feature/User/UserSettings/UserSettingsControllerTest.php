<?php

namespace Tests\Feature\User\UserSettings;

use Tests\Base\BaseFeatureTest;
use PHPUnit\Framework\Attributes\Test;
use App\Models\User\UserSetting;
use Illuminate\Support\Facades\Hash;

class UserSettingsControllerTest extends BaseFeatureTest
{
    #[Test]
    public function it_can_get_user_settings()
    {
        // UserSettings are automatically created by UserObserver when user is created
        
        $response = $this->actingAs($this->testUser, 'sanctum')->getJson('/api/user/settings');

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'notifications' => [
                    'email',
                    'push',
                    'sms',
                    'marketing',
                    'orderUpdates',
                    'newsletter'
                ],
                'preferences' => [
                    'language',
                    'timezone'
                ]
            ]
        ]);

        $response->assertJsonPath('data.notifications.email', true);
        $response->assertJsonPath('data.notifications.push', true);
    }

    #[Test]
    public function it_can_create_default_settings()
    {
        // Since UserObserver already creates settings, this tests that calling the endpoint again returns existing settings
        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/user/settings');

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'notifications' => [
                    'email',
                    'push',
                    'sms',
                    'marketing',
                    'orderUpdates',
                    'newsletter'
                ],
                'preferences' => [
                    'language',
                    'timezone'
                ]
            ]
        ]);

        $response->assertJsonPath('data.notifications.email', true);
        $response->assertJsonPath('data.notifications.push', true);

        $this->assertDatabaseHas('user_settings', [
            'user_uuid' => $this->testUser->uuid,
            'email_notifications' => true,
            'push_notifications' => true,
        ]);
    }

    #[Test]
    public function it_can_update_notification_preferences()
    {
        // UserSettings already exist from UserObserver
        
        $updateData = [
            'email' => false,
            'push' => false,
            'sms' => true,
            'marketing' => true,
            'orderUpdates' => false,
            'newsletter' => true,
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson(
            '/api/user/settings/notifications',
            $updateData
        );

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonPath('data.notifications.email', false);
        $response->assertJsonPath('data.notifications.push', false);
        $response->assertJsonPath('data.notifications.sms', true);

        $this->assertDatabaseHas('user_settings', [
            'user_uuid' => $this->testUser->uuid,
            'email_notifications' => false,
            'push_notifications' => false,
            'sms_notifications' => true,
            'marketing_notifications' => true,
            'order_update_notifications' => false,
            'newsletter_notifications' => true,
        ]);
    }

    #[Test]
    public function it_validates_notification_update_request()
    {
        // UserSettings already exist from UserObserver
        
        $response = $this->actingAs($this->testUser, 'sanctum')->putJson(
            '/api/user/settings/notifications',
            [
                'email' => 'not_boolean',
                'push' => 'invalid_value',
                'sms' => 123,
            ]
        );

        $this->assertValidationErrorResponse($response, [
            'email',
            'push',
            'sms',
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

        // Password change was successful - verify user can no longer use old password
        $this->testUser->refresh();
        $this->assertFalse(Hash::check('password123', $this->testUser->password));
        $this->assertTrue(Hash::check('newpassword456', $this->testUser->password));
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
            'password',
            'password_confirmation'
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

        // Should create default settings automatically
        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'notifications' => [
                    'email',
                    'push',
                    'sms',
                    'marketing',
                    'orderUpdates',
                    'newsletter'
                ],
                'preferences' => [
                    'language',
                    'timezone'
                ]
            ]
        ]);
        
        // Verify default settings were created in database
        $this->assertDatabaseHas('user_settings', [
            'user_uuid' => $newUser->uuid,
        ]);
    }

    #[Test]
    public function it_prevents_duplicate_default_settings_creation()
    {
        // UserSettings already exist from UserObserver, test that calling POST doesn't create duplicates
        
        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/user/settings');

        $this->assertSuccessfulJsonResponse($response); // Should return existing settings

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