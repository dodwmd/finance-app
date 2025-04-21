<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

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
        
        $categories = [
            'income' => ['Salary', 'Freelance', 'Investment', 'Gift', 'Refund'],
            'expense' => ['Food', 'Transport', 'Housing', 'Entertainment', 'Utilities', 'Shopping', 'Health', 'Education'],
            'transfer' => ['To Savings', 'To Checking', 'To Investment']
        ];
        
        $category = $this->faker->randomElement($categories[$type]);
        
        return [
            'user_id' => User::factory(),
            'description' => $this->getDescription($type, $category),
            'amount' => $this->getAmount($type),
            'type' => $type,
            'category' => $category,
            'transaction_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
        ];
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
