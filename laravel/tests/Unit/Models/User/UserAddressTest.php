<?php

namespace Tests\Unit\Models\User;

use App\Models\User\UserAddress;
use App\Models\User\User;
use App\Models\Country\Country;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

/**
 * Unit tests for UserAddress Model
 * Tests model attributes, casts, relationships, and scopes
 */
#[CoversClass(UserAddress::class)]
#[Group('unit')]
#[Group('models')]
#[Small]
class UserAddressTest extends UnitTestCase
{
    #[Test]
    public function model_has_correct_fillable_attributes(): void
    {
        // Arrange & Act
        $userAddress = new UserAddress();
        
        // Assert
        $expectedFillable = [
            'user_uuid',
            'type',
            'is_default',
            'first_name',
            'last_name',
            'company',
            'address_line_1',
            'address_line_2',
            'city',
            'state',
            'postal_code',
            'country_uuid',
            'phone',
        ];
        
        $this->assertEquals($expectedFillable, $userAddress->getFillable());
    }

    #[Test]
    public function model_has_correct_casts(): void
    {
        // Arrange & Act
        $userAddress = new UserAddress();
        $casts = $userAddress->getCasts();
        
        // Assert - Check specific cast
        $this->assertEquals('boolean', $casts['is_default']);
        
        // Verify we have the base datetime casts
        $this->assertEquals('datetime', $casts['deleted_at']); // From SoftDeletes
        
        // Verify cast count
        $this->assertGreaterThanOrEqual(2, count($casts));
    }

    #[Test]
    public function model_extends_base_uuid_model(): void
    {
        // Arrange & Act
        $userAddress = new UserAddress();
        
        // Assert
        $this->assertInstanceOf(\App\Models\Base\BaseUuidModel::class, $userAddress);
    }

    #[Test]
    public function user_address_can_be_created_with_all_attributes(): void
    {
        // Arrange
        $attributes = [
            'user_uuid' => 'test-user-uuid',
            'type' => 'billing',
            'is_default' => true,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company' => 'Acme Corp',
            'address_line_1' => '123 Main St',
            'address_line_2' => 'Apt 4B',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country_uuid' => 'us-country-uuid',
            'phone' => '+1234567890',
        ];

        // Act
        $userAddress = new UserAddress($attributes);

        // Assert
        $this->assertEquals('test-user-uuid', $userAddress->user_uuid);
        $this->assertEquals('billing', $userAddress->type);
        $this->assertTrue($userAddress->is_default);
        $this->assertEquals('John', $userAddress->first_name);
        $this->assertEquals('Doe', $userAddress->last_name);
        $this->assertEquals('Acme Corp', $userAddress->company);
        $this->assertEquals('123 Main St', $userAddress->address_line_1);
        $this->assertEquals('Apt 4B', $userAddress->address_line_2);
        $this->assertEquals('New York', $userAddress->city);
        $this->assertEquals('NY', $userAddress->state);
        $this->assertEquals('10001', $userAddress->postal_code);
        $this->assertEquals('us-country-uuid', $userAddress->country_uuid);
        $this->assertEquals('+1234567890', $userAddress->phone);
    }

