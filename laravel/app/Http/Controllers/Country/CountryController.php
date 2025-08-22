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

class CountryController extends BaseController
{
    public function __construct(protected CountryService $countryService) {}

    public function index(CountryListRequest $request): JsonResponse
    {
        $countries = $this->countryService->getAllCountries();

        return $this->ok(new CountryCollection($countries), 'Countries retrieved successfully');
    }

    public function show(Country $country): JsonResponse
    {
        return $this->ok(new CountryResource($country), 'Country retrieved successfully');
    }

    public function store(CountryCreateRequest $request): JsonResponse
    {
        $country = $this->countryService->createCountry($request->validated());

        return $this->created(new CountryResource($country), 'Country created successfully');
    }

    public function update(CountryUpdateRequest $request, Country $country): JsonResponse
    {
        $updatedCountry = $this->countryService->updateCountry($country->uuid, $request->validated());

        return $this->ok(new CountryResource($updatedCountry), 'Country updated successfully');
    }

    public function destroy(Country $country): JsonResponse
    {
        $this->countryService->deleteCountry($country->uuid);

        return $this->ok(null, 'Country deleted successfully');
    }

}