<?php

namespace App\Providers;

use App\Models\Order\Order;
use App\Models\User\UserAddress;
use App\Policies\Order\OrderPolicy;
use App\Policies\User\Address\AddressPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * Service provider for authorization configuration.
 * 
 * Registers model-to-policy mappings for authorization and handles
 * the registration of authentication and authorization services.
 * Maps models to their corresponding policy classes.
 *
 * @package App\Providers
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Order::class => OrderPolicy::class,
        UserAddress::class => AddressPolicy::class,
    ];

    /**
     * Register any application authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
