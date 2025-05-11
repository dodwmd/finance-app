<?php

namespace Database\Factories;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BankAccount>
 */
class BankAccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BankAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    public function definition(): array
    {
        $openingBalance = $this->faker->randomFloat(2, 0, 10000);

        return [
            'user_id' => User::factory(), // Associates with a new user by default
            'type' => $this->faker->randomElement(['bank', 'credit_card', 'cash']), // Broad type

            // Specific account details, aligning with migration changes
            'account_name' => $this->faker->words(2, true).' '.$this->faker->randomElement(['Chequing', 'Savings', 'Business', 'Investment']), // User-friendly account name
            'account_number' => $this->faker->optional()->bankAccountNumber,
            'bank_name' => $this->faker->company,
            'branch_name' => $this->faker->optional()->streetName.' Branch',
            'account_type' => $this->faker->randomElement(['chequing', 'savings', 'credit card', 'investment', 'loan', 'other']),
            'currency' => $this->faker->randomElement(['CAD', 'USD', 'EUR']), // Default CAD in migration, but faker can provide others
            'is_active' => true,
            'chart_of_account_id' => null, // Typically linked specifically, nullable in migration

            'bsb' => $this->faker->optional()->numerify('###-###'),
            'opening_balance' => $openingBalance,
            'current_balance' => $openingBalance, // Initially current balance is the same as opening
        ];
    }

    /**
     * Indicate that the account is a bank account.
     */
    public function bank(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'bank',
                'account_number' => $this->faker->bankAccountNumber,
                'bsb' => $this->faker->numerify('###-###'),
            ];
        });
    }

    /**
     * Indicate that the account is a credit card.
     */
    public function creditCard(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'credit_card',
                'account_number' => $this->faker->creditCardNumber,
                'bsb' => null, // Credit cards typically don't have BSBs
            ];
        });
    }

    /**
     * Indicate that the account is cash.
     */
    public function cash(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'cash',
                'account_number' => null,
                'bsb' => null,
            ];
        });
    }
}
