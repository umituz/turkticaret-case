<?php

namespace Database\Seeders\Auth;

use App\Models\Auth\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->createAdminUser();
        $this->createRegularUser();
    }

    private function createAdminUser(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('admin123'),
            ]
        );

        $admin->assignRole('Admin');
    }

    private function createRegularUser(): void
    {
        $regular = User::firstOrCreate(
            ['email' => 'user@test.com'],
            [
                'name' => 'Regular User',
                'password' => bcrypt('user123'),
            ]
        );

        $regular->assignRole('Regular');
    }
}
