<?php

namespace App\Http\Controllers\Language;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Language\LanguageCreateRequest;
use App\Http\Requests\Language\LanguageListRequest;
use App\Http\Requests\Language\LanguageUpdateRequest;
use App\Http\Resources\Language\LanguageCollection;
use App\Http\Resources\Language\LanguageResource;
use App\Models\Language\Language;
use App\Services\Language\LanguageService;
use Illuminate\Http\JsonResponse;

class LanguageController extends BaseController
{
    public function __construct(protected LanguageService $languageService) {}

    public function index(LanguageListRequest $request): JsonResponse
    {
        $languages = $this->languageService->getAllLanguages();

        return $this->ok(new LanguageCollection($languages), 'Languages retrieved successfully');
    }

    public function show(Language $language): JsonResponse
    {
        return $this->ok(new LanguageResource($language), 'Language retrieved successfully');
    }

    public function store(LanguageCreateRequest $request): JsonResponse
    {
        $language = $this->languageService->createLanguage($request->validated());

        return $this->created(new LanguageResource($language), 'Language created successfully');
    }

    public function update(LanguageUpdateRequest $request, Language $language): JsonResponse
    {
        $updatedLanguage = $this->languageService->updateLanguage($language->uuid, $request->validated());

        return $this->ok(new LanguageResource($updatedLanguage), 'Language updated successfully');
    }

    public function destroy(Language $language): JsonResponse
    {
        $this->languageService->deleteLanguage($language->uuid);

        return $this->ok(null, 'Language deleted successfully');
    }
}
