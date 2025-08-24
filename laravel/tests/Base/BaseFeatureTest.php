<?php

namespace Tests\Base;

use App\Models\Country\Country;
use App\Models\Currency\Currency;
use App\Models\Language\Language;
use App\Models\User\User;
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

    protected function setUp(): void
    {
        parent::setUp();
        
        // Reset permissions cache for Spatie Permission
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
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
            $language = Language::factory()->create([
                'code' => 'en',
                'locale' => 'en_US',
                'name' => 'English',
                'is_active' => true
            ]);
        }

        // Create Turkish language for TR country
        $turkishLanguage = Language::where('code', 'tr')->first();
        if (!$turkishLanguage) {
            $turkishLanguage = Language::factory()->create([
                'code' => 'tr',
                'locale' => 'tr_TR',
                'name' => 'Turkish',
                'is_active' => true
            ]);
        }

        // Create default currency or use existing one
        $currency = Currency::where('code', 'USD')->first();
        if (!$currency) {
            $currency = Currency::factory()->create([
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'decimals' => 2,
                'is_active' => true
            ]);
        }

        // Create TRY currency for Turkey
        $tryCurrency = Currency::where('code', 'TRY')->first();
        if (!$tryCurrency) {
            $tryCurrency = Currency::factory()->create([
                'code' => 'TRY',
                'name' => 'Turkish Lira',
                'symbol' => '₺',
                'decimals' => 2,
                'is_active' => true
            ]);
        }

        // Create default country or use existing one
        $country = Country::where('code', 'US')->first();
        if (!$country) {
            $country = Country::factory()->create([
                'code' => 'US',
                'name' => 'United States',
                'locale' => 'en_US',
                'currency_uuid' => $currency->uuid,
                'is_active' => true
            ]);
        }

        // Create TR country for tests
        $turkeyCountry = Country::where('code', 'TR')->first();
        if (!$turkeyCountry) {
            $turkeyCountry = Country::factory()->create([
                'code' => 'TR',
                'name' => 'Turkey',
                'locale' => 'tr_TR',
                'currency_uuid' => $tryCurrency->uuid,
                'is_active' => true
            ]);
        }

        // Create test user for authentication tests
        $this->testUser = User::factory()->create([
            'email' => 'test@turkticaret.test',
            'password' => 'password123',
            'email_verified_at' => now(),
            'language_uuid' => $language->uuid,
            'country_uuid' => $country->uuid,
        ]);


    }

    /**
     * Create an admin user for testing admin endpoints
     */
    protected function createAdminUser(): User
    {
        $adminUser = User::factory()->create([
            'email' => 'admin@turkticaret.test',
            'password' => 'password123',
            'email_verified_at' => now(),
            'language_uuid' => Language::where('code', 'en')->first()->uuid,
            'country_uuid' => Country::where('code', 'US')->first()->uuid,
        ]);
        
        return $adminUser;
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
     * Clean up after each test
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
