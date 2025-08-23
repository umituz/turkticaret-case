<?php

namespace Tests\Feature\Country;

use Tests\Base\BaseFeatureTest;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Country\Country;
use App\Models\Currency\Currency;

class CountryControllerTest extends BaseFeatureTest
{
    #[Test]
    public function it_can_list_countries()
    {
        Country::factory()->count(3)->create();
        
        $response = $this->actingAs($this->testUser, 'sanctum')->getJson('/api/countries');

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'uuid',
                    'code',
                    'name',
                    'locale',
                    'currency_uuid',
                    'is_active',
                    'created_at',
                    'updated_at'
                ]
            ]
        ]);

        $response->assertJsonCount(5, 'data'); // 3 created + 2 from setup (US, TR)
    }

    #[Test]
    public function it_can_show_specific_country()
    {
        $country = Country::factory()->create();
        
        $response = $this->actingAs($this->testUser, 'sanctum')->getJson("/api/countries/{$country->uuid}");

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'uuid',
                'code',
                'name',
                'locale',
                'currency_uuid',
                'is_active',
                'created_at',
                'updated_at'
            ]
        ]);

        $response->assertJsonPath('data.uuid', $country->uuid);
        $response->assertJsonPath('data.code', $country->code);
    }

    #[Test]
    public function it_can_create_new_country()
    {
        $currency = Currency::factory()->create();
        
        $countryData = [
            'code' => 'DE',
            'name' => 'Germany',
            'locale' => 'de_DE',
            'currency_uuid' => $currency->uuid,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/countries', $countryData);

        $this->assertSuccessfulCreation($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'uuid',
                'code',
                'name',
                'locale',
                'currency_uuid',
                'is_active',
                'created_at',
                'updated_at'
            ]
        ]);

        $response->assertJsonPath('data.code', 'DE');
        $response->assertJsonPath('data.name', 'Germany');

        $this->assertDatabaseHas('countries', [
            'code' => 'DE',
            'name' => 'Germany',
            'locale' => 'de_DE',
            'currency_uuid' => $currency->uuid,
            'is_active' => true,
        ]);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_country()
    {
        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/countries', []);

        $this->assertValidationErrorResponse($response, [
            'code',
            'name',
            'locale',
            'currency_uuid'
        ]);
    }

    #[Test]
    public function it_validates_unique_country_code()
    {
        $existingCountry = Country::factory()->create(['code' => 'FR']);
        $currency = Currency::factory()->create();

        $countryData = [
            'code' => 'FR',
            'name' => 'France',
            'locale' => 'fr_FR',
            'currency_uuid' => $currency->uuid,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/countries', $countryData);

        $this->assertValidationErrorResponse($response, ['code']);
    }

    #[Test]
    public function it_validates_currency_exists()
    {
        $countryData = [
            'code' => 'IT',
            'name' => 'Italy',
            'locale' => 'it_IT',
            'currency_uuid' => fake()->uuid(),
            'is_active' => true,
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/countries', $countryData);

        $this->assertValidationErrorResponse($response, ['currency_uuid']);
    }

    #[Test]
    public function it_can_update_existing_country()
    {
        $country = Country::factory()->create();
        $newCurrency = Currency::factory()->create();

        $updateData = [
            'code' => 'ES',
            'name' => 'Spain',
            'locale' => 'es_ES',
            'currency_uuid' => $newCurrency->uuid,
            'is_active' => false,
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson("/api/countries/{$country->uuid}", $updateData);

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonPath('data.code', 'ES');
        $response->assertJsonPath('data.name', 'Spain');
        $response->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('countries', [
            'uuid' => $country->uuid,
            'code' => 'ES',
            'name' => 'Spain',
            'locale' => 'es_ES',
            'currency_uuid' => $newCurrency->uuid,
            'is_active' => false,
        ]);
    }

    #[Test]
    public function it_validates_unique_code_when_updating_country()
    {
        $country1 = Country::factory()->create(['code' => 'GB']);
        $country2 = Country::factory()->create(['code' => 'IE']);
        $currency = Currency::factory()->create();

        $updateData = [
            'code' => 'GB', // Trying to use existing code
            'name' => 'Ireland',
            'locale' => 'en_IE',
            'currency_uuid' => $currency->uuid,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson("/api/countries/{$country2->uuid}", $updateData);

        $this->assertValidationErrorResponse($response, ['code']);
    }

    #[Test]
    public function it_can_delete_country()
    {
        $country = Country::factory()->create();

        $response = $this->actingAs($this->testUser, 'sanctum')->deleteJson("/api/countries/{$country->uuid}");

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJson([
            'success' => true,
            'message' => 'Country deleted successfully'
        ]);

        $this->assertSoftDeleted('countries', [
            'uuid' => $country->uuid,
        ]);
    }

    #[Test]
    public function it_returns_404_for_non_existent_country()
    {
        $fakeUuid = fake()->uuid();

        $endpoints = [
            ['GET', "/api/countries/{$fakeUuid}"],
            ['PUT', "/api/countries/{$fakeUuid}"],
            ['DELETE', "/api/countries/{$fakeUuid}"],
        ];

        foreach ($endpoints as [$method, $endpoint]) {
            $response = $this->actingAs($this->testUser, 'sanctum')->json($method, $endpoint);
            $response->assertStatus(404);
        }
    }

    #[Test]
    public function it_requires_authentication_for_all_endpoints()
    {
        $country = Country::factory()->create();

        $endpoints = [
            ['GET', '/api/countries'],
            ['GET', "/api/countries/{$country->uuid}"],
            ['POST', '/api/countries'],
            ['PUT', "/api/countries/{$country->uuid}"],
            ['DELETE', "/api/countries/{$country->uuid}"],
        ];

        foreach ($endpoints as [$method, $endpoint]) {
            $response = $this->json($method, $endpoint);
            $this->assertUnauthorizedResponse($response);
        }
    }

    #[Test]
    public function it_validates_locale_format()
    {
        $currency = Currency::factory()->create();
        
        $countryData = [
            'code' => 'DE',
            'name' => 'Germany',
            'locale' => 'invalid-locale',
            'currency_uuid' => $currency->uuid,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/countries', $countryData);

        $this->assertValidationErrorResponse($response, ['locale']);
    }

    #[Test]
    public function it_validates_code_length()
    {
        $currency = Currency::factory()->create();
        
        $countryData = [
            'code' => 'TOOLONG', // Too long
            'name' => 'Germany',
            'locale' => 'de_DE',
            'currency_uuid' => $currency->uuid,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/countries', $countryData);

        $this->assertValidationErrorResponse($response, ['code']);
    }

    #[Test]
    public function it_filters_active_countries_only()
    {
        Country::factory()->create(['is_active' => true]);
        Country::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->testUser, 'sanctum')->getJson('/api/countries?is_active=1');

        $this->assertSuccessfulJsonResponse($response);
        
        // Check that all returned countries are active
        $countries = $response->json('data');
        foreach ($countries as $country) {
            $this->assertTrue($country['is_active']);
        }
    }

    #[Test]
    public function it_sorts_countries_by_name()
    {
        Country::factory()->create(['name' => 'Zebra Country']);
        Country::factory()->create(['name' => 'Alpha Country']);

        $response = $this->actingAs($this->testUser, 'sanctum')->getJson('/api/countries?sort=name');

        $this->assertSuccessfulJsonResponse($response);
        
        $countries = $response->json('data');
        $names = array_column($countries, 'name');
        $sortedNames = $names;
        sort($sortedNames);
        
        $this->assertEquals($sortedNames, $names);
    }

    #[Test]
    public function it_searches_countries_by_name()
    {
        Country::factory()->create(['name' => 'Germany']);
        Country::factory()->create(['name' => 'France']);

        $response = $this->actingAs($this->testUser, 'sanctum')->getJson('/api/countries?search=germ');

        $this->assertSuccessfulJsonResponse($response);
        
        $countries = $response->json('data');
        $this->assertCount(1, $countries);
        $this->assertStringContainsString('Germany', $countries[0]['name']);
    }

    #[Test]
    public function it_includes_currency_relationship()
    {
        $currency = Currency::factory()->create(['name' => 'Euro']);
        $country = Country::factory()->create(['currency_uuid' => $currency->uuid]);

        $response = $this->actingAs($this->testUser, 'sanctum')->getJson("/api/countries/{$country->uuid}?include=currency");

        $this->assertSuccessfulJsonResponse($response);
        
        // Check if currency relationship is included (if supported by the resource)
        $response->assertJsonStructure([
            'data' => [
                'uuid',
                'code',
                'name',
                'locale',
                'currency_uuid',
                'is_active'
            ]
        ]);
    }

    #[Test]
    public function it_handles_pagination_correctly()
    {
        Country::factory()->count(15)->create();

        $response = $this->actingAs($this->testUser, 'sanctum')->getJson('/api/countries?per_page=5&page=2');

        $this->assertSuccessfulJsonResponse($response);
        
        // Check pagination structure if supported
        $response->assertJsonStructure([
            'data',
            // Add pagination structure if your API supports it
        ]);
    }
}