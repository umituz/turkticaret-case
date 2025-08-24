<?php

namespace Tests\Unit\Models\Setting;

use App\Models\Setting\Setting;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for Setting Model
 * Tests model attributes, casts, scopes and accessors
 */
#[CoversClass(Setting::class)]
#[Group('unit')]
#[Group('models')]
#[Small]
class SettingTest extends UnitTestCase
{
    #[Test]
    public function model_has_correct_fillable_attributes(): void
    {
        // Arrange & Act
        $setting = new Setting();
        
        // Assert
        $expectedFillable = [
            'key',
            'value',
            'type',
            'group',
            'description',
            'is_active',
            'is_editable',
        ];
        
        $this->assertEquals($expectedFillable, $setting->getFillable());
    }

    #[Test]
    public function model_has_correct_casts(): void
    {
        // Arrange & Act
        $setting = new Setting();
        $casts = $setting->getCasts();
        
        // Assert - Check specific casts we care about
        $this->assertEquals('array', $casts['value']);
        $this->assertEquals('boolean', $casts['is_active']);
        $this->assertEquals('boolean', $casts['is_editable']);
        $this->assertEquals('datetime', $casts['deleted_at']); // From SoftDeletes
        
        // Verify we have at least the expected casts count
        $this->assertGreaterThanOrEqual(4, count($casts));
    }

    #[Test]
    #[DataProvider('valueAsStringProvider')]
    public function get_value_as_string_attribute_converts_values_correctly(string $type, array $value, string $expectedOutput): void
    {
        // Arrange
        $setting = new Setting();
        $setting->type = $type;
        $setting->value = $value;

        // Act
        $result = $setting->value_as_string;

        // Assert
        $this->assertEquals($expectedOutput, $result);
    }

    public static function valueAsStringProvider(): array
    {
        return [
            'boolean_true' => ['boolean', ['value' => true], 'true'],
            'boolean_false' => ['boolean', ['value' => false], 'false'],
            'string_value' => ['string', ['value' => 'test string'], 'test string'],
            'integer_value' => ['integer', ['value' => 123], '123'],
            'json_value' => ['json', ['key' => 'value'], '{"key":"value"}'],
            'default_direct_value' => ['string', ['value' => 'direct'], 'direct'],
        ];
    }

    #[Test]
    #[DataProvider('typedValueProvider')]
    public function get_typed_value_attribute_converts_values_by_type($type, $inputValue, $expectedOutput, string $description): void
    {
        // Arrange
        $setting = new Setting();
        $setting->type = $type;
        $setting->value = $inputValue;

        // Act
        $result = $setting->typed_value;

        // Assert
        $this->assertEquals($expectedOutput, $result, $description);
        
        // Additional type assertions
        match ($type) {
            'boolean' => $this->assertIsBool($result),
            'integer' => $this->assertIsInt($result),
            'string' => $this->assertIsString($result),
            'json' => $this->assertIsArray($result),
            default => null
        };
    }

    public static function typedValueProvider(): array
    {
        return [
            'boolean_true_from_array' => ['boolean', ['value' => 1], true, 'Boolean conversion from array'],
            'boolean_false_from_array' => ['boolean', ['value' => 0], false, 'Boolean false conversion'],
            'integer_from_array' => ['integer', ['value' => '123'], 123, 'Integer conversion from string'],
            'integer_from_string' => ['integer', '456', 456, 'Direct integer conversion'],
            'string_from_array' => ['string', ['value' => 'test'], 'test', 'String extraction from array'],
            'string_direct' => ['string', 'direct string', 'direct string', 'Direct string value'],
            'json_array' => ['json', ['key' => 'value', 'nested' => ['data' => true]], ['key' => 'value', 'nested' => ['data' => true]], 'JSON array conversion'],
            'unknown_type' => ['unknown', 'raw value', 'raw value', 'Unknown type returns raw value'],
        ];
    }

    #[Test]
    public function get_typed_value_handles_json_encoded_strings(): void
    {
        // Arrange
        $setting = new Setting();
        $setting->type = 'string';
        $setting->value = '"quoted string"'; // JSON-encoded string

        // Act
        $result = $setting->typed_value;

        // Assert
        $this->assertEquals('quoted string', $result);
        $this->assertIsString($result);
    }

    #[Test]
    public function model_extends_base_uuid_model(): void
    {
        // Arrange & Act
        $setting = new Setting();
        
        // Assert
        $this->assertInstanceOf(\App\Models\Base\BaseUuidModel::class, $setting);
    }

    #[Test]
    public function model_uses_correct_table_name(): void
    {
        // Arrange & Act
        $setting = new Setting();
        
        // Assert
        $this->assertEquals('settings', $setting->getTable());
    }

    #[Test]
    public function setting_can_be_created_with_all_fillable_attributes(): void
    {
        // Arrange
        $attributes = [
            'key' => 'test_setting',
            'value' => ['value' => 'test value'],
            'type' => 'string',
            'group' => 'test',
            'description' => 'Test setting description',
            'is_active' => true,
            'is_editable' => false,
        ];

        // Act
        $setting = new Setting($attributes);

        // Assert
        $this->assertEquals('test_setting', $setting->key);
        $this->assertEquals(['value' => 'test value'], $setting->value);
        $this->assertEquals('string', $setting->type);
        $this->assertEquals('test', $setting->group);
        $this->assertEquals('Test setting description', $setting->description);
        $this->assertTrue($setting->is_active);
        $this->assertFalse($setting->is_editable);
    }
}