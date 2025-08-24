<?php

namespace App\Repositories\Base;

use App\Enums\ApiEnums;
use Illuminate\Database\Eloquent\Model;

/**
 * Base repository contract defining standard CRUD and query operations.
 * 
 * This interface establishes the foundational methods that all repository
 * implementations should provide. It includes standard database operations
 * like create, read, update, delete, as well as UUID-based operations
 * and soft delete management.
 *
 * @package App\Repositories\Base
 */
interface BaseRepositoryInterface
{
    /**
     * Get all records from the repository with applied filters.
     *
     * @return mixed Collection of records matching current query constraints
     */
    public function get();

    /**
     * Get all records from the repository without any filters.
     *
     * @return mixed Complete collection of all records in the repository
     */
    public function all();

    /**
     * Create a new record in the repository.
     *
     * @param array $data Associative array of field values for the new record
     * @return mixed The created model instance
     */
    public function create(array $data): mixed;

    /**
     * Find the first record matching the key-value pair or create a new one.
     *
     * @param string $key The field name to search by
     * @param array $data Data for finding existing record or creating new one
     * @return mixed The found or created model instance
     */
    public function firstOrCreate(string $key, array $data);

    /**
     * Check if a record exists with the given key-value pair.
     *
     * @param string $key The field name to search by
     * @param mixed $value The value to search for
     * @return bool True if record exists, false otherwise
     */
    public function exists(string $key, $value);

    /**
     * Get paginated results with optional relations.
     *
     * @param array $relations Array of relation names to eager load
     * @param int|null $count Number of items per page (null for default)
     * @return mixed Paginated collection of records
     */
    public function paginate(array $relations = [], int $count = null);

    /**
     * Get the total count of records in the repository.
     *
     * @return int Total number of records
     */
    public function total();

    /**
     * Limit the query to a specific number of records.
     *
     * @param int $count Maximum number of records to return
     * @return mixed Limited collection of records
     */
    public function take(int $count);

    /**
     * Get the underlying Eloquent model instance.
     *
     * @return Model The model instance used by this repository
     */
    public function getModel(): Model;

    /**
     * Find a record by its UUID.
     *
     * @param string $uuid The UUID to search for
     * @return mixed The found model instance or null
     */
    public function findByUuid(string $uuid);

    /**
     * Update a record by its UUID.
     *
     * @param string $uuid The UUID of the record to update
     * @param array $data Associative array of field values to update
     * @return mixed The updated model instance
     */
    public function updateByUuid(string $uuid, array $data);

    /**
     * Soft delete a record by its UUID.
     *
     * @param string $uuid The UUID of the record to delete
     * @return mixed Result of the delete operation
     */
    public function deleteByUuid(string $uuid);

    /**
     * Restore a soft-deleted record by its UUID.
     *
     * @param string $uuid The UUID of the record to restore
     * @return mixed Result of the restore operation
     */
    public function restoreByUuid(string $uuid);

    /**
     * Permanently delete a record by its UUID.
     *
     * @param string $uuid The UUID of the record to permanently delete
     * @return mixed Result of the force delete operation
     */
    public function forceDeleteByUuid(string $uuid);

    /**
     * Find a record by UUID with specified relations loaded.
     *
     * @param string $uuid The UUID to search for
     * @param array $relations Array of relation names to eager load
     * @return mixed The found model instance with relations or null
     */
    public function findByUuidWithRelations(string $uuid, array $relations = []);

    /**
     * Create multiple records in a single operation.
     *
     * @param array $data Array of associative arrays, each representing a record
     * @return mixed Collection of created model instances
     */
    public function createMany(array $data);
}