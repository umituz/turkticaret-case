<?php

namespace App\Http\Controllers\Country;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Country\CountryCreateRequest;
use App\Http\Requests\Country\CountryListRequest;
use App\Http\Requests\Country\CountryUpdateRequest;
use App\Http\Resources\Country\CountryCollection;
use App\Http\Resources\Country\CountryResource;
use App\Models\Country\Country;
use App\Services\Country\CountryService;
use Illuminate\Http\JsonResponse;

/**
 * REST API Controller for Country management.
 * 
 * Handles CRUD operations for countries including creating,
 * updating, deleting, and retrieving country information for
 * address and shipping purposes in the e-commerce system.
 *
 * @package App\Http\Controllers\Country
 */
class CountryController extends BaseController
{
    /**
     * Create a new CountryController instance.
     *
     * @param CountryService $countryService The country service for country operations
     */
    public function __construct(protected CountryService $countryService) {}

    /**
     * Display a listing of all available countries.
     *
     * @param CountryListRequest $request The validated request for country listing
     * @return JsonResponse JSON response containing country collection
     */
    public function index(CountryListRequest $request): JsonResponse
    {
        $countries = $this->countryService->getAllCountries();

        return $this->ok(new CountryCollection($countries), 'Countries retrieved successfully');
    }

    /**
     * Display the specified country.
     *
     * @param Country $country The country model instance resolved by route model binding
     * @return JsonResponse JSON response containing the country resource
     */
    public function show(Country $country): JsonResponse
    {
        return $this->ok(new CountryResource($country), 'Country retrieved successfully');
    }

    /**
     * Store a newly created country in storage.
     *
     * @param CountryCreateRequest $request The validated request containing country data
     * @return JsonResponse JSON response containing the created country resource with 201 status
     */
    public function store(CountryCreateRequest $request): JsonResponse
    {
        $country = $this->countryService->createCountry($request->validated());

        return $this->created(new CountryResource($country), 'Country created successfully');
    }

    /**
     * Update the specified country in storage.
     *
     * @param CountryUpdateRequest $request The validated request containing updated country data
     * @param Country $country The country model instance resolved by route model binding
     * @return JsonResponse JSON response containing the updated country resource
     */
    public function update(CountryUpdateRequest $request, Country $country): JsonResponse
    {
        $updatedCountry = $this->countryService->updateCountry($country->uuid, $request->validated());

        return $this->ok(new CountryResource($updatedCountry), 'Country updated successfully');
    }

    /**
     * Remove the specified country from storage.
     *
     * @param Country $country The country model instance resolved by route model binding
     * @return JsonResponse JSON response confirming country deletion
     */
    public function destroy(Country $country): JsonResponse
    {
        $this->countryService->deleteCountry($country->uuid);

        return $this->ok(null, 'Country deleted successfully');
    }

}