<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a test user
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // Default income categories
        $incomeCategories = [
            ['name' => 'Salary', 'type' => 'income', 'color' => '#4CAF50', 'icon' => 'money-bill'],
            ['name' => 'Freelance', 'type' => 'income', 'color' => '#2196F3', 'icon' => 'laptop'],
            ['name' => 'Investments', 'type' => 'income', 'color' => '#FF9800', 'icon' => 'chart-line'],
            ['name' => 'Rental Income', 'type' => 'income', 'color' => '#E91E63', 'icon' => 'home'],
            ['name' => 'Gifts', 'type' => 'income', 'color' => '#9C27B0', 'icon' => 'gift'],
            ['name' => 'Other Income', 'type' => 'income', 'color' => '#607D8B', 'icon' => 'plus-circle'],
        ];

        // Default expense categories
        $expenseCategories = [
            ['name' => 'Food', 'type' => 'expense', 'color' => '#4CAF50', 'icon' => 'utensils'],
            ['name' => 'Housing', 'type' => 'expense', 'color' => '#2196F3', 'icon' => 'home'],
            ['name' => 'Transportation', 'type' => 'expense', 'color' => '#FF9800', 'icon' => 'car'],
            ['name' => 'Entertainment', 'type' => 'expense', 'color' => '#E91E63', 'icon' => 'film'],
            ['name' => 'Health', 'type' => 'expense', 'color' => '#9C27B0', 'icon' => 'heartbeat'],
            ['name' => 'Shopping', 'type' => 'expense', 'color' => '#3F51B5', 'icon' => 'shopping-cart'],
            ['name' => 'Utilities', 'type' => 'expense', 'color' => '#795548', 'icon' => 'bolt'],
            ['name' => 'Education', 'type' => 'expense', 'color' => '#607D8B', 'icon' => 'graduation-cap'],
        ];

        // Default transfer categories
        $transferCategories = [
            ['name' => 'Bank Transfer', 'type' => 'transfer', 'color' => '#4CAF50', 'icon' => 'exchange-alt'],
            ['name' => 'Credit Card Payment', 'type' => 'transfer', 'color' => '#2196F3', 'icon' => 'credit-card'],
        ];

        // Combine all categories and create them for the user
        $allCategories = array_merge($incomeCategories, $expenseCategories, $transferCategories);

        foreach ($allCategories as $category) {
            Category::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $category['name'],
                    'type' => $category['type'],
                ],
                [
                    'color' => $category['color'],
                    'icon' => $category['icon'],
                ]
            );
        }
    }
}
