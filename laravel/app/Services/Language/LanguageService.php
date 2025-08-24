<?php

namespace App\Services\Language;

use App\Repositories\Language\LanguageRepositoryInterface;
use App\Models\Language\Language;
use App\Helpers\LanguageHelper;
use Illuminate\Database\Eloquent\Collection;

/**
 * Language Service for internationalization operations.
 *
 * Handles CRUD operations for system languages including creating,
 * updating, deleting, and retrieving language configurations for
 * internationalization and localization support.
 *
 * @package App\Services\Language
 */
class LanguageService
{
    /**
     * Create a new LanguageService instance.
     *
     * @param LanguageRepositoryInterface $languageRepository The language repository for data operations
     */
    public function __construct(protected LanguageRepositoryInterface $languageRepository) {}

    /**
     * Get all available languages.
     *
     * @return Collection Collection of all languages in the system
     */
    public function getAllLanguages(): Collection
    {
        return $this->languageRepository->all();
    }

    /**
     * Create a new language.
     *
     * @param array $data Language data including code, name, and locale information
     * @return Language The newly created language instance
     */
    public function createLanguage(array $data): Language
    {
        return $this->languageRepository->create($data);
    }

    /**
     * Update an existing language.
     *
     * @param string $uuid The UUID of the language to update
     * @param array $data Updated language data
     * @return Language The updated language instance
     * @throws \Exception
     */
    public function updateLanguage(string $uuid, array $data): Language
    {
        return $this->languageRepository->updateByUuid($uuid, $data);
    }

    /**
     * Delete a language.
     *
     * @param string $uuid The UUID of the language to delete
     * @return bool True if deletion was successful, false otherwise
     */
    public function deleteLanguage(string $uuid): bool
    {
        return $this->languageRepository->deleteByUuid($uuid);
    }

    /**
     * Get language by country locale string.
     *
     * @param string $locale The locale string (e.g., 'en_US', 'tr_TR')
     * @return Language The language instance matching the locale
     * @throws \InvalidArgumentException When language is not found for the given locale
     */
    public function getByCountryLocale(string $locale): Language
    {
        $languageCode = LanguageHelper::extractLanguageCodeFromLocale($locale);
        $language = $this->languageRepository->findByCode($languageCode);

        if (!$language) {
            throw new \InvalidArgumentException('Language not found for locale: ' . $locale);
        }

        return $language;
    }
}
