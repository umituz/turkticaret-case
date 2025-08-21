<?php

namespace App\Services\Cart;

use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Models\Product\Product;
use App\Repositories\Cart\CartRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;

class CartService
{
    public function __construct(
        protected CartRepositoryInterface $cartRepository,
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function getOrCreateCart(string $userUuid): Cart
    {
        $cart = $this->cartRepository->findByUserUuid($userUuid);

        if (!$cart) {
            $cart = $this->cartRepository->create(['user_uuid' => $userUuid]);
        }

        return $cart->load(['cartItems.product', 'cartItems' => function($query) {
            $query->with('product');
        }]);
    }

    public function addToCart(string $userUuid, array $data): Cart
    {
        $cart = $this->getOrCreateCart($userUuid);
        $product = $this->productRepository->findByUuid($data['product_uuid']);

        $existingItem = $cart->cartItems()->where('product_uuid', $product->uuid)->first();

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $existingItem->quantity + $data['quantity'],
            ]);
        } else {
            $cart->cartItems()->create([
                'product_uuid' => $product->uuid,
                'quantity' => $data['quantity'],
                'unit_price' => $product->price,
            ]);
        }

        return $cart->fresh(['cartItems.product']);
    }

    public function updateCartItem(string $userUuid, array $data): Cart
    {
        $cart = $this->getOrCreateCart($userUuid);
        
        $cartItem = $cart->cartItems()->where('product_uuid', $data['product_uuid'])->first();

        if ($cartItem) {
            $cartItem->update([
                'quantity' => $data['quantity'],
            ]);
        }

        return $cart->fresh(['cartItems.product']);
    }

    public function removeFromCart(string $userUuid, string $productUuid): Cart
    {
        $cart = $this->getOrCreateCart($userUuid);
        
        $cart->cartItems()->where('product_uuid', $productUuid)->delete();

        return $cart->fresh(['cartItems.product']);
    }

    public function clearCart(string $userUuid): void
    {
        $cart = $this->getOrCreateCart($userUuid);
        $cart->cartItems()->delete();
    }
}