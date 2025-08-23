<?php

namespace Tests\Unit\Enums\User;

use App\Enums\User\UserTypeEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Base\UnitTestCase;

/**
 * Comprehensive unit tests for UserTypeEnum
 * Tests all enum cases, helper methods, and authorization logic
 */
#[CoversClass(UserTypeEnum::class)]
final class UserTypeEnumTest extends UnitTestCase
{
    #[Test]
    public function it_has_all_expected_enum_cases(): void
    {
        $expectedCases = ['ADMIN', 'USER'];
        $actualCases = array_map(fn($case) => $case->name, UserTypeEnum::cases());

        $this->assertCount(count($expectedCases), $actualCases);
        
        foreach ($expectedCases as $expectedCase) {
            $this->assertContains($expectedCase, $actualCases, "Missing enum case: {$expectedCase}");
        }
    }

    #[Test]
    public function it_has_correct_enum_values(): void
    {
        $this->assertEquals('admin', UserTypeEnum::ADMIN->value);
        $this->assertEquals('user', UserTypeEnum::USER->value);
    }

    #[Test]
    public function get_available_types_returns_all_enum_values(): void
    {
        $types = UserTypeEnum::getAvailableTypes();
        
        $expectedTypes = ['admin', 'user'];

        $this->assertIsArray($types);
        $this->assertCount(count($expectedTypes), $types);
        
        foreach ($expectedTypes as $expectedType) {
            $this->assertContains($expectedType, $types, "Missing type: {$expectedType}");
        }
    }

    #[Test]
    #[DataProvider('labelDataProvider')]
    public function get_label_returns_correct_human_readable_labels(UserTypeEnum $enum, string $expectedLabel): void
    {
        $this->assertEquals($expectedLabel, $enum->getLabel());
    }

    public static function labelDataProvider(): array
    {
        return [
            [UserTypeEnum::ADMIN, 'Administrator'],
            [UserTypeEnum::USER, 'Regular User'],
        ];
    }

    #[Test]
    #[DataProvider('emailDataProvider')]
    public function get_email_returns_correct_test_emails(UserTypeEnum $enum, string $expectedEmail): void
    {
        $this->assertEquals($expectedEmail, $enum->getEmail());
    }

    public static function emailDataProvider(): array
    {
        return [
            [UserTypeEnum::ADMIN, 'admin@test.com'],
            [UserTypeEnum::USER, 'user@test.com'],
        ];
    }

    #[Test]
    #[DataProvider('adminAccessDataProvider')]
    public function has_admin_access_returns_correct_permissions(UserTypeEnum $enum, bool $expectedAccess): void
    {
        $this->assertEquals($expectedAccess, $enum->hasAdminAccess());
    }

    public static function adminAccessDataProvider(): array
    {
        return [
            [UserTypeEnum::ADMIN, true],
            [UserTypeEnum::USER, false],
        ];
    }

    #[Test]
    #[DataProvider('manageOrdersDataProvider')]
    public function can_manage_orders_returns_correct_permissions(UserTypeEnum $enum, bool $expectedPermission): void
    {
        $this->assertEquals($expectedPermission, $enum->canManageOrders());
    }

    public static function manageOrdersDataProvider(): array
    {
        return [
            [UserTypeEnum::ADMIN, true],
            [UserTypeEnum::USER, false],
        ];
    }

    #[Test]
    #[DataProvider('manageProductsDataProvider')]
    public function can_manage_products_returns_correct_permissions(UserTypeEnum $enum, bool $expectedPermission): void
    {
        $this->assertEquals($expectedPermission, $enum->canManageProducts());
    }

    public static function manageProductsDataProvider(): array
    {
        return [
            [UserTypeEnum::ADMIN, true],
            [UserTypeEnum::USER, false],
        ];
    }

    #[Test]
    #[DataProvider('manageUsersDataProvider')]
    public function can_manage_users_returns_correct_permissions(UserTypeEnum $enum, bool $expectedPermission): void
    {
        $this->assertEquals($expectedPermission, $enum->canManageUsers());
    }

    public static function manageUsersDataProvider(): array
    {
        return [
            [UserTypeEnum::ADMIN, true],
            [UserTypeEnum::USER, false],
        ];
    }

    #[Test]
    public function admin_type_has_all_permissions(): void
    {
        $admin = UserTypeEnum::ADMIN;
        
        $this->assertTrue($admin->hasAdminAccess(), 'Admin should have admin access');
        $this->assertTrue($admin->canManageOrders(), 'Admin should be able to manage orders');
        $this->assertTrue($admin->canManageProducts(), 'Admin should be able to manage products');
        $this->assertTrue($admin->canManageUsers(), 'Admin should be able to manage users');
    }

    #[Test]
    public function user_type_has_no_admin_permissions(): void
    {
        $user = UserTypeEnum::USER;
        
        $this->assertFalse($user->hasAdminAccess(), 'User should not have admin access');
        $this->assertFalse($user->canManageOrders(), 'User should not be able to manage orders');
        $this->assertFalse($user->canManageProducts(), 'User should not be able to manage products');
        $this->assertFalse($user->canManageUsers(), 'User should not be able to manage users');
    }

