<?php

namespace App\Enums\Country;

enum CountryEnum: string
{
    case TURKEY = 'TR';
    case UNITED_STATES = 'US';

    public function getDisplayName(): string
    {
        return match($this) {
            self::TURKEY => 'Turkey',
            self::UNITED_STATES => 'United States',
        };
    }

    public function getCurrencyCode(): string
    {
        return match($this) {
            self::TURKEY => 'TRY',
            self::UNITED_STATES => 'USD',
        };
    }

    public function getLocale(): string
    {
        return match($this) {
            self::TURKEY => 'tr_TR',
            self::UNITED_STATES => 'en_US',
        };
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
