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

  /**
 * REST API Controller for Currency management.
 *
 * Handles CRUD operations for system currencies including creating,
 * updating, deleting, and retrieving currency configurations for
 * multi-currency support in the e-commerce system.
 *
 * @package App\Http\Controllers\Currency
 */
class CurrencyController extends BaseController
{
    /**
     * Create a new CurrencyController instance.
     *
     * @param CurrencyService $currencyService The currency service for currency operations
     */
    public function __construct(protected CurrencyService $currencyService) {}

    /**
     * Display a listing of all available currencies.
     *
     * @param CurrencyListRequest $request The validated request for currency listing
     * @return JsonResponse JSON response containing currency collection
     */
    public function index(CurrencyListRequest $request): JsonResponse
    {
        $currencies = $this->currencyService->getAllCurrencies();

        return $this->ok(new CurrencyCollection($currencies), 'Currencies retrieved successfully');
    }

    /**
     * Display the specified currency.
     *
     * @param Currency $currency The currency model instance resolved by route model binding
     * @return JsonResponse JSON response containing the currency resource
     */
    public function show(Currency $currency): JsonResponse
    {
        return $this->ok(new CurrencyResource($currency), 'Currency retrieved successfully');
    }

    /**
     * Store a newly created currency in storage.
     *
     * @param CurrencyCreateRequest $request The validated request containing currency data
     * @return JsonResponse JSON response containing the created currency resource with 201 status
     */
    public function store(CurrencyCreateRequest $request): JsonResponse
    {
        $currency = $this->currencyService->createCurrency($request->validated());

        return $this->created(new CurrencyResource($currency), 'Currency created successfully');
    }

    /**
     * Update the specified currency in storage.
     *
     * @param CurrencyUpdateRequest $request The validated request containing updated currency data
     * @param Currency $currency The currency model instance resolved by route model binding
     * @return JsonResponse JSON response containing the updated currency resource
     */
    public function update(CurrencyUpdateRequest $request, Currency $currency): JsonResponse
    {
        $updatedCurrency = $this->currencyService->updateCurrency($currency->uuid, $request->validated());

        return $this->ok(new CurrencyResource($updatedCurrency), 'Currency updated successfully');
    }

    /**
     * Remove the specified currency from storage.
     *
     * @param Currency $currency The currency model instance resolved by route model binding
     * @return JsonResponse JSON response confirming currency deletion
     */
    public function destroy(Currency $currency): JsonResponse
    {
        $this->currencyService->deleteCurrency($currency->uuid);

        return $this->ok(null, 'Currency deleted successfully');
    }
}
