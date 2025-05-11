<?php

namespace App\Repositories;

use App\Contracts\Repositories\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseRepository constructor.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records
     */
    #[\Override]
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->all($columns);
    }

    /**
     * Find record by id
     *
     * @param  int|string  $id
     */
    #[\Override]
    public function find($id, array $columns = ['*']): ?Model
    {
        return $this->model->find($id, $columns);
    }

    /**
     * Create new record
     */
    #[\Override]
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update record
     *
     * @param  int|string  $id
     */
    #[\Override]
    public function update($id, array $data): Model
    {
        $record = $this->find($id);
        $record->update($data);

        return $record;
    }

    /**
     * Delete record
     *
     * @param  int|string  $id
     */
    #[\Override]
    public function delete($id): void
    {
        $this->find($id)->delete();
    }
}
