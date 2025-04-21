<?php

namespace App\Services;

use App\Contracts\Repositories\RecurringTransactionRepositoryInterface;
use App\Models\RecurringTransaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class RecurringTransactionService
{
    /**
     * The recurring transaction repository instance.
     */
    protected $recurringTransactionRepository;

    /**
     * Create a new service instance.
     */
    public function __construct(RecurringTransactionRepositoryInterface $recurringTransactionRepository)
    {
        $this->recurringTransactionRepository = $recurringTransactionRepository;
    }

    /**
     * Create a new recurring transaction.
     */
    public function createRecurringTransaction(array $data): RecurringTransaction
    {
        return $this->recurringTransactionRepository->create($data);
    }

    /**
     * Update a recurring transaction.
     */
    public function updateRecurringTransaction(RecurringTransaction $recurringTransaction, array $data): bool
    {
        return $this->recurringTransactionRepository->updateInstance($recurringTransaction, $data);
    }

    /**
     * Get all recurring transactions for a user.
     */
    public function getUserRecurringTransactions(int $userId): Collection
    {
        return $this->recurringTransactionRepository->getByUserId($userId);
    }

    /**
     * Get recurring transactions that are due.
     */
    public function getDueRecurringTransactions(?string $date = null): Collection
    {
        return $this->recurringTransactionRepository->getDueRecurringTransactions($date);
    }

    /**
     * Process a recurring transaction.
     */
    public function processRecurringTransaction(RecurringTransaction $recurringTransaction): bool
    {
        $currentDate = Carbon::now()->toDateString();
        $nextDueDate = $this->calculateNextDueDate($recurringTransaction);

        return $this->recurringTransactionRepository->markAsProcessed($recurringTransaction, $currentDate, $nextDueDate);
    }

    /**
     * Calculate the next due date for a recurring transaction.
     */
    protected function calculateNextDueDate(RecurringTransaction $recurringTransaction): string
    {
        $currentDueDate = Carbon::parse($recurringTransaction->next_due_date);

        switch ($recurringTransaction->frequency) {
            case 'daily':
                return $currentDueDate->addDay()->toDateString();
            case 'weekly':
                return $currentDueDate->addWeek()->toDateString();
            case 'biweekly':
                return $currentDueDate->addWeeks(2)->toDateString();
            case 'monthly':
                return $currentDueDate->addMonth()->toDateString();
            case 'quarterly':
                return $currentDueDate->addMonths(3)->toDateString();
            case 'annually':
                return $currentDueDate->addYear()->toDateString();
            default:
                return $currentDueDate->addMonth()->toDateString();
        }
    }

    /**
     * Toggle the status of a recurring transaction.
     */
    public function toggleStatus(RecurringTransaction $recurringTransaction): bool
    {
        $newStatus = $recurringTransaction->status === 'active' ? 'paused' : 'active';

        return $this->recurringTransactionRepository->updateInstance($recurringTransaction, [
            'status' => $newStatus,
        ]);
    }

    /**
     * Check if the recurring transaction has ended.
     */
    public function hasEnded(RecurringTransaction $recurringTransaction): bool
    {
        if (empty($recurringTransaction->end_date)) {
            return false;
        }

        $endDate = Carbon::parse($recurringTransaction->end_date);
        $today = Carbon::today();

        return $today->greaterThan($endDate);
    }
}
