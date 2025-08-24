<?php

namespace App\Http\Controllers\Shipping;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Shipping\ShippingMethodResource;
use App\Services\Shipping\ShippingService;
use Illuminate\Http\JsonResponse;

/**
 * REST API Controller for Shipping method management.
 * 
 * Provides endpoints for retrieving available shipping methods
 * and related shipping information for order fulfillment.
 *
 * @package App\Http\Controllers\Shipping
 */
class ShippingController extends BaseController
{
    /**
     * Create a new ShippingController instance.
     *
     * @param ShippingService $shippingService The shipping service for shipping operations
     */
    public function __construct(protected ShippingService $shippingService) {}

    /**
     * Get all available shipping methods.
     *
     * @return JsonResponse JSON response containing collection of available shipping methods
     */
    public function getMethods(): JsonResponse
    {
        $methods = $this->shippingService->getAvailableMethods();

        return $this->ok(ShippingMethodResource::collection($methods), 'Shipping methods retrieved successfully.');
    }
}