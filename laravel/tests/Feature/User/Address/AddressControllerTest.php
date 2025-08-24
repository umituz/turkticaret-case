<?php

namespace Tests\Feature\User\Address;

use Tests\Base\BaseFeatureTest;
use PHPUnit\Framework\Attributes\Test;
use App\Models\User\UserAddress;
use App\Models\Country\Country;
use App\Models\User\User;

class AddressControllerTest extends BaseFeatureTest
{
    #[Test]
    public function it_can_list_user_addresses()
    {
        $user = $this->testUser;
        UserAddress::factory()->count(3)->create(['user_uuid' => $user->uuid]);
        
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/addresses');

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'uuid',
                    'title',
                    'full_name',
                    'phone',
                    'address_line_1',
                    'address_line_2',
                    'city',
                    'state',
                    'postal_code',
                    'country_uuid',
                    'is_default',
                    'created_at',
                    'updated_at',
                ]
            ]
        ]);
        $response->assertJsonCount(3, 'data');
    }

    #[Test]
    public function it_requires_authentication_to_list_addresses()
    {
        $response = $this->getJson('/api/addresses');

        $response->assertStatus(401);
    }

    #[Test]
    public function it_can_create_a_new_address()
    {
        $user = $this->testUser;
        $country = Country::factory()->create();
        $addressData = $this->createValidAddressData($country->uuid);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/addresses', $addressData);

        $this->assertSuccessfulJsonResponse($response, 200);
        $response->assertJsonFragment([
            'title' => $addressData['title'],
            'full_name' => $addressData['full_name'],
            'phone' => $addressData['phone'],
            'address_line_1' => $addressData['address_line_1'],
            'city' => $addressData['city'],
        ]);

        $this->assertDatabaseHas('user_addresses', [
            'user_uuid' => $user->uuid,
            'title' => $addressData['title'],
            'full_name' => $addressData['full_name'],
            'phone' => $addressData['phone'],
        ]);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_address()
    {
        $user = $this->testUser;
        
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/addresses', []);

        $this->assertValidationErrorResponse($response);
        $response->assertJsonValidationErrors([
            'title',
            'full_name', 
            'phone',
            'address_line_1',
            'city',
            'state',
            'postal_code',
            'country_uuid'
        ]);
    }

    #[Test]
    public function it_validates_country_exists_when_creating_address()
    {
        $user = $this->testUser;
        $addressData = $this->createValidAddressData('non-existent-uuid');

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/addresses', $addressData);

        $this->assertValidationErrorResponse($response);
        $response->assertJsonValidationErrors(['country_uuid']);
    }

    #[Test]
    public function it_can_show_user_owned_address()
    {
        $user = $this->testUser;
        $address = UserAddress::factory()->create(['user_uuid' => $user->uuid]);
        
        $response = $this->actingAs($user, 'sanctum')->getJson("/api/addresses/{$address->uuid}");

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonFragment([
            'uuid' => $address->uuid,
            'title' => $address->title,
            'full_name' => $address->full_name,
        ]);
    }

    #[Test]
    public function it_prevents_showing_other_users_address()
    {
        $user = $this->testUser;
        $otherUser = User::factory()->create();
        $address = UserAddress::factory()->create(['user_uuid' => $otherUser->uuid]);
        
        $response = $this->actingAs($user, 'sanctum')->getJson("/api/addresses/{$address->uuid}");

        $response->assertStatus(403);
    }

    #[Test]
    public function it_can_update_user_owned_address()
    {
        $user = $this->testUser;
        $address = UserAddress::factory()->create(['user_uuid' => $user->uuid]);
        $country = Country::factory()->create();
        
        $updateData = $this->createValidAddressData($country->uuid, [
            'title' => 'Updated Address',
            'full_name' => 'Updated Name',
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson("/api/addresses/{$address->uuid}", $updateData);

        $this->assertSuccessfulJsonResponse($response);
        $response->assertJsonFragment([
            'uuid' => $address->uuid,
            'title' => $updateData['title'],
            'full_name' => $updateData['full_name'],
        ]);

        $this->assertDatabaseHas('user_addresses', [
            'uuid' => $address->uuid,
            'title' => $updateData['title'],
            'full_name' => $updateData['full_name'],
        ]);
    }

    #[Test]
    public function it_prevents_updating_other_users_address()
    {
        $user = $this->testUser;
        $otherUser = User::factory()->create();
        $address = UserAddress::factory()->create(['user_uuid' => $otherUser->uuid]);
        $country = Country::factory()->create();
        
        $updateData = $this->createValidAddressData($country->uuid);

        $response = $this->actingAs($user, 'sanctum')->putJson("/api/addresses/{$address->uuid}", $updateData);

        $response->assertStatus(403);
    }

    #[Test]
    public function it_can_delete_user_owned_address()
    {
        $user = $this->testUser;
        $address = UserAddress::factory()->create(['user_uuid' => $user->uuid]);
        
        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/addresses/{$address->uuid}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('user_addresses', ['uuid' => $address->uuid]);
    }

    #[Test]
    public function it_prevents_deleting_other_users_address()
    {
        $user = $this->testUser;
        $otherUser = User::factory()->create();
        $address = UserAddress::factory()->create(['user_uuid' => $otherUser->uuid]);
        
        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/addresses/{$address->uuid}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('user_addresses', ['uuid' => $address->uuid, 'deleted_at' => null]);
    }

    #[Test]
    public function it_requires_authentication_for_all_address_operations()
    {
        $address = UserAddress::factory()->create();

        // Test all endpoints without authentication
        $endpoints = [
            ['GET', '/api/addresses'],
            ['POST', '/api/addresses'],
            ['GET', "/api/addresses/{$address->uuid}"],
            ['PUT', "/api/addresses/{$address->uuid}"],
            ['DELETE', "/api/addresses/{$address->uuid}"],
        ];

        foreach ($endpoints as [$method, $url]) {
            $response = $this->json($method, $url);
            $response->assertStatus(401);
        }
    }

    private function createValidAddressData(string $countryUuid = null, array $overrides = []): array
    {
        $country = $countryUuid ? null : Country::factory()->create();
        
        return array_merge([
            'title' => 'Home Address',
            'full_name' => 'John Doe',
            'phone' => '+905551234567',
            'address_line_1' => '123 Main Street',
            'address_line_2' => 'Apt 4B',
            'city' => 'Istanbul',
            'state' => 'Istanbul',
            'postal_code' => '34000',
            'country_uuid' => $countryUuid ?? $country->uuid,
            'is_default' => false,
        ], $overrides);
    }
}