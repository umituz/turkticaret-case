<?php

namespace Database\Seeders\Auth;

use App\Enums\User\UserTypeEnum;
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
            ['email' => UserTypeEnum::ADMIN->getEmail()],
            [
                'name' => UserTypeEnum::ADMIN->getLabel(),
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
            ['email' => UserTypeEnum::USER->getEmail()],
            [
                'name' => UserTypeEnum::USER->getLabel(),
                'password' => 'user123',
                'language_uuid' => $defaultLanguage->uuid,
                'country_uuid' => $defaultCountry->uuid,
            ]
        );

        $regular->assignRole('Regular');
    }
}
