<?php

namespace App\Helpers;

class LanguageHelper
{
    /**
     * Extract language code from locale string
     *
     * @param string $locale (e.g., 'tr_TR', 'en_US', 'fr_FR')
     * @return string (e.g., 'tr', 'en', 'fr')
     */
    public static function extractLanguageCodeFromLocale(string $locale): string
    {
        return substr($locale, 0, 2);
    }
}
