<?php

namespace App\DTOs\User\Address;

class AddressDTO
{
    public function __construct(
        public ?string $type = null,
        public ?bool $is_default = null,
        public ?string $first_name = null,
        public ?string $last_name = null,
        public ?string $company = null,
        public ?string $address_line_1 = null,
        public ?string $address_line_2 = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $postal_code = null,
        public ?string $country_uuid = null,
        public ?string $phone = null,
    ) {}

    public static function forCreate(array $data): self
    {
        return new self(
            type: $data['type'] ?? 'shipping',
            is_default: $data['is_default'] ?? false,
            first_name: $data['first_name'] ?? null,
            last_name: $data['last_name'] ?? null,
            company: $data['company'] ?? null,
            address_line_1: $data['address_line_1'] ?? null,
            address_line_2: $data['address_line_2'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            postal_code: $data['postal_code'] ?? null,
            country_uuid: $data['country_uuid'] ?? null,
            phone: $data['phone'] ?? null,
        );
    }

    public static function forUpdate(array $data): self
    {
        return new self(
            type: $data['type'] ?? null,
            is_default: $data['is_default'] ?? null,
            first_name: $data['first_name'] ?? null,
            last_name: $data['last_name'] ?? null,
            company: $data['company'] ?? null,
            address_line_1: $data['address_line_1'] ?? null,
            address_line_2: $data['address_line_2'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            postal_code: $data['postal_code'] ?? null,
            country_uuid: $data['country_uuid'] ?? null,
            phone: $data['phone'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'is_default' => $this->is_default,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'company' => $this->company,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country_uuid' => $this->country_uuid,
            'phone' => $this->phone,
        ];
    }

    public function toFilteredArray(): array
    {
        return array_filter($this->toArray(), fn($value) => $value !== null);
    }
}