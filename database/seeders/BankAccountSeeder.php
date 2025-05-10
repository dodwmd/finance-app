<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\User;
use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::where('email', 'admin@example.com')->first();

        if (! $adminUser) {
            $this->command->error('Admin user (admin@example.com) not found. Cannot seed Bank Accounts.');

            return;
        }

        // Find asset COA accounts to link bank accounts to
        $cashCoa = ChartOfAccount::where('user_id', $adminUser->id)
            ->where('type', 'asset') // Ensure lowercase
            ->where('system_account_tag', 'default_bank_current') // Using the tag from ChartOfAccountSeeder
            ->first();

        $savingsCoa = ChartOfAccount::where('user_id', $adminUser->id)
            ->where('type', 'asset') // Ensure lowercase
            ->where('account_code', '1113') // Referring to 'Bank Savings Account' from COA seeder
            ->first();

        if (! $cashCoa) {
            $this->command->warn('Default current bank COA (system_account_tag: default_bank_current) not found for admin. Cannot seed primary bank account.');
        } else {
            BankAccount::factory()->create([
                'user_id' => $adminUser->id,
                'chart_of_account_id' => $cashCoa->id,
                'account_name' => 'Main Chequing Account',
                'account_number' => '123456789',
                'bank_name' => 'Vibe National Bank',
                'branch_name' => 'Main Branch',
                'account_type' => 'chequing',
                'currency' => 'CAD', // Default currency
                'current_balance' => 15000.75,
                'is_active' => true,
            ]);
        }

        if (! $savingsCoa) {
            $this->command->warn('Savings bank COA (account_code: 1113) not found for admin. Cannot seed savings account.');
        } else {
            BankAccount::factory()->create([
                'user_id' => $adminUser->id,
                'chart_of_account_id' => $savingsCoa->id,
                'account_name' => 'High-Interest Savings',
                'account_number' => '987654321',
                'bank_name' => 'Vibe Trust',
                'branch_name' => 'Online Division',
                'account_type' => 'savings',
                'currency' => 'CAD',
                'current_balance' => 7500.50,
                'is_active' => true,
            ]);
        }

        $this->command->info('Bank accounts seeded for admin user (if COAs were found).');
    }
}
