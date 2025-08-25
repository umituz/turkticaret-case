<?php

namespace App\Repositories\Base;

/**
 * Soft delete repository interface for soft deletion operations.
 * 
 * Defines the contract for soft delete operations including delete, restore,
 * and force delete. Only repositories that work with soft deletable models
 * need to implement this interface, following Interface Segregation Principle.
 *
 * @package App\Repositories\Base
 */
interface SoftDeleteRepositoryInterface
{
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
}