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
            ['email' => 'admin@turkticaret.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password123'),
            ]
        );

        $admin->assignRole('Admin');
    }

    private function createRegularUser(): void
    {
        $regular = User::firstOrCreate(
            ['email' => 'user@turkticaret.com'],
            [
                'name' => 'Regular User',
                'password' => bcrypt('password123'),
            ]
        );

        $regular->assignRole('Regular');
    }
}
