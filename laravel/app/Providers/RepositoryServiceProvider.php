<?php

namespace App\Providers;

use App\Repositories\Cart\CartRepository;
use App\Repositories\Cart\CartRepositoryInterface;
use App\Repositories\Category\CategoryRepository;
use App\Repositories\Category\CategoryRepositoryInterface;
use App\Repositories\Country\CountryRepository;
use App\Repositories\Country\CountryRepositoryInterface;
use App\Repositories\Currency\CurrencyRepository;
use App\Repositories\Currency\CurrencyRepositoryInterface;
use App\Repositories\Dashboard\DashboardRepository;
use App\Repositories\Dashboard\DashboardRepositoryInterface;
use App\Repositories\Language\LanguageRepository;
use App\Repositories\Language\LanguageRepositoryInterface;
use App\Repositories\Order\OrderRepository;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Repositories\Order\OrderStatusRepository;
use App\Repositories\Order\OrderStatusRepositoryInterface;
use App\Repositories\Product\ProductRepository;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\Setting\SettingsRepository;
use App\Repositories\Setting\SettingsRepositoryInterface;
use App\Repositories\User\UserRepository;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\User\UserSettings\UserSettingsRepository;
use App\Repositories\User\UserSettings\UserSettingsRepositoryInterface;
use App\Repositories\User\Address\AddressRepository;
use App\Repositories\User\Address\AddressRepositoryInterface;
use App\Repositories\Shipping\ShippingMethodRepository;
use App\Repositories\Shipping\ShippingMethodRepositoryInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for binding repository interfaces to their implementations.
 * 
 * This provider registers all repository interface to concrete implementation
 * bindings in the service container, enabling dependency injection throughout
 * the application following the Repository pattern.
 *
 * @package App\Providers
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register repository bindings in the service container.
     * 
     * Binds all repository interfaces to their concrete implementations
     * to enable dependency injection and loose coupling.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(CartRepositoryInterface::class, CartRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(CountryRepositoryInterface::class, CountryRepository::class);
        $this->app->bind(CurrencyRepositoryInterface::class, CurrencyRepository::class);
        $this->app->bind(LanguageRepositoryInterface::class,  LanguageRepository::class);
        $this->app->bind(UserSettingsRepositoryInterface::class, UserSettingsRepository::class);
        $this->app->bind(SettingsRepositoryInterface::class, SettingsRepository::class);
        $this->app->bind(OrderStatusRepositoryInterface::class, OrderStatusRepository::class);
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
        $this->app->bind(AddressRepositoryInterface::class, AddressRepository::class);
        $this->app->bind(ShippingMethodRepositoryInterface::class, ShippingMethodRepository::class);
    }

    /**
     * Bootstrap repository services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
