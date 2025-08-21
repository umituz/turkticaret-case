<?php

namespace App\Repositories\Cart;

use App\Models\Cart\Cart;
use App\Repositories\Base\BaseRepositoryInterface;

interface CartRepositoryInterface extends BaseRepositoryInterface
{
    public function findByUserUuid(string $userUuid): ?Cart;
}