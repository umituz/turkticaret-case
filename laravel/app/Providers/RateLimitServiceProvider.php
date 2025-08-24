<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for configuring rate limiting.
 * 
 * Sets up API rate limiting configuration with different limits
 * for authenticated and anonymous users. Disables rate limiting
 * in testing environment for seamless test execution.
 *
 * @package App\Providers
 */
class RateLimitServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            if (app()->environment('testing')) {
                return Limit::none();
            }

            $key = $request->user()?->id ?: $request->ip();
            return Limit::perMinute(60)->by('api:' . $key);
        });
    }

}
