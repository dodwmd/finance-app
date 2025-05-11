<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecurringTransaction>
 */
class RecurringTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    public function definition(): array
    {
        $types = ['income', 'expense'];
        $type = $this->faker->randomElement($types);
        $frequencies = ['daily', 'weekly', 'biweekly', 'monthly', 'quarterly', 'annually'];
        $frequency = $this->faker->randomElement($frequencies);

        $startDate = Carbon::now()->subDays($this->faker->numberBetween(0, 30));
        $nextDueDate = Carbon::parse($startDate)->addDays($this->faker->numberBetween(0, 30));
        $hasEndDate = $this->faker->boolean(30); // 30% chance to have an end date
        $endDate = $hasEndDate ? Carbon::parse($nextDueDate)->addMonths($this->faker->numberBetween(1, 12)) : null;

        $status = $this->faker->randomElement(['active', 'paused']);
        $lastProcessedDate = $status === 'active' ? Carbon::parse($startDate)->subDays($this->faker->numberBetween(1, 30)) : null;

        return [
            'user_id' => User::factory(),
            'description' => $this->getDescription($type),
            'amount' => $this->getAmount($type),
            'type' => $type,
            'category_id' => function (array $attributes) {
                $user = User::find($attributes['user_id']);
                $categoryType = $attributes['type'];

                return Category::factory()->create([
                    'user_id' => $user->id,
                    'type' => $categoryType,
                ])->id;
            },
            'frequency' => $frequency,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'next_due_date' => $nextDueDate,
            'last_processed_date' => $lastProcessedDate,
            'status' => $status,
        ];
    }

    /**
     * Configure the factory to create an active recurring transaction.
     */
    public function active(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
            ];
        });
    }

    /**
     * Configure the factory to create a paused recurring transaction.
     */
    public function paused(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'paused',
            ];
        });
    }

    /**
     * Configure the factory to create a recurring transaction due today.
     */
    public function dueToday(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'next_due_date' => now()->toDateString(),
                'status' => 'active',
            ];
        });
    }

    /**
     * Generate a realistic amount based on transaction type
     */
    private function getAmount(string $type): float
    {
        return match ($type) {
            'income' => $this->faker->randomFloat(2, 100, 5000),
            'expense' => $this->faker->randomFloat(2, 5, 1000),
            default => $this->faker->randomFloat(2, 50, 2000),
        };
    }

    /**
     * Generate a realistic description based on type
     */
    private function getDescription(string $type): string
    {
        $incomeDescriptions = [
            'Monthly Salary',
            'Freelance Payment',
            'Rental Income',
            'Investment Dividend',
            'Side Gig Income',
        ];

        $expenseDescriptions = [
            'Rent Payment',
            'Mortgage Payment',
            'Phone Bill',
            'Internet Subscription',
            'Streaming Service',
            'Gym Membership',
            'Insurance Premium',
            'Utility Bill',
        ];

        return match ($type) {
            'income' => $this->faker->randomElement($incomeDescriptions),
            'expense' => $this->faker->randomElement($expenseDescriptions),
            default => 'Recurring Payment',
        };
    }
}
