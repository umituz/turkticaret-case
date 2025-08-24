<?php

namespace App\Enums\Country;

/**
 * Country enumeration for geographic and localization support
 * 
 * Defines supported countries in the application with their ISO codes,
 * display names, currencies, and locale information. Used for country
 * selection, currency handling, and regional settings.
 * 
 * @package App\Enums\Country
 */
enum CountryEnum: string
{
    case TURKEY = 'TR';
    case UNITED_STATES = 'US';

    /**
     * Get display name for the country
     * 
     * @return string Country name in English
     */
    public function getDisplayName(): string
    {
        return match($this) {
            self::TURKEY => 'Turkey',
            self::UNITED_STATES => 'United States',
        };
    }

    /**
     * Get currency code for the country
     * 
     * @return string ISO currency code (e.g., 'TRY', 'USD')
     */
    public function getCurrencyCode(): string
    {
        return match($this) {
            self::TURKEY => 'TRY',
            self::UNITED_STATES => 'USD',
        };
    }

    /**
     * Get locale code for the country
     * 
     * @return string Locale code (e.g., 'tr_TR', 'en_US')
     */
    public function getLocale(): string
    {
        return match($this) {
            self::TURKEY => 'tr_TR',
            self::UNITED_STATES => 'en_US',
        };
    }

    /**
     * Get all country values as array
     * 
     * @return array Array of all country ISO codes
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all country display names as array
     * 
     * @return array Array of all country display names
     */
    public static function names(): array
    {
        return array_map(fn (self $case) => $case->getDisplayName(), self::cases());
    }
}
