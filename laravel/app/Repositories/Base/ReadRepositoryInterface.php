<?php

namespace App\Repositories\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only repository interface for query operations.
 * 
 * Defines the contract for read operations including finding, filtering,
 * and retrieving records without any data modification capabilities.
 * Follows Interface Segregation Principle by focusing only on read operations.
 *
 * @package App\Repositories\Base
 */
interface ReadRepositoryInterface
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
     * Find a record by UUID with specified relations loaded.
     *
     * @param string $uuid The UUID to search for
     * @param array $relations Array of relation names to eager load
     * @return mixed The found model instance with relations or null
     */
    public function findByUuidWithRelations(string $uuid, array $relations = []);
}