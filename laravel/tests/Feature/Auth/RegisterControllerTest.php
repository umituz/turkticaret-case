<?php

namespace Tests\Feature\Auth;

use App\Models\Auth\User;
use Tests\Base\BaseFeatureTest;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Hash;

class RegisterControllerTest extends BaseFeatureTest
{
    #[Test]
    public function it_can_register_a_new_user_successfully()
    {
        $userData = $this->createValidRegistrationData();

        $response = $this->postJson('/api/register', $userData);

        $this->assertSuccessfulCreation($response);
        $response->assertJson([
            'success' => true,
            'message' => 'Registration successful.',
        ]);

        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'uuid',
                    'name',
                    'email',
                    'created_at'
                ],
                'token',
                'token_type'
            ]
        ]);

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);

        $this->assertEquals('Bearer', $response->json('data.token_type'));
        $this->assertValidUuidInResponse($response, 'data.user.uuid');
    }

    #[Test]
    public function it_validates_required_fields()
    {
        $response = $this->postJson('/api/register', []);

        $this->assertValidationErrorResponse($response, [
            'name',
            'email',
            'password'
        ]);
    }

    #[Test]
    public function it_validates_email_format()
    {
        $userData = $this->createValidRegistrationData([
            'email' => 'invalid-email'
        ]);

        $response = $this->postJson('/api/register', $userData);

        $this->assertValidationErrorResponse($response, ['email']);
    }

    #[Test]
    public function it_validates_unique_email()
    {
        $existingUser = $this->createTestUser();
        $userData = $this->createValidRegistrationData([
            'email' => $existingUser->email
        ]);

        $response = $this->postJson('/api/register', $userData);

        $this->assertValidationErrorResponse($response, ['email']);
    }

    #[Test]
    public function it_validates_password_confirmation()
    {
        $userData = $this->createValidRegistrationData([
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword123!'
        ]);

        $response = $this->postJson('/api/register', $userData);

        $this->assertValidationErrorResponse($response, ['password']);
    }

    #[Test]
    public function it_validates_password_strength()
    {
        $userData = $this->createValidRegistrationData([
            'password' => '123',
            'password_confirmation' => '123'
        ]);

        $response = $this->postJson('/api/register', $userData);

        $this->assertValidationErrorResponse($response, ['password']);
    }

    #[Test]
    public function it_validates_name_length()
    {
        $userData = $this->createValidRegistrationData([
            'name' => 'a' // Too short
        ]);

        $response = $this->postJson('/api/register', $userData);

        $this->assertValidationErrorResponse($response, ['name']);
    }

    #[Test]
    public function it_hashes_password_correctly()
    {
        $userData = $this->createValidRegistrationData();

        $response = $this->postJson('/api/register', $userData);

        $this->assertSuccessfulCreation($response);

        $user = User::where('email', $userData['email'])->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check($userData['password'], $user->password));
        $this->assertNotEquals($userData['password'], $user->password);
    }

    #[Test]
    public function it_creates_user_without_requiring_email_verification()
    {
        $userData = $this->createValidRegistrationData();

        $response = $this->postJson('/api/register', $userData);

        $this->assertSuccessfulCreation($response);

        $user = User::where('email', $userData['email'])->first();
        $this->assertNotNull($user);
        $this->assertEquals($userData['name'], $user->name);
        $this->assertEquals(strtolower(trim($userData['email'])), $user->email);
    }

    #[Test]
    public function it_returns_valid_authentication_token()
    {
        $userData = $this->createValidRegistrationData();

        $response = $this->postJson('/api/register', $userData);

        $this->assertSuccessfulCreation($response);

        $token = $response->json('data.token');
        $this->assertNotEmpty($token);
        $this->assertIsString($token);
        
        // Verify token can be used for authentication
        $authenticatedResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get('/api/profile');

        $authenticatedResponse->assertStatus(200);
    }

    #[Test]
    public function it_sanitizes_user_input()
    {
        $userData = $this->createValidRegistrationData([
            'name' => '  Test User  ',
            'email' => '  TEST@EXAMPLE.COM  '
        ]);

        $response = $this->postJson('/api/register', $userData);

        $this->assertSuccessfulCreation($response);

        $user = User::where('email', strtolower(trim($userData['email'])))->first();
        $this->assertNotNull($user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
    }

    #[Test]
    public function it_handles_concurrent_registration_attempts()
    {
        $userData = $this->createValidRegistrationData();

        // Simulate concurrent requests
        $responses = [];
        for ($i = 0; $i < 3; $i++) {
            $responses[] = $this->postJson('/api/register', $userData);
        }

        // First request should succeed
        $this->assertSuccessfulCreation($responses[0], 201);

        // Subsequent requests should fail due to unique email constraint
        for ($i = 1; $i < 3; $i++) {
            $this->assertValidationErrorResponse($responses[$i], ['email']);
        }

        // Verify only one user was created
        $userCount = User::where('email', $userData['email'])->count();
        $this->assertEquals(1, $userCount);
    }
}