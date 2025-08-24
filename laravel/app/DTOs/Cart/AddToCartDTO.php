<?php

namespace App\DTOs\Cart;

/**
 * Data Transfer Object for adding items to cart
 * 
 * Handles the transfer and validation of cart item data when adding products
 * to user shopping carts. This DTO ensures type safety and provides a clean
 * interface for cart operations.
 * 
 * @property string $product_uuid UUID of the product to add to cart (required)
 * @property int $quantity Quantity of the product to add (required, positive integer)
 * 
 * @package App\DTOs\Cart
 */
class AddToCartDTO
{
    /**
     * Create a new AddToCartDTO instance
     * 
     * @param string $product_uuid UUID of the product to add to cart
     * @param int $quantity Quantity of the product (must be positive)
     */
    public function __construct(
        public string $product_uuid,
        public int $quantity,
    ) {}

    /**
     * Create DTO from validated request data
     * 
     * Factory method to create an AddToCartDTO instance from validated request data.
     * Handles the transformation of array data into a typed DTO object.
     * 
     * @param array $data Validated request data containing product_uuid and quantity
     * @return self New AddToCartDTO instance
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