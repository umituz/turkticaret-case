<?php

namespace App\Enums\Language;

/**
 * Language enumeration for internationalization support
 * 
 * Defines supported languages in the application with their locales,
 * display names, and configuration. Used for language switching and
 * localization throughout the application.
 * 
 * @package App\Enums\Language
 */
enum LanguageEnum: string
{
    case TURKISH = 'tr';
    case ENGLISH = 'en';

    /**
     * Get display name for the language in English
     * 
     * @return string Language display name
     */
    public function getDisplayName(): string
    {
        return match($this) {
            self::TURKISH => 'Turkish',
            self::ENGLISH => 'English',
        };
    }

    /**
     * Get native name for the language
     * 
     * @return string Language name in its native script
     */
    public function getNativeName(): string
    {
        return match($this) {
            self::TURKISH => 'Türkçe',
            self::ENGLISH => 'English',
        };
    }

    /**
     * Get locale code for the language
     * 
     * @return string Locale code (e.g., 'tr_TR', 'en_US')
     */
    public function getLocale(): string
    {
        return match($this) {
            self::TURKISH => 'tr_TR',
            self::ENGLISH => 'en_US',
        };
    }

    /**
     * Get text direction for the language
     * 
     * @return string Text direction ('ltr' for left-to-right)
     */
    public function getDirection(): string
    {
        return 'ltr';
    }

    /**
     * Check if the language is active
     * 
     * @return bool Always true (all defined languages are active)
     */
    public function isActive(): bool
    {
        return true;
    }

    /**
     * Get all language values as array
     * 
     * @return array Array of all language codes
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all language display names as array
     * 
     * @return array Array of all language display names
     */
    public static function names(): array
    {
        return array_map(fn (self $case) => $case->getDisplayName(), self::cases());
    }
}
