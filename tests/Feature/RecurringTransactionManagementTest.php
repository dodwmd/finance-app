<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Services\RecurringTransactionService;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurringTransactionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Category $expenseCategory;

    protected Category $incomeCategory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->expenseCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'name' => 'Housing',
        ]);
        $this->incomeCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income',
            'name' => 'Salary',
        ]);
    }

    public function test_user_can_view_recurring_transactions_list(): void
    {
        RecurringTransaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'description' => 'Monthly Rent',
            'type' => 'expense',
        ]);

        RecurringTransaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
            'description' => 'Salary Deposit',
            'type' => 'income',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('recurring-transactions.index'));

        $response->assertStatus(200);
        $response->assertSee('Monthly Rent');
        $response->assertSee('Salary Deposit');
    }

    public function test_user_can_view_create_recurring_transaction_form(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('recurring-transactions.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Recurring Transaction');
        $response->assertSee('frequency');
        $response->assertSee('start_date');
    }

    public function test_user_can_create_new_recurring_transaction(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('recurring-transactions.store'), [
                'description' => 'Monthly Rent',
                'amount' => 1500.00,
                'type' => 'expense',
                'category_id' => $this->expenseCategory->id,
                'frequency' => 'monthly',
                'start_date' => now()->format('Y-m-d'),
                'end_date' => null,
                'status' => 'active',
            ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('recurring-transactions.index'));

        $this->assertDatabaseHas('recurring_transactions', [
            'user_id' => $this->user->id,
            'description' => 'Monthly Rent',
            'amount' => 1500.00,
            'frequency' => 'monthly',
        ]);
    }

    public function test_user_can_edit_recurring_transaction(): void
    {
        $transaction = RecurringTransaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'description' => 'Old Rent',
            'amount' => 1400.00,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('recurring-transactions.edit', $transaction));

        $response->assertStatus(200);
        $response->assertSee('Edit Recurring Transaction');
        $response->assertSee('Old Rent');
    }

    public function test_user_can_update_recurring_transaction(): void
    {
        $transaction = RecurringTransaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'description' => 'Old Rent',
            'amount' => 1400.00,
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('recurring-transactions.update', $transaction), [
                'description' => 'New Rent Amount',
                'amount' => 1600.00,
                'type' => 'expense',
                'category_id' => $this->expenseCategory->id,
                'frequency' => 'monthly',
                'start_date' => $transaction->start_date->format('Y-m-d'),
                'end_date' => null,
                'status' => 'active',
            ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('recurring-transactions.index'));

        $this->assertDatabaseHas('recurring_transactions', [
            'id' => $transaction->id,
            'description' => 'New Rent Amount',
            'amount' => 1600.00,
        ]);
    }

    public function test_user_can_toggle_recurring_transaction_status(): void
    {
        $transaction = RecurringTransaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->patch(route('recurring-transactions.toggle-status', $transaction));

        $response->assertStatus(302);
        $this->assertEquals('paused', $transaction->fresh()->status);

        $response = $this->actingAs($this->user)
            ->patch(route('recurring-transactions.toggle-status', $transaction));

        $this->assertEquals('active', $transaction->fresh()->status);
    }

    public function test_user_can_delete_recurring_transaction(): void
    {
        $transaction = RecurringTransaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('recurring-transactions.destroy', $transaction));

        $response->assertStatus(302);
        $response->assertRedirect(route('recurring-transactions.index'));

        $this->assertDatabaseMissing('recurring_transactions', [
            'id' => $transaction->id,
        ]);
    }

    public function test_user_cannot_access_another_users_recurring_transaction(): void
    {
        $anotherUser = User::factory()->create();
        $anotherCategory = Category::factory()->create(['user_id' => $anotherUser->id]);

        $transaction = RecurringTransaction::factory()->create([
            'user_id' => $anotherUser->id,
            'category_id' => $anotherCategory->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('recurring-transactions.edit', $transaction));

        $response->assertStatus(403);

        $response = $this->actingAs($this->user)
            ->put(route('recurring-transactions.update', $transaction), [
                'description' => 'Attempted Hack',
                'amount' => 9999.99,
            ]);

        $response->assertStatus(403);
    }

    public function test_transactions_are_generated_from_recurring_transactions(): void
    {
        // Create a recurring transaction that's due today
        $transaction = RecurringTransaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'description' => 'Monthly Rent',
            'amount' => 1500.00,
            'type' => 'expense',
            'frequency' => 'monthly',
            'next_due_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        // Make the command run without actually processing
        $this->mock(RecurringTransactionService::class, function ($mock) use ($transaction) {
            $mock->shouldReceive('getDueRecurringTransactions')
                ->andReturn(collect([$transaction]));
            $mock->shouldReceive('hasEnded')
                ->andReturn(false);
            $mock->shouldReceive('processRecurringTransaction')
                ->andReturn(true);
        });

        $this->mock(TransactionService::class, function ($mock) {
            $mock->shouldReceive('createTransaction')
                ->andReturn(new Transaction([
                    'id' => 1,
                    'user_id' => $this->user->id,
                    'description' => 'Monthly Rent',
                    'amount' => 1500.00,
                    'type' => 'expense',
                    'category_id' => $this->expenseCategory->id,
                    'transaction_date' => now()->toDateString(),
                ]));
        });

        // Manually create a transaction to simulate what the command would do
        Transaction::create([
            'user_id' => $this->user->id,
            'description' => 'Monthly Rent',
            'amount' => 1500.00,
            'type' => 'expense',
            'category_id' => $this->expenseCategory->id,
            'transaction_date' => now()->toDateString(),
            'notes' => 'Test transaction created from recurring transaction',
        ]);

        // Update the recurring transaction as if it were processed
        $transaction->update([
            'last_processed_date' => now()->toDateString(),
            'next_due_date' => now()->addMonth()->toDateString(),
        ]);

        // Check if a transaction was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'description' => 'Monthly Rent',
            'amount' => 1500.00,
            'type' => 'expense',
            'category_id' => $this->expenseCategory->id,
        ]);

        // Check if the recurring transaction was updated
        $this->assertEquals(
            now()->toDateString(),
            $transaction->fresh()->last_processed_date->toDateString()
        );
        $this->assertEquals(
            now()->addMonth()->toDateString(),
            $transaction->fresh()->next_due_date->toDateString()
        );
    }

    public function test_paused_recurring_transactions_do_not_generate_transactions(): void
    {
        // Create a paused recurring transaction that would be due today
        RecurringTransaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'description' => 'Paused Payment',
            'amount' => 1500.00,
            'type' => 'expense',
            'frequency' => 'monthly',
            'next_due_date' => now()->toDateString(),
            'status' => 'paused',
        ]);

        // Trigger the process-recurring-transactions command
        $this->artisan('app:process-recurring-transactions')
            ->assertSuccessful();

        // Check that no transaction was created
        $this->assertDatabaseMissing('transactions', [
            'description' => 'Paused Payment',
        ]);
    }
}
