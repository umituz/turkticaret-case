<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Cart\CartAddRequest;
use App\Http\Requests\Cart\CartUpdateRequest;
use App\Http\Resources\Cart\CartResource;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;

class CartController extends BaseController
{
    public function __construct(protected CartService $cartService) {}

    public function index(): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart(auth()->id());
        
        return $this->ok(new CartResource($cart));
    }

    public function add(CartAddRequest $request): JsonResponse
    {
        $cart = $this->cartService->addToCart(
            auth()->id(),
            $request->validated()
        );

        return $this->ok(new CartResource($cart));
    }

    public function update(CartUpdateRequest $request): JsonResponse
    {
        $cart = $this->cartService->updateCartItem(
            auth()->id(),
            $request->validated()
        );

        return $this->ok(new CartResource($cart));
    }

    public function remove(string $productUuid): JsonResponse
    {
        $cart = $this->cartService->removeFromCart(auth()->id(), $productUuid);

        return $this->ok(new CartResource($cart));
    }

    public function clear(): JsonResponse
    {
        $this->cartService->clearCart(auth()->id());

        return $this->noContent();
    }
}