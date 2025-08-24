<?php

namespace Tests\Unit\Models\Shipping;

use App\Models\Shipping\ShippingMethod;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

/**
 * Unit tests for ShippingMethod Model
 * Tests model attributes, casts, scopes, and custom accessors
 */
#[CoversClass(ShippingMethod::class)]
#[Group('unit')]
#[Group('models')]
#[Small]
class ShippingMethodTest extends UnitTestCase
{
    #[Test]
    public function model_has_correct_fillable_attributes(): void
    {
        // Arrange & Act
        $shippingMethod = new ShippingMethod();
        
        // Assert
        $expectedFillable = [
            'name',
            'description',
            'price',
            'min_delivery_days',
            'max_delivery_days',
            'is_active',
            'sort_order',
        ];
        
        $this->assertEquals($expectedFillable, $shippingMethod->getFillable());
    }

    #[Test]
    public function model_has_correct_casts(): void
    {
        // Arrange & Act
        $shippingMethod = new ShippingMethod();
        $casts = $shippingMethod->getCasts();
        
        // Assert - Check specific casts
        $this->assertEquals('decimal:2', $casts['price']);
        $this->assertEquals('boolean', $casts['is_active']);
        $this->assertEquals('integer', $casts['min_delivery_days']);
        $this->assertEquals('integer', $casts['max_delivery_days']);
        $this->assertEquals('integer', $casts['sort_order']);
        
        // Verify we have the base datetime casts
        $this->assertEquals('datetime', $casts['deleted_at']); // From SoftDeletes
        
        // Verify cast count
        $this->assertGreaterThanOrEqual(6, count($casts));
    }

    #[Test]
    public function model_extends_base_uuid_model(): void
    {
        // Arrange & Act
        $shippingMethod = new ShippingMethod();
        
        // Assert
        $this->assertInstanceOf(\App\Models\Base\BaseUuidModel::class, $shippingMethod);
    }

    #[Test]
    public function shipping_method_can_be_created_with_all_attributes(): void
    {
        // Arrange
        $attributes = [
            'name' => 'Express Shipping',
            'description' => 'Fast delivery within 1-2 business days',
            'price' => 15.99,
            'min_delivery_days' => 1,
            'max_delivery_days' => 2,
            'is_active' => true,
            'sort_order' => 1,
        ];

        // Act
        $shippingMethod = new ShippingMethod($attributes);

        // Assert
        $this->assertEquals('Express Shipping', $shippingMethod->name);
        $this->assertEquals('Fast delivery within 1-2 business days', $shippingMethod->description);
        $this->assertEquals(15.99, $shippingMethod->price);
        $this->assertEquals(1, $shippingMethod->min_delivery_days);
        $this->assertEquals(2, $shippingMethod->max_delivery_days);
        $this->assertTrue($shippingMethod->is_active);
        $this->assertEquals(1, $shippingMethod->sort_order);
    }

    #[Test]
    public function casts_work_correctly_for_all_fields(): void
    {
        // Arrange
        $shippingMethod = new ShippingMethod([
            'name' => 'Standard Shipping',
            'price' => '9.99', // String will be cast to decimal
            'min_delivery_days' => '3', // String will be cast to integer
            'max_delivery_days' => '5', // String will be cast to integer
            'is_active' => 1, // Integer will be cast to boolean
            'sort_order' => '2', // String will be cast to integer
        ]);

        // Act & Assert
        $this->assertIsString($shippingMethod->price);
        $this->assertEquals('9.99', $shippingMethod->price);
        $this->assertIsInt($shippingMethod->min_delivery_days);
        $this->assertEquals(3, $shippingMethod->min_delivery_days);
        $this->assertIsInt($shippingMethod->max_delivery_days);
        $this->assertEquals(5, $shippingMethod->max_delivery_days);
        $this->assertIsBool($shippingMethod->is_active);
        $this->assertTrue($shippingMethod->is_active);
        $this->assertIsInt($shippingMethod->sort_order);
        $this->assertEquals(2, $shippingMethod->sort_order);
    }

    #[Test]
    public function scope_active_filters_active_methods(): void
    {
        // Arrange & Act
        $shippingMethod = new ShippingMethod();
        $query = $shippingMethod->newQuery();
        $scopedQuery = $shippingMethod->scopeActive($query);
        
        // Assert - This is a basic test that the scope exists and returns a query
        $this->assertSame($query, $scopedQuery);
    }

