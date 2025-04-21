<?php

namespace App\Repositories;

use App\Contracts\Repositories\RecurringTransactionRepositoryInterface;
use App\Models\RecurringTransaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class RecurringTransactionRepository extends BaseRepository implements RecurringTransactionRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(RecurringTransaction $model)
    {
        parent::__construct($model);
    }

    /**
     * Create a new recurring transaction.
     */
    public function create(array $data): RecurringTransaction
    {
        return $this->model->create($data);
    }

    /**
     * Update a recurring transaction.
     */
    public function update(RecurringTransaction $recurringTransaction, array $data): bool
    {
        return $recurringTransaction->update($data);
    }

    /**
     * Get all recurring transactions for a user.
     */
    public function getByUserId(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->with('category')
            ->orderBy('next_due_date')
            ->get();
    }

    /**
     * Get active recurring transactions that are due.
     */
    public function getDueRecurringTransactions(?string $date = null): Collection
    {
        $date = $date ?? now()->toDateString();

        return $this->model->where('status', 'active')
            ->where('next_due_date', '<=', $date)
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->with(['user', 'category'])
            ->get();
    }

    /**
     * Mark a recurring transaction as processed.
     */
    public function markAsProcessed(RecurringTransaction $recurringTransaction, string $processedDate, string $nextDueDate): bool
    {
        return $recurringTransaction->update([
            'last_processed_date' => $processedDate,
            'next_due_date' => $nextDueDate,
        ]);
    }

    /**
     * Get a recurring transaction by ID.
     */
    public function find(int $id): ?RecurringTransaction
    {
        return $this->model->with(['user', 'category'])->find($id);
    }
}
