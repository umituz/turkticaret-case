<?php

namespace App\Http\Controllers\User\Address;

use App\Http\Controllers\BaseController;
use App\Http\Requests\User\Address\AddressCreateRequest;
use App\Http\Requests\User\Address\AddressUpdateRequest;
use App\Http\Resources\User\Address\AddressResource;
use App\Models\User\UserAddress;
use App\Services\User\Address\AddressService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

/**
 * REST API Controller for User Address management.
 *
 * Handles CRUD operations for user addresses with ownership verification.
 * All operations are scoped to the authenticated user and include proper
 * authorization checks to ensure users can only access their own addresses.
 *
 * @package App\Http\Controllers\User\Address
 */
class AddressController extends BaseController
{
    /**
     * Create a new AddressController instance.
     *
     * @param AddressService $addressService The address service for address operations
     */
    public function __construct(protected AddressService $addressService) {}

    /**
     * Display a listing of the authenticated user's addresses.
     *
     * @return JsonResponse JSON response containing the user's address collection
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $addresses = $this->addressService->getUserAddresses($user);

        return $this->ok(AddressResource::collection($addresses), 'Addresses retrieved successfully.');
    }

    /**
     * Store a newly created address for the authenticated user.
     *
     * @param AddressCreateRequest $request The validated request containing address data
     * @return JsonResponse JSON response containing the created address resource
     */
    public function store(AddressCreateRequest $request): JsonResponse
    {
        $user = auth()->user();
        $address = $this->addressService->createAddress($user, $request->validated());

        return $this->ok(new AddressResource($address), 'Address created successfully.');
    }

    /**
     * Display the specified address with ownership verification.
     *
     * @param UserAddress $userAddress The user address model instance resolved by route model binding
     * @return JsonResponse JSON response containing the address resource or error if not owned by user
     * @throws AuthorizationException
     */
    public function show(UserAddress $userAddress): JsonResponse
    {
        $this->authorize('view', $userAddress);

        return $this->ok(new AddressResource($userAddress), 'Address retrieved successfully.');
    }

    /**
     * Update the specified address with ownership verification.
     *
     * @param AddressUpdateRequest $request The validated request containing updated address data
     * @param UserAddress $userAddress The user address model instance resolved by route model binding
     * @return JsonResponse JSON response containing the updated address resource or error if not owned by user
     * @throws AuthorizationException
     */
    public function update(AddressUpdateRequest $request, UserAddress $userAddress): JsonResponse
    {
        $this->authorize('update', $userAddress);

        $address = $this->addressService->updateAddressByModel($userAddress, $request->validated());

        return $this->ok(new AddressResource($address), 'Address updated successfully.');
    }

    /**
     * Remove the specified address with ownership verification.
     *
     * @param UserAddress $userAddress The user address model instance resolved by route model binding
     * @return JsonResponse JSON response confirming address deletion or error if not owned by user or deletion failed
     * @throws AuthorizationException
     */
    public function destroy(UserAddress $userAddress): JsonResponse
    {
        $this->authorize('delete', $userAddress);

        $deleted = $this->addressService->deleteAddressByModel($userAddress);

        if (!$deleted) {
            return $this->error(null, 'Failed to delete address.', 500);
        }

        return $this->noContent(null, 'Address deleted successfully.');
    }
}