    #[Test]
    public function get_delivery_time_attribute_returns_single_day_format(): void
    {
        // Arrange
        $shippingMethod = new ShippingMethod([
            'min_delivery_days' => 1,
            'max_delivery_days' => 1,
        ]);

        // Act
        $deliveryTime = $shippingMethod->getDeliveryTimeAttribute();

        // Assert
        $this->assertEquals('1 business day', $deliveryTime);
    }

    #[Test]
    public function get_delivery_time_attribute_returns_multiple_days_format(): void
    {
        // Arrange
        $shippingMethod = new ShippingMethod([
            'min_delivery_days' => 3,
            'max_delivery_days' => 5,
        ]);

        // Act
        $deliveryTime = $shippingMethod->getDeliveryTimeAttribute();

        // Assert
        $this->assertEquals('3-5 business days', $deliveryTime);
    }

    #[Test]
    public function get_delivery_time_attribute_handles_same_multiple_days(): void
    {
        // Arrange
        $shippingMethod = new ShippingMethod([
            'min_delivery_days' => 3,
            'max_delivery_days' => 3,
        ]);

        // Act
        $deliveryTime = $shippingMethod->getDeliveryTimeAttribute();

        // Assert
        $this->assertEquals('3 business days', $deliveryTime);
    }

    #[Test]
    public function get_delivery_time_attribute_handles_zero_days(): void
    {
        // Arrange
        $shippingMethod = new ShippingMethod([
            'min_delivery_days' => 0,
            'max_delivery_days' => 0,
        ]);

        // Act
        $deliveryTime = $shippingMethod->getDeliveryTimeAttribute();

        // Assert
        $this->assertEquals('0 business day', $deliveryTime);
    }

    #[Test]
    public function model_handles_inactive_shipping_method(): void
    {
        // Arrange
        $inactiveMethod = new ShippingMethod([
            'name' => 'Discontinued Method',
            'description' => 'No longer available',
            'price' => 0.00,
            'min_delivery_days' => 7,
            'max_delivery_days' => 14,
            'is_active' => false,
            'sort_order' => 99,
        ]);

        // Act & Assert
        $this->assertEquals('Discontinued Method', $inactiveMethod->name);
        $this->assertFalse($inactiveMethod->is_active);
        $this->assertEquals(0.00, $inactiveMethod->price);
        $this->assertEquals('7-14 business days', $inactiveMethod->getDeliveryTimeAttribute());
    }

    #[Test]
    public function model_handles_premium_shipping_method(): void
    {
        // Arrange
        $premiumMethod = new ShippingMethod([
            'name' => 'Same Day Delivery',
            'description' => 'Delivered within hours',
            'price' => 29.99,
            'min_delivery_days' => 0,
            'max_delivery_days' => 1,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        // Act & Assert
        $this->assertEquals('Same Day Delivery', $premiumMethod->name);
        $this->assertEquals(29.99, $premiumMethod->price);
        $this->assertEquals(0, $premiumMethod->sort_order);
        $this->assertEquals('0-1 business days', $premiumMethod->getDeliveryTimeAttribute());
    }

    #[Test]
    public function model_handles_free_shipping(): void
    {
        // Arrange
        $freeShipping = new ShippingMethod([
            'name' => 'Free Shipping',
            'description' => 'Standard delivery at no cost',
            'price' => 0.00,
            'min_delivery_days' => 5,
            'max_delivery_days' => 7,
            'is_active' => true,
            'sort_order' => 10,
        ]);

        // Act & Assert
        $this->assertEquals('Free Shipping', $freeShipping->name);
        $this->assertEquals(0.00, $freeShipping->price);
        $this->assertTrue($freeShipping->is_active);
        $this->assertEquals('5-7 business days', $freeShipping->getDeliveryTimeAttribute());
    }

    #[Test]
    public function model_uses_has_factory_trait(): void
    {
        // Arrange & Act & Assert
        $this->assertTrue(
            in_array(\Illuminate\Database\Eloquent\Factories\HasFactory::class, class_uses_recursive(ShippingMethod::class)),
            'ShippingMethod model should use HasFactory trait'
        );
    }

    #[Test]
    public function model_supports_different_sort_orders(): void
    {
        // Arrange
        $methods = [
            new ShippingMethod(['sort_order' => 1]),
            new ShippingMethod(['sort_order' => 5]),
            new ShippingMethod(['sort_order' => 10]),
        ];

        // Act & Assert
        $this->assertEquals(1, $methods[0]->sort_order);
        $this->assertEquals(5, $methods[1]->sort_order);
        $this->assertEquals(10, $methods[2]->sort_order);
    }
}