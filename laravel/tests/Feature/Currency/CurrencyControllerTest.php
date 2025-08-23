<?php

namespace Tests\Feature\Currency;

use Tests\Base\BaseFeatureTest;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Currency\Currency;

class CurrencyControllerTest extends BaseFeatureTest
{
    #[Test]
    public function it_can_list_currencies()
    {
        Currency::factory()->count(3)->create();
        
        $response = $this->actingAs($this->testUser, 'sanctum')->getJson('/api/currencies');

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'uuid',
                    'code',
                    'name',
                    'symbol',
                    'decimals',
                    'is_active',
                    'created_at',
                    'updated_at'
                ]
            ]
        ]);

        $response->assertJsonCount(5, 'data'); // 3 created + 2 from setup (USD, TRY)
    }

    #[Test]
    public function it_can_show_specific_currency()
    {
        $currency = Currency::factory()->create();
        
        $response = $this->actingAs($this->testUser, 'sanctum')->getJson("/api/currencies/{$currency->uuid}");

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'uuid',
                'code',
                'name',
                'symbol',
                'decimals',
                'is_active',
                'created_at',
                'updated_at'
            ]
        ]);

        $response->assertJsonPath('data.uuid', $currency->uuid);
        $response->assertJsonPath('data.code', $currency->code);
    }

    #[Test]
    public function it_can_create_new_currency()
    {
        $currencyData = [
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
            'decimals' => 2,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/currencies', $currencyData);

        $this->assertSuccessfulCreation($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'uuid',
                'code',
                'name',
                'symbol',
                'decimals',
                'is_active',
                'created_at',
                'updated_at'
            ]
        ]);

        $response->assertJsonPath('data.code', 'EUR');
        $response->assertJsonPath('data.name', 'Euro');

        $this->assertDatabaseHas('currencies', [
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
            'decimals' => 2,
            'is_active' => true,
        ]);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_currency()
    {
        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/currencies', []);

        $this->assertValidationErrorResponse($response, [
            'code',
            'name',
            'symbol',
            'decimals'
        ]);
    }

    #[Test]
    public function it_validates_unique_currency_code()
    {
        $existingCurrency = Currency::factory()->create(['code' => 'GBP']);

        $currencyData = [
            'code' => 'GBP',
            'name' => 'British Pound',
            'symbol' => '£',
            'decimals' => 2,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/currencies', $currencyData);

        $this->assertValidationErrorResponse($response, ['code']);
    }

    #[Test]
    public function it_can_update_existing_currency()
    {
        $currency = Currency::factory()->create();

        $updateData = [
            'code' => 'JPY',
            'name' => 'Japanese Yen',
            'symbol' => '¥',
            'decimals' => 0,
            'is_active' => false,
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson("/api/currencies/{$currency->uuid}", $updateData);

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonPath('data.code', 'JPY');
        $response->assertJsonPath('data.name', 'Japanese Yen');
        $response->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('currencies', [
            'uuid' => $currency->uuid,
            'code' => 'JPY',
            'name' => 'Japanese Yen',
            'symbol' => '¥',
            'decimals' => 0,
            'is_active' => false,
        ]);
    }

    #[Test]
    public function it_validates_unique_code_when_updating_currency()
    {
        $currency1 = Currency::factory()->create(['code' => 'CAD']);
        $currency2 = Currency::factory()->create(['code' => 'AUD']);

        $updateData = [
            'code' => 'CAD', // Trying to use existing code
            'name' => 'Australian Dollar',
            'symbol' => '$',
            'decimals' => 2,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson("/api/currencies/{$currency2->uuid}", $updateData);

        $this->assertValidationErrorResponse($response, ['code']);
    }

    #[Test]
    public function it_can_delete_currency()
    {
        $currency = Currency::factory()->create();

        $response = $this->actingAs($this->testUser, 'sanctum')->deleteJson("/api/currencies/{$currency->uuid}");

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJson([
            'success' => true,
            'message' => 'Currency deleted successfully'
        ]);

        $this->assertSoftDeleted('currencies', [
            'uuid' => $currency->uuid,
        ]);
    }

    #[Test]
    public function it_returns_404_for_non_existent_currency()
    {
        $fakeUuid = fake()->uuid();

        $endpoints = [
            ['GET', "/api/currencies/{$fakeUuid}"],
            ['PUT', "/api/currencies/{$fakeUuid}"],
            ['DELETE', "/api/currencies/{$fakeUuid}"],
        ];

        foreach ($endpoints as [$method, $endpoint]) {
            $response = $this->actingAs($this->testUser, 'sanctum')->json($method, $endpoint);
            $response->assertStatus(404);
        }
    }

    #[Test]
    public function it_requires_authentication_for_all_endpoints()
    {
        $currency = Currency::factory()->create();

        $endpoints = [
            ['GET', '/api/currencies'],
            ['GET', "/api/currencies/{$currency->uuid}"],
            ['POST', '/api/currencies'],
            ['PUT', "/api/currencies/{$currency->uuid}"],
            ['DELETE', "/api/currencies/{$currency->uuid}"],
        ];

        foreach ($endpoints as [$method, $endpoint]) {
            $response = $this->json($method, $endpoint);
            $this->assertUnauthorizedResponse($response);
        }
    }

    #[Test]
    public function it_validates_decimals_range()
    {
        $currencyData = [
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
            'decimals' => -1, // Invalid
            'is_active' => true,
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/currencies', $currencyData);

        $this->assertValidationErrorResponse($response, ['decimals']);
    }

    #[Test]
    public function it_validates_code_format()
    {
        $currencyData = [
            'code' => 'toolong', // Too long
            'name' => 'Euro',
            'symbol' => '€',
            'decimals' => 2,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/currencies', $currencyData);

        $this->assertValidationErrorResponse($response, ['code']);
    }

    #[Test]
    public function it_filters_active_currencies_only()
    {
        Currency::factory()->create(['is_active' => true]);
        Currency::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->testUser, 'sanctum')->getJson('/api/currencies?is_active=1');

        $this->assertSuccessfulJsonResponse($response);
        
        // Check that all returned currencies are active
        $currencies = $response->json('data');
        foreach ($currencies as $currency) {
            $this->assertTrue($currency['is_active']);
        }
    }

    #[Test]
    public function it_sorts_currencies_by_name()
    {
        Currency::factory()->create(['name' => 'Zebra Currency']);
        Currency::factory()->create(['name' => 'Alpha Currency']);

        $response = $this->actingAs($this->testUser, 'sanctum')->getJson('/api/currencies?sort=name');

        $this->assertSuccessfulJsonResponse($response);
        
        $currencies = $response->json('data');
        $names = array_column($currencies, 'name');
        $sortedNames = $names;
        sort($sortedNames);
        
        $this->assertEquals($sortedNames, $names);
    }

    #[Test]
    public function it_searches_currencies_by_name()
    {
        Currency::factory()->create(['name' => 'Euro']);
        Currency::factory()->create(['name' => 'Dollar']);

        $response = $this->actingAs($this->testUser, 'sanctum')->getJson('/api/currencies?search=euro');

        $this->assertSuccessfulJsonResponse($response);
        
        $currencies = $response->json('data');
        $this->assertCount(1, $currencies);
        $this->assertStringContainsString('Euro', $currencies[0]['name']);
    }

    #[Test]
    public function it_handles_different_decimal_places()
    {
        $currencies = [
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥', 'decimals' => 0],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'BHD', 'name' => 'Bahraini Dinar', 'symbol' => 'BD', 'decimals' => 3],
        ];

        foreach ($currencies as $currencyData) {
            $currencyData['is_active'] = true;
            $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/currencies', $currencyData);
            $this->assertSuccessfulCreation($response);
            $response->assertJsonPath('data.decimals', $currencyData['decimals']);
        }
    }
}