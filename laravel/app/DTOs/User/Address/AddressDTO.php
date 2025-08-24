<?php

namespace App\DTOs\User\Address;

/**
 * Data Transfer Object for user address management
 * 
 * Handles address data for both shipping and billing purposes. This DTO supports
 * partial updates by allowing null values, making it suitable for both create
 * and update operations. All fields are optional to support flexible address handling.
 * 
 * @property string|null $type Address type ('shipping' or 'billing')
 * @property bool|null $is_default Whether this is the default address
 * @property string|null $first_name Recipient's first name
 * @property string|null $last_name Recipient's last name
 * @property string|null $company Company name (optional)
 * @property string|null $address_line_1 Primary address line
 * @property string|null $address_line_2 Secondary address line (apt, suite, etc.)
 * @property string|null $city City name
 * @property string|null $state State or province
 * @property string|null $postal_code Postal or ZIP code
 * @property string|null $country_uuid UUID reference to country entity
 * @property string|null $phone Contact phone number
 */
class AddressDTO
{
    /**
     * Create a new AddressDTO instance
     * 
     * All parameters are nullable to support partial updates and flexible
     * address creation scenarios. Use factory methods for specific contexts.
     * 
     * @param string|null $type Address type (defaults to 'shipping' for new addresses)
     * @param bool|null $is_default Default address flag
     * @param string|null $first_name Recipient's first name
     * @param string|null $last_name Recipient's last name
     * @param string|null $company Optional company name
     * @param string|null $address_line_1 Primary street address
     * @param string|null $address_line_2 Additional address information
     * @param string|null $city City name
     * @param string|null $state State or province code
     * @param string|null $postal_code Postal code
     * @param string|null $country_uuid Country reference UUID
     * @param string|null $phone Contact phone number
     */
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

    /**
     * Create AddressDTO for new address creation
     * 
     * Factory method that provides sensible defaults for creating new addresses.
     * Sets type to 'shipping' and is_default to false if not specified.
     * 
     * @param array $data Address data from request
     * @return self New AddressDTO instance with create defaults
     */
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

    /**
     * Create AddressDTO for address updates
     * 
     * Factory method for partial updates. All fields default to null,
     * allowing selective updates of only the fields provided in the request.
     * 
     * @param array $data Partial address data for update
     * @return self New AddressDTO instance for updates
     */
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

    /**
     * Convert DTO to array including all fields
     * 
     * Returns all address fields including null values. Useful for
     * complete address representations and debugging.
     * 
     * @return array Complete array representation with all fields
     */
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

    /**
     * Convert DTO to filtered array excluding null values
     * 
     * Returns only non-null fields, making it ideal for partial updates
     * where only provided fields should be modified in the database.
     * 
     * @return array Filtered array with only non-null values
     */
    public function toFilteredArray(): array
    {
        return array_filter($this->toArray(), fn($value) => $value !== null);
    }
}