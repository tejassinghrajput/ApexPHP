<?php

namespace App\Core\Contracts;

use App\Core\ApexORM\Model;

/**
 * Defines the standard methods for a data repository.
 * This ensures a consistent data access layer across the application.
 */
interface RepositoryInterface
{
    /**
     * Retrieve all records.
     *
     * @return array An array of Model instances.
     */
    public function all(): array;

    /**
     * Find a record by its primary key.
     *
     * @param int $id
     * @return Model|null
     */
    public function find(int $id): ?Model;

    /**
     * Create a new record.
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Update a record by its primary key.
     *
     * @param int $id
     * @param array $data
     * @return Model|null
     */
    public function update(int $id, array $data): ?Model;

    /**
     * Delete a record by its primary key.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
