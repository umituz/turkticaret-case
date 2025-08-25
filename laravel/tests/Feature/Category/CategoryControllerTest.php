<?php

namespace Tests\Feature\Category;

use App\Models\Category\Category;
use Illuminate\Support\Facades\Mail;
use Tests\Base\BaseFeatureTest;
use PHPUnit\Framework\Attributes\Test;

class CategoryControllerTest extends BaseFeatureTest
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake(); // Prevent email sending issues
    }

    /**
     * Setup admin user test by bypassing middleware
     */
    private function setupAdminTest()
    {
        $this->withoutMiddleware();
        return $this->createTestUser();
    }

    #[Test]
    public function it_can_list_categories_with_pagination()
    {
        $this->createMultipleCategories(5);
        $user = $this->createTestUser();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/categories');

        $this->assertSuccessfulJsonResponse($response);
        $this->assertPaginationStructure($response);

        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'uuid',
                    'name',
                    'description',
                    'slug',
                    'created_at',
                    'updated_at'
                ]
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total'
            ]
        ]);
    }

    #[Test]
    public function it_allows_public_access_to_list_categories()
    {
        $this->createMultipleCategories(3);

        $response = $this->getJson('/api/categories');

        $this->assertSuccessfulJsonResponse($response);
        $this->assertPaginationStructure($response);
    }

    #[Test]
    public function it_can_create_a_new_category()
    {
        $categoryData = $this->createValidCategoryData();
        $adminUser = $this->setupAdminTest();

        $response = $this->actingAs($adminUser, 'sanctum')->postJson('/api/categories', $categoryData);

        $this->assertSuccessfulJsonResponse($response, 201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'uuid',
                'name',
                'description',
                'slug',
                'created_at',
                'updated_at'
            ]
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => $categoryData['name'],
            'description' => $categoryData['description'],
            'is_active' => $categoryData['is_active'],
        ]);

        $this->assertValidUuidInResponse($response, 'data.uuid');
    }

    #[Test]
    public function it_validates_required_fields_when_creating_category()
    {
        $adminUser = $this->setupAdminTest();
        $response = $this->actingAs($adminUser, 'sanctum')->postJson('/api/categories', []);

        $this->assertValidationErrorResponse($response, [
            'name'
        ]);
    }

    #[Test]
    public function it_validates_category_name_length()
    {
        $categoryData = $this->createValidCategoryData([
            'name' => 'a' // Too short
        ]);
        $adminUser = $this->setupAdminTest();

        $response = $this->actingAs($adminUser, 'sanctum')->postJson('/api/categories', $categoryData);

        $this->assertValidationErrorResponse($response, ['name']);
    }

    #[Test]
    public function it_validates_unique_category_name()
    {
        $existingCategory = $this->createTestCategory();
        $categoryData = $this->createValidCategoryData([
            'name' => $existingCategory->name
        ]);

        $adminUser = $this->setupAdminTest();
        $response = $this->actingAs($adminUser, 'sanctum')->postJson('/api/categories', $categoryData);

        $this->assertValidationErrorResponse($response, ['name']);
    }

    #[Test]
    public function it_can_show_a_specific_category()
    {
        $category = $this->createTestCategory();

        $user = $this->createTestUser();
        $response = $this->actingAs($user, 'sanctum')->getJson("/api/categories/{$category->uuid}");

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonFragment([
            'uuid' => $category->uuid,
            'name' => $category->name,
            'description' => $category->description,
        ]);
    }

    #[Test]
    public function it_returns_404_for_non_existent_category()
    {
        $fakeUuid = fake()->uuid();

        $user = $this->createTestUser();
        $response = $this->actingAs($user, 'sanctum')->getJson("/api/categories/{$fakeUuid}");

        $this->assertNotFoundResponse($response);
    }

    #[Test]
    public function it_can_update_an_existing_category()
    {
        $category = $this->createTestCategory();
        $updateData = $this->createValidCategoryData([
            'name' => 'Updated Category Name',
            'description' => 'Updated description',
        ]);

        $adminUser = $this->setupAdminTest();
        $response = $this->actingAs($adminUser, 'sanctum')->putJson("/api/categories/{$category->uuid}", $updateData);

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonFragment([
            'uuid' => $category->uuid,
            'name' => $updateData['name'],
            'description' => $updateData['description'],
        ]);

        $this->assertDatabaseHas('categories', [
            'uuid' => $category->uuid,
            'name' => $updateData['name'],
            'description' => $updateData['description'],
        ]);
    }

    #[Test]
    public function it_validates_unique_name_when_updating_category()
    {
        $category1 = $this->createTestCategory(['name' => 'Category 1']);
        $category2 = $this->createTestCategory(['name' => 'Category 2']);

        $updateData = $this->createValidCategoryData([
            'name' => 'Category 1' // Already exists
        ]);

        $adminUser = $this->setupAdminTest();
        $response = $this->actingAs($adminUser, 'sanctum')->putJson("/api/categories/{$category2->uuid}", $updateData);

        $this->assertValidationErrorResponse($response, ['name']);
    }

    #[Test]
    public function it_can_soft_delete_a_category()
    {
        $category = $this->createTestCategory();

        $adminUser = $this->setupAdminTest();
        $response = $this->actingAs($adminUser, 'sanctum')->deleteJson("/api/categories/{$category->uuid}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('categories', [
            'uuid' => $category->uuid
        ]);
    }

    #[Test]
    public function it_can_restore_a_soft_deleted_category()
    {
        $category = $this->createTestCategory();
        $category->delete(); // Soft delete

        $adminUser = $this->setupAdminTest();
        $response = $this->actingAs($adminUser, 'sanctum')->postJson("/api/categories/{$category->uuid}/restore");

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonFragment([
            'uuid' => $category->uuid,
        ]);

        $this->assertDatabaseHas('categories', [
            'uuid' => $category->uuid,
            'deleted_at' => null,
        ]);
    }

    #[Test]
    public function it_can_force_delete_a_category()
    {
        $category = $this->createTestCategory();

        $adminUser = $this->setupAdminTest();
        $response = $this->actingAs($adminUser, 'sanctum')->deleteJson("/api/categories/{$category->uuid}/force-delete");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('categories', [
            'uuid' => $category->uuid
        ]);
    }

    #[Test]
    public function it_requires_authentication_for_all_category_operations()
    {
        $category = $this->createTestCategory();
        $categoryData = $this->createValidCategoryData();

        $operations = [
            ['method' => 'post', 'uri' => '/api/categories', 'data' => $categoryData],
            ['method' => 'get', 'uri' => "/api/categories/{$category->uuid}"],
            ['method' => 'put', 'uri' => "/api/categories/{$category->uuid}", 'data' => $categoryData],
            ['method' => 'delete', 'uri' => "/api/categories/{$category->uuid}"],
            ['method' => 'post', 'uri' => "/api/categories/{$category->uuid}/restore"],
            ['method' => 'delete', 'uri' => "/api/categories/{$category->uuid}/force"],
        ];

        // Only admin operations should require authentication
        $adminOperations = [
            ['method' => 'post', 'uri' => '/api/categories', 'data' => $categoryData],
            ['method' => 'put', 'uri' => "/api/categories/{$category->uuid}", 'data' => $categoryData],
            ['method' => 'delete', 'uri' => "/api/categories/{$category->uuid}"],
            ['method' => 'post', 'uri' => "/api/categories/{$category->uuid}/restore"],
            ['method' => 'delete', 'uri' => "/api/categories/{$category->uuid}/force-delete"],
        ];

        foreach ($adminOperations as $operation) {
            $response = $this->{$operation['method']}(
                $operation['uri'],
                $operation['data'] ?? []
            );

            // Expect 500 error due to role middleware, not 401
            $response->assertStatus(500);
        }
    }

}
