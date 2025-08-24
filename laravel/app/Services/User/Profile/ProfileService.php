<?php

namespace App\Services\User\Profile;

use App\DTOs\Profile\ProfileUpdateDTO;
use App\Http\Resources\User\Profile\ProfileStatResource;
use App\Models\User\User;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Order\OrderRepositoryInterface;

class ProfileService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected OrderRepositoryInterface $orderRepository
    ) {}

    public function getProfile(User $user): User
    {
        return $user;
    }

    public function updateProfile(User $user, array $data): User
    {
        $updateData = ProfileUpdateDTO::fromArray($data);
        $this->userRepository->updateByUuid($user->uuid, $updateData->toArray());

        return $this->userRepository->findByUuid($user->uuid);
    }

    public function getUserStats(User $user): ProfileStatResource
    {
        $orders = $this->orderRepository->findByUserId($user->id);
        
        $totalOrders = $orders->count();
        $totalSpent = $orders->sum('total');
        $averageOrderValue = $totalOrders > 0 ? $totalSpent / $totalOrders : 0;
        
        $lastOrder = $orders->sortByDesc('created_at')->first();

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
