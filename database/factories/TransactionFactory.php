<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['income', 'expense', 'transfer'];
        $type = $this->faker->randomElement($types);

        // Define a mapping of category names by type
        $categoryNames = [
            'income' => ['Salary', 'Freelance', 'Investment', 'Gift', 'Refund'],
            'expense' => ['Food', 'Transport', 'Housing', 'Entertainment', 'Utilities', 'Shopping', 'Health', 'Education'],
            'transfer' => ['To Savings', 'To Checking', 'To Investment'],
        ];

        // Pick a random category name from the type
        $categoryName = $this->faker->randomElement($categoryNames[$type]);

        // Create a user for the transaction
        $user = User::factory()->create();

        // Get or create a category for the transaction
        $category = $this->getOrCreateCategory($user->id, $categoryName, $type);

        return [
            'user_id' => $user->id,
            'description' => $this->getDescription($type, $categoryName),
            'amount' => $this->getAmount($type),
            'type' => $type,
            'category_id' => $category->id,
            'transaction_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
        ];
    }

    /**
     * Get or create a category by name and type for a user
     */
    private function getOrCreateCategory(int $userId, string $categoryName, string $type): Category
    {
        // Define standard colors and icons for categories
        $colorMap = [
            'income' => '#4CAF50',
            'expense' => '#F44336',
            'transfer' => '#2196F3',
        ];

        $iconMap = [
            'Salary' => 'money-bill',
            'Freelance' => 'laptop',
            'Investment' => 'chart-line',
            'Gift' => 'gift',
            'Refund' => 'undo',
            'Food' => 'utensils',
            'Transport' => 'car',
            'Housing' => 'home',
            'Entertainment' => 'film',
            'Utilities' => 'bolt',
            'Shopping' => 'shopping-cart',
            'Health' => 'heartbeat',
            'Education' => 'graduation-cap',
            'To Savings' => 'piggy-bank',
            'To Checking' => 'exchange-alt',
            'To Investment' => 'chart-line',
        ];

        // Get default color and icon or fallbacks
        $color = $colorMap[$type] ?? '#607D8B';
        $icon = $iconMap[$categoryName] ?? 'tag';

        // Find or create the category
        return Category::firstOrCreate(
            [
                'user_id' => $userId,
                'name' => $categoryName,
                'type' => $type,
            ],
            [
                'color' => $color,
                'icon' => $icon,
            ]
        );
    }

    /**
     * Generate a realistic amount based on transaction type
     */
    private function getAmount(string $type): float
    {
        return match ($type) {
            'income' => $this->faker->randomFloat(2, 100, 5000),
            'expense' => $this->faker->randomFloat(2, 5, 1000),
            'transfer' => $this->faker->randomFloat(2, 50, 2000),
        };
    }

    /**
     * Generate a realistic description based on type and category
     */
    private function getDescription(string $type, string $category): string
    {
        $descriptions = [
            'Salary' => ['Monthly Salary', 'Bonus Payment', 'Overtime Pay'],
            'Freelance' => ['Client Project', 'Consulting Fee', 'Contract Work'],
            'Investment' => ['Dividend Payment', 'Stock Sale', 'Interest Income'],
            'Gift' => ['Birthday Gift', 'Holiday Gift', 'Family Support'],
            'Refund' => ['Product Return', 'Service Refund', 'Tax Refund'],

            'Food' => ['Grocery Shopping', 'Restaurant Meal', 'Coffee Shop', 'Food Delivery'],
            'Transport' => ['Fuel', 'Public Transport', 'Taxi Ride', 'Parking Fee'],
            'Housing' => ['Rent Payment', 'Mortgage', 'House Repairs', 'Furniture'],
            'Entertainment' => ['Movie Tickets', 'Concert', 'Streaming Service', 'Gaming'],
            'Utilities' => ['Electricity Bill', 'Water Bill', 'Internet Bill', 'Phone Bill'],
            'Shopping' => ['Clothing Purchase', 'Electronics', 'Home Goods', 'Online Shopping'],
            'Health' => ['Doctor Visit', 'Medication', 'Gym Membership', 'Health Insurance'],
            'Education' => ['Course Fee', 'Textbooks', 'Tuition Payment', 'Workshop'],

            'To Savings' => ['Transfer to Savings', 'Emergency Fund Deposit', 'Monthly Savings'],
            'To Checking' => ['Transfer to Checking', 'Bill Payment Transfer', 'Regular Transfer'],
            'To Investment' => ['Investment Deposit', 'Retirement Contribution', 'Stock Purchase'],
        ];

        $options = $descriptions[$category] ?? ["Payment for {$category}"];

        return $this->faker->randomElement($options);
    }
}
