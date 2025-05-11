<?php

namespace App\Repositories;

use App\Contracts\Repositories\TransactionRepositoryInterface;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

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
    #[\Override]
    public function getByUserId(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    /**
     * Get transactions by user ID and type
     */
    #[\Override]
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
    #[\Override]
    public function getByDateRange(int $userId, string $startDate, string $endDate): Collection
    {
        // Parse dates to ensure proper format
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        return $this->model->where('user_id', $userId)
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    /**
     * Get sum of transactions by type
     */
    #[\Override]
    public function getSumByType(int $userId, string $type): float
    {
        return $this->model->where('user_id', $userId)
            ->where('type', $type)
            ->sum('amount');
    }
}
