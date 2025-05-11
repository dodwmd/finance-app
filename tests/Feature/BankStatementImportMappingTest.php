<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\BankStatementImport;
use App\Models\StagedTransaction;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BankStatementImportMappingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected BankAccount $bankAccount;

    protected BankStatementImport $import;

    protected string $testCsvPath = 'imports/test_statement.csv';

    protected string $testCsvOriginalName = 'test_statement.csv';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local'); // Use a fake local disk

        $this->user = User::factory()->create();
        $this->bankAccount = BankAccount::factory()->for($this->user)->create();

        // Create a dummy CSV file for testing
        $csvContent = "Date,Description,Amount\n"
                    ."2023-01-01,Test Debit,100.00\n"
                    ."2023-01-02,Test Credit,-50.00\n"; // Note: Amount column for credit is negative here as an example
        Storage::disk('local')->put($this->testCsvPath, $csvContent);

        $this->import = BankStatementImport::factory()->for($this->user)->for($this->bankAccount)->create([
            'original_file_path' => $this->testCsvPath,
            'original_headers' => ['Date', 'Description', 'Amount'],
            'column_mapping' => [
                'transaction_date' => 'Date',
                'description' => 'Description',
                'amount_type' => 'single',
                'amount' => 'Amount',
                'debit_amount' => null,
                'credit_amount' => null,
            ],
            'status' => 'pending_mapping', // Initial status for testing mapping UI
        ]);
    }

    /**
     * Test that the column mapping form is displayed correctly.
     */
    // public function test_user_can_view_column_mapping_form(): void
    // {
    //     $response = $this->actingAs($this->user)
    //                      ->get(route('bank-accounts.import.mapping.show', ['bankAccount' => $this->bankAccount->id, 'import' => $this->import->id]));

    //     $response->assertOk();
    //     $response->assertViewIs('bank-accounts.import.mapping');
    //     $response->assertViewHas('bankAccount', $this->bankAccount);
    //     $response->assertViewHas('import', $this->import);
    //     // Check if original_headers are passed and used in the view (e.g., for dropdowns)
    //     $response->assertViewHas('original_headers', $this->import->original_headers);
    // }

    /**
     * Test successful update of column mapping with a single amount column.
     */
    // public function test_user_can_successfully_update_mapping_with_single_amount_column(): void
    // {
    //     $mappingData = [
    //         'transaction_date_column' => 'Date',
    //         'description_column' => 'Description',
    //         'amount_type' => 'single',
    //         'amount_column' => 'Amount',
    //     ];

    //     $response = $this->actingAs($this->user)
    //                      ->put(route('bank-accounts.import.mapping.update', ['bankAccount' => $this->bankAccount->id, 'import' => $this->import->id]), $mappingData);

    //     $response->assertRedirect(route('bank-accounts.staged.review', $this->bankAccount));
    //     $response->assertSessionHas('success');

    //     $this->import->refresh();
    //     $this->assertEquals('Date', $this->import->column_mapping['transaction_date']);
    //     $this->assertEquals('Description', $this->import->column_mapping['description']);
    //     $this->assertEquals('single', $this->import->column_mapping['amount_type']);
    //     $this->assertEquals('Amount', $this->import->column_mapping['amount']);
    //     $this->assertNull($this->import->column_mapping['debit_amount']);
    //     $this->assertNull($this->import->column_mapping['credit_amount']);
    //     $this->assertEquals('awaiting_review', $this->import->status);

    //     // Assert that StagedTransactions were created/updated
    //     $this->assertDatabaseCount('staged_transactions', 2); // Assuming 2 rows in CSV
    //     $stagedTransactions = StagedTransaction::where('bank_statement_import_id', $this->import->id)->get();

    //     $firstStaged = $stagedTransactions->firstWhere('original_raw_data', json_encode(['Date' => '2023-01-01', 'Description' => 'Test Debit', 'Amount' => '100.00']));
    //     $this->assertNotNull($firstStaged);
    //     $this->assertEquals(Carbon::parse('2023-01-01')->toDateString(), Carbon::parse($firstStaged->transaction_date)->toDateString());
    //     $this->assertEquals('Test Debit', $firstStaged->description);
    //     $this->assertEquals(100.00, $firstStaged->amount);
    //     $this->assertNotNull($firstStaged->data_hash);

    //     $secondStaged = $stagedTransactions->firstWhere('original_raw_data', json_encode(['Date' => '2023-01-02', 'Description' => 'Test Credit', 'Amount' => '-50.00']));
    //     $this->assertNotNull($secondStaged);
    //     $this->assertEquals(Carbon::parse('2023-01-02')->toDateString(), Carbon::parse($secondStaged->transaction_date)->toDateString());
    //     $this->assertEquals('Test Credit', $secondStaged->description);
    //     $this->assertEquals(-50.00, $secondStaged->amount);
    //     $this->assertNotNull($secondStaged->data_hash);
    // }

    /**
     * Test successful update of column mapping with separate debit and credit columns.
     */
    public function test_user_can_successfully_update_mapping_with_separate_debit_credit_columns(): void
    {
        // Prepare a CSV with separate debit/credit columns
        $separateCsvContent = "Transaction Date,Details,Debit,Credit\n"
                            ."2023-02-01,Expense Item,200.00,\n"
                            ."2023-02-02,Income Item,,75.50\n";
        $separateCsvPath = 'imports/separate_test_statement.csv';
        Storage::disk('local')->put($separateCsvPath, $separateCsvContent);

        $importSeparate = BankStatementImport::factory()->for($this->user)->for($this->bankAccount)->create([
            'original_file_path' => $separateCsvPath,
            'original_headers' => ['Transaction Date', 'Details', 'Debit', 'Credit'],
            'status' => 'pending_mapping',
        ]);

        $mappingData = [
            'transaction_date_column' => 'Transaction Date',
            'description_column' => 'Details',
            'amount_type' => 'separate',
            'debit_amount_column' => 'Debit',
            'credit_amount_column' => 'Credit',
        ];

        $response = $this->actingAs($this->user)
            ->put(route('bank-accounts.import.mapping.update', ['bankAccount' => $this->bankAccount->id, 'import' => $importSeparate->id]), $mappingData);

        $response->assertRedirect(route('bank-accounts.staged.review', $this->bankAccount));
        $response->assertSessionHas('success');

        $importSeparate->refresh();
        $this->assertEquals('Transaction Date', $importSeparate->column_mapping['transaction_date']);
        $this->assertEquals('Details', $importSeparate->column_mapping['description']);
        $this->assertEquals('separate', $importSeparate->column_mapping['amount_type']);
        $this->assertEquals('Debit', $importSeparate->column_mapping['debit_amount']);
        $this->assertEquals('Credit', $importSeparate->column_mapping['credit_amount']);
        $this->assertNull($importSeparate->column_mapping['amount']);
        $this->assertEquals('awaiting_review', $importSeparate->status);

        // Assert StagedTransactions for the separate import
        $this->assertDatabaseHas('staged_transactions', [
            'bank_statement_import_id' => $importSeparate->id,
            'description' => 'Expense Item',
            'amount' => -200.00, // Debits are negative
        ]);
        $this->assertDatabaseHas('staged_transactions', [
            'bank_statement_import_id' => $importSeparate->id,
            'description' => 'Income Item',
            'amount' => 75.50,
        ]);
    }

    /**
     * Test that updating column mapping fails with invalid data.
     *
     * @dataProvider invalidMappingDataProvider
     */
    //    #[\PHPUnit\Framework\Attributes\DataProvider('invalidMappingDataProvider')]
    //    public function test_update_mapping_fails_with_invalid_data(array $invalidData, string $expectedErrorField): void
    //    {
    //        $response = $this->actingAs($this->user)
    //                         ->from(route('bank-accounts.import.mapping.show', ['bankAccount' => $this->bankAccount->id, 'import' => $this->import->id]))
    //                         ->put(route('bank-accounts.import.mapping.update', ['bankAccount' => $this->bankAccount->id, 'import' => $this->import->id]), $invalidData);
    //
    //        $response->assertRedirect(route('bank-accounts.import.mapping.show', ['bankAccount' => $this->bankAccount->id, 'import' => $this->import->id]));
    //        $response->assertSessionHasErrors($expectedErrorField);
    //    }
    //
    //    public static function invalidMappingDataProvider(): array
    //    {
    //        return [
    //            'missing transaction_date_column' => [['transaction_date_column' => '', 'description_column' => 'Description', 'amount_type' => 'single', 'amount_column' => 'Amount'], 'transaction_date_column'],
    //            'invalid transaction_date_column (not in headers)' => [['transaction_date_column' => 'Invalid Date Col', 'description_column' => 'Description', 'amount_type' => 'single', 'amount_column' => 'Amount'], 'transaction_date_column'],
    //            'missing amount_type' => [['transaction_date_column' => 'Date', 'description_column' => 'Description', 'amount_type' => '', 'amount_column' => 'Amount'], 'amount_type'],
    //            'invalid amount_type' => [['transaction_date_column' => 'Date', 'description_column' => 'Description', 'amount_type' => 'triple', 'amount_column' => 'Amount'], 'amount_type'],
    //            'single amount type missing amount_column' => [['transaction_date_column' => 'Date', 'description_column' => 'Description', 'amount_type' => 'single', 'amount_column' => ''], 'amount_column'],
    //            'single amount type invalid amount_column (not in headers)' => [['transaction_date_column' => 'Date', 'description_column' => 'Description', 'amount_type' => 'single', 'amount_column' => 'Invalid Amount Col'], 'amount_column'],
    //            'separate amount type missing debit_amount_column' => [['transaction_date_column' => 'Date', 'description_column' => 'Description', 'amount_type' => 'separate', 'debit_amount_column' => ''], 'debit_amount_column'],
    //            'separate amount type invalid debit_amount_column (not in headers)' => [['transaction_date_column' => 'Date', 'description_column' => 'Description', 'amount_type' => 'separate', 'debit_amount_column' => 'Invalid Debit Col'], 'debit_amount_column'],
    //            'separate amount type missing credit_amount_column' => [['transaction_date_column' => 'Date', 'description_column' => 'Description', 'amount_type' => 'separate', 'debit_amount_column' => 'Amount', 'credit_amount_column' => ''], 'credit_amount_column'],
    //            'separate amount type invalid credit_amount_column (not in headers)' => [['transaction_date_column' => 'Date', 'description_column' => 'Description', 'amount_type' => 'separate', 'debit_amount_column' => 'Amount', 'credit_amount_column' => 'Invalid Credit Col'], 'credit_amount_column'],
    //        ];
    //    }

    /**
     * Test that updating mapping fails if the original CSV file is missing.
     */
    //    public function test_update_mapping_fails_if_original_csv_file_is_missing(): void
    //    {
    //        // Delete the original CSV file
    //        Storage::disk('local')->delete($this->import->original_file_path);
    //
    //        $mappingData = [
    //            'transaction_date_column' => 'Date',
    //            'description_column' => 'Description',
    //            'amount_type' => 'single',
    //            'amount_column' => 'Amount',
    //        ];
    //
    //        $response = $this->actingAs($this->user)
    //                         ->from(route('bank-accounts.import.mapping.show', ['bankAccount' => $this->bankAccount->id, 'import' => $this->import->id]))
    //                         ->put(route('bank-accounts.import.mapping.update', ['bankAccount' => $this->bankAccount->id, 'import' => $this->import->id]), $mappingData);
    //
    //        $response->assertRedirect(route('bank-accounts.import.mapping.show', ['bankAccount' => $this->bankAccount->id, 'import' => $this->import->id]));
    //        $response->assertSessionHas('error', 'Original CSV file not found. Cannot re-process.');
    //
    //        $this->import->refresh();
    //        // Assert that the import record's mapping and status, and staged transactions remain unchanged
    //        // (or assert that they reflect the state before the failed update attempt)
    //        $this->assertEquals('pending_mapping', $this->import->status); // Status should not have changed
    //        $this->assertDatabaseCount('staged_transactions', 0); // No new transactions should be staged
    //    }

    // public function test_update_mapping_correctly_applies_duplicate_detection(): void
    // {
    //     // Create an existing transaction that should match one from the CSV
    //     // The default CSV in setUp has: "2023-01-01,Test Debit,100.00"
    //     $existingTransaction = \App\Models\Transaction::factory()->for($this->user)->for($this->bankAccount)->create([
    //         'transaction_date' => '2023-01-01',
    //         'description' => 'An Existing Debit Transaction',
    //         'amount' => 100.00, // Matching amount
    //         'type' => 'expense', // Or 'income' depending on how your factory/logic sets it for positive amount
    //     ]);

    //     $mappingData = [
    //         'transaction_date_column' => 'Date',
    //         'description_column' => 'Description',
    //         'amount_type' => 'single',
    //         'amount_column' => 'Amount',
    //     ];

    //     $this->actingAs($this->user)
    //          ->put(route('bank-accounts.import.mapping.update', ['bankAccount' => $this->bankAccount->id, 'import' => $this->import->id]), $mappingData);

    //     $this->import->refresh();
    //     $this->assertEquals('awaiting_review', $this->import->status);

    //     $this->assertDatabaseHas('staged_transactions', [
    //         'bank_statement_import_id' => $this->import->id,
    //         'transaction_date' => \Illuminate\Support\Carbon::parse('2023-01-01')->startOfDay()->toDateTimeString(),
    //         'amount' => 100,
    //         'status' => 'potential_duplicate',
    //         'matched_transaction_id' => $existingTransaction->id,
    //     ]);

    //     // Ensure the other transaction from CSV is normal
    //     $this->assertDatabaseHas('staged_transactions', [
    //         'bank_statement_import_id' => $this->import->id,
    //         'transaction_date' => \Illuminate\Support\Carbon::parse('2023-01-02')->startOfDay()->toDateTimeString(),
    //         'amount' => -50,
    //         'status' => 'pending_review',
    //         'matched_transaction_id' => null,
    //     ]);
    // }
}
