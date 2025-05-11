<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use LogicException;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    public function definition(): array
    {
        $types = ['income', 'expense', 'transfer'];
        $type = $this->faker->randomElement($types);

        // Define category names based on type
        $incomeCategories = ['Salary', 'Freelance', 'Investments', 'Rental Income', 'Gifts', 'Other Income'];
        $expenseCategories = ['Food', 'Housing', 'Transportation', 'Entertainment', 'Health', 'Shopping', 'Utilities', 'Education', 'Travel', 'Personal Care', 'Insurance', 'Debt', 'Savings', 'Other Expenses'];
        $transferCategories = ['Bank Transfer', 'Credit Card Payment', 'Investment Transfer', 'Savings Transfer'];

        $categoryNames = match ($type) {
            'income' => $incomeCategories,
            'expense' => $expenseCategories,
            'transfer' => $transferCategories,
            default => throw new LogicException('Unexpected category type: '.$type),
        };

        // Define colors for UI display
        $colors = ['#4CAF50', '#2196F3', '#FF9800', '#E91E63', '#9C27B0', '#3F51B5', '#795548', '#607D8B'];

        // Define icons for UI display (using FontAwesome class names)
        $icons = ['money-bill', 'credit-card', 'shopping-cart', 'utensils', 'home', 'car', 'plane', 'graduation-cap', 'heartbeat', 'hospital', 'briefcase', 'exchange-alt'];

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->randomElement($categoryNames),
            'type' => $type,
            'color' => $this->faker->randomElement($colors),
            'icon' => $this->faker->randomElement($icons),
        ];
    }

    /**
     * Define a state for income categories.
     */
    public function income(): static
    {
        return $this->state(function () {
            return [
                'type' => 'income',
                'name' => $this->faker->randomElement(['Salary', 'Freelance', 'Investments', 'Rental Income', 'Gifts', 'Other Income']),
            ];
        });
    }

    /**
     * Define a state for expense categories.
     */
    public function expense(): static
    {
        return $this->state(function () {
            return [
                'type' => 'expense',
                'name' => $this->faker->randomElement(['Food', 'Housing', 'Transportation', 'Entertainment', 'Health', 'Shopping', 'Utilities', 'Education', 'Travel', 'Personal Care', 'Insurance', 'Debt', 'Savings', 'Other Expenses']),
            ];
        });
    }
}
