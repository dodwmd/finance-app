<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\BankAccount;
use App\Models\Budget;
use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\FinancialGoal;
use App\Models\RecurringTransaction as ModelsRecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a specific admin user if not exists
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'), // Consider a more secure default if this is for production seeding
                'email_verified_at' => now(),
            ]
        );

        // Only run the seeders if their respective tables are empty
        if (Category::count() === 0) {
            $this->call(CategorySeeder::class);
        } else {
            $this->command->info('Categories table is not empty, skipping CategorySeeder');
        }

        if (Transaction::count() === 0) {
            $this->call(TransactionSeeder::class);
        } else {
            $this->command->info('Transactions table is not empty, skipping TransactionSeeder');
        }

        if (Budget::count() === 0) {
            $this->call(BudgetSeeder::class);
        } else {
            $this->command->info('Budgets table is not empty, skipping BudgetSeeder');
        }

        if (FinancialGoal::count() === 0) {
            $this->call(FinancialGoalSeeder::class);
        } else {
            $this->command->info('Financial goals table is not empty, skipping FinancialGoalSeeder');
        }

        if (ChartOfAccount::count() === 0) {
            $this->call(ChartOfAccountSeeder::class);
        } else {
            $this->command->info('Chart of Accounts table is not empty, skipping ChartOfAccountSeeder');
        }

        if (BankAccount::count() === 0) {
            $this->call(BankAccountSeeder::class);
        } else {
            $this->command->info('Bank Accounts table is not empty, skipping BankAccountSeeder');
        }

        if (ModelsRecurringTransaction::count() === 0) {
            $this->call(RecurringTransactionSeeder::class);
        } else {
            $this->command->info('Recurring Transactions table is not empty, skipping RecurringTransactionSeeder');
        }
    }
}
