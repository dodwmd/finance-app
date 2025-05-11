<?php

namespace Database\Factories;

use App\Models\BankAccount;
use App\Models\BankStatementImport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BankStatementImport>
 */
class BankStatementImportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BankStatementImport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    public function definition(): array
    {
        // Ensure a dummy file exists for testing
        $dummyCsvPath = 'imports/dummy_statement_for_factory.csv';
        $dummyCsvContent = "Date,Description,Amount\n2023-01-01,Test Debit,100.00\n2023-01-02,Test Credit,-50.00";
        Storage::disk('local')->put($dummyCsvPath, $dummyCsvContent);

        return [
            'user_id' => User::factory(),
            'bank_account_id' => BankAccount::factory(),
            'original_file_path' => $dummyCsvPath,
            'original_headers' => ['Date', 'Description', 'Amount'],
            'status' => 'pending_mapping', // Default status
            'column_mapping' => [
                'transaction_date' => null,
                'description' => null,
                'amount_type' => 'single',
                'amount' => null,
                'debit_amount' => null,
                'credit_amount' => null,
            ],
            'file_hash' => $this->faker->sha256,
            'processed_row_count' => 0,
            'total_row_count' => 2, // Based on dummy CSV content
        ];
    }
}
