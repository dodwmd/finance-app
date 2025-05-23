<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BudgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('No users found. Skipping budget seeding.');

            return;
        }

        // Default expense categories to create if user has none
        $defaultExpenseCategories = [
            ['name' => 'Food', 'type' => 'expense', 'color' => '#4CAF50', 'icon' => 'utensils'],
            ['name' => 'Housing', 'type' => 'expense', 'color' => '#2196F3', 'icon' => 'home'],
            ['name' => 'Transportation', 'type' => 'expense', 'color' => '#FF9800', 'icon' => 'car'],
        ];

        // Default income category
        $defaultIncomeCategory = [
            'name' => 'Salary', 'type' => 'income', 'color' => '#4CAF50', 'icon' => 'money-bill',
        ];

        foreach ($users as $user) {
            // Get categories for this user
            $expenseCategories = Category::where('user_id', $user->id)
                ->where('type', 'expense')
                ->get();

            $incomeCategories = Category::where('user_id', $user->id)
                ->where('type', 'income')
                ->get();

            // Create default categories if none exist
            if ($expenseCategories->isEmpty()) {
                $this->command->info("Creating default expense categories for user {$user->id}");

                foreach ($defaultExpenseCategories as $category) {
                    $expenseCategories->push(Category::create([
                        'user_id' => $user->id,
                        'name' => $category['name'],
                        'type' => $category['type'],
                        'color' => $category['color'],
                        'icon' => $category['icon'],
                    ]));
                }
            }

            if ($incomeCategories->isEmpty()) {
                $this->command->info("Creating default income category for user {$user->id}");

                $incomeCategories->push(Category::create([
                    'user_id' => $user->id,
                    'name' => $defaultIncomeCategory['name'],
                    'type' => $defaultIncomeCategory['type'],
                    'color' => $defaultIncomeCategory['color'],
                    'icon' => $defaultIncomeCategory['icon'],
                ]));
            }

            // Create monthly budgets for expense categories
            foreach ($expenseCategories as $category) {
                $startDate = Carbon::now()->startOfMonth();
                $endDate = (clone $startDate)->addMonth()->subDay();

                Budget::create([
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                    'name' => "Monthly {$category->name} Budget",
                    'amount' => rand(50, 500),
                    'period' => 'monthly',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'is_active' => true,
                ]);
            }

            // Create a quarterly budget for a random expense category
            if ($expenseCategories->isNotEmpty()) {
                $randomCategory = $expenseCategories->random();
                $startDate = Carbon::now()->startOfMonth();
                $endDate = (clone $startDate)->addMonths(3)->subDay();

                Budget::create([
                    'user_id' => $user->id,
                    'category_id' => $randomCategory->id,
                    'name' => "Quarterly {$randomCategory->name} Budget",
                    'amount' => rand(500, 2000),
                    'period' => 'quarterly',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'is_active' => true,
                ]);
            }

            // Create an annual budget for a random expense category
            if ($expenseCategories->isNotEmpty()) {
                $randomCategory = $expenseCategories->random();
                $startDate = Carbon::now()->startOfYear();
                $endDate = (clone $startDate)->addYear()->subDay();

                Budget::create([
                    'user_id' => $user->id,
                    'category_id' => $randomCategory->id,
                    'name' => "Annual {$randomCategory->name} Budget",
                    'amount' => rand(2000, 10000),
                    'period' => 'yearly',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'is_active' => true,
                ]);
            }

            // Create an inactive budget
            if ($expenseCategories->isNotEmpty()) {
                $randomCategory = $expenseCategories->random();
                $startDate = Carbon::now()->subMonths(3)->startOfMonth();
                $endDate = (clone $startDate)->addMonth()->subDay();

                Budget::create([
                    'user_id' => $user->id,
                    'category_id' => $randomCategory->id,
                    'name' => "Past {$randomCategory->name} Budget",
                    'amount' => rand(50, 500),
                    'period' => 'monthly',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'is_active' => false,
                    'notes' => 'This is an archived budget from the past.',
                ]);
            }

            // Create budgets for income categories if any exist
            if ($incomeCategories->isNotEmpty()) {
                $randomCategory = $incomeCategories->random();
                $startDate = Carbon::now()->startOfMonth();
                $endDate = (clone $startDate)->addMonth()->subDay();

                Budget::create([
                    'user_id' => $user->id,
                    'category_id' => $randomCategory->id,
                    'name' => "Monthly {$randomCategory->name} Target",
                    'amount' => rand(1000, 5000),
                    'period' => 'monthly',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'is_active' => true,
                    'notes' => 'This is an income target rather than an expense limit.',
                ]);
            }
        }
    }
}
