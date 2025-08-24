<?php

namespace App\Enums\Currency;

/**
 * Currency enumeration for multi-currency support
 * 
 * Defines the supported currencies in the e-commerce system along with
 * their display properties and formatting rules. This enum centralizes
 * currency-related logic and ensures consistent currency handling throughout
 * the application.
 * 
 * @method static self TRY() Turkish Lira currency
 * @method static self USD() US Dollar currency
 */
enum CurrencyEnum: string
{
    /**
     * Turkish Lira
     * The default currency for the Turkish market
     */
    case TRY = 'TRY';
    
    /**
     * US Dollar
     * International currency for global transactions
     */
    case USD = 'USD';

    /**
     * Get the full display name of the currency
     * 
     * Returns human-readable currency names for UI display purposes.
     * 
     * @return string Full currency name
     */
    public function getDisplayName(): string
    {
        return match($this) {
            self::TRY => 'Turkish Lira',
            self::USD => 'US Dollar',
        };
    }

    /**
     * Get the currency symbol
     * 
     * Returns the appropriate currency symbol for price display.
     * These symbols are used in formatted price strings.
     * 
     * @return string Currency symbol (₺ for TRY, $ for USD)
     */
    public function getSymbol(): string
    {
        return match($this) {
            self::TRY => '₺',
            self::USD => '$',
        };
    }

    /**
     * Get the number of decimal places for the currency
     * 
     * Defines the precision for monetary calculations and display.
     * Most currencies use 2 decimal places, but this can be extended
     * for currencies that require different precision.
     * 
     * @return int Number of decimal places (currently 2 for all currencies)
     */
    public function getDecimals(): int
    {
        return 2;
    }

    /**
     * Check if the currency is currently active
     * 
     * Determines whether this currency is available for use in the system.
     * This allows for easy enabling/disabling of currencies without
     * removing them from the codebase.
     * 
     * @return bool True if currency is active (currently all are active)
     */
    public function isActive(): bool
    {
        return true;
    }

    /**
     * Get all currency code values
     * 
     * Returns an array of all currency codes (ISO 4217 format).
     * Useful for validation rules and currency selection dropdowns.
     * 
     * @return array Array of currency codes ['TRY', 'USD']
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all currency display names
     * 
     * Returns an array of human-readable currency names.
     * Useful for generating user-friendly currency selection options.
     * 
     * @return array Array of currency display names
     */
    public static function names(): array
    {
        return array_map(fn (self $case) => $case->getDisplayName(), self::cases());
    }
}
