<?php

namespace App\Providers;

use App\Models\Category\Category;
use App\Models\Order\Order;
use App\Models\Product\Product;
use App\Observers\Category\CategoryObserver;
use App\Observers\Order\OrderObserver;
use App\Observers\Product\ProductObserver;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Category::observe(CategoryObserver::class);
        Order::observe(OrderObserver::class);
        Product::observe(ProductObserver::class);
    }
}