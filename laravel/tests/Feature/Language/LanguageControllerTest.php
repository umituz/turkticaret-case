<?php

namespace Tests\Feature\Language;

use Tests\Base\BaseFeatureTest;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Language\Language;

class LanguageControllerTest extends BaseFeatureTest
{
    #[Test]
    public function it_can_list_languages_without_authentication()
    {
        Language::factory()->count(3)->create();
        
        $response = $this->getJson('/api/languages');

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
                    'is_active',
                    'created_at',
                    'updated_at'
                ]
            ]
        ]);

        $response->assertJsonCount(5, 'data'); // 3 created + 2 from setup (en, tr)
    }

    #[Test]
    public function it_can_show_specific_language()
    {
        $language = Language::factory()->create();
        
        $response = $this->actingAs($this->testUser, 'sanctum')->getJson("/api/languages/{$language->uuid}");

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'uuid',
                'code',
                'name',
                'locale',
                'is_active',
                'created_at',
                'updated_at'
            ]
        ]);

        $response->assertJsonPath('data.uuid', $language->uuid);
        $response->assertJsonPath('data.code', $language->code);
    }

    #[Test]
    public function it_can_create_new_language()
    {
        $languageData = [
            'code' => 'de',
            'name' => 'German',
            'locale' => 'de_DE',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/languages', $languageData);

        $this->assertSuccessfulCreation($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'uuid',
                'code',
                'name',
                'locale',
                'is_active',
                'created_at',
                'updated_at'
            ]
        ]);

        $response->assertJsonPath('data.code', 'de');
        $response->assertJsonPath('data.name', 'German');

        $this->assertDatabaseHas('languages', [
            'code' => 'de',
            'name' => 'German',
            'locale' => 'de_DE',
            'is_active' => true,
        ]);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_language()
    {
        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/languages', []);

        $this->assertValidationErrorResponse($response, [
            'code',
            'name',
            'locale'
        ]);
    }

    #[Test]
    public function it_validates_unique_language_code()
    {
        $existingLanguage = Language::factory()->create(['code' => 'es']);

        $languageData = [
            'code' => 'es',
            'name' => 'Spanish',
            'locale' => 'es_ES',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/languages', $languageData);

        $this->assertValidationErrorResponse($response, ['code']);
    }

    #[Test]
    public function it_can_update_existing_language()
    {
        $language = Language::factory()->create();

        $updateData = [
            'code' => 'fr',
            'name' => 'French',
            'locale' => 'fr_FR',
            'is_active' => false,
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson("/api/languages/{$language->uuid}", $updateData);

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonPath('data.code', 'fr');
        $response->assertJsonPath('data.name', 'French');
        $response->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('languages', [
            'uuid' => $language->uuid,
            'code' => 'fr',
            'name' => 'French',
            'locale' => 'fr_FR',
            'is_active' => false,
        ]);
    }

    #[Test]
    public function it_validates_unique_code_when_updating_language()
    {
        $language1 = Language::factory()->create(['code' => 'it']);
        $language2 = Language::factory()->create(['code' => 'pt']);

        $updateData = [
            'code' => 'it', // Trying to use existing code
            'name' => 'Portuguese',
            'locale' => 'pt_PT',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->putJson("/api/languages/{$language2->uuid}", $updateData);

        $this->assertValidationErrorResponse($response, ['code']);
    }

    #[Test]
    public function it_can_delete_language()
    {
        $language = Language::factory()->create();

        $response = $this->actingAs($this->testUser, 'sanctum')->deleteJson("/api/languages/{$language->uuid}");

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJson([
            'success' => true,
            'message' => 'Language deleted successfully'
        ]);

        $this->assertSoftDeleted('languages', [
            'uuid' => $language->uuid,
        ]);
    }

    #[Test]
    public function it_returns_404_for_non_existent_language()
    {
        $fakeUuid = fake()->uuid();

        $endpoints = [
            ['GET', "/api/languages/{$fakeUuid}"],
            ['PUT', "/api/languages/{$fakeUuid}"],
            ['DELETE', "/api/languages/{$fakeUuid}"],
        ];

        foreach ($endpoints as [$method, $endpoint]) {
            $response = $this->actingAs($this->testUser, 'sanctum')->json($method, $endpoint);
            $response->assertStatus(404);
        }
    }

    #[Test]
    public function it_requires_authentication_for_protected_endpoints()
    {
        $language = Language::factory()->create();

        $endpoints = [
            ['GET', "/api/languages/{$language->uuid}"],
            ['POST', '/api/languages'],
            ['PUT', "/api/languages/{$language->uuid}"],
            ['DELETE', "/api/languages/{$language->uuid}"],
        ];

        foreach ($endpoints as [$method, $endpoint]) {
            $response = $this->json($method, $endpoint);
            $this->assertUnauthorizedResponse($response);
        }
    }

    #[Test]
    public function it_validates_locale_format()
    {
        $languageData = [
            'code' => 'de',
            'name' => 'German',
            'locale' => 'invalid-locale',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/languages', $languageData);

        $this->assertValidationErrorResponse($response, ['locale']);
    }

    #[Test]
    public function it_validates_code_length()
    {
        $languageData = [
            'code' => 'toolong',
            'name' => 'German', 
            'locale' => 'de_DE',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->testUser, 'sanctum')->postJson('/api/languages', $languageData);

        $this->assertValidationErrorResponse($response, ['code']);
    }

    #[Test]
    public function it_filters_active_languages_only()
    {
        Language::factory()->create(['is_active' => true]);
        Language::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/languages?is_active=1');

        $this->assertSuccessfulJsonResponse($response);
        
        // Check that all returned languages are active
        $languages = $response->json('data');
        foreach ($languages as $language) {
            $this->assertTrue($language['is_active']);
        }
    }

    #[Test]
    public function it_sorts_languages_alphabetically()
    {
        Language::factory()->create(['name' => 'Zebra Language']);
        Language::factory()->create(['name' => 'Alpha Language']);

        $response = $this->getJson('/api/languages?sort=name');

        $this->assertSuccessfulJsonResponse($response);
        
        $languages = $response->json('data');
        $names = array_column($languages, 'name');
        $sortedNames = $names;
        sort($sortedNames);
        
        $this->assertEquals($sortedNames, $names);
    }

    #[Test]
    public function it_searches_languages_by_name()
    {
        Language::factory()->create(['name' => 'Spanish']);
        Language::factory()->create(['name' => 'French']);

        $response = $this->getJson('/api/languages?search=span');

        $this->assertSuccessfulJsonResponse($response);
        
        $languages = $response->json('data');
        $this->assertCount(1, $languages);
        $this->assertStringContainsString('Spanish', $languages[0]['name']);
    }

    #[Test]
    public function it_handles_pagination_correctly()
    {
        Language::factory()->count(15)->create();

        $response = $this->getJson('/api/languages?per_page=5&page=2');

        $this->assertSuccessfulJsonResponse($response);
        
        // Check pagination structure if supported
        $response->assertJsonStructure([
            'data',
            // Add pagination structure if your API supports it
        ]);
    }
}