<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

interface RepositoryInterface
{
    /**
     * Get all records
     * 
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Find record by id
     * 
     * @param int|string $id
     * @param array $columns
     * @return Model|null
     */
    public function find($id, array $columns = ['*']): ?Model;

    /**
     * Create new record
     * 
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Update record
     * 
     * @param int|string $id
     * @param array $data
     * @return Model
     */
    public function update($id, array $data): Model;

    /**
     * Delete record
     * 
     * @param int|string $id
     * @return bool
     */
    public function delete($id): bool;
}
