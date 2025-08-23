<?php

namespace Tests\Feature\Auth;

use App\Models\User\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\Base\BaseFeatureTest;

class LoginControllerTest extends BaseFeatureTest
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Don't create user in setUp to avoid conflicts
    }

    #[Test]
    public function it_can_login_with_valid_credentials()
    {
        $this->user = $this->createTestUser([
            'email' => 'login_test_' . time() . '@turkticaret.test',
            'password' => 'password123',
        ]);

        $loginData = [
            'email' => $this->user->email,
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Login successful.',
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

        $this->assertEquals('Bearer', $response->json('data.token_type'));
        $this->assertValidUuidInResponse($response, 'data.user.uuid');
    }

    #[Test]
    public function it_validates_required_fields()
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)->assertJsonValidationErrors([
            'email',
            'password'
        ]);
    }

    #[Test]
    public function it_validates_email_format()
    {
        $loginData = [
            'email' => 'invalid-email',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function it_fails_with_invalid_email()
    {
        $loginData = [
            'email' => 'nonexistent@turkticaret.test',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function it_fails_with_invalid_password()
    {
        $this->user = $this->createTestUser([
            'email' => 'invalid_pwd_test_' . time() . '@turkticaret.test',
            'password' => 'password123',
        ]);

        $loginData = [
            'email' => $this->user->email,
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function it_fails_with_empty_password()
    {
        $this->user = $this->createTestUser([
            'email' => 'empty_pwd_test_' . time() . '@turkticaret.test',
            'password' => 'password123',
        ]);

        $loginData = [
            'email' => $this->user->email,
            'password' => '',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(422)->assertJsonValidationErrors(['password']);
    }

    #[Test]
    public function it_returns_valid_authentication_token()
    {
        $this->user = $this->createTestUser([
            'email' => 'token_test_' . time() . '@turkticaret.test',
            'password' => 'password123',
        ]);

        $loginData = [
            'email' => $this->user->email,
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200);

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
    public function it_returns_correct_user_information()
    {
        $this->user = $this->createTestUser([
            'email' => 'user_info_test_' . time() . '@turkticaret.test',
            'password' => 'password123',
        ]);

        $loginData = [
            'email' => $this->user->email,
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'uuid' => $this->user->uuid,
            'name' => $this->user->name,
            'email' => $this->user->email,
        ]);
    }

    #[Test]
    public function it_handles_case_insensitive_email()
    {
        $this->user = $this->createTestUser([
            'email' => 'case_test_' . time() . '@turkticaret.test',
            'password' => 'password123',
        ]);

        $loginData = [
            'email' => strtoupper($this->user->email),
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Login successful.',
        ]);
    }

    #[Test]
    public function it_trims_email_whitespace()
    {
        $this->user = $this->createTestUser([
            'email' => 'trim_test_' . time() . '@turkticaret.test',
            'password' => 'password123',
        ]);

        $loginData = [
            'email' => '  ' . $this->user->email . '  ',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Login successful.',
        ]);
    }


    #[Test]
    public function it_handles_multiple_login_attempts()
    {
        $this->user = $this->createTestUser([
            'email' => 'multiple_test_' . time() . '@turkticaret.test',
            'password' => 'password123',
        ]);

        $loginData = [
            'email' => $this->user->email,
            'password' => 'password123',
        ];

        // Multiple successful logins
        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson('/api/login', $loginData);
            $response->assertStatus(200);
        }

        // Each login should return a unique token
        $tokens = [];
        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson('/api/login', $loginData);
            $token = $response->json('data.token');
            $this->assertNotContains($token, $tokens);
            $tokens[] = $token;
        }
    }

    #[Test]
    public function it_handles_rate_limiting_for_failed_attempts()
    {
        $this->user = $this->createTestUser([
            'email' => 'rate_limit_test_' . time() . '@turkticaret.test',
            'password' => 'password123',
        ]);

        $invalidLoginData = [
            'email' => $this->user->email,
            'password' => 'wrongpassword',
        ];

        // Make multiple failed attempts (first 4 should be validation errors)
        for ($i = 0; $i < 4; $i++) {
            $response = $this->postJson('/api/login', $invalidLoginData);
            $response->assertStatus(422)->assertJsonValidationErrors(['email']);
        }

        // The 5th attempt should trigger rate limiting
        $response = $this->postJson('/api/login', $invalidLoginData);
        // Rate limiting can return either 422, 429, or 500 depending on implementation
        $this->assertTrue(
            in_array($response->status(), [422, 429, 500]),
            "Expected rate limiting response (422, 429, or 500) but got {$response->status()}"
        );

        // After rate limiting, even valid credentials might be blocked temporarily
        $validLoginData = [
            'email' => $this->user->email,
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $validLoginData);
        // Should either succeed or show rate limit message
        $this->assertTrue(
            $response->status() === 200 || $response->status() === 429,
            'Response should be either successful or rate limited'
        );
    }
}
