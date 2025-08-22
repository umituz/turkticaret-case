<?php

namespace Database\Seeders\Auth;

use App\Models\Auth\User;
use App\Models\Country\Country;
use App\Models\Language\Language;
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
        $defaultLanguage = Language::where('code', 'tr')->first();
        $defaultCountry = Country::where('code', 'TR')->first();

        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin',
                'password' => 'admin123',
                'language_uuid' => $defaultLanguage->uuid,
                'country_uuid' => $defaultCountry->uuid,
            ]
        );

        $admin->assignRole('Admin');
    }

    private function createRegularUser(): void
    {
        $defaultLanguage = Language::where('code', 'tr')->first();
        $defaultCountry = Country::where('code', 'TR')->first();

        $regular = User::firstOrCreate(
            ['email' => 'user@test.com'],
            [
                'name' => 'User',
                'password' => 'user123',
                'language_uuid' => $defaultLanguage->uuid,
                'country_uuid' => $defaultCountry->uuid,
            ]
        );

        $regular->assignRole('Regular');
    }
}
