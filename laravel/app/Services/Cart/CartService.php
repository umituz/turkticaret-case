<?php

namespace App\Services\Cart;

use App\Exceptions\Product\InsufficientStockException;
use App\Models\Cart\Cart;
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

    /**
     * @throws InsufficientStockException
     */
    public function addToCart(string $userUuid, array $data): Cart
    {
        $cart = $this->getOrCreateCart($userUuid);
        $product = $this->productRepository->findByUuid($data['product_uuid']);

        $existingItem = $cart->cartItems()->where('product_uuid', $product->uuid)->first();
        $requestedQuantity = $data['quantity'];

        if ($existingItem) {
            $totalQuantity = $existingItem->quantity + $requestedQuantity;

            if (!$product->hasStock($totalQuantity)) {
                throw new InsufficientStockException($product->name, $totalQuantity, $product->stock_quantity);
            }

            $existingItem->update([
                'quantity' => $totalQuantity,
            ]);
        } else {
            if (!$product->hasStock($requestedQuantity)) {
                throw new InsufficientStockException($product->name, $requestedQuantity, $product->stock_quantity);
            }

            $cart->cartItems()->create([
                'product_uuid' => $product->uuid,
                'quantity' => $requestedQuantity,
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
            $product = $this->productRepository->findByUuid($data['product_uuid']);

            if (!$product->hasStock($data['quantity'])) {
                throw new InsufficientStockException($product->name, $data['quantity'], $product->stock_quantity);
            }

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
