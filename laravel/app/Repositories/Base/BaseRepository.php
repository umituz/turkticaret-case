<?php

namespace App\Repositories\Base;

use App\Enums\ApiEnums;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Base Repository providing common database operations.
 *
 * Implements the repository pattern with comprehensive CRUD operations,
 * transaction management, pagination, soft deletes, and error handling.
 * All application repositories should extend this base implementation.
 *
 * @package App\Repositories\Base
 */
class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    /**
     * Create a new BaseRepository instance.
     *
     * @param Model $model The Eloquent model instance for this repository
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get the Eloquent model instance.
     *
     * @return Model The model instance used by this repository
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Get all records from the model.
     *
     * @return Collection Collection of all model records
     */
    public function get()
    {
        return $this->model->get();
    }

    /**
     * Get all records ordered by creation date descending.
     *
     * @return Collection Collection of all model records ordered by created_at desc
     */
    public function all()
    {
        return $this->model->orderBy('created_at', 'desc')->get();
    }

    /**
     * Execute a callback within a database transaction.
     *
     * @param callable $callback The callback function to execute within the transaction
     * @return mixed The result of the callback function
     * @throws ModelNotFoundException When a model is not found
     * @throws Exception When any other error occurs during transaction execution
     */
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

    /**
     * Create a new model record.
     *
     * @param array $data The data to create the model with
     * @return mixed The created model instance
     * @throws Exception When creation fails
     */
    public function create(array $data): mixed
    {
        return $this->executeInTransaction(function () use ($data) {
            return $this->model->create($data);
        });
    }

    /**
     * Find the first record matching the key or create it if not found.
     *
     * @param string $key The key field to search by
     * @param array $data The data array containing the key value and creation data
     * @return Model The found or created model instance
     */
    public function firstOrCreate(string $key, array $data)
    {
        return $this->model->firstOrCreate([$key => $data[$key]], $data);
    }

    /**
     * Check if a record exists with the given key and value.
     *
     * @param string $key The column name to check
     * @param mixed $value The value to search for
     * @return bool True if record exists, false otherwise
     */
    public function exists(string $key, $value)
    {
        return $this->model->where($key, $value)->exists();
    }

    /**
     * Get paginated results with optional eager loading.
     *
     * @param array $relations Array of relationships to eager load
     * @param int|null $count Number of items per page, defaults to ApiEnums::DEFAULT_PAGINATION
     * @return LengthAwarePaginator Paginated results
     */
    public function paginate(array $relations = [], int $count = null)
    {
        $query = $this->model->newQuery();
        if (!empty($relations)) {
            $query = $query->with($relations);
        }
        return $query->paginate($count ?? ApiEnums::DEFAULT_PAGINATION->value);
    }

    /**
     * Get the total count of records in the model.
     *
     * @return int The total number of records
     */
    public function total()
    {
        return $this->model->count();
    }

    /**
     * Get a limited number of records.
     *
     * @param int $count The number of records to retrieve
     * @return Collection Collection of limited records
     */
    public function take(int $count)
    {
        return $this->model->take($count)->get();
    }

    /**
     * Find a record by its UUID.
     *
     * @param string $uuid The UUID to search for
     * @return Model The found model instance
     * @throws ModelNotFoundException When no record is found with the given UUID
     */
    public function findByUuid(string $uuid)
    {
        $record = $this->model->where('uuid', $uuid)->first();
        if (!$record) {
            throw new ModelNotFoundException();
        }
        return $record;
    }

    /**
     * Update a record by its UUID.
     *
     * @param string $uuid The UUID of the record to update
     * @param array $data The data to update the record with
     * @return Model The updated model instance
     * @throws ModelNotFoundException When no record is found with the given UUID
     * @throws Exception When update operation fails
     */
    public function updateByUuid(string $uuid, array $data)
    {
        return $this->executeInTransaction(function () use ($uuid, $data) {
            $item = $this->model->where('uuid', $uuid)->firstOrFail();
            $item->update($data);
            return $item;
        });
    }

    /**
     * Soft delete a record by its UUID.
     *
     * @param string $uuid The UUID of the record to delete
     * @return bool True if deletion was successful, false otherwise
     * @throws Exception When delete operation fails
     */
    public function deleteByUuid(string $uuid)
    {
        return $this->executeInTransaction(function () use ($uuid) {
            return $this->model->where('uuid', $uuid)->delete();
        });
    }

    /**
     * Restore a soft-deleted record by its UUID.
     *
     * @param string $uuid The UUID of the record to restore
     * @return bool True if restoration was successful, false otherwise
     * @throws Exception When restore operation fails
     */
    public function restoreByUuid(string $uuid)
    {
        return $this->executeInTransaction(function () use ($uuid) {
            return $this->model->where('uuid', $uuid)->restore();
        });
    }

    /**
     * Permanently delete a record by its UUID.
     *
     * @param string $uuid The UUID of the record to permanently delete
     * @return bool True if force deletion was successful, false otherwise
     * @throws ModelNotFoundException When no record is found with the given UUID
     * @throws Exception When force delete operation fails
     */
    public function forceDeleteByUuid(string $uuid)
    {
        return $this->executeInTransaction(function () use ($uuid) {
            $item = $this->findByUuid($uuid);
            return $item->forceDelete();
        });
    }

    /**
     * Find a record by UUID with eager loaded relationships.
     *
     * @param string $uuid The UUID to search for
     * @param array $relations Array of relationships to eager load
     * @return Model The found model instance with loaded relationships
     * @throws ModelNotFoundException When no record is found with the given UUID
     */
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

    /**
     * Create multiple records in a single operation.
     *
     * @param array $data Array of data arrays for creating multiple records
     * @return bool True if insertion was successful, false otherwise
     * @throws Exception When bulk creation fails
     */
    public function createMany(array $data)
    {
        return $this->executeInTransaction(function () use ($data) {
            return $this->model->insert($data);
        });
    }

    /**
     * Get a fresh query builder instance for the model.
     *
     * @return Builder The Eloquent query builder instance
     */
    public function getQuery(): Builder
    {
        return $this->model->newQuery();
    }
}
