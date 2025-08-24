<?php

namespace Tests\Unit\Enums\Setting;

use App\Enums\Setting\SettingKeyEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Base\UnitTestCase;

/**
 * Comprehensive unit tests for SettingKeyEnum
 * Tests all enum cases, helper methods, and business logic
 */
#[CoversClass(SettingKeyEnum::class)]
final class SettingKeyEnumTest extends UnitTestCase
{
    #[Test]
    public function it_has_all_expected_enum_cases(): void
    {
        $expectedCases = [
            'DEFAULT_CURRENCY',
            'DEFAULT_LANGUAGE', 
            'DEFAULT_COUNTRY',
            'DEFAULT_TIMEZONE',
            'TAX_ENABLED',
            'SHIPPING_ENABLED',
            'MAINTENANCE_MODE',
            'APP_NAME',
            'APP_URL',
            'REGISTRATION_ENABLED',
            'ITEMS_PER_PAGE',
            'THEME',
            'LOGO_URL',
            'EMAIL_NOTIFICATIONS_ENABLED',
            'SMS_NOTIFICATIONS_ENABLED'
        ];

        $actualCases = array_map(fn($case) => $case->name, SettingKeyEnum::cases());

        $this->assertCount(count($expectedCases), $actualCases);
        
        foreach ($expectedCases as $expectedCase) {
            $this->assertContains($expectedCase, $actualCases, "Missing enum case: {$expectedCase}");
        }
    }

    #[Test]
    public function it_has_correct_enum_values(): void
    {
        $expectedValues = [
            'DEFAULT_CURRENCY' => 'default_currency',
            'DEFAULT_LANGUAGE' => 'default_language',
            'DEFAULT_COUNTRY' => 'default_country',
            'DEFAULT_TIMEZONE' => 'default_timezone',
            'TAX_ENABLED' => 'tax_enabled',
            'SHIPPING_ENABLED' => 'shipping_enabled',
            'MAINTENANCE_MODE' => 'maintenance_mode',
            'APP_NAME' => 'app_name',
            'APP_URL' => 'app_url',
            'REGISTRATION_ENABLED' => 'registration_enabled',
            'ITEMS_PER_PAGE' => 'items_per_page',
            'THEME' => 'theme',
            'LOGO_URL' => 'logo_url',
            'EMAIL_NOTIFICATIONS_ENABLED' => 'email_notifications_enabled',
            'SMS_NOTIFICATIONS_ENABLED' => 'sms_notifications_enabled'
        ];

        foreach ($expectedValues as $caseName => $expectedValue) {
            $enum = constant(SettingKeyEnum::class . '::' . $caseName);
            $this->assertEquals($expectedValue, $enum->value, "Incorrect value for {$caseName}");
        }
    }

    #[Test]
    public function get_available_keys_returns_all_enum_values(): void
    {
        $keys = SettingKeyEnum::getAvailableKeys();
        
        $expectedKeys = [
            'default_currency',
            'default_language',
            'default_country',
            'default_timezone',
            'tax_enabled',
            'shipping_enabled',
            'maintenance_mode',
            'app_name',
            'app_url',
            'registration_enabled',
            'items_per_page',
            'theme',
            'logo_url',
            'email_notifications_enabled',
            'sms_notifications_enabled'
        ];

        $this->assertIsArray($keys);
        $this->assertCount(count($expectedKeys), $keys);
        
        foreach ($expectedKeys as $expectedKey) {
            $this->assertContains($expectedKey, $keys, "Missing key: {$expectedKey}");
        }
    }

    #[Test]
    #[DataProvider('labelDataProvider')]
    public function get_label_returns_correct_human_readable_labels(SettingKeyEnum $enum, string $expectedLabel): void
    {
        $this->assertEquals($expectedLabel, $enum->getLabel());
    }

