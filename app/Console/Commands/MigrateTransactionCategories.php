<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MigrateTransactionCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-transaction-categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate transactions from string categories to category_id foreign keys';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting transaction category migration...');

        $this->info('1. Getting all distinct users with transactions...');
        $users = User::whereHas('transactions')->get();
        $this->info("Found {$users->count()} users with transactions");

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $totalTransactions = 0;
        $migratedTransactions = 0;

        foreach ($users as $user) {
            $this->migrateCategoriesForUser($user, $totalTransactions, $migratedTransactions);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Migration complete!');
        $this->info("Total transactions: {$totalTransactions}");
        $this->info("Successfully migrated transactions: {$migratedTransactions}");
        $this->info('Failed migrations: '.($totalTransactions - $migratedTransactions));

        return Command::SUCCESS;
    }

    /**
     * Migrate categories for a specific user's transactions.
     */
    private function migrateCategoriesForUser(User $user, &$totalTransactions, &$migratedTransactions): void
    {
        // Get all transactions for this user
        $transactions = Transaction::where('user_id', $user->id)->get();
        $totalTransactions += $transactions->count();

        if ($transactions->isEmpty()) {
            return;
        }

        // Get all categories for this user
        $categories = Category::where('user_id', $user->id)->get();

        // Create a default category for each type to use as fallback
        $defaultCategories = [];
        foreach (['income', 'expense', 'transfer'] as $type) {
            $defaultCategory = $categories->where('type', $type)->where('name', 'Other '.Str::ucfirst($type))->first();

            if (! $defaultCategory) {
                // Create default category if it doesn't exist
                $defaultCategory = Category::create([
                    'user_id' => $user->id,
                    'name' => 'Other '.Str::ucfirst($type),
                    'type' => $type,
                    'color' => '#607D8B',
                    'icon' => 'question-circle',
                ]);
            }

            $defaultCategories[$type] = $defaultCategory->id;
        }

        // Process each transaction
        foreach ($transactions as $transaction) {
            // Skip if already has category_id
            if ($transaction->category_id) {
                $migratedTransactions++;

                continue;
            }

            $oldCategoryName = strtolower($transaction->category);
            $transactionType = $transaction->type;

            // Try to find a matching category by name
            $category = $categories
                ->where('type', $transactionType)
                ->first(function ($category) use ($oldCategoryName) {
                    return strtolower($category->name) === $oldCategoryName ||
                           Str::contains(strtolower($category->name), $oldCategoryName);
                });

            // Use default category if no match found
            if (! $category) {
                $categoryId = $defaultCategories[$transactionType];
            } else {
                $categoryId = $category->id;
            }

            // Update the transaction
            $transaction->category_id = $categoryId;
            if ($transaction->save()) {
                $migratedTransactions++;
            }
        }
    }
}
