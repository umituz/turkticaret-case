<?php

namespace App\Repositories\Cart;

use App\Models\Cart\Cart;
use App\Repositories\Base\BaseRepository;

class CartRepository extends BaseRepository implements CartRepositoryInterface
{
    public function __construct(Cart $model)
    {
        parent::__construct($model);
    }

    public function findByUserUuid(string $userUuid): ?Cart
    {
        return $this->model->where('user_uuid', $userUuid)->first();
    }
}