    public static function labelDataProvider(): array
    {
        return [
            [SettingKeyEnum::DEFAULT_CURRENCY, 'Default Currency'],
            [SettingKeyEnum::DEFAULT_LANGUAGE, 'Default Language'],
            [SettingKeyEnum::DEFAULT_COUNTRY, 'Default Country'],
            [SettingKeyEnum::DEFAULT_TIMEZONE, 'Default Timezone'],
            [SettingKeyEnum::TAX_ENABLED, 'Tax Enabled'],
            [SettingKeyEnum::SHIPPING_ENABLED, 'Shipping Enabled'],
            [SettingKeyEnum::MAINTENANCE_MODE, 'Maintenance Mode'],
            [SettingKeyEnum::APP_NAME, 'Application Name'],
            [SettingKeyEnum::APP_URL, 'Application URL'],
            [SettingKeyEnum::REGISTRATION_ENABLED, 'User Registration Enabled'],
            [SettingKeyEnum::ITEMS_PER_PAGE, 'Items Per Page'],
            [SettingKeyEnum::THEME, 'Application Theme'],
            [SettingKeyEnum::LOGO_URL, 'Logo URL'],
            [SettingKeyEnum::EMAIL_NOTIFICATIONS_ENABLED, 'Email Notifications Enabled'],
            [SettingKeyEnum::SMS_NOTIFICATIONS_ENABLED, 'SMS Notifications Enabled']
        ];
    }

    #[Test]
    #[DataProvider('groupDataProvider')]
    public function get_group_returns_correct_setting_groups(SettingKeyEnum $enum, string $expectedGroup): void
    {
        $this->assertEquals($expectedGroup, $enum->getGroup());
    }

    public static function groupDataProvider(): array
    {
        return [
            // Commerce group
            [SettingKeyEnum::DEFAULT_CURRENCY, 'commerce'],
            [SettingKeyEnum::TAX_ENABLED, 'commerce'],
            [SettingKeyEnum::SHIPPING_ENABLED, 'commerce'],
            
            // Localization group
            [SettingKeyEnum::DEFAULT_LANGUAGE, 'localization'],
            [SettingKeyEnum::DEFAULT_COUNTRY, 'localization'],
            [SettingKeyEnum::DEFAULT_TIMEZONE, 'localization'],
            
            // System group
            [SettingKeyEnum::MAINTENANCE_MODE, 'system'],
            [SettingKeyEnum::APP_NAME, 'system'],
            [SettingKeyEnum::APP_URL, 'system'],
            [SettingKeyEnum::REGISTRATION_ENABLED, 'system'],
            
            // UI group
            [SettingKeyEnum::ITEMS_PER_PAGE, 'ui'],
            [SettingKeyEnum::THEME, 'ui'],
            [SettingKeyEnum::LOGO_URL, 'ui'],
            
            // Notification group
            [SettingKeyEnum::EMAIL_NOTIFICATIONS_ENABLED, 'notification'],
            [SettingKeyEnum::SMS_NOTIFICATIONS_ENABLED, 'notification']
        ];
    }

    #[Test]
    #[DataProvider('typeDataProvider')]
    public function get_type_returns_correct_data_types(SettingKeyEnum $enum, string $expectedType): void
    {
        $this->assertEquals($expectedType, $enum->getType());
    }

    public static function typeDataProvider(): array
    {
        return [
            // String types
            [SettingKeyEnum::DEFAULT_CURRENCY, 'string'],
            [SettingKeyEnum::DEFAULT_LANGUAGE, 'string'],
            [SettingKeyEnum::DEFAULT_COUNTRY, 'string'],
            [SettingKeyEnum::DEFAULT_TIMEZONE, 'string'],
            [SettingKeyEnum::APP_NAME, 'string'],
            [SettingKeyEnum::APP_URL, 'string'],
            [SettingKeyEnum::THEME, 'string'],
            [SettingKeyEnum::LOGO_URL, 'string'],
            
            // Boolean types
            [SettingKeyEnum::TAX_ENABLED, 'boolean'],
            [SettingKeyEnum::SHIPPING_ENABLED, 'boolean'],
            [SettingKeyEnum::MAINTENANCE_MODE, 'boolean'],
            [SettingKeyEnum::REGISTRATION_ENABLED, 'boolean'],
            [SettingKeyEnum::EMAIL_NOTIFICATIONS_ENABLED, 'boolean'],
            [SettingKeyEnum::SMS_NOTIFICATIONS_ENABLED, 'boolean'],
            
            // Integer types
            [SettingKeyEnum::ITEMS_PER_PAGE, 'integer']
        ];
    }

