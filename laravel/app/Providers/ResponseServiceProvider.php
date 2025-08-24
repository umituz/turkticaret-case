<?php

namespace App\Providers;

use App\Helpers\MacroHelper;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for registering custom response macros.
 * 
 * This provider adds custom response macros to the ResponseFactory
 * to provide consistent API response formatting throughout the application.
 * Includes success and error response macros.
 *
 * @package App\Providers
 */
class ResponseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap response macros.
     * 
     * Registers custom success and error response macros using MacroHelper
     * to provide consistent API response formatting.
     *
     * @param ResponseFactory $factory The response factory instance
     * @return void
     */
    public function boot(ResponseFactory $factory)
    {
        $factory->macro('success', MacroHelper::success($factory));

        $factory->macro('error', MacroHelper::error($factory));
    }
}
