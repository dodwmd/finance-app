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
    #[\Override]
    public function create(array $data): RecurringTransaction
    {
        return $this->model->create($data);
    }

    /**
     * Update a recurring transaction.
     *
     * @param  int|string  $id
     */
    #[\Override]
    public function update($id, array $data): RecurringTransaction
    {
        $record = $this->find($id);
        $record->update($data);

        return $record;
    }

    /**
     * Update a specific recurring transaction instance.
     */
    #[\Override]
    public function updateInstance(RecurringTransaction $recurringTransaction, array $data): bool
    {
        return $recurringTransaction->update($data);
    }

    /**
     * Get all recurring transactions for a user.
     */
    #[\Override]
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
    #[\Override]
    public function getDueRecurringTransactions(?string $date = null): Collection
    {
        // Ensure we're using a date string without time components
        $date = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();

        // Use raw date comparison to avoid issues with Carbon object comparison
        return $this->model->where('status', 'active')
            ->whereRaw('DATE(next_due_date) <= ?', [$date])
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')
                    ->orWhereRaw('DATE(end_date) >= ?', [$date]);
            })
            ->with(['user', 'category'])
            ->get();
    }

    /**
     * Mark a recurring transaction as processed.
     */
    #[\Override]
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
    #[\Override]
    public function find($id, array $columns = ['*']): ?RecurringTransaction
    {
        /** @var RecurringTransaction|null */
        return $this->model->with(['user', 'category'])->find($id, $columns);
    }
}