    #[Test]
    #[DataProvider('defaultValueDataProvider')]
    public function get_default_value_returns_correct_defaults(SettingKeyEnum $enum, $expectedDefault): void
    {
        $defaultValue = $enum->getDefaultValue();
        $this->assertIsArray($defaultValue);
        $this->assertArrayHasKey('value', $defaultValue);
        $this->assertEquals($expectedDefault, $defaultValue['value']);
    }

    public static function defaultValueDataProvider(): array
    {
        return [
            // String defaults
            [SettingKeyEnum::DEFAULT_CURRENCY, 'TRY'],
            [SettingKeyEnum::DEFAULT_LANGUAGE, 'tr'],
            [SettingKeyEnum::DEFAULT_COUNTRY, 'TR'],
            [SettingKeyEnum::DEFAULT_TIMEZONE, 'Europe/Istanbul'],
            [SettingKeyEnum::APP_NAME, 'TurkTicaret'],
            [SettingKeyEnum::APP_URL, 'http://localhost:8080'],
            [SettingKeyEnum::THEME, 'default'],
            [SettingKeyEnum::LOGO_URL, '/images/logo.png'],
            
            // Boolean defaults
            [SettingKeyEnum::TAX_ENABLED, true],
            [SettingKeyEnum::SHIPPING_ENABLED, true],
            [SettingKeyEnum::MAINTENANCE_MODE, false],
            [SettingKeyEnum::REGISTRATION_ENABLED, true],
            [SettingKeyEnum::EMAIL_NOTIFICATIONS_ENABLED, true],
            [SettingKeyEnum::SMS_NOTIFICATIONS_ENABLED, false],
            
            // Integer defaults
            [SettingKeyEnum::ITEMS_PER_PAGE, 20]
        ];
    }

    #[Test]
    #[DataProvider('descriptionDataProvider')]
    public function get_description_returns_correct_descriptions(SettingKeyEnum $enum, string $expectedDescription): void
    {
        $this->assertEquals($expectedDescription, $enum->getDescription());
    }

    public static function descriptionDataProvider(): array
    {
        return [
            [SettingKeyEnum::DEFAULT_CURRENCY, 'Default currency for guest users and new registrations'],
            [SettingKeyEnum::DEFAULT_LANGUAGE, 'Default language for guest users'],
            [SettingKeyEnum::DEFAULT_COUNTRY, 'Default country for guest users'],
            [SettingKeyEnum::DEFAULT_TIMEZONE, 'Default timezone for the application'],
            [SettingKeyEnum::TAX_ENABLED, 'Enable tax calculations globally'],
            [SettingKeyEnum::SHIPPING_ENABLED, 'Enable shipping functionality'],
            [SettingKeyEnum::MAINTENANCE_MODE, 'Put application in maintenance mode'],
            [SettingKeyEnum::APP_NAME, 'Application display name'],
            [SettingKeyEnum::APP_URL, 'Base URL of the application'],
            [SettingKeyEnum::REGISTRATION_ENABLED, 'Allow new user registrations'],
            [SettingKeyEnum::ITEMS_PER_PAGE, 'Default number of items per page in listings'],
            [SettingKeyEnum::THEME, 'Default theme for the application'],
            [SettingKeyEnum::LOGO_URL, 'URL or path to the application logo'],
            [SettingKeyEnum::EMAIL_NOTIFICATIONS_ENABLED, 'Enable email notifications globally'],
            [SettingKeyEnum::SMS_NOTIFICATIONS_ENABLED, 'Enable SMS notifications globally']
        ];
    }

    #[Test]
    public function enum_instances_can_be_created_from_values(): void
    {
        $enum = SettingKeyEnum::from('default_currency');
        $this->assertSame(SettingKeyEnum::DEFAULT_CURRENCY, $enum);

        $enum = SettingKeyEnum::from('maintenance_mode');
        $this->assertSame(SettingKeyEnum::MAINTENANCE_MODE, $enum);
    }

    #[Test]
    public function try_from_returns_null_for_invalid_values(): void
    {
        $result = SettingKeyEnum::tryFrom('invalid_key');
        $this->assertNull($result);

        $result = SettingKeyEnum::tryFrom('');
        $this->assertNull($result);
    }

    #[Test]
    public function enum_can_be_serialized_to_json(): void
    {
        $enum = SettingKeyEnum::DEFAULT_CURRENCY;
        $json = json_encode($enum);
        
        $this->assertEquals('"default_currency"', $json);
    }

