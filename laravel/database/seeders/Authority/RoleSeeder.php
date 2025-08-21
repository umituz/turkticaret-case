<?php

namespace Database\Seeders\Authority;

use App\Models\Authority\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->createRoles();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function createRoles(): void
    {
        Role::firstOrCreate(
            ['name' => 'Admin'],
            [
                'name' => 'Admin',
                'guard_name' => 'web',
            ]
        );

        Role::firstOrCreate(
            ['name' => 'Regular'],
            [
                'name' => 'Regular',
                'guard_name' => 'web',
            ]
        );
    }
}