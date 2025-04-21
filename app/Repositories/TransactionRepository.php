<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class TransactionRepository extends BaseRepository
{
    /**
     * TransactionRepository constructor.
     *
     * @param Transaction $model
     */
    public function __construct(Transaction $model)
    {
        parent::__construct($model);
    }

    /**
     * Get transactions by user ID
     *
     * @param int $userId
     * @return Collection
     */
    public function getByUserId(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    /**
     * Get transactions by user ID and type
     *
     * @param int $userId
     * @param string $type
     * @return Collection
     */
    public function getByUserIdAndType(int $userId, string $type): Collection
    {
        return $this->model->where('user_id', $userId)
            ->where('type', $type)
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    /**
     * Get transactions by date range
     *
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getByDateRange(int $userId, string $startDate, string $endDate): Collection
    {
        return $this->model->where('user_id', $userId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    /**
     * Get sum of transactions by type
     *
     * @param int $userId
     * @param string $type
     * @return float
     */
    public function getSumByType(int $userId, string $type): float
    {
        return $this->model->where('user_id', $userId)
            ->where('type', $type)
            ->sum('amount');
    }
}
