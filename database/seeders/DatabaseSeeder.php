<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Budget;
use App\Models\Category;
use App\Models\FinancialGoal;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
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
    }
}