    #[Test]
    public function model_has_user_relationship(): void
    {
        // Arrange & Act
        $userAddress = new UserAddress();
        $relationship = $userAddress->user();
        
        // Assert
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relationship);
        $this->assertEquals('user_uuid', $relationship->getForeignKeyName());
        $this->assertEquals('uuid', $relationship->getOwnerKeyName());
    }

    #[Test]
    public function model_has_country_relationship(): void
    {
        // Arrange & Act
        $userAddress = new UserAddress();
        $relationship = $userAddress->country();
        
        // Assert
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relationship);
        $this->assertEquals('country_uuid', $relationship->getForeignKeyName());
        $this->assertEquals('uuid', $relationship->getOwnerKeyName());
    }

    #[Test]
    public function boolean_cast_works_correctly_for_is_default(): void
    {
        // Arrange
        $userAddress = new UserAddress([
            'user_uuid' => 'test-user',
            'is_default' => 1, // Integer will be cast to boolean
            'first_name' => 'Test',
            'last_name' => 'User',
            'address_line_1' => 'Test Address',
            'city' => 'Test City',
            'country_uuid' => 'test-country',
        ]);

        // Act & Assert
        $this->assertIsBool($userAddress->is_default);
        $this->assertTrue($userAddress->is_default);
        
        // Test with false value
        $userAddress->is_default = 0;
        $this->assertIsBool($userAddress->is_default);
        $this->assertFalse($userAddress->is_default);
    }

    #[Test]
    public function model_handles_optional_fields(): void
    {
        // Arrange
        $userAddress = new UserAddress([
            'user_uuid' => 'test-user',
            'type' => 'shipping',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'address_line_1' => '456 Oak Ave',
            'city' => 'Boston',
            'country_uuid' => 'us-uuid',
            // Optional fields not provided
        ]);

        // Act & Assert
        $this->assertEquals('test-user', $userAddress->user_uuid);
        $this->assertEquals('shipping', $userAddress->type);
        $this->assertNull($userAddress->company);
        $this->assertNull($userAddress->address_line_2);
        $this->assertNull($userAddress->state);
        $this->assertNull($userAddress->postal_code);
        $this->assertNull($userAddress->phone);
    }

    #[Test]
    public function scope_default_filters_default_addresses(): void
    {
        // Arrange & Act
        $userAddress = new UserAddress();
        $query = $userAddress->newQuery();
        $scopedQuery = $userAddress->scopeDefault($query);
        
        // Assert - This is a basic test that the scope exists and returns a query
        $this->assertSame($query, $scopedQuery);
    }

    #[Test]
    public function scope_by_type_filters_by_address_type(): void
    {
        // Arrange & Act
        $userAddress = new UserAddress();
        $query = $userAddress->newQuery();
        $scopedQuery = $userAddress->scopeByType($query, 'billing');
        
        // Assert - This is a basic test that the scope exists and returns a query
        $this->assertSame($query, $scopedQuery);
    }

    #[Test]
    public function model_supports_different_address_types(): void
    {
        // Arrange & Act
        $billingAddress = new UserAddress(['type' => 'billing']);
        $shippingAddress = new UserAddress(['type' => 'shipping']);
        
        // Assert
        $this->assertEquals('billing', $billingAddress->type);
        $this->assertEquals('shipping', $shippingAddress->type);
    }

    #[Test]
    public function model_handles_international_addresses(): void
    {
        // Arrange
        $internationalAddress = new UserAddress([
            'user_uuid' => 'intl-user',
            'type' => 'shipping',
            'first_name' => 'Pierre',
            'last_name' => 'Dupont',
            'address_line_1' => '123 Rue de la Paix',
            'city' => 'Paris',
            'state' => 'Île-de-France',
            'postal_code' => '75001',
            'country_uuid' => 'fr-country-uuid',
            'phone' => '+33123456789',
        ]);

        // Act & Assert
        $this->assertEquals('intl-user', $internationalAddress->user_uuid);
        $this->assertEquals('Pierre', $internationalAddress->first_name);
        $this->assertEquals('123 Rue de la Paix', $internationalAddress->address_line_1);
        $this->assertEquals('Paris', $internationalAddress->city);
        $this->assertEquals('75001', $internationalAddress->postal_code);
        $this->assertEquals('+33123456789', $internationalAddress->phone);
    }

    #[Test]
    public function model_uses_has_factory_trait(): void
    {
        // Arrange & Act & Assert
        $this->assertTrue(
            in_array(\Illuminate\Database\Eloquent\Factories\HasFactory::class, class_uses(UserAddress::class)),
            'UserAddress model should use HasFactory trait'
        );
    }
}