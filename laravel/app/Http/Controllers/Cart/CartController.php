<?php

namespace App\Http\Controllers\Cart;

use App\DTOs\Cart\CartItemDTO;
use App\Exceptions\Product\InsufficientStockException;
use App\Exceptions\Product\OutOfStockException;
use App\Helpers\AuthHelper;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Cart\CartAddRequest;
use App\Http\Requests\Cart\CartRemoveRequest;
use App\Http\Requests\Cart\CartUpdateRequest;
use App\Http\Resources\Cart\CartResource;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;

/**
 * REST API Controller for Shopping Cart management.
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
        $cart = $this->cartService->getOrCreateCart(AuthHelper::getUserUuid());

        return $this->ok(new CartResource($cart));
    }

    /**
     * Add a product to the authenticated user's cart.
     *
     * @param CartAddRequest $request The validated request containing product UUID and quantity
     * @return JsonResponse JSON response containing the updated cart resource
     * @throws InsufficientStockException
     * @throws OutOfStockException
     */
    public function add(CartAddRequest $request): JsonResponse
    {
        $cartItemData = CartItemDTO::fromArray($request->validated());
        $cart = $this->cartService->addToCart(AuthHelper::getUserUuid(), $cartItemData);

        return $this->ok(new CartResource($cart));
    }

    /**
     * Update the quantity of a product in the cart.
     *
     * @param CartUpdateRequest $request The validated request containing product UUID and new quantity
     * @return JsonResponse JSON response containing the updated cart resource
     * @throws InsufficientStockException
     * @throws OutOfStockException
     */
    public function update(CartUpdateRequest $request): JsonResponse
    {
        $cartItemData = CartItemDTO::fromArray($request->validated());
        $cart = $this->cartService->updateCartItem(AuthHelper::getUserUuid(), $cartItemData);

        return $this->ok(new CartResource($cart));
    }

    /**
     * Remove a specific product from the cart.
     *
     * @param CartRemoveRequest $request The validated request containing product UUID
     * @return JsonResponse JSON response containing the updated cart resource
     */
    public function remove(CartRemoveRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $cart = $this->cartService->removeFromCart(AuthHelper::getUserUuid(), $validated['product_uuid']);

        return $this->ok(new CartResource($cart));
    }

    /**
     * Clear all items from the authenticated user's cart.
     *
     * @return JsonResponse JSON response with 204 No Content status
     */
    public function clear(): JsonResponse
    {
        $this->cartService->clearCart(AuthHelper::getUserUuid());

        return $this->noContent();
    }
}
