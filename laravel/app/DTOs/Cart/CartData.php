<?php

namespace App\DTOs\Cart;

use App\Models\Cart\Cart;

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
    ) {}

    /**
     * Create DTO from Cart model
     * 
     * Factory method to create a CartData instance from a Cart model with all
     * its related cart items. Calculates totals, quantities, and validates
     * stock availability and item status.
     * 
     * @param Cart $cart Cart model with loaded cartItems relationship
     * @return self New CartData instance
     */
    public static function fromModel(Cart $cart): self
    {
        $items = CartItemData::fromCollection($cart->cartItems);
        $totalItems = array_sum(array_map(fn($item) => $item->quantity, $items));
        $subtotal = array_sum(array_map(fn($item) => $item->total_price, $items));
        
        // Check for stock and availability issues
        $hasStockIssues = collect($items)->some(fn($item) => $item->hasStockIssue());
        $hasUnavailableItems = collect($items)->some(fn($item) => !$item->isProductAvailable());
        
        return new self(
            uuid: $cart->uuid,
            user_uuid: $cart->user_uuid,
            items: $items,
            total_items: $totalItems,
            subtotal: $subtotal,
            total_amount: $subtotal, // Can add taxes, shipping, discounts here later
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

    /**
     * Get formatted subtotal (in actual currency, divided by 100)
     * 
     * Converts the subtotal from cents to actual currency value
     * for display purposes.
     * 
     * @return float Subtotal amount in actual currency
     */
    public function getFormattedSubtotal(): float
    {
        return $this->subtotal / 100;
    }

    /**
     * Get formatted total amount (in actual currency, divided by 100)
     * 
     * Converts the total amount from cents to actual currency value
     * for display purposes.
     * 
     * @return float Total amount in actual currency
     */
    public function getFormattedTotalAmount(): float
    {
        return $this->total_amount / 100;
    }

    /**
     * Check if cart is empty
     * 
     * Determines whether the cart contains any items.
     * 
     * @return bool True if cart has no items, false otherwise
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Check if cart is ready for checkout
     * 
     * Validates that the cart is not empty and has no stock issues
     * or unavailable items that would prevent checkout.
     * 
     * @return bool True if cart can proceed to checkout
     */
    public function isReadyForCheckout(): bool
    {
        return !$this->isEmpty() 
            && !$this->has_stock_issues 
            && !$this->has_unavailable_items;
    }

    /**
     * Get items with stock issues
     * 
     * @return CartItemData[]
     */
    public function getItemsWithStockIssues(): array
    {
        return array_filter($this->items, fn($item) => $item->hasStockIssue());
    }

    /**
     * Get unavailable items
     * 
     * @return CartItemData[]
     */
    public function getUnavailableItems(): array
    {
        return array_filter($this->items, fn($item) => !$item->isProductAvailable());
    }

    /**
     * Get total unique products count
     * 
     * Returns the number of unique products in the cart,
     * regardless of their quantities.
     * 
     * @return int Number of unique products
     */
    public function getTotalUniqueProducts(): int
    {
        return count($this->items);
    }
}