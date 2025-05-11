<?php

namespace App\Repositories;

use App\Contracts\Repositories\FinancialGoalRepositoryInterface;
use App\Models\FinancialGoal;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Repository implementation for FinancialGoal operations.
 */
class FinancialGoalRepository implements FinancialGoalRepositoryInterface
{
    /**
     * FinancialGoal model instance.
     *
     * @var FinancialGoal
     */
    protected $model;

    /**
     * Create a new repository instance.
     */
    public function __construct(FinancialGoal $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
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
    #[\Override]
    public function getById(int $id): ?FinancialGoal
    {
        return $this->model->with('category')->find($id);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function create(array $data): FinancialGoal
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function update(int $id, array $data): ?FinancialGoal
    {
        $goal = $this->model->find($id);
        if (! $goal) {
            return null;
        }

        $goal->update($data);

        return $goal->fresh();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function delete(int $id): void
    {
        $goal = $this->model->findOrFail($id);
        $goal->delete();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function updateAmount(int $id, float $amount, bool $isIncrement = true): ?FinancialGoal
    {
        $goal = $this->model->find($id);
        if (! $goal) {
            return null;
        }

        if ($isIncrement) {
            $goal->current_amount += $amount;
        } else {
            $goal->current_amount = $amount;
        }

        // Check if goal is completed
        if ($goal->current_amount >= $goal->target_amount) {
            $goal->is_completed = true;
        }

        $goal->save();

        return $goal->fresh();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getActiveGoals(int $userId, ?string $type = null): Collection
    {
        $query = $this->model->where('user_id', $userId)
            ->where('is_active', true)
            ->with('category');

        if ($type) {
            $query->where('type', $type);
        }

        return $query->get();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getGoalsDueWithin(int $userId, int $days): Collection
    {
        $futureDate = now()->addDays($days)->toDateString();

        return $this->model->where('user_id', $userId)
            ->where('is_active', true)
            ->where('is_completed', false)
            ->where('target_date', '<=', $futureDate)
            ->with('category')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getGoalProgress(int $goalId): array
    {
        $goal = $this->model->with('category')->findOrFail($goalId);

        // Calculate days remaining
        $daysTotal = $goal->start_date->diffInDays($goal->target_date);
        $daysRemaining = now()->diffInDays($goal->target_date, false);
        $daysElapsed = $daysTotal - max(0, $daysRemaining);

        // Calculate time percentage
        $timePercentage = $daysTotal > 0 ? min(100, round(($daysElapsed / $daysTotal) * 100, 2)) : 0;

        // Calculate amount percentage
        $amountPercentage = $goal->target_amount > 0
            ? min(100, round(($goal->current_amount / $goal->target_amount) * 100, 2))
            : 0;

        return [
            'goal' => $goal,
            'current_amount' => $goal->current_amount,
            'remaining_amount' => max(0, $goal->target_amount - $goal->current_amount),
            'amount_percentage' => $amountPercentage,
            'time_percentage' => $timePercentage,
            'days_total' => $daysTotal,
            'days_remaining' => max(0, $daysRemaining),
            'days_elapsed' => $daysElapsed,
            'is_overdue' => $daysRemaining < 0 && ! $goal->is_completed,
            'is_on_track' => $amountPercentage >= $timePercentage,
        ];
    }
}
