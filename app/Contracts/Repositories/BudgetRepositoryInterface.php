<?php

namespace App\Contracts\Repositories;

use App\Models\Budget;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Interface for Budget repository operations.
 */
interface BudgetRepositoryInterface
{
    /**
     * Get all budgets for a user with optional pagination.
     *
     * @param  int  $userId  The user ID
     * @param  int  $perPage  Number of items per page
     */
    public function getAllForUser(int $userId, int $perPage = 10): LengthAwarePaginator;

    /**
     * Get a specific budget by ID.
     *
     * @param  int  $id  The budget ID
     */
    public function getById(int $id): ?Budget;

    /**
     * Create a new budget.
     *
     * @param  array  $data  The budget data
     */
    public function create(array $data): Budget;

    /**
     * Update an existing budget.
     *
     * @param  int  $id  The budget ID
     * @param  array  $data  The updated budget data
     */
    public function update(int $id, array $data): ?Budget;

    /**
     * Delete a budget.
     *
     * @param  int  $id  The budget ID
     */
    public function delete(int $id): bool;

    /**
     * Get active budgets for a user.
     *
     * @param  int  $userId  The user ID
     * @param  string|null  $period  Filter by period (monthly, quarterly, yearly)
     */
    public function getActiveBudgets(int $userId, ?string $period = null): Collection;

    /**
     * Get budget progress with spending information.
     *
     * @param  int  $budgetId  The budget ID
     * @return array Budget with progress data
     */
    public function getBudgetProgress(int $budgetId): array;

    /**
     * Get all budgets that are current (active and within date range).
     *
     * @param  int  $userId  The user ID
     */
    public function getCurrentBudgets(int $userId): Collection;
}
