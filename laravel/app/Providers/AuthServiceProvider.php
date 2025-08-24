<?php

namespace App\Providers;

use App\Models\Order\Order;
use App\Models\User\UserAddress;
use App\Policies\Order\OrderPolicy;
use App\Policies\User\Address\AddressPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
