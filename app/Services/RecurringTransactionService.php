<?php

namespace App\Services;

use App\Contracts\Repositories\RecurringTransactionRepositoryInterface;
use App\Models\RecurringTransaction;
use Illuminate\Database\Eloquent\Collection;

class RecurringTransactionService
{
    /**
     * The recurring transaction repository instance.
     */
    protected $recurringTransactionRepository;

    /**
     * The transaction service instance.
     */
    protected $transactionService;

    /**
     * Create a new service instance.
     */
    public function __construct(
        RecurringTransactionRepositoryInterface $recurringTransactionRepository,
        TransactionService $transactionService
    ) {
        $this->recurringTransactionRepository = $recurringTransactionRepository;
        $this->transactionService = $transactionService;
    }

    /**
     * Create a new recurring transaction.
     */
    public function createRecurringTransaction(array $data): RecurringTransaction
    {
        // Ensure next_due_date is set if not provided
        if (! isset($data['next_due_date'])) {
            $data['next_due_date'] = $data['start_date'];
        }

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
     * Process due recurring transactions.
     */
    public function processDueRecurringTransactions(?string $date = null): array
    {
        $date = $date ?? now()->toDateString();
        $dueRecurringTransactions = $this->recurringTransactionRepository->getDueRecurringTransactions($date);

        $results = [
            'processed' => 0,
            'failed' => 0,
            'details' => [],
        ];

        foreach ($dueRecurringTransactions as $recurringTransaction) {
            try {
                // Generate a new transaction
                $transactionData = [
                    'user_id' => $recurringTransaction->user_id,
                    'description' => $recurringTransaction->description,
                    'amount' => $recurringTransaction->amount,
                    'type' => $recurringTransaction->type,
                    'category_id' => $recurringTransaction->category_id,
                    'transaction_date' => $date,
                ];

                $transaction = $this->transactionService->createTransaction($transactionData);

                // Calculate the next due date
                $nextDueDate = $recurringTransaction->calculateNextDueDate($recurringTransaction->next_due_date);

                // Update the recurring transaction with new dates
                $this->recurringTransactionRepository->markAsProcessed(
                    $recurringTransaction,
                    $date,
                    $nextDueDate->toDateString()
                );

                // Check if we should mark as completed (if end_date has been reached)
                if ($recurringTransaction->end_date && $nextDueDate->toDateString() > $recurringTransaction->end_date) {
                    $this->recurringTransactionRepository->updateInstance($recurringTransaction, ['status' => 'completed']);
                }

                $results['processed']++;
                $results['details'][] = [
                    'id' => $recurringTransaction->id,
                    'description' => $recurringTransaction->description,
                    'status' => 'success',
                    'transaction_id' => $transaction->id,
                ];
            } catch (\Exception $e) {
                $results['failed']++;
                $results['details'][] = [
                    'id' => $recurringTransaction->id,
                    'description' => $recurringTransaction->description,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get a recurring transaction by ID.
     *
     * @param  int|string  $id
     */
    public function findRecurringTransaction($id): ?RecurringTransaction
    {
        return $this->recurringTransactionRepository->find($id);
    }

    /**
     * Toggle the status of a recurring transaction.
     */
    public function toggleStatus(RecurringTransaction $recurringTransaction): bool
    {
        $newStatus = $recurringTransaction->status === 'active' ? 'paused' : 'active';

        return $this->recurringTransactionRepository->updateInstance($recurringTransaction, ['status' => $newStatus]);
    }
}
