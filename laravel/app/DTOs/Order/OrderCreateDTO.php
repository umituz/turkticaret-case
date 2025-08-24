<?php

namespace App\DTOs\Order;

/**
 * Data Transfer Object for order creation operations.
 * 
 * Encapsulates validated order data including shipping information
 * and optional notes for creating new orders from cart contents.
 * Provides type-safe data transfer between layers.
 *
 * @property string $shipping_address The shipping address for the order
 * @property string|null $notes Optional notes for the order
 * 
 * @package App\DTOs\Order
 */
readonly class OrderCreateDTO
{
    public function __construct(
        public string $shipping_address,
        public ?string $notes = null,
    ) {}

    /**
     * Create OrderCreateDTO instance from array data.
     *
     * @param array $data Array containing shipping_address and optional notes
     * @return self New OrderCreateDTO instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            shipping_address: $data['shipping_address'],
            notes: $data['notes'] ?? null,
        );
    }

    /**
     * Convert DTO to array representation.
     *
     * @return array Array containing order creation data
     */
    public function toArray(): array
    {
        return [
            'shipping_address' => $this->shipping_address,
            'notes' => $this->notes,
        ];
    }
}