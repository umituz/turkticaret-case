<?php

namespace App\Providers;

use App\Helpers\MacroHelper;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\ServiceProvider;

class ResponseServiceProvider extends ServiceProvider
{
    public function boot(ResponseFactory $factory)
    {
        $factory->macro('success', MacroHelper::success($factory));

        $factory->macro('error', MacroHelper::error($factory));
    }
}
