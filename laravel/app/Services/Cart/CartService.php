<?php

namespace App\Services\Cart;

use App\DTOs\Cart\AddToCartDTO;
use App\DTOs\Cart\CartData;
use App\DTOs\Cart\UpdateCartItemDTO;
use App\Exceptions\Product\InsufficientStockException;
use App\Exceptions\Product\OutOfStockException;
use App\Models\Cart\Cart;
use App\Models\Product\Product;
use App\Repositories\Cart\CartRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use DB;
use Exception;

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
     * @param AddToCartDTO $data DTO containing product_uuid and quantity
     * @return CartData The updated cart data
     * @throws InsufficientStockException When requested quantity exceeds available stock
     * @throws OutOfStockException When the product is completely out of stock
     */
    public function addToCart(string $userUuid, AddToCartDTO $data): CartData
    {
        return DB::transaction(function () use ($userUuid, $data) {
            $cart = $this->getOrCreateCart($userUuid);
            $product = $this->productRepository->findByUuid($data->product_uuid);

            $this->validateProductStock($product, $data->quantity);

            // Use repository for atomic upsert operation
            $this->cartRepository->upsertCartItem(
                $cart->uuid,
                $product->uuid,
                $data->quantity,
                $product->price
            );

            // Find the cart item after upsert for final validation
            $cartItem = $this->cartRepository->findCartItem($cart->uuid, $product->uuid);

            if (!$cartItem) {
                throw new Exception("Cart item not found after upsert operation for product: {$product->name}");
            }

            // Final stock validation after upsert
            if (!$product->hasStock($cartItem->quantity)) {
                throw new InsufficientStockException($product->name, $cartItem->quantity, $product->stock_quantity);
            }

            return CartData::fromModel($cart->fresh(['cartItems.product']));
        });
    }

    /**
     * Update quantity of a specific item in the user's cart.
     *
     * @param string $userUuid The UUID of the user updating cart item
     * @param UpdateCartItemDTO $data DTO containing product_uuid and new quantity
     * @return CartData The updated cart data
     * @throws InsufficientStockException When new quantity exceeds available stock
     * @throws OutOfStockException When the product is completely out of stock
     */
    public function updateCartItem(string $userUuid, UpdateCartItemDTO $data): CartData
    {
        return DB::transaction(function () use ($userUuid, $data) {
            $cart = $this->getOrCreateCart($userUuid);
            $cartItem = $this->cartRepository->findCartItem($cart->uuid, $data->product_uuid);

            if ($cartItem) {
                $product = $this->productRepository->findByUuid($data->product_uuid);
                $this->validateProductStock($product, $data->quantity);

                $this->cartRepository->updateCartItemQuantity(
                    $cart->uuid,
                    $data->product_uuid,
                    $data->quantity
                );
            }

            return CartData::fromModel($cart->fresh(['cartItems.product']));
        });
    }

    /**
     * Remove a specific product from the user's cart.
     *
     * @param string $userUuid The UUID of the user removing from cart
     * @param string $productUuid The UUID of the product to remove
     * @return CartData The updated cart data
     */
    public function removeFromCart(string $userUuid, string $productUuid): CartData
    {
        $cart = $this->getOrCreateCart($userUuid);

        $this->cartRepository->removeCartItem($cart->uuid, $productUuid);

        return CartData::fromModel($cart->fresh(['cartItems.product']));
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
        $this->cartRepository->clearCartItems($cart->uuid);
    }

    /**
     * Validate product stock availability.
     *
     * @param Product $product The product to validate
     * @param int $requestedQuantity The requested quantity
     * @throws OutOfStockException When the product is completely out of stock
     * @throws InsufficientStockException When requested quantity exceeds available stock
     * @return void
     */
    private function validateProductStock($product, int $requestedQuantity): void
    {
        if ($product->stock_quantity == 0) {
            throw new OutOfStockException($product->name);
        }

        if (!$product->hasStock($requestedQuantity)) {
            throw new InsufficientStockException($product->name, $requestedQuantity, $product->stock_quantity);
        }
    }
}
