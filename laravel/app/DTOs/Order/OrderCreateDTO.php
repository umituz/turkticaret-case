<?php

namespace App\DTOs\Order;

readonly class OrderCreateDTO
{
    public function __construct(
        public string $shipping_address,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            shipping_address: $data['shipping_address'],
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'shipping_address' => $this->shipping_address,
            'notes' => $this->notes,
        ];
    }
}