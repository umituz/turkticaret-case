<?php

namespace App\DTOs\Cart;

use App\Models\Cart\CartItem;
use App\Models\Product\Product;

/**
 * Data Transfer Object for cart item representation
 * 
 * Provides consistent structure for cart item data across the application,
 * including product details, pricing, and availability information for
 * individual items within a shopping cart.
 * 
 * @property string $uuid Unique identifier for the cart item
 * @property string $product_uuid UUID of the associated product
 * @property string $product_name Name of the product
 * @property string $product_sku Stock Keeping Unit for the product
 * @property string|null $product_image_path Path to product image (optional)
 * @property int $quantity Quantity of this item in cart
 * @property int $unit_price Unit price in cents
 * @property int $total_price Total price for this quantity in cents
 * @property int $available_stock Available stock quantity for the product
 * @property bool $is_available Whether the product is available for purchase
 * 
 * @package App\DTOs\Cart
 */
class CartItemData
{
    /**
     * Create a new CartItemData instance
     * 
     * @param string $uuid Unique identifier for the cart item
     * @param string $product_uuid UUID of the associated product
     * @param string $product_name Name of the product
     * @param string $product_sku Stock Keeping Unit for the product
     * @param string|null $product_image_path Path to product image
     * @param int $quantity Quantity of this item in cart
     * @param int $unit_price Unit price in cents
     * @param int $total_price Total price for this quantity in cents
     * @param int $available_stock Available stock quantity for the product
     * @param bool $is_available Whether the product is available for purchase
     */
    public function __construct(
        public string $uuid,
        public string $product_uuid,
        public string $product_name,
        public string $product_sku,
        public ?string $product_image_path,
        public int $quantity,
        public int $unit_price,
        public int $total_price,
        public int $available_stock,
        public bool $is_available,
    ) {}

    /**
     * Create DTO from CartItem model
     */
    public static function fromModel(CartItem $cartItem): self
    {
        $product = $cartItem->product;
        
        return new self(
            uuid: $cartItem->uuid,
            product_uuid: $cartItem->product_uuid,
            product_name: $product->name,
            product_sku: $product->sku,
            product_image_path: $product->image_path,
            quantity: $cartItem->quantity,
            unit_price: $cartItem->unit_price,
            total_price: $cartItem->quantity * $cartItem->unit_price,
            available_stock: $product->stock_quantity,
            is_available: $product->is_active && $product->stock_quantity > 0,
        );
    }

    /**
     * Create DTO collection from CartItem models
     * 
     * @param \Illuminate\Database\Eloquent\Collection<CartItem> $cartItems
     * @return array<self>
     */
    public static function fromCollection($cartItems): array
    {
        return $cartItems->map(fn (CartItem $item) => self::fromModel($item))->toArray();
    }

    /**
     * Convert DTO to array for API responses
     */
    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'product' => [
                'uuid' => $this->product_uuid,
                'name' => $this->product_name,
                'sku' => $this->product_sku,
                'image_path' => $this->product_image_path,
                'available_stock' => $this->available_stock,
                'is_available' => $this->is_available,
            ],
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
        ];
    }

    /**
     * Get formatted price (in actual currency, divided by 100)
     */
    public function getFormattedUnitPrice(): float
    {
        return $this->unit_price / 100;
    }

    /**
     * Get formatted total price (in actual currency, divided by 100)
     */
    public function getFormattedTotalPrice(): float
    {
        return $this->total_price / 100;
    }

    /**
     * Check if item has stock issues
     */
    public function hasStockIssue(): bool
    {
        return $this->quantity > $this->available_stock;
    }

    /**
     * Check if product is still available
     */
    public function isProductAvailable(): bool
    {
        return $this->is_available;
    }
}