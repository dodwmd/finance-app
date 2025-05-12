<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\BankStatementImport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankStatementImportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a user can view the import form
     *
     * @return void
     */
    public function test_user_can_view_import_form()
    {
        // Create a user and bank account
        $user = User::factory()->create();
        $bankAccount = BankAccount::factory()->for($user)->create();

        $response = $this->actingAs($user)
            ->get(route('bank-accounts.import.form', $bankAccount));

        $response->assertStatus(200);
        $response->assertViewIs('bank-accounts.import.create');
        $response->assertViewHas('bankAccount', $bankAccount);
    }

    /**
     * Test that the import form cannot be accessed by unauthorized users
     *
     * @return void
     */
    public function test_import_form_cannot_be_accessed_by_unauthorized_users()
    {
        // Create two users and a bank account for the first user
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $bankAccount = BankAccount::factory()->for($user1)->create();

        // User2 tries to access user1's bank account import form
        $response = $this->actingAs($user2)
            ->get(route('bank-accounts.import.form', $bankAccount));

        $response->assertStatus(403); // Forbidden
    }

    /**
     * Test that a user can view the mapping form
     *
     * @return void
     */
    public function test_user_can_view_mapping_form()
    {
        // Create a user and bank account
        $user = User::factory()->create();
        $bankAccount = BankAccount::factory()->for($user)->create();

        // Create a BankStatementImport with pending_mapping status
        $import = BankStatementImport::create([
            'user_id' => $user->id,
            'bank_account_id' => $bankAccount->id,
            'original_filename' => 'test_import.csv',
            'original_file_path' => 'bank_statements/test_import.csv',
            'original_headers' => ['custom_date', 'notes', 'debit', 'credit'],
            'status' => 'pending_mapping',
            'column_mapping' => [
                'transaction_date' => null,
                'description' => null,
                'amount_type' => null,
                'amount' => null,
                'debit_amount' => null,
                'credit_amount' => null,
            ],
        ]);

        // Verify user can access the mapping form
        $response = $this->actingAs($user)
            ->get(route('bank-accounts.import.mapping.show', [
                'bankAccount' => $bankAccount->id,
                'import' => $import->id,
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('bank-accounts.import.mapping');
        $response->assertViewHas('import', $import);
    }

    /**
     * Test that mapping form cannot be accessed by unauthorized users
     *
     * @return void
     */
    public function test_mapping_form_cannot_be_accessed_by_unauthorized_users()
    {
        // Create two users and a bank account for the first user
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $bankAccount = BankAccount::factory()->for($user1)->create();

        // Create an import for user1
        $import = BankStatementImport::create([
            'user_id' => $user1->id,
            'bank_account_id' => $bankAccount->id,
            'original_filename' => 'test_import.csv',
            'original_file_path' => 'bank_statements/test_import.csv',
            'original_headers' => ['custom_date', 'notes', 'debit', 'credit'],
            'status' => 'pending_mapping',
            'column_mapping' => [
                'transaction_date' => null,
                'description' => null,
                'amount_type' => null,
                'amount' => null,
                'debit_amount' => null,
                'credit_amount' => null,
            ],
        ]);

        // User2 tries to access user1's mapping form
        $response = $this->actingAs($user2)
            ->get(route('bank-accounts.import.mapping.show', [
                'bankAccount' => $bankAccount->id,
                'import' => $import->id,
            ]));

        $response->assertStatus(403); // Forbidden
    }
}
