<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\FinancialGoal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FinancialGoalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            // Get categories for user (income categories for saving goals)
            $categories = Category::where('user_id', $user->id)->get();

            if ($categories->isEmpty()) {
                $this->command->info("No categories found for user {$user->id}. Skipping financial goal seeding for this user.");

                continue;
            }

            // Get one income category for savings goals
            $savingsCategory = $categories->where('type', 'income')->first();

            // Get one expense category for debt/purchase goals
            $expenseCategory = $categories->where('type', 'expense')->first();

            // If we don't have both types, just use what we have
            $savingsCategory = $savingsCategory ?? $categories->first();
            $expenseCategory = $expenseCategory ?? $categories->first();

            // Create an emergency fund goal
            FinancialGoal::create([
                'user_id' => $user->id,
                'category_id' => $savingsCategory->id,
                'name' => 'Emergency Fund',
                'target_amount' => 5000.00,
                'current_amount' => 1000.00,
                'type' => 'saving',
                'start_date' => Carbon::now()->startOfMonth(),
                'target_date' => Carbon::now()->addMonths(6),
                'description' => 'Building an emergency fund to cover 3-6 months of expenses.',
                'is_active' => true,
                'is_completed' => false,
            ]);

            // Create a vacation goal
            FinancialGoal::create([
                'user_id' => $user->id,
                'category_id' => $savingsCategory->id,
                'name' => 'Dream Vacation',
                'target_amount' => 3000.00,
                'current_amount' => 500.00,
                'type' => 'saving',
                'start_date' => Carbon::now()->startOfMonth(),
                'target_date' => Carbon::now()->addYear(),
                'description' => 'Saving for a vacation next year.',
                'is_active' => true,
                'is_completed' => false,
            ]);

            // Create a debt payment goal
            FinancialGoal::create([
                'user_id' => $user->id,
                'category_id' => $expenseCategory->id,
                'name' => 'Pay Off Credit Card',
                'target_amount' => 2500.00,
                'current_amount' => 800.00,
                'type' => 'debt',
                'start_date' => Carbon::now()->startOfMonth(),
                'target_date' => Carbon::now()->addMonths(8),
                'description' => 'Goal to pay off remaining credit card debt.',
                'is_active' => true,
                'is_completed' => false,
            ]);
        }
    }
}
