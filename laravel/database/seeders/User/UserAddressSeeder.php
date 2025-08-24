<?php

namespace Database\Seeders\User;

use App\Enums\User\UserTypeEnum;
use App\Models\Country\Country;
use App\Models\User\User;
use App\Models\User\UserAddress;
use Illuminate\Database\Seeder;

class UserAddressSeeder extends Seeder
{
    public function run(): void
    {
        $this->createUserAddresses();
    }

    private function createUserAddresses(): void
    {
        $defaultCountry = Country::where('code', 'TR')->first();
        $regularUser = User::where('email', UserTypeEnum::USER->getEmail())->first();

        if (!$regularUser || !$defaultCountry) {
            return;
        }

        $this->createAddressesForUser($regularUser, $defaultCountry);
    }

    private function createAddressesForUser(User $user, Country $country): void
    {
        UserAddress::firstOrCreate(
            [
                'user_uuid' => $user->uuid,
                'type' => 'billing',
                'is_default' => true
            ],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'company' => 'Test Company',
                'address_line_1' => 'Test Street 123',
                'address_line_2' => 'Apt 4B',
                'city' => 'Istanbul',
                'state' => 'Marmara',
                'postal_code' => '34000',
                'country_uuid' => $country->uuid,
                'phone' => '+90 555 123 4567',
            ]
        );

        UserAddress::firstOrCreate(
            [
                'user_uuid' => $user->uuid,
                'type' => 'shipping',
                'is_default' => false
            ],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'address_line_1' => 'Shipping Address 456',
                'city' => 'Ankara',
                'state' => 'Ankara',
                'postal_code' => '06000',
                'country_uuid' => $country->uuid,
                'phone' => '+90 555 987 6543',
            ]
        );
    }
}