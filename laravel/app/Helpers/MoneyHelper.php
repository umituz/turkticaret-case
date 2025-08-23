<?php

namespace App\Helpers;

class MoneyHelper
{
    public static function getAmountInfo(int $value, string $currency = '₺'): array
    {
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

    public static function formatAmount(int $value, string $currency = '₺'): string
    {
        $amount = $value / 100;
        return number_format($amount, 2) . ' ' . $currency;
    }

    public static function convertToMinorUnits(float $amount): int
    {
        return (int) round($amount * 100);
    }

    public static function convertFromMinorUnits(int $value): float
    {
        return $value / 100;
    }
}
