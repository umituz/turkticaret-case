<?php

namespace App\Enums\Language;

enum LanguageEnum: string
{
    case TURKISH = 'tr';
    case ENGLISH = 'en';

    public function getDisplayName(): string
    {
        return match($this) {
            self::TURKISH => 'Turkish',
            self::ENGLISH => 'English',
        };
    }

    public function getNativeName(): string
    {
        return match($this) {
            self::TURKISH => 'Türkçe',
            self::ENGLISH => 'English',
        };
    }

    public function getLocale(): string
    {
        return match($this) {
            self::TURKISH => 'tr_TR',
            self::ENGLISH => 'en_US',
        };
    }

    public function getDirection(): string
    {
        return 'ltr';
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
