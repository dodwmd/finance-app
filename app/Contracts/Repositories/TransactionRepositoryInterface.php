<?php

namespace App\Contracts\Repositories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

interface TransactionRepositoryInterface extends RepositoryInterface
{
    /**
     * Get transactions by user ID
     *
     * @param int $userId
     * @return Collection
     */
    public function getByUserId(int $userId): Collection;

    /**
     * Get transactions by user ID and type
     *
     * @param int $userId
     * @param string $type
     * @return Collection
     */
    public function getByUserIdAndType(int $userId, string $type): Collection;

    /**
     * Get transactions by date range
     *
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getByDateRange(int $userId, string $startDate, string $endDate): Collection;

    /**
     * Get sum of transactions by type
     *
     * @param int $userId
     * @param string $type
     * @return float
     */
    public function getSumByType(int $userId, string $type): float;
}
