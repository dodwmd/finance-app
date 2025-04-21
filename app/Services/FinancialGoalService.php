<?php

namespace App\Services;

use App\Contracts\Repositories\FinancialGoalRepositoryInterface;
use App\Models\FinancialGoal;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class FinancialGoalService
{
    /**
     * The financial goal repository instance.
     *
     * @var FinancialGoalRepositoryInterface
     */
    protected $repository;

    /**
     * Create a new service instance.
     */
    public function __construct(FinancialGoalRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all financial goals for a user.
     *
     * @param  int  $userId  The user ID
     * @param  int  $perPage  Number of items per page
     */
    public function getAllGoals(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->getAllForUser($userId, $perPage);
    }

    /**
     * Get a specific financial goal by ID.
     *
     * @param  int  $id  The financial goal ID
     */
    public function getGoalById(int $id): ?FinancialGoal
    {
        return $this->repository->getById($id);
    }

    /**
     * Create a new financial goal.
     *
     * @param  array  $data  The financial goal data
     */
    public function createGoal(array $data): FinancialGoal
    {
        return $this->repository->create($data);
    }

    /**
     * Update an existing financial goal.
     *
     * @param  int  $id  The financial goal ID
     * @param  array  $data  The updated financial goal data
     */
    public function updateGoal(int $id, array $data): ?FinancialGoal
    {
        return $this->repository->update($id, $data);
    }

    /**
     * Delete a financial goal.
     *
     * @param  int  $id  The financial goal ID
     */
    public function deleteGoal(int $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Update the current amount of a financial goal.
     *
     * @param  int  $id  The financial goal ID
     * @param  float  $amount  The amount to add or set
     * @param  bool  $isIncrement  Whether to increment or set directly
     */
    public function updateGoalAmount(int $id, float $amount, bool $isIncrement = true): ?FinancialGoal
    {
        return $this->repository->updateAmount($id, $amount, $isIncrement);
    }

    /**
     * Get active financial goals for a user.
     *
     * @param  int  $userId  The user ID
     * @param  string|null  $type  Filter by goal type
     */
    public function getActiveGoals(int $userId, ?string $type = null): Collection
    {
        return $this->repository->getActiveGoals($userId, $type);
    }

    /**
     * Get financial goals with progress information.
     *
     * @param  int  $userId  The user ID
     */
    public function getGoalsWithProgress(int $userId): Collection
    {
        $goals = $this->repository->getActiveGoals($userId);

        return $goals->map(function ($goal) {
            $progress = $this->repository->getGoalProgress($goal->id);

            return array_merge(['goal' => $goal], $progress);
        });
    }

    /**
     * Get goal progress data.
     *
     * @param  int  $goalId  The financial goal ID
     */
    public function getGoalProgress(int $goalId): array
    {
        return $this->repository->getGoalProgress($goalId);
    }

    /**
     * Get upcoming goals (due within a specific period).
     *
     * @param  int  $userId  The user ID
     * @param  int  $days  Number of days to look ahead
     */
    public function getUpcomingGoals(int $userId, int $days = 30): Collection
    {
        return $this->repository->getGoalsDueWithin($userId, $days);
    }

    /**
     * Get financial goals summary.
     *
     * @param  int  $userId  The user ID
     */
    public function getGoalsSummary(int $userId): array
    {
        $activeGoals = $this->repository->getActiveGoals($userId);
        $completedGoals = $this->repository->getAllForUser($userId)->filter(function ($goal) {
            return $goal->is_completed;
        });

        $totalTargetAmount = $activeGoals->sum('target_amount');
        $totalCurrentAmount = $activeGoals->sum('current_amount');
        $totalRemaining = max(0, $totalTargetAmount - $totalCurrentAmount);

        $overallProgress = $totalTargetAmount > 0
            ? min(100, round(($totalCurrentAmount / $totalTargetAmount) * 100, 2))
            : 0;

        return [
            'total_goals' => $activeGoals->count(),
            'completed_goals' => $completedGoals->count(),
            'total_target_amount' => $totalTargetAmount,
            'total_current_amount' => $totalCurrentAmount,
            'total_remaining' => $totalRemaining,
            'overall_progress' => $overallProgress,
        ];
    }

    /**
     * Get goal type options.
     */
    public function getGoalTypeOptions(): array
    {
        return [
            'saving' => 'Saving',
            'debt_repayment' => 'Debt Repayment',
            'investment' => 'Investment',
            'purchase' => 'Major Purchase',
            'emergency_fund' => 'Emergency Fund',
            'education' => 'Education',
            'retirement' => 'Retirement',
            'other' => 'Other',
        ];
    }
}
