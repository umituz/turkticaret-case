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

 /**
 * REST API Controller for Language management.
 *
 * Handles CRUD operations for system languages including creating,
 * updating, deleting, and retrieving language configurations for
 * internationalization support.
 *
 * @package App\Http\Controllers\Language
 */
class LanguageController extends BaseController
{
    /**
     * Create a new LanguageController instance.
     *
     * @param LanguageService $languageService The language service for language operations
     */
    public function __construct(protected LanguageService $languageService) {}

    /**
     * Display a listing of all available languages.
     *
     * @param LanguageListRequest $request The validated request for language listing
     * @return JsonResponse JSON response containing language collection
     */
    public function index(LanguageListRequest $request): JsonResponse
    {
        $languages = $this->languageService->getAllLanguages();

        return $this->ok(new LanguageCollection($languages), 'Languages retrieved successfully');
    }

    /**
     * Display the specified language.
     *
     * @param Language $language The language model instance resolved by route model binding
     * @return JsonResponse JSON response containing the language resource
     */
    public function show(Language $language): JsonResponse
    {
        return $this->ok(new LanguageResource($language), 'Language retrieved successfully');
    }

    /**
     * Store a newly created language in storage.
     *
     * @param LanguageCreateRequest $request The validated request containing language data
     * @return JsonResponse JSON response containing the created language resource with 201 status
     */
    public function store(LanguageCreateRequest $request): JsonResponse
    {
        $language = $this->languageService->createLanguage($request->validated());

        return $this->created(new LanguageResource($language), 'Language created successfully');
    }

    /**
     * Update the specified language in storage.
     *
     * @param LanguageUpdateRequest $request The validated request containing updated language data
     * @param Language $language The language model instance resolved by route model binding
     * @return JsonResponse JSON response containing the updated language resource
     */
    public function update(LanguageUpdateRequest $request, Language $language): JsonResponse
    {
        $updatedLanguage = $this->languageService->updateLanguage($language->uuid, $request->validated());

        return $this->ok(new LanguageResource($updatedLanguage), 'Language updated successfully');
    }

    /**
     * Remove the specified language from storage.
     *
     * @param Language $language The language model instance resolved by route model binding
     * @return JsonResponse JSON response confirming language deletion
     */
    public function destroy(Language $language): JsonResponse
    {
        $this->languageService->deleteLanguage($language->uuid);

        return $this->ok(null, 'Language deleted successfully');
    }
}
