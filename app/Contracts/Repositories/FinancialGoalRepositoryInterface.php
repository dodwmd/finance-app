<?php

namespace App\Contracts\Repositories;

use App\Models\FinancialGoal;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Interface for FinancialGoal repository operations.
 */
interface FinancialGoalRepositoryInterface
{
    /**
     * Get all financial goals for a user with optional pagination.
     *
     * @param  int  $userId  The user ID
     * @param  int  $perPage  Number of items per page
     */
    public function getAllForUser(int $userId, int $perPage = 10): LengthAwarePaginator;

    /**
     * Get a specific financial goal by ID.
     *
     * @param  int  $id  The financial goal ID
     */
    public function getById(int $id): ?FinancialGoal;

    /**
     * Create a new financial goal.
     *
     * @param  array  $data  The financial goal data
     */
    public function create(array $data): FinancialGoal;

    /**
     * Update an existing financial goal.
     *
     * @param  int  $id  The financial goal ID
     * @param  array  $data  The updated financial goal data
     */
    public function update(int $id, array $data): ?FinancialGoal;

    /**
     * Delete a financial goal.
     *
     * @param  int  $id  The financial goal ID
     */
    public function delete(int $id): bool;

    /**
     * Update the current amount of a financial goal.
     *
     * @param  int  $id  The financial goal ID
     * @param  float  $amount  The amount to add to current_amount
     * @param  bool  $isIncrement  Whether to increment (true) or set directly (false)
     */
    public function updateAmount(int $id, float $amount, bool $isIncrement = true): ?FinancialGoal;

    /**
     * Get active financial goals for a user.
     *
     * @param  int  $userId  The user ID
     * @param  string|null  $type  Filter by goal type
     */
    public function getActiveGoals(int $userId, ?string $type = null): Collection;

    /**
     * Get financial goals that are due within a specific timeframe.
     *
     * @param  int  $userId  The user ID
     * @param  int  $days  Number of days to look ahead
     */
    public function getGoalsDueWithin(int $userId, int $days): Collection;

    /**
     * Get goals progress data with percentage information.
     *
     * @param  int  $goalId  The financial goal ID
     */
    public function getGoalProgress(int $goalId): array;
}
