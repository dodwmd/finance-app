<?php

namespace App\Contracts\Repositories;

use App\Models\RecurringTransaction;
use Illuminate\Database\Eloquent\Collection;

interface RecurringTransactionRepositoryInterface
{
    /**
     * Create a new recurring transaction.
     */
    public function create(array $data): RecurringTransaction;

    /**
     * Update a recurring transaction.
     */
    public function update(RecurringTransaction $recurringTransaction, array $data): bool;

    /**
     * Get all recurring transactions for a user.
     */
    public function getByUserId(int $userId): Collection;

    /**
     * Get active recurring transactions that are due.
     */
    public function getDueRecurringTransactions(?string $date = null): Collection;

    /**
     * Mark a recurring transaction as processed.
     */
    public function markAsProcessed(RecurringTransaction $recurringTransaction, string $processedDate, string $nextDueDate): bool;

    /**
     * Get a recurring transaction by ID.
     */
    public function find(int $id): ?RecurringTransaction;
}
