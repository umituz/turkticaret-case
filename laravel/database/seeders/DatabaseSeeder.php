<?php

namespace Database\Seeders;

use database\seeders\Auth\UserSeeder;
use Database\Seeders\Authority\PermissionSeeder;
use Database\Seeders\Authority\RolePermissionSeeder;
use Database\Seeders\Authority\RoleSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            CartSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
