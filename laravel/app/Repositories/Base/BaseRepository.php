<?php

namespace App\Repositories\Base;

use App\Enums\ApiEnums;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function get()
    {
        return $this->model->get();
    }

    public function all()
    {
        return $this->model->orderBy('created_at', 'desc')->get();
    }

    protected function executeInTransaction(callable $callback)
    {
        try {
            DB::beginTransaction();
            $result = $callback();
            DB::commit();
            return $result;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in repository operation: ' . $e->getMessage());
            throw new Exception('An error occurred while processing the operation: ' . $e->getMessage());
        }
    }

    public function create(array $data): mixed
    {
        return $this->executeInTransaction(function () use ($data) {
            return $this->model->create($data);
        });
    }

    public function firstOrCreate(string $key, array $data)
    {
        return $this->model->firstOrCreate([$key => $data[$key]], $data);
    }

    public function exists(string $key, $value)
    {
        return $this->model->where($key, $value)->exists();
    }

    public function paginate(array $relations = [], int $count = ApiEnums::DEFAULT_PAGINATION)
    {
        $query = $this->model->newQuery();
        if (!empty($relations)) {
            $query = $query->with($relations);
        }
        return $query->paginate($count);
    }

    public function total()
    {
        return $this->model->count();
    }

    public function take(int $count)
    {
        return $this->model->take($count)->get();
    }

    public function findByUuid(string $uuid)
    {
        $record = $this->model->where('uuid', $uuid)->first();
        if (!$record) {
            throw new ModelNotFoundException();
        }
        return $record;
    }

    public function updateByUuid(string $uuid, array $data)
    {
        return $this->executeInTransaction(function () use ($uuid, $data) {
            $item = $this->model->where('uuid', $uuid)->firstOrFail();
            $item->update($data);
            return $item;
        });
    }

    public function deleteByUuid(string $uuid)
    {
        return $this->executeInTransaction(function () use ($uuid) {
            return $this->model->where('uuid', $uuid)->delete();
        });
    }

    public function restoreByUuid(string $uuid)
    {
        return $this->executeInTransaction(function () use ($uuid) {
            return $this->model->where('uuid', $uuid)->restore();
        });
    }

    public function forceDeleteByUuid(string $uuid)
    {
        return $this->executeInTransaction(function () use ($uuid) {
            $item = $this->findByUuid($uuid);
            return $item->forceDelete();
        });
    }

    public function findByUuidWithRelations(string $uuid, array $relations = [])
    {
        $query = $this->model->newQuery();
        if (!empty($relations)) {
            $query = $query->with($relations);
        }
        $record = $query->where('uuid', $uuid)->first();
        if (!$record) {
            throw new ModelNotFoundException();
        }
        return $record;
    }

    public function createMany(array $data)
    {
        return $this->executeInTransaction(function () use ($data) {
            return $this->model->insert($data);
        });
    }

    public function getQuery(): Builder
    {
        return $this->model->newQuery();
    }
}