<?php

namespace Database\Seeders;

use Database\Seeders\Authority\PermissionSeeder;
use Database\Seeders\Authority\RolePermissionSeeder;
use Database\Seeders\Authority\RoleSeeder;
use Database\Seeders\Cart\CartSeeder;
use Database\Seeders\Category\CategorySeeder;
use Database\Seeders\Country\CountrySeeder;
use Database\Seeders\Currency\CurrencySeeder;
use Database\Seeders\Language\LanguageSeeder;
use Database\Seeders\Order\OrderSeeder;
use Database\Seeders\Product\ProductSeeder;
use Database\Seeders\Setting\SettingsSeeder;
use Database\Seeders\User\UserSeeder;
use Database\Seeders\User\UserAddressSeeder;
use Database\Seeders\User\UserSettingsSeeder;
use Database\Seeders\Shipping\ShippingMethodSeeder;
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
            LanguageSeeder::class,
            CurrencySeeder::class,
            CountrySeeder::class,
            SettingsSeeder::class,
            UserSeeder::class,
            UserAddressSeeder::class,
            UserSettingsSeeder::class,
            ShippingMethodSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            #CartSeeder::class, // not necessary
            #OrderSeeder::class, // not necessary
        ]);
    }
}
