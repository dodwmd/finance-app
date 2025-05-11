<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\RecurringTransactionService;
use App\Services\TransactionService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ProcessRecurringTransactionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-recurring-transactions {--date= : The date to process transactions for (format: Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process recurring transactions that are due';

    /**
     * The recurring transaction service.
     */
    protected $recurringTransactionService;

    /**
     * The transaction service.
     */
    protected $transactionService;

    /**
     * Create a new command instance.
     */
    public function __construct(
        RecurringTransactionService $recurringTransactionService,
        TransactionService $transactionService
    ) {
        parent::__construct();
        $this->recurringTransactionService = $recurringTransactionService;
        $this->transactionService = $transactionService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date'))->toDateString() : null;
        $dueTransactions = $this->recurringTransactionService->getDueRecurringTransactions($date);

        $this->info("Found {$dueTransactions->count()} recurring transactions due for processing");

        if ($dueTransactions->isEmpty()) {
            $this->info('No recurring transactions to process');

            return 0;
        }

        $processedCount = 0;

        foreach ($dueTransactions as $recurringTransaction) {
            $this->info("Processing recurring transaction: {$recurringTransaction->description}");

            // Check if the transaction has ended
            if ($this->recurringTransactionService->hasEnded($recurringTransaction)) {
                $this->warn("Recurring transaction {$recurringTransaction->id} has ended");

                continue;
            }

            // Create a transaction from the recurring transaction
            $transaction = $this->createTransactionFromRecurring($recurringTransaction);

            // Process the recurring transaction
            $processed = $this->recurringTransactionService->processRecurringTransaction($recurringTransaction);

            if ($processed) {
                $processedCount++;
                $this->info("Created transaction {$transaction->id} and updated next due date to {$recurringTransaction->next_due_date->format('Y-m-d')}");
            } else {
                $this->error("Failed to process recurring transaction {$recurringTransaction->id}");
            }
        }

        $this->info("Successfully processed {$processedCount} recurring transactions");

        return 0;
    }

    /**
     * Create a transaction from a recurring transaction.
     *
     * @param  \App\Models\RecurringTransaction  $recurringTransaction
     */
    protected function createTransactionFromRecurring($recurringTransaction): Transaction
    {
        $data = [
            'user_id' => $recurringTransaction->user_id,
            'description' => $recurringTransaction->description,
            'amount' => $recurringTransaction->amount,
            'type' => $recurringTransaction->type,
            'category_id' => $recurringTransaction->category_id,
            'transaction_date' => Carbon::now()->toDateString(),
            'notes' => "Automatically created from recurring transaction #{$recurringTransaction->id}",
        ];

        return $this->transactionService->createTransaction($data);
    }
}
