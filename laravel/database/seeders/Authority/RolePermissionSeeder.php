<?php

namespace Database\Seeders\Authority;

use App\Enums\Authority\PermissionEnum;
use App\Models\Authority\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->assignPermissionsToRoles();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function assignPermissionsToRoles(): void
    {
        $this->assignAdminPermissions();
        $this->assignRegularPermissions();
    }

    private function assignAdminPermissions(): void
    {
        $adminRole = Role::where('name', 'Admin')->first();

        if (!$adminRole) {
            throw new \Exception('Admin role not found. Please run RoleSeeder first.');
        }

        $adminPermissions = [
            PermissionEnum::USER_READ->value,
            PermissionEnum::USER_CREATE->value,
            PermissionEnum::USER_UPDATE->value,
            PermissionEnum::USER_DELETE->value,
            PermissionEnum::PRODUCT_READ->value,
            PermissionEnum::PRODUCT_CREATE->value,
            PermissionEnum::PRODUCT_UPDATE->value,
            PermissionEnum::PRODUCT_DELETE->value,
            PermissionEnum::CATEGORY_READ->value,
            PermissionEnum::CATEGORY_CREATE->value,
            PermissionEnum::CATEGORY_UPDATE->value,
            PermissionEnum::CATEGORY_DELETE->value,
            PermissionEnum::ORDER_READ->value,
            PermissionEnum::ORDER_CREATE->value,
            PermissionEnum::ORDER_UPDATE->value,
            PermissionEnum::ORDER_DELETE->value,
            PermissionEnum::ORDER_MANAGE->value,
            PermissionEnum::CART_READ->value,
            PermissionEnum::CART_UPDATE->value,
        ];

        $adminRole->syncPermissions($adminPermissions);
    }

    private function assignRegularPermissions(): void
    {
        $regularRole = Role::where('name', 'Regular')->first();

        if (!$regularRole) {
            throw new \Exception('Regular role not found. Please run RoleSeeder first.');
        }

        $regularPermissions = [
            PermissionEnum::PRODUCT_READ->value,
            PermissionEnum::CATEGORY_READ->value,
            PermissionEnum::ORDER_READ->value,
            PermissionEnum::ORDER_CREATE->value,
            PermissionEnum::CART_READ->value,
            PermissionEnum::CART_UPDATE->value,
        ];

        $regularRole->syncPermissions($regularPermissions);
    }
}
