<?php

namespace App\Console\Commands;

use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessRecurringTransactions extends Command
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
    protected $description = 'Process due recurring transactions and create actual transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->option('date');
        
        if ($date) {
            try {
                // Validate the date format
                $date = Carbon::createFromFormat('Y-m-d', $date)->toDateString();
            } catch (\Exception $e) {
                $this->error('Invalid date format. Please use Y-m-d (e.g., 2025-04-21)');
                return 1;
            }
        } else {
            $date = now()->toDateString();
        }

        $this->info("Processing recurring transactions for {$date}...");

        // Get all active recurring transactions that are due
        $dueRecurringTransactions = RecurringTransaction::where('status', 'active')
            ->where('next_due_date', '<=', $date)
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->with(['user', 'category'])
            ->get();

        $processed = 0;
        $failed = 0;
        $details = [];

        foreach ($dueRecurringTransactions as $recurringTransaction) {
            try {
                // Generate a new transaction
                $transaction = Transaction::create([
                    'user_id' => $recurringTransaction->user_id,
                    'description' => $recurringTransaction->description,
                    'amount' => $recurringTransaction->amount,
                    'type' => $recurringTransaction->type,
                    'category_id' => $recurringTransaction->category_id,
                    'transaction_date' => $date,
                ]);

                // Calculate the next due date
                $nextDueDate = $this->calculateNextDueDate($recurringTransaction);
                
                // Update the recurring transaction with new dates
                $recurringTransaction->update([
                    'last_processed_date' => $date,
                    'next_due_date' => $nextDueDate->toDateString(),
                ]);

                // Check if we should mark as completed (if end_date has been reached)
                if ($recurringTransaction->end_date && $nextDueDate->toDateString() > $recurringTransaction->end_date) {
                    $recurringTransaction->update(['status' => 'completed']);
                }

                $processed++;
                $details[] = [
                    'id' => $recurringTransaction->id,
                    'description' => $recurringTransaction->description,
                    'status' => 'success',
                    'transaction_id' => $transaction->id,
                ];
            } catch (\Exception $e) {
                $failed++;
                $details[] = [
                    'id' => $recurringTransaction->id,
                    'description' => $recurringTransaction->description,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
                
                Log::error("Failed to process recurring transaction: {$e->getMessage()}", [
                    'recurring_transaction_id' => $recurringTransaction->id,
                    'description' => $recurringTransaction->description,
                ]);
            }
        }

        $this->info("Processed {$processed} recurring transactions successfully.");
        
        if ($failed > 0) {
            $this->warn("{$failed} recurring transactions failed to process.");
        }

        // Display details of processed transactions
        if (!empty($details)) {
            $this->newLine();
            $this->info('Transaction Details:');
            
            $tableRows = [];
            foreach ($details as $detail) {
                $tableRows[] = [
                    $detail['id'],
                    $detail['description'],
                    $detail['status'],
                    $detail['status'] === 'success' ? $detail['transaction_id'] : ($detail['error'] ?? 'Unknown error'),
                ];
            }
            
            $this->table(
                ['ID', 'Description', 'Status', 'Result'],
                $tableRows
            );
        }

        return 0;
    }

    /**
     * Calculate the next occurrence date based on frequency and current date.
     */
    private function calculateNextDueDate(RecurringTransaction $recurringTransaction): Carbon
    {
        $fromDate = $recurringTransaction->next_due_date ?? $recurringTransaction->start_date;
        
        return match ($recurringTransaction->frequency) {
            'daily' => Carbon::parse($fromDate)->addDay(),
            'weekly' => Carbon::parse($fromDate)->addWeek(),
            'biweekly' => Carbon::parse($fromDate)->addWeeks(2),
            'monthly' => Carbon::parse($fromDate)->addMonth(),
            'quarterly' => Carbon::parse($fromDate)->addMonths(3),
            'annually' => Carbon::parse($fromDate)->addYear(),
            default => Carbon::parse($fromDate)->addMonth(),
        };
    }
}
