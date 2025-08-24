<?php

namespace App\Services\User\Profile;

use App\DTOs\Profile\ProfileUpdateDTO;
use App\Http\Resources\User\Profile\ProfileStatResource;
use App\Models\User\User;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Order\OrderRepositoryInterface;

/**
 * ProfileService handles user profile operations including profile retrieval,
 * updates, and statistics generation. This service provides comprehensive
 * profile management functionality for authenticated users.
 * 
 * @package App\Services\User\Profile
 */
class ProfileService
{
    /**
     * ProfileService constructor.
     * 
     * @param UserRepositoryInterface $userRepository User repository for database operations
     * @param OrderRepositoryInterface $orderRepository Order repository for statistics calculation
     */
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected OrderRepositoryInterface $orderRepository
    ) {}

    /**
     * Retrieve user profile information.
     * Returns the authenticated user model for profile display purposes.
     * 
     * @param User $user The authenticated user model
     * @return User The user profile data
     */
    public function getProfile(User $user): User
    {
        return $user;
    }

    /**
     * Update user profile information with validated data.
     * Processes profile update request through DTO validation and
     * updates the user record in the database.
     * 
     * @param User $user The user to update
     * @param array $data Raw profile update data from request
     * @return User The updated user model with fresh data
     */
    public function updateProfile(User $user, array $data): User
    {
        $updateData = ProfileUpdateDTO::fromArray($data);
        $this->userRepository->updateByUuid($user->uuid, $updateData->toArray());

        return $this->userRepository->findByUuid($user->uuid);
    }

    /**
     * Generate comprehensive user statistics including order history,
     * spending patterns, and account information for profile dashboard.
     * Calculates total orders, spending amounts, average order values,
     * and provides last order details.
     * 
     * @param User $user The user to generate statistics for
     * @return ProfileStatResource Formatted user statistics resource
     */
    public function getUserStats(User $user): ProfileStatResource
    {
        $ordersPaginated = $this->orderRepository->findByUserUuid($user->uuid);
        $orders = $ordersPaginated->items(); // Get the collection from pagination
        
        $totalOrders = $ordersPaginated->total(); // Use total from pagination
        $totalSpent = collect($orders)->sum('total_amount') / 100; // Convert from kuruş to lira
        $averageOrderValue = $totalOrders > 0 ? $totalSpent / $totalOrders : 0;
        
        $lastOrder = collect($orders)->sortByDesc('created_at')->first();

        $stats = [
            'total_orders' => $totalOrders,
            'total_spent' => $totalSpent,
            'average_order_value' => $averageOrderValue,
            'member_since' => $user->created_at->toISOString(),
            'last_order' => $lastOrder ? [
                'order_number' => $lastOrder->order_number,
                'total' => $lastOrder->total,
                'status' => $lastOrder->status,
                'created_at' => $lastOrder->created_at->toISOString(),
            ] : null,
        ];

        return new ProfileStatResource($stats);
    }
}
