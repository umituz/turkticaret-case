<?php

namespace App\Repositories\User;

use App\Models\Auth\User;
use App\Repositories\Base\BaseRepository;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        $email = strtolower(trim($email));
        return $this->model->where('email', $email)->first();
    }
}
