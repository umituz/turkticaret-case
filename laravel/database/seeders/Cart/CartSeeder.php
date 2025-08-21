<?php

namespace Database\Seeders\Cart;

use App\Models\Auth\User;
use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Models\Product\Product;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        $users->each(function ($user) {
            $cart = Cart::create(['user_uuid' => $user->uuid]);

            $products = Product::take(3)->get();
            $products->each(function ($product) use ($cart) {
                CartItem::create([
                    'cart_uuid' => $cart->uuid,
                    'product_uuid' => $product->uuid,
                    'quantity' => rand(1, 3),
                    'unit_price' => $product->price,
                ]);
            });
        });
    }
}
