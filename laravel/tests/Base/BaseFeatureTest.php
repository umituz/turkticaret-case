<?php

namespace Tests\Base;

use App\Models\Auth\User;
use App\Models\Language\Language;
use App\Models\Currency\Currency;
use App\Models\Country\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use Tests\TestCase;
use Tests\Traits\FeatureAssertionsTrait;
use Tests\Traits\FeatureMocksTrait;

/**
 * Base test case for feature tests with Laravel application context
 * Provides database refresh and common testing utilities for HTTP testing
 */
#[Group('feature')]
#[Medium]
abstract class BaseFeatureTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    use FeatureAssertionsTrait, FeatureMocksTrait;

    protected User $testUser;
    protected array $authHeaders = [];
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->setupFeatureTestEnvironment();
    }

    /**
     * Setup feature test environment with Laravel application
     */
    protected function setupFeatureTestEnvironment(): void
    {
        // Create default language for users or use existing one
        $language = Language::where('code', 'en')->first();
        if (!$language) {
            $language = Language::factory()->english()->active()->create();
        }

        // Create default currency or use existing one
        $currency = Currency::where('code', 'USD')->first();
        if (!$currency) {
            $currency = Currency::factory()->create([
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'decimals' => 2,
            ]);
        }

        // Create default country or use existing one
        $country = Country::where('code', 'US')->first();
        if (!$country) {
            $country = Country::factory()
                ->withCurrency($currency)
                ->create([
                    'code' => 'US',
                    'name' => 'United States',
                ]);
        }

        // Create test user for authentication tests
        $this->testUser = User::factory()->create([
            'email' => 'test@turkticaret.test',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'language_uuid' => $language->uuid,
            'country_uuid' => $country->uuid,
        ]);

        // Setup default auth headers
        $this->authHeaders = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Create authenticated user and return it
     */
    protected function createAuthenticatedUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'email_verified_at' => now(),
        ], $attributes));
    }

    /**
     * Act as the test user for requests
     */
    protected function actAsTestUser(): static
    {
        return $this->actingAs($this->testUser);
    }

    /**
     * Act as a specific user for requests
     */
    protected function actAsUser(User $user): static
    {
        return $this->actingAs($user);
    }

    /**
     * Make authenticated GET request
     */
    protected function authGet(string $uri, array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->actAsTestUser()
            ->withHeaders(array_merge($this->authHeaders, $headers))
            ->get($uri);
    }

    /**
     * Make authenticated POST request
     */
    protected function authPost(string $uri, array $data = [], array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->actAsTestUser()
            ->withHeaders(array_merge($this->authHeaders, $headers))
            ->post($uri, $data);
    }

    /**
     * Make authenticated PUT request
     */
    protected function authPut(string $uri, array $data = [], array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->actAsTestUser()
            ->withHeaders(array_merge($this->authHeaders, $headers))
            ->put($uri, $data);
    }

    /**
     * Make authenticated DELETE request
     */
    protected function authDelete(string $uri, array $data = [], array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->actAsTestUser()
            ->withHeaders(array_merge($this->authHeaders, $headers))
            ->delete($uri, $data);
    }

    /**
     * Make unauthenticated JSON GET request
     */
    protected function jsonGet(string $uri, array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->withHeaders(array_merge($this->authHeaders, $headers))
            ->get($uri);
    }

    /**
     * Make unauthenticated JSON POST request
     */
    protected function jsonPost(string $uri, array $data = [], array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->withHeaders(array_merge($this->authHeaders, $headers))
            ->post($uri, $data);
    }

    /**
     * Create test data with UUID
     */
    protected function createTestData(array $overrides = []): array
    {
        $defaults = [
            'uuid' => fake()->uuid(),
            'name' => fake()->name(),
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
        ];

        return array_merge($defaults, $overrides);
    }

    /**
     * Assert successful JSON response
     */
    protected function assertSuccessfulJsonResponse(\Illuminate\Testing\TestResponse $response, int $status = 200): void
    {
        $response->assertStatus($status)
            ->assertHeader('content-type', 'application/json');
    }

    /**
     * Assert validation error response
     */
    protected function assertValidationErrorResponse(\Illuminate\Testing\TestResponse $response, array $expectedErrors = []): void
    {
        $response->assertStatus(422)
            ->assertHeader('content-type', 'application/json');

        if (!empty($expectedErrors)) {
            $response->assertJsonValidationErrors($expectedErrors);
        }
    }

    /**
     * Assert unauthorized response
     */
    protected function assertUnauthorizedResponse(\Illuminate\Testing\TestResponse $response): void
    {
        $response->assertStatus(401)
            ->assertHeader('content-type', 'application/json');
    }

    /**
     * Assert forbidden response
     */
    protected function assertForbiddenResponse(\Illuminate\Testing\TestResponse $response): void
    {
        $response->assertStatus(403)
            ->assertHeader('content-type', 'application/json');
    }

    /**
     * Assert not found response
     */
    protected function assertNotFoundResponse(\Illuminate\Testing\TestResponse $response): void
    {
        $response->assertStatus(404)
            ->assertHeader('content-type', 'application/json');
    }

    /**
     * Clean up after each test
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }
}