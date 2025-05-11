<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Budget::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    public function definition(): array
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = (clone $startDate)->addMonth()->subDay();

        $periods = ['monthly', 'quarterly', 'yearly'];
        $period = $this->faker->randomElement($periods);

        // Adjust end date based on period
        if ($period === 'quarterly') {
            $endDate = (clone $startDate)->addMonths(3)->subDay();
        } elseif ($period === 'yearly') {
            $endDate = (clone $startDate)->addYear()->subDay();
        }

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'name' => $this->faker->sentence(3),
            'amount' => $this->faker->randomFloat(2, 50, 1000),
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'notes' => $this->faker->optional(0.7)->paragraph(1),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
        ];
    }

    /**
     * Indicate that the budget is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the budget is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set the budget period to monthly.
     */
    public function monthly(): static
    {
        $startDate = Carbon::now()->startOfMonth();

        return $this->state(fn (array $attributes) => [
            'period' => 'monthly',
            'start_date' => $startDate,
            'end_date' => (clone $startDate)->addMonth()->subDay(),
        ]);
    }

    /**
     * Set the budget period to quarterly.
     */
    public function quarterly(): static
    {
        $startDate = Carbon::now()->startOfMonth();

        return $this->state(fn (array $attributes) => [
            'period' => 'quarterly',
            'start_date' => $startDate,
            'end_date' => (clone $startDate)->addMonths(3)->subDay(),
        ]);
    }

    /**
     * Set the budget period to yearly.
     */
    public function yearly(): static
    {
        $startDate = Carbon::now()->startOfMonth();

        return $this->state(fn (array $attributes) => [
            'period' => 'yearly',
            'start_date' => $startDate,
            'end_date' => (clone $startDate)->addYear()->subDay(),
        ]);
    }
}
