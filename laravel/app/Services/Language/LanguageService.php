<?php

namespace App\Services\Language;

use App\Repositories\Language\LanguageRepositoryInterface;
use App\Models\Language\Language;
use Illuminate\Database\Eloquent\Collection;

class LanguageService
{
    public function __construct(protected LanguageRepositoryInterface $languageRepository) {}

    public function getAllLanguages(): Collection
    {
        return $this->languageRepository->all();
    }

    public function createLanguage(array $data): Language
    {
        return $this->languageRepository->create($data);
    }

    public function updateLanguage(string $uuid, array $data): Language
    {
        return $this->languageRepository->updateByUuid($uuid, $data);
    }

    public function deleteLanguage(string $uuid): bool
    {
        return $this->languageRepository->deleteByUuid($uuid);
    }
}