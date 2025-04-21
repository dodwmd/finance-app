<?php

namespace App\Repositories;

use App\Contracts\Repositories\BudgetRepositoryInterface;
use App\Models\Budget;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Repository implementation for Budget operations.
 */
class BudgetRepository implements BudgetRepositoryInterface
{
    /**
     * Budget model instance.
     *
     * @var Budget
     */
    protected $model;

    /**
     * Create a new repository instance.
     */
    public function __construct(Budget $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllForUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->where('user_id', $userId)
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getById(int $id): ?Budget
    {
        return $this->model->with('category')->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Budget
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update(int $id, array $data): ?Budget
    {
        $budget = $this->model->find($id);
        if (! $budget) {
            return null;
        }

        $budget->update($data);

        return $budget->fresh();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): bool
    {
        $budget = $this->model->find($id);
        if (! $budget) {
            return false;
        }

        return $budget->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveBudgets(int $userId, ?string $period = null): Collection
    {
        $query = $this->model->where('user_id', $userId)
            ->where('is_active', true)
            ->with('category');

        if ($period) {
            $query->where('period', $period);
        }

        return $query->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getBudgetProgress(int $budgetId): array
    {
        $budget = $this->model->with('category')->findOrFail($budgetId);
        $startDate = $budget->start_date;
        $endDate = $budget->end_date ?? $this->calculateEndDateFromPeriod($budget->start_date, $budget->period);

        // Get total spent for the budget's category within date range
        $spent = $this->calculateSpentAmount($budget->user_id, $budget->category_id, $startDate, $endDate);

        // Calculate progress percentage
        $percentage = $budget->amount > 0 ? min(100, round(($spent / $budget->amount) * 100, 2)) : 0;

        return [
            'budget' => $budget,
            'spent' => $spent,
            'remaining' => max(0, $budget->amount - $spent),
            'percentage' => $percentage,
            'is_exceeded' => $spent > $budget->amount,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentBudgets(int $userId): Collection
    {
        $today = Carbon::today()->toDateString();

        return $this->model->where('user_id', $userId)
            ->where('is_active', true)
            ->where('start_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $today);
            })
            ->with('category')
            ->get();
    }

    /**
     * Calculate the end date based on the start date and period.
     *
     * @param  string  $startDate  The start date
     * @param  string  $period  The period (monthly, quarterly, yearly)
     * @return string The calculated end date
     */
    private function calculateEndDateFromPeriod(string $startDate, string $period): string
    {
        $date = Carbon::parse($startDate);

        return match ($period) {
            'monthly' => $date->copy()->addMonth()->subDay()->toDateString(),
            'quarterly' => $date->copy()->addMonths(3)->subDay()->toDateString(),
            'yearly' => $date->copy()->addYear()->subDay()->toDateString(),
            default => $date->copy()->addMonth()->subDay()->toDateString(),
        };
    }

    /**
     * Calculate amount spent for a category within a date range.
     *
     * @param  int  $userId  The user ID
     * @param  int|null  $categoryId  The category ID
     * @param  string  $startDate  The start date
     * @param  string  $endDate  The end date
     * @return float The amount spent
     */
    private function calculateSpentAmount(int $userId, ?int $categoryId, string $startDate, string $endDate): float
    {
        $query = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->sum('amount');
    }
}
