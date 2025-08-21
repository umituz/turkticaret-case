<?php

namespace App\Repositories\Base;

use App\Enums\ApiEnums;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    public function get();

    public function all();

    public function create(array $data): mixed;

    public function firstOrCreate(string $key, array $data);

    public function exists(string $key, $value);

    public function paginate(array $relations = [], int $count = ApiEnums::DEFAULT_PAGINATION);

    public function total();

    public function take(int $count);

    public function getModel(): Model;

    public function findByUuid(string $uuid);

    public function updateByUuid(string $uuid, array $data);

    public function deleteByUuid(string $uuid);

    public function restoreByUuid(string $uuid);

    public function forceDeleteByUuid(string $uuid);

    public function findByUuidWithRelations(string $uuid, array $relations = []);

    public function createMany(array $data);
}