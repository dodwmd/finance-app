<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Category $expenseCategory;

    private Category $incomeCategory;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();

        // Create test categories
        $this->expenseCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'name' => 'Test Expense',
            'color' => '#FF5733',
        ]);

        $this->incomeCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income',
            'name' => 'Test Income',
            'color' => '#33FF57',
        ]);
    }

    /**
     * Test transaction index page renders correctly
     */
    public function test_transaction_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get(route('transactions.index'));

        $response->assertStatus(200);
        $response->assertViewIs('transactions.index');
    }

    /**
     * Test transaction create page renders correctly
     */
    public function test_transaction_create_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get(route('transactions.create'));

        $response->assertStatus(200);
        $response->assertViewIs('transactions.create');
    }

    /**
     * Test transaction store functionality for expense
     */
    public function test_transaction_can_be_stored_for_expense(): void
    {
        $transactionData = [
            'type' => 'expense',
            'amount' => 100.50,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Test Expense Transaction',
            'category_id' => $this->expenseCategory->id,
            'notes' => 'Test notes for expense',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('transactions.store'), $transactionData);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'expense',
            'amount' => 100.50,
            'description' => 'Test Expense Transaction',
            'category_id' => $this->expenseCategory->id,
        ]);
    }

    /**
     * Test transaction store functionality for income
     */
    public function test_transaction_can_be_stored_for_income(): void
    {
        $transactionData = [
            'type' => 'income',
            'amount' => 1500.75,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Test Income Transaction',
            'category_id' => $this->incomeCategory->id,
            'notes' => 'Test notes for income',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('transactions.store'), $transactionData);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'income',
            'amount' => 1500.75,
            'description' => 'Test Income Transaction',
            'category_id' => $this->incomeCategory->id,
        ]);
    }

    /**
     * Test transaction validation errors
     */
    public function test_transaction_store_validates_input(): void
    {
        // Missing required fields
        $response = $this->actingAs($this->user)
            ->post(route('transactions.store'), [
                'type' => 'expense',
                // Missing amount, transaction_date, description and category_id
            ]);

        $response->assertSessionHasErrors(['amount', 'transaction_date', 'description', 'category_id']);
    }

    /**
     * Test transaction edit page renders correctly
     */
    public function test_transaction_edit_page_can_be_rendered(): void
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'category_id' => $this->expenseCategory->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('transactions.edit', $transaction));

        $response->assertStatus(200);
        $response->assertViewIs('transactions.edit');
        $response->assertViewHas('transaction', $transaction);
    }

    /**
     * Test transaction update functionality
     */
    public function test_transaction_can_be_updated(): void
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'amount' => 200,
            'description' => 'Original description',
            'category_id' => $this->expenseCategory->id,
        ]);

        $updatedData = [
            'type' => 'expense',
            'amount' => 250.50,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Updated description',
            'category_id' => $this->expenseCategory->id,
            'notes' => 'Updated notes',
        ];

        $response = $this->actingAs($this->user)
            ->put(route('transactions.update', $transaction), $updatedData);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'user_id' => $this->user->id,
            'amount' => 250.50,
            'description' => 'Updated description',
        ]);
    }

    /**
     * Test transaction delete functionality
     */
    public function test_transaction_can_be_deleted(): void
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'category_id' => $this->expenseCategory->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('transactions.destroy', $transaction));

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);
    }

    /**
     * Test user can only access their own transactions
     */
    public function test_user_cannot_access_other_users_transactions(): void
    {
        // Create another user
        $anotherUser = User::factory()->create();

        // Create a transaction for the other user
        $otherUserTransaction = Transaction::factory()->create([
            'user_id' => $anotherUser->id,
            'type' => 'expense',
            'category_id' => Category::factory()->create([
                'user_id' => $anotherUser->id,
                'type' => 'expense',
            ])->id,
        ]);

        // Try to view the other user's transaction
        $response = $this->actingAs($this->user)
            ->get(route('transactions.edit', $otherUserTransaction));

        $response->assertStatus(403);

        // Try to update the other user's transaction
        $response = $this->actingAs($this->user)
            ->put(route('transactions.update', $otherUserTransaction), [
                'type' => 'expense',
                'amount' => 999,
                'transaction_date' => now()->format('Y-m-d'),
                'description' => 'Hacked transaction',
                'category_id' => $this->expenseCategory->id,
            ]);

        $response->assertStatus(403);

        // Try to delete the other user's transaction
        $response = $this->actingAs($this->user)
            ->delete(route('transactions.destroy', $otherUserTransaction));

        $response->assertStatus(403);

        // Verify the transaction was not modified
        $this->assertDatabaseHas('transactions', [
            'id' => $otherUserTransaction->id,
            'user_id' => $anotherUser->id,
        ]);
    }

    /**
     * Test transaction list filters
     */
    public function test_transaction_list_can_be_filtered(): void
    {
        // Create multiple transactions
        Transaction::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'category_id' => $this->expenseCategory->id,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Test expense transaction',
        ]);

        Transaction::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'type' => 'income',
            'category_id' => $this->incomeCategory->id,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Test income transaction',
        ]);

        // Test filter by type
        $response = $this->actingAs($this->user)
            ->get(route('transactions.index', ['type' => 'expense']));

        $response->assertStatus(200);
        $response->assertViewHas('transactions');

        // Test filter by category
        $response = $this->actingAs($this->user)
            ->get(route('transactions.index', ['category_id' => $this->incomeCategory->id]));

        $response->assertStatus(200);
        $response->assertViewHas('transactions');

        // Test filter by date range
        $startDate = now()->subDays(7)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');
        $response = $this->actingAs($this->user)
            ->get(route('transactions.index', [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]));

        $response->assertStatus(200);
        $response->assertViewHas('transactions');
    }
}
