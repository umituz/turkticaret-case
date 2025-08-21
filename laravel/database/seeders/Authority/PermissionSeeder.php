<?php

namespace Database\Seeders\Authority;

use App\Enums\Authority\PermissionEnum;
use App\Models\Authority\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->createPermissions();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function createPermissions(): void
    {
        $permissions = PermissionEnum::values();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                [
                    'name' => $permission,
                    'guard_name' => 'web',
                ]
            );
        }
    }
}
