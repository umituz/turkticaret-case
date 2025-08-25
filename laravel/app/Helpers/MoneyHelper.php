<?php

namespace App\Helpers;

use App\Enums\Setting\SettingKeyEnum;

/**
 * Helper class for money-related operations and formatting.
 *
 * This class provides utilities for working with monetary values,
 * including conversion between major and minor units (cents),
 * formatting for display, and amount information analysis.
 * All amounts are stored as integers in minor units (cents) for precision.
 *
 * @package App\Helpers
 */
class MoneyHelper
{
    /**
     * Get comprehensive information about a monetary amount.
     *
     * Provides raw value, formatted display, and classification
     * of the amount as positive, negative, or zero.
     *
     * @param int $value Amount in minor units (cents)
     * @param string|null $currency Currency symbol for formatting
     * @return array Array containing raw, formatted, and type information
     */
    public static function getAmountInfo(int $value, string $currency = null): array
    {
        $currency = $currency ?? SettingKeyEnum::DEFAULT_CURRENCY->getDefaultValue()['value'];
        $raw = $value / 100;
        $formatted = number_format($raw, 2) . ' ' . $currency;

        $type = match (true) {
            $value > 0 => 'positive',
            $value < 0 => 'negative',
            default => 'nil'
        };

        return [
            'raw' => $raw,
            'formatted' => $formatted,
            'formatted_minus' => $formatted,
            'type' => $type
        ];
    }

    /**
     * Format a monetary amount for display.
     *
     * @param int $value Amount in minor units (cents)
     * @param string|null $currency Currency symbol for formatting
     * @return string Formatted amount with currency symbol
     */
    public static function formatAmount(int $value, string $currency = null): string
    {
        $currency = $currency ?? SettingKeyEnum::DEFAULT_CURRENCY->getDefaultValue()['value'];
        $amount = $value / 100;

        return number_format($amount, 2) . ' ' . $currency;
    }

    /**
     * Convert a decimal amount to minor units (cents).
     *
     * @param float $amount Decimal amount to convert
     * @return int Amount in minor units (cents)
     */
    public static function convertToMinorUnits(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * Convert minor units (cents) to decimal amount.
     *
     * @param int $value Amount in minor units (cents)
     * @return float Decimal amount
     */
    public static function convertFromMinorUnits(int $value): float
    {
        return $value / 100;
    }
}
