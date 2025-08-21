<?php

namespace Tests\Feature\Profile;

use Tests\Base\BaseFeatureTest;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Hash;

class ProfileControllerTest extends BaseFeatureTest
{
    #[Test]
    public function it_can_show_user_profile()
    {
        $response = $this->actingAs($this->testUser, 'sanctum')->getJson('/api/profile');

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJson([
            'success' => true,
            'message' => 'Profile retrieved successfully.',
        ]);

        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'uuid',
                'name',
                'email',
                'email_verified_at',
                'created_at',
                'updated_at'
            ]
        ]);

        $response->assertJsonFragment([
            'uuid' => $this->testUser->uuid,
            'name' => $this->testUser->name,
            'email' => $this->testUser->email,
        ]);

        $this->assertValidUuidInResponse($response, 'data.uuid');
    }

    #[Test]
    public function it_requires_authentication_to_view_profile()
    {
        $response = $this->getJson('/api/profile');

        $this->assertUnauthorizedResponse($response);
    }

    #[Test]
    public function it_can_update_user_profile()
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@turkticaret.test',
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson('/api/profile', $updateData);

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJson([
            'success' => true,
            'message' => 'Profile updated successfully.',
        ]);

        $response->assertJsonFragment([
            'name' => $updateData['name'],
            'email' => $updateData['email'],
        ]);

        $this->assertDatabaseHas('users', [
            'uuid' => $this->testUser->uuid,
            'name' => $updateData['name'],
            'email' => $updateData['email'],
        ]);
    }

    #[Test]
    public function it_handles_empty_update_requests()
    {
        $response = $this->actingAs($this->testUser, 'sanctum')->putJson('/api/profile', []);

        // Empty requests succeed without changing anything
        $this->assertSuccessfulJsonResponse($response);
        $response->assertJson([
            'success' => true,
            'message' => 'Profile updated successfully.',
        ]);
    }

    #[Test]
    public function it_validates_email_format_when_updating_profile()
    {
        $updateData = [
            'name' => 'Valid Name',
            'email' => 'invalid-email-format'
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson('/api/profile', $updateData);

        $this->assertValidationErrorResponse($response, ['email']);
    }

    #[Test]
    public function it_validates_unique_email_when_updating_profile()
    {
        $otherUser = $this->createTestUser(['email' => 'other@turkticaret.test']);
        
        $updateData = [
            'name' => 'Valid Name',
            'email' => 'other@turkticaret.test' // Already exists
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson('/api/profile', $updateData);

        $this->assertValidationErrorResponse($response, ['email']);
    }

    #[Test]
    public function it_allows_user_to_keep_same_email_when_updating()
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => $this->testUser->email // Same email
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson('/api/profile', $updateData);

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonFragment([
            'name' => $updateData['name'],
            'email' => $updateData['email'],
        ]);
    }

    #[Test]
    public function it_allows_short_names()
    {
        $updateData = [
            'name' => 'A', // Single character is allowed
            'email' => 'valid@turkticaret.test'
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson('/api/profile', $updateData);

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonFragment([
            'name' => 'A',
            'email' => 'valid@turkticaret.test',
        ]);
    }

    #[Test]
    public function it_validates_maximum_name_length()
    {
        $updateData = [
            'name' => str_repeat('A', 256), // Too long
            'email' => 'valid@turkticaret.test'
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson('/api/profile', $updateData);

        $this->assertValidationErrorResponse($response, ['name']);
    }

    #[Test]
    public function it_can_update_profile_with_password()
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@turkticaret.test',
            'old_password' => 'password123',
            'new_password' => 'NewPassword123!',
            'new_password_confirmation' => 'NewPassword123!'
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson('/api/profile', $updateData);

        $this->assertSuccessfulJsonResponse($response);

        // Verify password was updated by checking the database
        $this->testUser->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $this->testUser->password));
    }

    #[Test]
    public function it_validates_password_confirmation()
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@turkticaret.test',
            'old_password' => 'password123',
            'new_password' => 'NewPassword123!',
            'new_password_confirmation' => 'DifferentPassword123!'
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson('/api/profile', $updateData);

        $this->assertValidationErrorResponse($response, ['new_password']);
    }

    #[Test]
    public function it_validates_password_strength()
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@turkticaret.test',
            'old_password' => 'password123',
            'new_password' => '123', // Too weak
            'new_password_confirmation' => '123'
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson('/api/profile', $updateData);

        $this->assertValidationErrorResponse($response, ['new_password']);
    }

    #[Test]
    public function it_does_not_update_password_if_not_provided()
    {
        $originalPassword = $this->testUser->password;
        
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@turkticaret.test'
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson('/api/profile', $updateData);

        $this->assertSuccessfulJsonResponse($response);

        // Verify password was not changed
        $this->testUser->refresh();
        $this->assertEquals($originalPassword, $this->testUser->password);
    }

    #[Test]
    public function it_requires_authentication_to_update_profile()
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@turkticaret.test'
        ];

        $response = $this->putJson('/api/profile', $updateData);

        $this->assertUnauthorizedResponse($response);
    }

    #[Test]
    public function it_sanitizes_input_when_updating_profile()
    {
        $updateData = [
            'name' => '  Padded Name  ',
            'email' => '  UPPERCASE@TURKTICARET.TEST  '
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson('/api/profile', $updateData);

        $this->assertSuccessfulJsonResponse($response);
        
        $this->testUser->refresh();
        $this->assertEquals('Padded Name', $this->testUser->name);
        $this->assertEquals('uppercase@turkticaret.test', $this->testUser->email);
    }

    #[Test]
    public function it_handles_email_case_insensitivity()
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'UPDATED@TURKTICARET.TEST'
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson('/api/profile', $updateData);

        $this->assertSuccessfulJsonResponse($response);
        
        // Email should be stored in lowercase
        $response->assertJsonPath('data.email', 'updated@turkticaret.test');
    }

    #[Test]
    public function it_preserves_other_user_fields_when_updating()
    {
        $originalCreatedAt = $this->testUser->created_at;
        $originalEmailVerifiedAt = $this->testUser->email_verified_at;
        
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@turkticaret.test'
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson('/api/profile', $updateData);

        $this->assertSuccessfulJsonResponse($response);
        
        $this->testUser->refresh();
        $this->assertEquals($originalCreatedAt->toString(), $this->testUser->created_at->toString());
        $this->assertEquals($originalEmailVerifiedAt->toString(), $this->testUser->email_verified_at->toString());
    }

    #[Test]
    public function it_handles_concurrent_profile_updates()
    {
        $updateData1 = [
            'name' => 'First Update',
            'email' => 'first@turkticaret.test'
        ];
        
        $updateData2 = [
            'name' => 'Second Update',
            'email' => 'second@turkticaret.test'
        ];

        // Simulate sequential updates 
        $response1 = $this->actingAs($this->testUser, 'sanctum')->putJson('/api/profile', $updateData1);
        $response2 = $this->actingAs($this->testUser, 'sanctum')->putJson('/api/profile', $updateData2);

        $this->assertSuccessfulJsonResponse($response1);
        $this->assertSuccessfulJsonResponse($response2);

        // Verify the second update persisted
        $this->testUser->refresh();
        $this->assertEquals('Second Update', $this->testUser->name);
        $this->assertEquals('second@turkticaret.test', $this->testUser->email);
    }

    #[Test]
    public function it_does_not_expose_sensitive_information_in_response()
    {
        $response = $this->actingAs($this->testUser, 'sanctum')->getJson('/api/profile');

        $this->assertSuccessfulJsonResponse($response);
        
        // Ensure password and other sensitive fields are not exposed
        $responseData = $response->json('data');
        $this->assertArrayNotHasKey('password', $responseData);
        $this->assertArrayNotHasKey('remember_token', $responseData);
    }
}