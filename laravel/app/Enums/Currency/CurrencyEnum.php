<?php

namespace App\Enums\Currency;

enum CurrencyEnum: string
{
    case TRY = 'TRY';
    case USD = 'USD';

    public function getDisplayName(): string
    {
        return match($this) {
            self::TRY => 'Turkish Lira',
            self::USD => 'US Dollar',
        };
    }

    public function getSymbol(): string
    {
        return match($this) {
            self::TRY => '₺',
            self::USD => '$',
        };
    }

    public function getDecimals(): int
    {
        return 2;
    }

    public function isActive(): bool
    {
        return true;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function names(): array
    {
        return array_map(fn (self $case) => $case->getDisplayName(), self::cases());
    }
}
