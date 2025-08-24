<?php

namespace App\Services\Cart;

use App\DTOs\Cart\CartData;
use App\DTOs\Cart\UpdateCartItemDTO;
use App\Exceptions\Product\InsufficientStockException;
use App\Exceptions\Product\OutOfStockException;
use App\Models\Cart\Cart;
use App\Repositories\Cart\CartRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;

/**
 * Shopping Cart Service for cart management operations.
 *
 * Handles comprehensive cart operations including creating carts,
 * adding/updating/removing items, stock validation, and cart clearing.
 * Implements business rules for inventory management and cart persistence.
 *
 * @package App\Services\Cart
 */
class CartService
{
    /**
     * Create a new CartService instance.
     *
     * @param CartRepositoryInterface $cartRepository The cart repository for data operations
     * @param ProductRepositoryInterface $productRepository The product repository for product data
     */
    public function __construct(
        protected CartRepositoryInterface $cartRepository,
        protected ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Get existing cart or create a new one for the user.
     *
     * @param string $userUuid The UUID of the user to get or create cart for
     * @return Cart The user's cart with loaded relationships
     */
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
     * Add a product to the user's cart with stock validation.
     *
     * @param string $userUuid The UUID of the user adding to cart
     * @param array $data Array containing product_uuid and quantity
     * @return Cart The updated cart with loaded relationships
     * @throws InsufficientStockException When requested quantity exceeds available stock
     * @throws OutOfStockException When the product is completely out of stock
     */
    public function addToCart(string $userUuid, \App\DTOs\Cart\AddToCartDTO $data): \App\DTOs\Cart\CartData
    {
        return \DB::transaction(function () use ($userUuid, $data) {
            $cart = $this->getOrCreateCart($userUuid);
            $product = $this->productRepository->findByUuid($data->product_uuid);
            $requestedQuantity = $data->quantity;

            if ($product->stock_quantity == 0) {
                throw new OutOfStockException($product->name);
            }

            // Use PostgreSQL's ON CONFLICT to handle race conditions atomically
            $cartItemUuid = \Str::uuid();
            $now = now();
            
            \DB::statement("
                INSERT INTO cart_items (uuid, cart_uuid, product_uuid, quantity, unit_price, deleted_at, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NULL, ?, ?)
                ON CONFLICT (cart_uuid, product_uuid)
                DO UPDATE SET 
                    quantity = cart_items.quantity + EXCLUDED.quantity,
                    updated_at = EXCLUDED.updated_at,
                    deleted_at = NULL
            ", [
                $cartItemUuid,
                $cart->uuid,
                $product->uuid,
                $requestedQuantity,
                $product->price,
                $now,
                $now
            ]);

            // Find the cart item after upsert
            $cartItem = $cart->cartItems()
                ->where('product_uuid', $product->uuid)
                ->first();

            // Check if cart item was found
            if (!$cartItem) {
                throw new \Exception("Cart item not found after upsert operation for product: {$product->name}");
            }

            // Validate stock after update
            if (!$product->hasStock($cartItem->quantity)) {
                throw new InsufficientStockException($product->name, $cartItem->quantity, $product->stock_quantity);
            }

            return \App\DTOs\Cart\CartData::fromModel($cart->fresh(['cartItems.product']));
        });
    }

    /**
     * Update quantity of a specific item in the user's cart.
     *
     * @param string $userUuid The UUID of the user updating cart item
     * @param UpdateCartItemDTO $data Array containing product_uuid and new quantity
     * @return CartData The updated cart with loaded relationships
     * @throws InsufficientStockException When new quantity exceeds available stock
     * @throws OutOfStockException When the product is completely out of stock
     */
    public function updateCartItem(string $userUuid, \App\DTOs\Cart\UpdateCartItemDTO $data): \App\DTOs\Cart\CartData
    {
        $cart = $this->getOrCreateCart($userUuid);

        $cartItem = $cart->cartItems()->where('product_uuid', $data->product_uuid)->first();

        if ($cartItem) {
            $product = $this->productRepository->findByUuid($data->product_uuid);

            if ($product->stock_quantity == 0) {
                throw new OutOfStockException($product->name);
            } elseif (!$product->hasStock($data->quantity)) {
                throw new InsufficientStockException($product->name, $data->quantity, $product->stock_quantity);
            }

            $cartItem->update([
                'quantity' => $data->quantity,
            ]);
        }

        return \App\DTOs\Cart\CartData::fromModel($cart->fresh(['cartItems.product']));
    }

    /**
     * Remove a specific product from the user's cart.
     *
     * @param string $userUuid The UUID of the user removing from cart
     * @param string $productUuid The UUID of the product to remove
     * @return Cart The updated cart with loaded relationships
     */
    public function removeFromCart(string $userUuid, string $productUuid): Cart
    {
        $cart = $this->getOrCreateCart($userUuid);

        $cart->cartItems()->where('product_uuid', $productUuid)->delete();

        return $cart->fresh(['cartItems.product']);
    }

    /**
     * Clear all items from the user's cart.
     *
     * @param string $userUuid The UUID of the user whose cart to clear
     * @return void
     */
    public function clearCart(string $userUuid): void
    {
        $cart = $this->getOrCreateCart($userUuid);
        $cart->cartItems()->delete();
    }
}
