<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\RecurringTransaction;
use App\Models\User; // Assuming categories might be used
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RecurringTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::where('email', 'admin@example.com')->first();

        if (! $adminUser) {
            $this->command->error('Admin user (admin@example.com) not found. Cannot seed Recurring Transactions.');

            return;
        }

        $mainBankAccount = BankAccount::where('user_id', $adminUser->id)
            ->where('account_number', '123456789') // From BankAccountSeeder
            ->first();

        $rentExpenseCoa = ChartOfAccount::where('user_id', $adminUser->id)
            ->where('account_code', '6200') // Rent Expense from ChartOfAccountSeeder
            ->first();

        $utilitiesExpenseCoa = ChartOfAccount::where('user_id', $adminUser->id)
            ->where('account_code', '6300') // Utilities from ChartOfAccountSeeder
            ->first();

        $internetExpenseCoa = ChartOfAccount::where('user_id', $adminUser->id)
            ->where('account_code', '6400') // Telephone & Internet from ChartOfAccountSeeder
            ->first();

        // Assuming a generic 'Housing' category for rent, and 'Utilities' for others
        // You might need to run CategorySeeder first or ensure these exist.
        $housingCategory = Category::where('user_id', $adminUser->id)->where('name', 'Housing')->first();
        $utilitiesCategory = Category::where('user_id', $adminUser->id)->where('name', 'Utilities')->first();

        if (! $mainBankAccount) {
            $this->command->warn('Main bank account not found for admin. Skipping some recurring transactions.');
        }
        if (! $rentExpenseCoa) {
            $this->command->warn('Rent expense COA (6200) not found for admin. Skipping rent recurring transaction.');
        }
        if (! $utilitiesExpenseCoa) {
            $this->command->warn('Utilities expense COA (6300) not found for admin. Skipping utilities recurring transaction.');
        }
        if (! $internetExpenseCoa) {
            $this->command->warn('Internet expense COA (6400) not found for admin. Skipping internet recurring transaction.');
        }
        if (! $housingCategory) {
            $this->command->warn('Housing category not found for admin. Rent transaction will have no category.');
        }
        if (! $utilitiesCategory) {
            $this->command->warn('Utilities category not found for admin. Utilities/Internet transactions will have no category.');
        }

        if ($mainBankAccount && $rentExpenseCoa) {
            RecurringTransaction::factory()->create([
                'user_id' => $adminUser->id,
                'bank_account_id' => $mainBankAccount->id,
                'chart_of_account_id' => $rentExpenseCoa->id,
                'category_id' => $housingCategory->id ?? null,
                'type' => 'expense',
                'amount' => 2000.00,
                'frequency' => 'monthly',
                'start_date' => Carbon::now()->startOfMonth(),
                'next_due_date' => Carbon::now()->startOfMonth(),
                'description' => 'Monthly Apartment Rent',
                'is_active' => true,
            ]);
        }

        if ($mainBankAccount && $utilitiesExpenseCoa) {
            RecurringTransaction::factory()->create([
                'user_id' => $adminUser->id,
                'bank_account_id' => $mainBankAccount->id,
                'chart_of_account_id' => $utilitiesExpenseCoa->id,
                'category_id' => $utilitiesCategory->id ?? null,
                'type' => 'expense',
                'amount' => 150.00,
                'frequency' => 'monthly',
                'start_date' => Carbon::now()->startOfMonth()->addDays(5),
                'next_due_date' => Carbon::now()->startOfMonth()->addDays(5),
                'description' => 'Monthly Utilities (Hydro, Water)',
                'is_active' => true,
            ]);
        }

        if ($mainBankAccount && $internetExpenseCoa) {
            RecurringTransaction::factory()->create([
                'user_id' => $adminUser->id,
                'bank_account_id' => $mainBankAccount->id,
                'chart_of_account_id' => $internetExpenseCoa->id,
                'category_id' => $utilitiesCategory->id ?? null, // Can also be its own 'Internet' category
                'type' => 'expense',
                'amount' => 80.00,
                'frequency' => 'monthly',
                'start_date' => Carbon::now()->startOfMonth()->addDays(10),
                'next_due_date' => Carbon::now()->startOfMonth()->addDays(10),
                'description' => 'Monthly Internet Bill',
                'is_active' => true,
            ]);
        }

        $this->command->info('Recurring transactions seeded for admin user (if dependencies were found).');
    }
}
