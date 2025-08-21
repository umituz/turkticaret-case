<?php

namespace App\Providers;

use App\Models\Category\Category;
use App\Observers\Category\CategoryObserver;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Category::observe(CategoryObserver::class);
    }
}