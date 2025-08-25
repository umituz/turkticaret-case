<?php

namespace App\DTOs\Cart;

use App\Models\Cart\Cart;
use App\Services\Cart\CartValidationService;

/**
 * Data Transfer Object for complete cart representation
 *
 * Provides consistent structure for cart data across the application,
 * including all cart items and calculated totals. This DTO encapsulates
 * the complete shopping cart state with item details and business logic
 * for checkout validation.
 *
 * @property string $uuid Unique identifier for the cart
 * @property string $user_uuid UUID of the cart owner
 * @property CartItemData[] $items Array of cart item data objects
 * @property int $total_items Total quantity of all items in cart
 * @property int $subtotal Subtotal in cents (before taxes/shipping)
 * @property int $total_amount Final total in cents
 * @property bool $has_stock_issues Whether any items have stock issues
 * @property bool $has_unavailable_items Whether any items are unavailable
 *
 * @package App\DTOs\Cart
 */
class CartData
{
    /**
     * Create a new CartData instance
     *
     * @param string $uuid Unique identifier for the cart
     * @param string $user_uuid UUID of the cart owner
     * @param CartItemData[] $items Array of cart item data objects
     * @param int $total_items Total quantity of all items in cart
     * @param int $subtotal Subtotal amount in cents
     * @param int $total_amount Final total amount in cents
     * @param bool $has_stock_issues Whether any items have stock issues
     * @param bool $has_unavailable_items Whether any items are unavailable
     */
    public function __construct(
        public string $uuid,
        public string $user_uuid,
        public array $items,
        public int $total_items,
        public int $subtotal,
        public int $total_amount,
        public bool $has_stock_issues,
        public bool $has_unavailable_items,
    ) {
        // Alias for compatibility
        $this->cartItems = $this->items;
    }

    public array $cartItems;

    /**
     * Create DTO from Cart model
     *
     * Factory method to create a CartData instance from a Cart model with all
     * its related cart items. Uses dedicated services for calculations and validations.
     *
     * @param Cart $cart Cart model with loaded cartItems relationship
     * @return self New CartData instance
     */
    public static function fromModel(Cart $cart): self
    {
        // Ensure cartItems relationship is loaded
        if (!$cart->relationLoaded('cartItems')) {
            $cart->load('cartItems.product');
        }

        $items = CartItemData::fromCollection($cart->cartItems);

        $validationService = app(CartValidationService::class);
        $totalItems = collect($items)->sum(fn($item) => $item->quantity);
        $subtotal = collect($items)->sum(fn($item) => $item->total_price);
        $hasStockIssues = $validationService->hasStockIssues($items);
        $hasUnavailableItems = $validationService->hasUnavailableItems($items);

        return new self(
            uuid: $cart->uuid,
            user_uuid: $cart->user_uuid,
            items: $items,
            total_items: $totalItems,
            subtotal: $subtotal,
            total_amount: $subtotal,
            has_stock_issues: $hasStockIssues,
            has_unavailable_items: $hasUnavailableItems,
        );
    }

    /**
     * Convert DTO to array for API responses
     *
     * Transforms the CartData instance into an array format suitable for
     * API responses and frontend consumption, including all cart details
     * and summary information.
     *
     * @return array Array representation with cart data and summary
     */
    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'user_uuid' => $this->user_uuid,
            'items' => array_map(fn($item) => $item->toArray(), $this->items),
            'summary' => [
                'total_items' => $this->total_items,
                'subtotal' => $this->subtotal,
                'total_amount' => $this->total_amount,
                'has_stock_issues' => $this->has_stock_issues,
                'has_unavailable_items' => $this->has_unavailable_items,
            ],
        ];
    }

}
