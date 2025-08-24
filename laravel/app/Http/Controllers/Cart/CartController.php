<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Cart\CartAddRequest;
use App\Http\Requests\Cart\CartUpdateRequest;
use App\Http\Resources\Cart\CartResource;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;

/**
 * REST API Controller for Shopping Cart management.
 * 
 * Handles shopping cart operations including adding/removing products,
 * updating quantities, viewing cart contents, and clearing the entire cart.
 * All cart operations are scoped to the authenticated user.
 *
 * @package App\Http\Controllers\Cart
 */
class CartController extends BaseController
{
    /**
     * Create a new CartController instance.
     *
     * @param CartService $cartService The cart service for business logic operations
     */
    public function __construct(protected CartService $cartService) {}

    /**
     * Display the authenticated user's shopping cart.
     *
     * @return JsonResponse JSON response containing the user's cart resource
     */
    public function index(): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart(auth()->id());
        $cartData = \App\DTOs\Cart\CartData::fromModel($cart);
        
        return $this->ok($cartData->toArray());
    }

    /**
     * Add a product to the authenticated user's cart.
     *
     * @param CartAddRequest $request The validated request containing product UUID and quantity
     * @return JsonResponse JSON response containing the updated cart resource
     */
    public function add(CartAddRequest $request): JsonResponse
    {
        $addToCartData = \App\DTOs\Cart\AddToCartDTO::fromArray($request->validated());
        
        $cart = $this->cartService->addToCart(
            auth()->id(),
            $addToCartData
        );

        return $this->ok($cart->toArray());
    }

    /**
     * Update the quantity of a product in the cart.
     *
     * @param CartUpdateRequest $request The validated request containing product UUID and new quantity
     * @return JsonResponse JSON response containing the updated cart resource
     */
    public function update(CartUpdateRequest $request): JsonResponse
    {
        $updateCartItemData = \App\DTOs\Cart\UpdateCartItemDTO::fromArray($request->validated());
        
        $cart = $this->cartService->updateCartItem(
            auth()->id(),
            $updateCartItemData
        );

        return $this->ok($cart->toArray());
    }

    /**
     * Remove a specific product from the cart.
     *
     * @param string $productUuid The UUID of the product to remove from cart
     * @return JsonResponse JSON response containing the updated cart resource
     */
    public function remove(string $productUuid): JsonResponse
    {
        $cart = $this->cartService->removeFromCart(auth()->id(), $productUuid);

        return $this->ok(new CartResource($cart));
    }

    /**
     * Clear all items from the authenticated user's cart.
     *
     * @return JsonResponse JSON response with 204 No Content status
     */
    public function clear(): JsonResponse
    {
        $this->cartService->clearCart(auth()->id());

        return $this->noContent();
    }
}