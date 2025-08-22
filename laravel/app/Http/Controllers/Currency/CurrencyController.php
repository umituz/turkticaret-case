<?php

namespace App\Http\Controllers\Currency;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Currency\CurrencyCreateRequest;
use App\Http\Requests\Currency\CurrencyListRequest;
use App\Http\Requests\Currency\CurrencyUpdateRequest;
use App\Http\Resources\Currency\CurrencyCollection;
use App\Http\Resources\Currency\CurrencyResource;
use App\Models\Currency\Currency;
use App\Services\Currency\CurrencyService;
use Illuminate\Http\JsonResponse;

class CurrencyController extends BaseController
{
    public function __construct(protected CurrencyService $currencyService) {}

    public function index(CurrencyListRequest $request): JsonResponse
    {
        $currencies = $this->currencyService->getAllCurrencies();

        return $this->ok(new CurrencyCollection($currencies), 'Currencies retrieved successfully');
    }

    public function show(Currency $currency): JsonResponse
    {
        return $this->ok(new CurrencyResource($currency), 'Currency retrieved successfully');
    }

    public function store(CurrencyCreateRequest $request): JsonResponse
    {
        $currency = $this->currencyService->createCurrency($request->validated());

        return $this->created(new CurrencyResource($currency), 'Currency created successfully');
    }

    public function update(CurrencyUpdateRequest $request, Currency $currency): JsonResponse
    {
        $updatedCurrency = $this->currencyService->updateCurrency($currency->uuid, $request->validated());

        return $this->ok(new CurrencyResource($updatedCurrency), 'Currency updated successfully');
    }

    public function destroy(Currency $currency): JsonResponse
    {
        $this->currencyService->deleteCurrency($currency->uuid);

        return $this->ok(null, 'Currency deleted successfully');
    }
}