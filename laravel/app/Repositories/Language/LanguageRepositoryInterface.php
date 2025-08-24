<?php

namespace App\Repositories\Language;

use App\Models\Language\Language;

/**
 * Contract for Language repository implementations.
 * 
 * Defines the required methods for Language data access layer operations
 * including language code lookups, locale management, and language-specific
 * data retrieval functionality.
 *
 * @package App\Repositories\Language
 */
interface LanguageRepositoryInterface
{
    /**
     * Find a language by its language code.
     *
     * @param string $code The language code to search for (e.g., 'en', 'tr')
     * @return Language|null The language instance if found, null otherwise
     */
    public function findByCode(string $code): ?Language;
}
