<?php

namespace App\Providers;

use App\Models\Auth\User;
use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Models\Category\Category;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Product\Product;
use App\Observers\Auth\UserObserver;
use App\Observers\Cart\CartObserver;
use App\Observers\Cart\CartItemObserver;
use App\Observers\Category\CategoryObserver;
use App\Observers\Order\OrderObserver;
use App\Observers\Order\OrderItemObserver;
use App\Observers\Product\ProductObserver;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        User::observe(UserObserver::class);
        Cart::observe(CartObserver::class);
        CartItem::observe(CartItemObserver::class);
        Category::observe(CategoryObserver::class);
        Order::observe(OrderObserver::class);
        OrderItem::observe(OrderItemObserver::class);
        Product::observe(ProductObserver::class);
    }
}