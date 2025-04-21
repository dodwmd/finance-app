<?php

namespace App\Repositories;

use App\Contracts\Repositories\TransactionRepositoryInterface;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

class TransactionRepository extends BaseRepository implements TransactionRepositoryInterface
{
    /**
     * TransactionRepository constructor.
     */
    public function __construct(Transaction $model)
    {
        parent::__construct($model);
    }

    /**
     * Get transactions by user ID
     */
    public function getByUserId(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    /**
     * Get transactions by user ID and type
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
     */
    public function getSumByType(int $userId, string $type): float
    {
        return $this->model->where('user_id', $userId)
            ->where('type', $type)
            ->sum('amount');
    }
}