    #[Test]
    public function enum_instances_can_be_created_from_values(): void
    {
        $adminEnum = UserTypeEnum::from('admin');
        $this->assertSame(UserTypeEnum::ADMIN, $adminEnum);

        $userEnum = UserTypeEnum::from('user');
        $this->assertSame(UserTypeEnum::USER, $userEnum);
    }

    #[Test]
    public function try_from_returns_null_for_invalid_values(): void
    {
        $result = UserTypeEnum::tryFrom('invalid_type');
        $this->assertNull($result);

        $result = UserTypeEnum::tryFrom('');
        $this->assertNull($result);

        $result = UserTypeEnum::tryFrom('moderator');
        $this->assertNull($result);
    }

    #[Test]
    public function enum_can_be_serialized_to_json(): void
    {
        $adminJson = json_encode(UserTypeEnum::ADMIN);
        $this->assertEquals('"admin"', $adminJson);

        $userJson = json_encode(UserTypeEnum::USER);
        $this->assertEquals('"user"', $userJson);
    }

    #[Test]
    public function enum_values_are_unique(): void
    {
        $values = array_map(fn($case) => $case->value, UserTypeEnum::cases());
        $uniqueValues = array_unique($values);
        
        $this->assertCount(count($values), $uniqueValues, 'Enum values must be unique');
    }

    #[Test]
    public function all_labels_are_non_empty_strings(): void
    {
        foreach (UserTypeEnum::cases() as $case) {
            $label = $case->getLabel();
            $this->assertIsString($label, "Label for {$case->name} should be string");
            $this->assertNotEmpty($label, "Label for {$case->name} should not be empty");
        }
    }

    #[Test]
    public function all_emails_are_valid_format(): void
    {
        foreach (UserTypeEnum::cases() as $case) {
            $email = $case->getEmail();
            $this->assertIsString($email, "Email for {$case->name} should be string");
            $this->assertNotEmpty($email, "Email for {$case->name} should not be empty");
            $this->assertStringContainsString('@', $email, "Email for {$case->name} should contain @");
            $this->assertMatchesRegularExpression(
                '/^[^\s@]+@[^\s@]+\.[^\s@]+$/',
                $email,
                "Email for {$case->name} should be valid format"
            );
        }
    }

    #[Test]
    public function test_emails_match_expected_domains(): void
    {
        foreach (UserTypeEnum::cases() as $case) {
            $email = $case->getEmail();
            $this->assertStringEndsWith('@test.com', $email, "Email for {$case->name} should use test.com domain");
        }
    }

    #[Test]
    public function permission_methods_return_boolean_values(): void
    {
        foreach (UserTypeEnum::cases() as $case) {
            $this->assertIsBool($case->hasAdminAccess(), "hasAdminAccess for {$case->name} should return boolean");
            $this->assertIsBool($case->canManageOrders(), "canManageOrders for {$case->name} should return boolean");
            $this->assertIsBool($case->canManageProducts(), "canManageProducts for {$case->name} should return boolean");
            $this->assertIsBool($case->canManageUsers(), "canManageUsers for {$case->name} should return boolean");
        }
    }

    #[Test]
    public function admin_type_is_only_admin_type(): void
    {
        $adminTypes = array_filter(
            UserTypeEnum::cases(),
            fn($case) => $case->hasAdminAccess()
        );

        $this->assertCount(1, $adminTypes, 'Only one admin type should exist');
        $this->assertSame(UserTypeEnum::ADMIN, $adminTypes[0], 'Admin should be the only admin type');
    }

    #[Test]
    public function non_admin_types_have_consistent_permissions(): void
    {
        $nonAdminTypes = array_filter(
            UserTypeEnum::cases(),
            fn($case) => !$case->hasAdminAccess()
        );

        foreach ($nonAdminTypes as $case) {
            $this->assertFalse($case->canManageOrders(), "Non-admin {$case->name} should not manage orders");
            $this->assertFalse($case->canManageProducts(), "Non-admin {$case->name} should not manage products");
            $this->assertFalse($case->canManageUsers(), "Non-admin {$case->name} should not manage users");
        }
    }

    #[Test]
    public function enum_case_comparison_works_correctly(): void
    {
        $admin1 = UserTypeEnum::ADMIN;
        $admin2 = UserTypeEnum::from('admin');
        $user = UserTypeEnum::USER;

        $this->assertTrue($admin1 === $admin2, 'Same enum instances should be identical');
        $this->assertFalse($admin1 === $user, 'Different enum instances should not be identical');
    }

    #[Test]
    public function all_expected_methods_exist(): void
    {
        $reflection = new \ReflectionClass(UserTypeEnum::class);
        
        $expectedMethods = [
            'getAvailableTypes',
            'getLabel',
            'getEmail',
            'hasAdminAccess',
            'canManageOrders',
            'canManageProducts',
            'canManageUsers'
        ];

        foreach ($expectedMethods as $methodName) {
            $this->assertTrue(
                $reflection->hasMethod($methodName),
                "Method {$methodName} should exist in UserTypeEnum"
            );
        }
    }

    #[Test]
    public function static_method_returns_array(): void
    {
        $types = UserTypeEnum::getAvailableTypes();
        $this->assertIsArray($types, 'getAvailableTypes should return an array');
        $this->assertNotEmpty($types, 'getAvailableTypes should not return empty array');
    }
}