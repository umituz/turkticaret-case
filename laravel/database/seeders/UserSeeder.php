<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => 'admin123',
        ]);

        User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@test.com',
            'password' => 'user123',
        ]);
    }
}
