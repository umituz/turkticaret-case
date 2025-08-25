<?php

namespace App\Repositories\Base;

/**
 * Write repository interface for data modification operations.
 * 
 * Defines the contract for write operations including creating and updating
 * records. Separated from read operations to follow Interface Segregation Principle.
 * Read-only repositories don't need to implement these methods.
 *
 * @package App\Repositories\Base
 */
interface WriteRepositoryInterface
{
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
     * Update a record by its UUID.
     *
     * @param string $uuid The UUID of the record to update
     * @param array $data Associative array of field values to update
     * @return mixed The updated model instance
     */
    public function updateByUuid(string $uuid, array $data);

    /**
     * Create multiple records in a single operation.
     *
     * @param array $data Array of associative arrays, each representing a record
     * @return mixed Collection of created model instances
     */
    public function createMany(array $data);
}