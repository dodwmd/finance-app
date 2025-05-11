<?php

namespace App\Services;

use App\Contracts\Repositories\BudgetRepositoryInterface;
use App\Models\Budget;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Service class for budget-related business logic.
 */
class BudgetService
{
    /**
     * @var BudgetRepositoryInterface
     */
    protected $budgetRepository;

    /**
     * Create a new BudgetService instance.
     */
    public function __construct(
        BudgetRepositoryInterface $budgetRepository
    ) {
        $this->budgetRepository = $budgetRepository;
    }

    /**
     * Get all budgets for a user.
     */
    public function getAllBudgets(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->budgetRepository->getAllForUser($userId, $perPage);
    }

    /**
     * Get a budget by ID.
     */
    public function getBudgetById(int $id): ?Budget
    {
        return $this->budgetRepository->getById($id);
    }

    /**
     * Create a new budget.
     */
    public function createBudget(array $data): Budget
    {
        // Set default end date based on period if not provided
        if (empty($data['end_date']) && ! empty($data['start_date']) && ! empty($data['period'])) {
            $data['end_date'] = $this->calculateEndDate($data['start_date'], $data['period']);
        }

        return $this->budgetRepository->create($data);
    }

    /**
     * Update an existing budget.
     */
    public function updateBudget(int $id, array $data): void
    {
        // Update end date if period or start date changed
        if (
            (isset($data['period']) || isset($data['start_date'])) &&
            (empty($data['end_date']) || isset($data['period']) || isset($data['start_date']))
        ) {
            $budget = $this->budgetRepository->getById($id);
            $startDate = $data['start_date'] ?? $budget->start_date;
            $period = $data['period'] ?? $budget->period;
            $data['end_date'] = $this->calculateEndDate($startDate, $period);
        }

        $this->budgetRepository->update($id, $data);
    }

    /**
     * Delete a budget by its ID.
     *
     * @param  int  $id  The ID of the budget to delete.
     * @return bool True if deletion was successful, false otherwise.
     *
     * @psalm-suppress PossiblyUnusedReturnValue
     */
    public function deleteBudget(int $id): bool
    {
        $this->budgetRepository->delete($id);

        return true;
    }

    /**
     * Get all active budgets with progress information.
     */
    public function getActiveBudgetsWithProgress(int $userId, ?string $period = null): Collection
    {
        $budgets = $this->budgetRepository->getActiveBudgets($userId, $period);

        return $budgets->map(function ($budget) {
            $progress = $this->budgetRepository->getBudgetProgress($budget->id);

            return array_merge(['budget' => $budget], $progress);
        });
    }

    /**
     * Get budget progress for a specific budget.
     *
     * @param  int  $budgetId  The budget ID
     * @return array Budget with progress data
     */
    public function getBudgetProgress(int $budgetId): array
    {
        return $this->budgetRepository->getBudgetProgress($budgetId);
    }

    /**
     * Get all current budgets (active and within date range) with progress.
     */
    public function getCurrentBudgetsWithProgress(int $userId): Collection
    {
        $budgets = $this->budgetRepository->getCurrentBudgets($userId);

        return $budgets->map(function ($budget) {
            $progress = $this->budgetRepository->getBudgetProgress($budget->id);

            return $progress;
        });
    }

    /**
     * Get budget overview statistics.
     */
    public function getBudgetOverview(int $userId): array
    {
        $currentBudgets = $this->getCurrentBudgetsWithProgress($userId);

        $totalBudgeted = $currentBudgets->sum('budget.amount');
        $totalSpent = $currentBudgets->sum('spent');
        $totalRemaining = $currentBudgets->sum('remaining');

        $exceededBudgets = $currentBudgets->filter(function ($budget) {
            return $budget['is_exceeded'];
        });

        $nearLimitBudgets = $currentBudgets->filter(function ($budget) {
            return ! $budget['is_exceeded'] && $budget['percentage'] >= 80;
        });

        return [
            'total_budgeted' => $totalBudgeted,
            'total_spent' => $totalSpent,
            'total_remaining' => $totalRemaining,
            'exceeded_count' => $exceededBudgets->count(),
            'near_limit_count' => $nearLimitBudgets->count(),
            'exceeded_budgets' => $exceededBudgets,
            'near_limit_budgets' => $nearLimitBudgets,
        ];
    }

    /**
     * Get available period options for budgets.
     */
    public function getPeriodOptions(): array
    {
        return [
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'yearly' => 'Yearly',
        ];
    }

    /**
     * Calculate end date based on start date and period.
     */
    private function calculateEndDate(string $startDate, string $period): string
    {
        $date = Carbon::parse($startDate);

        return match ($period) {
            'monthly' => $date->copy()->addMonth()->subDay()->toDateString(),
            'quarterly' => $date->copy()->addMonths(3)->subDay()->toDateString(),
            'yearly' => $date->copy()->addYear()->subDay()->toDateString(),
            default => $date->copy()->addMonth()->subDay()->toDateString(),
        };
    }
}
