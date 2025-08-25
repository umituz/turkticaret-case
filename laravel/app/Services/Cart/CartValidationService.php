<?php

namespace App\Services\Cart;

use App\DTOs\Cart\CartData;

/**
 * Cart Validation Service for business rule validation.
 *
 * Handles cart validation business logic including checkout readiness,
 * stock validation, and item availability checks. Extracted from CartData DTO
 * to follow Single Responsibility Principle.
 *
 * @package App\Services\Cart
 */
class CartValidationService
{
    /**
     * Check if cart is ready for checkout.
     *
     * Validates that the cart has items and no blocking issues like
     * stock problems or unavailable items that would prevent checkout.
     *
     * @param CartData $cart The cart to validate
     * @return bool True if cart can proceed to checkout
     */
    public function isReadyForCheckout(CartData $cart): bool
    {
        return !$this->isEmpty($cart)
            && !$cart->has_stock_issues
            && !$cart->has_unavailable_items;
    }

    /**
     * Check if cart is empty.
     *
     * @param CartData $cart The cart to check
     * @return bool True if cart has no items
     */
    public function isEmpty(CartData $cart): bool
    {
        return empty($cart->items) || $cart->total_items === 0;
    }

    /**
     * Check if cart has stock issues.
     *
     * Analyzes all cart items to determine if any have stock availability issues.
     *
     * @param array $items Array of CartItemData items
     * @return bool True if any items have stock issues
     */
    public function hasStockIssues(array $items): bool
    {
        return collect($items)->some(fn($item) => $item->hasStockIssue());
    }

    /**
     * Check if cart has unavailable items.
     *
     * Analyzes all cart items to determine if any are unavailable.
     *
     * @param array $items Array of CartItemData items
     * @return bool True if any items are unavailable
     */
    public function hasUnavailableItems(array $items): bool
    {
        return collect($items)->some(fn($item) => !$item->isProductAvailable());
    }
}
