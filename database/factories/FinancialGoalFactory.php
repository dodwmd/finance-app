<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FinancialGoal>
 */
class FinancialGoalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-1 year', 'now');
        $target = $this->faker->dateTimeBetween($start, '+2 years');

        return [
            'user_id' => \App\Models\User::factory(),
            'category_id' => null,
            'name' => $this->faker->sentence(3),
            'target_amount' => $this->faker->randomFloat(2, 100, 10000),
            'current_amount' => $this->faker->randomFloat(2, 0, 5000),
            'type' => $this->faker->randomElement(['saving', 'debt_repayment', 'investment', 'purchase']),
            'start_date' => $start->format('Y-m-d'),
            'target_date' => $target->format('Y-m-d'),
            'description' => $this->faker->optional()->sentence(8),
            'is_completed' => false,
            'is_active' => true,
        ];
    }
}
