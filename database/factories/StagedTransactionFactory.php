<?php

namespace Database\Factories;

use App\Models\BankStatementImport;
use App\Models\StagedTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StagedTransaction>
 */
class StagedTransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StagedTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    public function definition(): array
    {
        return [
            'bank_statement_import_id' => BankStatementImport::factory(),
            'transaction_date' => Carbon::now()->toDateString(),
            'description' => $this->faker->sentence,
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'type' => $this->faker->randomElement(['debit', 'credit']),
            'status' => 'pending_review',
            'original_raw_data' => json_encode(['col1' => 'val1', 'col2' => 'val2']),
            'data_hash' => Str::random(32),
            'user_id' => \App\Models\User::factory(),
            'bank_account_id' => \App\Models\BankAccount::factory(),
        ];
    }

    /**
     * Indicate that the transaction belongs to a specific bank statement import.
     */
    public function forImport(BankStatementImport $import): static
    {
        return $this->state(fn (array $_attributes) => [
            'bank_statement_import_id' => $import->id,
            'user_id' => $import->user_id,
            'bank_account_id' => $import->bank_account_id,
        ]);
    }
}
