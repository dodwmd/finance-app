<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface TransactionRepositoryInterface extends RepositoryInterface
{
    /**
     * Get transactions by user ID
     */
    public function getByUserId(int $userId): Collection;

    /**
     * Get transactions by user ID and type
     */
    public function getByUserIdAndType(int $userId, string $type): Collection;

    /**
     * Get transactions by date range
     */
    public function getByDateRange(int $userId, string $startDate, string $endDate): Collection;

    /**
     * Get sum of transactions by type
     */
    public function getSumByType(int $userId, string $type): float;
}
