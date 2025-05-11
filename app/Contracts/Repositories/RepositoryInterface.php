<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    /**
     * Get all records
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Find record by id
     *
     * @param  int|string  $id
     */
    public function find($id, array $columns = ['*']): ?Model;

    /**
     * Create new record
     */
    public function create(array $data): Model;

    /**
     * Update record
     *
     * @param  int|string  $id
     */
    public function update($id, array $data): Model;

    /**
     * Delete record
     *
     * @param  int|string  $id
     */
    public function delete($id): void;
}
