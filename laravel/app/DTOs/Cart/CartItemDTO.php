<?php

namespace App\DTOs\Cart;

/**
 * Data Transfer Object for cart item operations
 * 
 * Unified DTO for both adding items to cart and updating cart item quantities.
 * This DTO ensures type safety and provides a clean interface for all cart
 * item operations including adding new products and updating existing quantities.
 * 
 * @property string $product_uuid UUID of the product (required)
 * @property int $quantity Quantity of the product (required, positive integer)
 * 
 * @package App\DTOs\Cart
 */
class CartItemDTO
{
    /**
     * Create a new CartItemDTO instance
     * 
     * @param string $product_uuid UUID of the product
     * @param int $quantity Quantity of the product (must be positive)
     */
    public function __construct(
        public string $product_uuid,
        public int $quantity,
    ) {}

    /**
     * Create DTO from validated request data
     * 
     * Factory method to create a CartItemDTO instance from validated request data.
     * Handles the transformation of array data into a typed DTO object.
     * 
     * @param array $data Validated request data containing product_uuid and quantity
     * @return self New CartItemDTO instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            product_uuid: $data['product_uuid'],
            quantity: $data['quantity'],
        );
    }

    /**
     * Convert DTO to array for database operations
     * 
     * Transforms the DTO properties to an array format suitable for
     * database operations and API responses.
     * 
     * @return array Array representation with cart item data
     */
    public function toArray(): array
    {
        return [
            'product_uuid' => $this->product_uuid,
            'quantity' => $this->quantity,
        ];
    }
}