    #[Test]
    public function enum_values_are_unique(): void
    {
        $values = array_map(fn($case) => $case->value, SettingKeyEnum::cases());
        $uniqueValues = array_unique($values);
        
        $this->assertCount(count($values), $uniqueValues, 'Enum values must be unique');
    }

    #[Test]
    public function all_groups_are_valid_categories(): void
    {
        $validGroups = ['commerce', 'localization', 'system', 'ui', 'notification'];
        
        foreach (SettingKeyEnum::cases() as $case) {
            $group = $case->getGroup();
            $this->assertContains($group, $validGroups, "Invalid group '{$group}' for case {$case->name}");
        }
    }

    #[Test]
    public function all_types_are_valid_data_types(): void
    {
        $validTypes = ['string', 'boolean', 'integer'];
        
        foreach (SettingKeyEnum::cases() as $case) {
            $type = $case->getType();
            $this->assertContains($type, $validTypes, "Invalid type '{$type}' for case {$case->name}");
        }
    }

    #[Test]
    public function default_values_match_declared_types(): void
    {
        foreach (SettingKeyEnum::cases() as $case) {
            $defaultValue = $case->getDefaultValue();
            $this->assertIsArray($defaultValue);
            $this->assertArrayHasKey('value', $defaultValue);
            
            $actualValue = $defaultValue['value'];
            $expectedType = $case->getType();
            
            switch ($expectedType) {
                case 'string':
                    $this->assertIsString($actualValue, "Default value for {$case->name} should be string");
                    break;
                case 'boolean':
                    $this->assertIsBool($actualValue, "Default value for {$case->name} should be boolean");
                    break;
                case 'integer':
                    $this->assertIsInt($actualValue, "Default value for {$case->name} should be integer");
                    break;
            }
        }
    }

    #[Test]
    public function all_labels_are_non_empty_strings(): void
    {
        foreach (SettingKeyEnum::cases() as $case) {
            $label = $case->getLabel();
            $this->assertIsString($label, "Label for {$case->name} should be string");
            $this->assertNotEmpty($label, "Label for {$case->name} should not be empty");
        }
    }

    #[Test]
    public function all_descriptions_are_non_empty_strings(): void
    {
        foreach (SettingKeyEnum::cases() as $case) {
            $description = $case->getDescription();
            $this->assertIsString($description, "Description for {$case->name} should be string");
            $this->assertNotEmpty($description, "Description for {$case->name} should not be empty");
        }
    }

    #[Test]
    public function commerce_group_contains_expected_settings(): void
    {
        $commerceSettings = array_filter(
            SettingKeyEnum::cases(),
            fn($case) => $case->getGroup() === 'commerce'
        );

        $commerceNames = array_map(fn($case) => $case->name, $commerceSettings);
        
        $expectedCommerce = ['DEFAULT_CURRENCY', 'TAX_ENABLED', 'SHIPPING_ENABLED'];
        
        foreach ($expectedCommerce as $expected) {
            $this->assertContains($expected, $commerceNames, "Missing commerce setting: {$expected}");
        }
    }

    #[Test]
    public function localization_group_contains_expected_settings(): void
    {
        $localizationSettings = array_filter(
            SettingKeyEnum::cases(),
            fn($case) => $case->getGroup() === 'localization'
        );

        $localizationNames = array_map(fn($case) => $case->name, $localizationSettings);
        
        $expectedLocalization = ['DEFAULT_LANGUAGE', 'DEFAULT_COUNTRY', 'DEFAULT_TIMEZONE'];
        
        foreach ($expectedLocalization as $expected) {
            $this->assertContains($expected, $localizationNames, "Missing localization setting: {$expected}");
        }
    }

    #[Test]
    public function notification_group_contains_expected_settings(): void
    {
        $notificationSettings = array_filter(
            SettingKeyEnum::cases(),
            fn($case) => $case->getGroup() === 'notification'
        );

        $notificationNames = array_map(fn($case) => $case->name, $notificationSettings);
        
        $expectedNotification = ['EMAIL_NOTIFICATIONS_ENABLED', 'SMS_NOTIFICATIONS_ENABLED'];
        
        foreach ($expectedNotification as $expected) {
            $this->assertContains($expected, $notificationNames, "Missing notification setting: {$expected}");
        }
    }
}