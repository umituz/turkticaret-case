<?php

namespace App\DTOs\Setting;

/**
 * Data Transfer Object for application setting updates.
 * 
 * This DTO encapsulates setting key-value pairs for updating application
 * configuration settings. It supports various value types and provides
 * multiple creation methods for different data sources.
 *
 * @package App\DTOs\Setting
 */
readonly class SettingUpdateDTO
{
    /**
     * Create a new setting update DTO instance.
     *
     * @param string $key The setting key identifier
     * @param mixed $value The new value for the setting (supports various types)
     */
    public function __construct(
        public string $key,
        public mixed $value,
    ) {}

    /**
     * Create DTO instance from array data.
     *
     * @param array $data Array containing key and value fields
     * @return self New SettingUpdateDTO instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            key: $data['key'],
            value: $data['value'],
        );
    }

    /**
     * Create DTO instance from individual parameters.
     *
     * @param string $key The setting key identifier
     * @param mixed $value The new value for the setting
     * @return self New SettingUpdateDTO instance
     */
    public static function fromRequest(string $key, mixed $value): self
    {
        return new self(
            key: $key,
            value: $value,
        );
    }
}