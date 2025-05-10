<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the specific admin user
        $adminUser = User::where('email', 'admin@example.com')->first();

        if (! $adminUser) {
            $this->command->error('Admin user (admin@example.com) not found. Run DatabaseSeeder first or create the user manually.');

            return;
        }

        // Check if the admin user already has COA entries
        if ($adminUser->chartOfAccounts()->count() > 0) {
            $this->command->info("Admin user {$adminUser->email} already has a Chart of Accounts. Skipping ChartOfAccountSeeder.");

            return;
        }

        $standardCoa = [
            // Assets (1000-1999)
            ['code_prefix' => '1', 'name' => 'Assets', 'type' => 'asset', 'children' => [
                ['code' => '1100', 'name' => 'Current Assets', 'type' => 'asset', 'children' => [
                    ['code' => '1110', 'name' => 'Cash and Cash Equivalents', 'type' => 'asset', 'children' => [
                        ['code' => '1111', 'name' => 'Petty Cash', 'type' => 'asset', 'allow_direct_posting' => true],
                        ['code' => '1112', 'name' => 'Bank Current Account', 'type' => 'asset', 'allow_direct_posting' => true, 'system_account_tag' => 'default_bank_current'],
                        ['code' => '1113', 'name' => 'Bank Savings Account', 'type' => 'asset', 'allow_direct_posting' => true],
                    ]],
                    ['code' => '1120', 'name' => 'Accounts Receivable', 'type' => 'asset', 'allow_direct_posting' => true, 'system_account_tag' => 'accounts_receivable'],
                    ['code' => '1130', 'name' => 'Inventory', 'type' => 'asset', 'allow_direct_posting' => true],
                    ['code' => '1140', 'name' => 'Prepaid Expenses', 'type' => 'asset', 'allow_direct_posting' => true],
                ]],
                ['code' => '1200', 'name' => 'Non-Current Assets', 'type' => 'asset', 'children' => [
                    ['code' => '1210', 'name' => 'Property, Plant, and Equipment', 'type' => 'asset', 'allow_direct_posting' => true],
                    ['code' => '1220', 'name' => 'Accumulated Depreciation', 'type' => 'asset', 'allow_direct_posting' => true, 'system_account_tag' => 'accumulated_depreciation'], // Contra-asset
                    ['code' => '1230', 'name' => 'Intangible Assets', 'type' => 'asset', 'allow_direct_posting' => true],
                ]],
            ]],
            // Liabilities (2000-2999)
            ['code_prefix' => '2', 'name' => 'Liabilities', 'type' => 'liability', 'children' => [
                ['code' => '2100', 'name' => 'Current Liabilities', 'type' => 'liability', 'children' => [
                    ['code' => '2110', 'name' => 'Accounts Payable', 'type' => 'liability', 'allow_direct_posting' => true, 'system_account_tag' => 'accounts_payable'],
                    ['code' => '2120', 'name' => 'Credit Cards Payable', 'type' => 'liability', 'allow_direct_posting' => true, 'system_account_tag' => 'default_credit_card'],
                    ['code' => '2130', 'name' => 'GST Payable', 'type' => 'liability', 'allow_direct_posting' => true, 'system_account_tag' => 'gst_payable'],
                    ['code' => '2140', 'name' => 'PAYG Withholdings Payable', 'type' => 'liability', 'allow_direct_posting' => true, 'system_account_tag' => 'payg_withholding_payable'],
                    ['code' => '2150', 'name' => 'Superannuation Payable', 'type' => 'liability', 'allow_direct_posting' => true, 'system_account_tag' => 'superannuation_payable'],
                    ['code' => '2160', 'name' => 'Accrued Expenses', 'type' => 'liability', 'allow_direct_posting' => true],
                ]],
                ['code' => '2200', 'name' => 'Non-Current Liabilities', 'type' => 'liability', 'children' => [
                    ['code' => '2210', 'name' => 'Loans Payable (Long Term)', 'type' => 'liability', 'allow_direct_posting' => true],
                ]],
            ]],
            // Equity (3000-3999)
            ['code_prefix' => '3', 'name' => 'Equity', 'type' => 'equity', 'children' => [
                ['code' => '3100', 'name' => 'Owner\'s Equity', 'type' => 'equity', 'allow_direct_posting' => true, 'system_account_tag' => 'owners_equity'],
                ['code' => '3200', 'name' => 'Owner\'s Drawings', 'type' => 'equity', 'allow_direct_posting' => true, 'system_account_tag' => 'owners_drawings'],
                ['code' => '3300', 'name' => 'Retained Earnings', 'type' => 'equity', 'allow_direct_posting' => true, 'system_account_tag' => 'retained_earnings'],
            ]],
            // Revenue (4000-4999)
            ['code_prefix' => '4', 'name' => 'Revenue', 'type' => 'revenue', 'children' => [
                ['code' => '4100', 'name' => 'Sales Revenue', 'type' => 'revenue', 'allow_direct_posting' => true],
                ['code' => '4200', 'name' => 'Service Revenue', 'type' => 'revenue', 'allow_direct_posting' => true],
                ['code' => '4300', 'name' => 'Interest Income', 'type' => 'revenue', 'allow_direct_posting' => true],
            ]],
            // Cost of Goods Sold (5000-5999)
            ['code_prefix' => '5', 'name' => 'Cost of Goods Sold', 'type' => 'costofgoodssold', 'children' => [
                ['code' => '5100', 'name' => 'Purchases', 'type' => 'costofgoodssold', 'allow_direct_posting' => true],
                ['code' => '5200', 'name' => 'Freight In', 'type' => 'costofgoodssold', 'allow_direct_posting' => true],
            ]],
            // Expenses (6000-9999) - Grouped for better organization
            ['code_prefix' => '6', 'name' => 'Operating Expenses', 'type' => 'expense', 'children' => [
                ['code' => '6100', 'name' => 'Wages & Salaries', 'type' => 'expense', 'allow_direct_posting' => true],
                ['code' => '6150', 'name' => 'Superannuation Expense', 'type' => 'expense', 'allow_direct_posting' => true],
                ['code' => '6200', 'name' => 'Rent Expense', 'type' => 'expense', 'allow_direct_posting' => true],
                ['code' => '6300', 'name' => 'Utilities', 'type' => 'expense', 'allow_direct_posting' => true],
                ['code' => '6400', 'name' => 'Telephone & Internet', 'type' => 'expense', 'allow_direct_posting' => true],
                ['code' => '6500', 'name' => 'Office Supplies', 'type' => 'expense', 'allow_direct_posting' => true],
                ['code' => '6600', 'name' => 'Advertising & Marketing', 'type' => 'expense', 'allow_direct_posting' => true],
                ['code' => '6700', 'name' => 'Bank Fees', 'type' => 'expense', 'allow_direct_posting' => true],
                ['code' => '6800', 'name' => 'Depreciation Expense', 'type' => 'expense', 'allow_direct_posting' => true],
                ['code' => '6900', 'name' => 'Insurance Expense', 'type' => 'expense', 'allow_direct_posting' => true],
                ['code' => '7100', 'name' => 'Repairs & Maintenance', 'type' => 'expense', 'allow_direct_posting' => true],
                ['code' => '7200', 'name' => 'Travel Expense', 'type' => 'expense', 'allow_direct_posting' => true],
                ['code' => '7300', 'name' => 'Accounting & Legal Fees', 'type' => 'expense', 'allow_direct_posting' => true],
                ['code' => '9000', 'name' => 'Miscellaneous Expense', 'type' => 'expense', 'allow_direct_posting' => true],
            ]],
        ];

        DB::transaction(function () use ($adminUser, $standardCoa) {
            $this->command->info("Seeding Chart of Accounts for admin user: {$adminUser->email}");
            foreach ($standardCoa as $topLevelAccountData) {
                $this->createAccountRecursive($adminUser, $topLevelAccountData, null);
            }
        });
    }

    /**
     * Recursively creates chart accounts.
     */
    private function createAccountRecursive(User $user, array $accountData, ?int $parentId): void
    {
        $accountCode = $accountData['code'] ?? ($accountData['code_prefix'].'000');

        // This check is now mostly redundant due to the check at the start of run(),
        // but kept as a safeguard for the recursive calls if structure implies potential overlap.
        $existing = ChartOfAccount::where('user_id', $user->id)->where('account_code', $accountCode)->first();
        if ($existing) {
            if (! empty($accountData['children'])) {
                foreach ($accountData['children'] as $childData) {
                    $this->createAccountRecursive($user, $childData, $existing->id);
                }
            }

            return;
        }

        $account = ChartOfAccount::create([
            'user_id' => $user->id,
            'account_code' => $accountCode,
            'name' => $accountData['name'],
            'type' => strtolower($accountData['type']), // Ensure type is lowercase
            'description' => $accountData['description'] ?? null,
            'parent_id' => $parentId,
            'is_active' => $accountData['is_active'] ?? true,
            'allow_direct_posting' => $accountData['allow_direct_posting'] ?? ($parentId === null ? false : true),
            'system_account_tag' => $accountData['system_account_tag'] ?? null,
        ]);

        if (! empty($accountData['children'])) {
            foreach ($accountData['children'] as $childData) {
                $this->createAccountRecursive($user, $childData, $account->id);
            }
        }
    }
}
