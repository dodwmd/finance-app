<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    /**
     * Create a new category.
     */
    public function createCategory(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * Get all categories for a user.
     */
    public function getCategoriesByUser(int $userId): Collection
    {
        return Category::where('user_id', $userId)->orderBy('name')->get();
    }

    /**
     * Get categories by type.
     */
    public function getCategoriesByType(int $userId, ?string $type = null): Collection
    {
        $query = Category::where('user_id', $userId);

        if ($type) {
            $query->where('type', $type);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Create default categories for a new user.
     */
    public function createDefaultCategoriesForUser(User $user): void
    {
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

        DB::transaction(function () use ($user, $allCategories) {
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
        });
    }

    /**
     * Delete a category.
     */
    public function deleteCategory(int $categoryId): bool
    {
        return Category::destroy($categoryId) > 0;
    }

    /**
     * Update a category.
     */
    public function updateCategory(int $categoryId, array $data): bool
    {
        return Category::where('id', $categoryId)->update($data) > 0;
    }

    /**
     * Generate a random color for a new category.
     */
    public function generateRandomColor(): string
    {
        $colors = [
            '#4CAF50', '#2196F3', '#FF9800', '#E91E63', '#9C27B0',
            '#3F51B5', '#795548', '#607D8B', '#F44336', '#FFEB3B',
            '#009688', '#8BC34A', '#673AB7', '#CDDC39', '#FF5722',
        ];

        return $colors[array_rand($colors)];
    }
}
