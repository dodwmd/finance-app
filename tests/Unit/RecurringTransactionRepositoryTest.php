<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\User;
use App\Repositories\RecurringTransactionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurringTransactionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected RecurringTransactionRepository $repository;

    protected User $user;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new RecurringTransactionRepository(new RecurringTransaction);
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_can_create_recurring_transaction(): void
    {
        $data = [
            'user_id' => $this->user->id,
            'description' => 'Monthly Rent',
            'amount' => 1500.00,
            'type' => 'expense',
            'category_id' => $this->category->id,
            'frequency' => 'monthly',
            'start_date' => '2025-01-01',
            'next_due_date' => '2025-01-01',
            'status' => 'active',
        ];

        $transaction = $this->repository->create($data);

        $this->assertInstanceOf(RecurringTransaction::class, $transaction);
        $this->assertEquals('Monthly Rent', $transaction->description);
        $this->assertEquals(1500.00, $transaction->amount);
        $this->assertEquals('monthly', $transaction->frequency);
        $this->assertEquals('active', $transaction->status);
    }

    public function test_can_update_recurring_transaction(): void
    {
        $transaction = RecurringTransaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'description' => 'Initial Description',
            'amount' => 1000.00,
        ]);

        $updatedTransaction = $this->repository->update($transaction->id, [
            'description' => 'Updated Description',
            'amount' => 1200.00,
        ]);

        $this->assertEquals('Updated Description', $updatedTransaction->description);
        $this->assertEquals(1200.00, $updatedTransaction->amount);
    }

    public function test_can_update_recurring_transaction_instance(): void
    {
        $transaction = RecurringTransaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);

        $result = $this->repository->updateInstance($transaction, [
            'status' => 'paused',
        ]);

        $this->assertTrue($result);
        $this->assertEquals('paused', $transaction->fresh()->status);
    }

    public function test_can_get_recurring_transactions_by_user_id(): void
    {
        RecurringTransaction::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        // Create transactions for another user
        $anotherUser = User::factory()->create();
        $anotherCategory = Category::factory()->create(['user_id' => $anotherUser->id]);
        RecurringTransaction::factory()->count(2)->create([
            'user_id' => $anotherUser->id,
            'category_id' => $anotherCategory->id,
        ]);

        $transactions = $this->repository->getByUserId($this->user->id);

        $this->assertCount(3, $transactions);
        $this->assertEquals($this->user->id, $transactions->first()->user_id);
    }

    public function test_can_mark_recurring_transaction_as_processed(): void
    {
        $transaction = RecurringTransaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'last_processed_date' => null,
            'next_due_date' => now()->toDateString(),
        ]);

        $processedDate = now()->toDateString();
        $nextDueDate = now()->addMonth()->toDateString();

        $result = $this->repository->markAsProcessed($transaction, $processedDate, $nextDueDate);

        $this->assertTrue($result);
        $this->assertEquals($processedDate, $transaction->fresh()->last_processed_date->toDateString());
        $this->assertEquals($nextDueDate, $transaction->fresh()->next_due_date->toDateString());
    }

    public function test_can_find_transaction_by_id(): void
    {
        $originalTransaction = RecurringTransaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'description' => 'Find Me',
        ]);

        $foundTransaction = $this->repository->find($originalTransaction->id);

        $this->assertInstanceOf(RecurringTransaction::class, $foundTransaction);
        $this->assertEquals($originalTransaction->id, $foundTransaction->id);
        $this->assertEquals('Find Me', $foundTransaction->description);
        $this->assertTrue($foundTransaction->relationLoaded('user'));
        $this->assertTrue($foundTransaction->relationLoaded('category'));
    }

    public function test_can_get_due_recurring_transactions(): void
    {
        // Create some transactions with different due dates and statuses
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $tomorrow = now()->addDay()->toDateString();
        $nextWeek = now()->addWeek()->toDateString();

        // 1. Transaction due today (should be returned)
        $tx1 = RecurringTransaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'description' => 'Due Today, No End Date',
            'status' => 'active',
            'next_due_date' => $today,
            'end_date' => null,
        ]);

        // 2. Transaction due yesterday (should be returned)
        $tx2 = RecurringTransaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'description' => 'Due Yesterday, No End Date',
            'status' => 'active',
            'next_due_date' => $yesterday,
            'end_date' => null,
        ]);

        // 3. Transaction due tomorrow (should NOT be returned)
        $tx3 = RecurringTransaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'description' => 'Due Tomorrow, No End Date',
            'status' => 'active',
            'next_due_date' => $tomorrow,
            'end_date' => null,
        ]);

        // 4. Transaction with inactive status (should NOT be returned)
        $tx4 = RecurringTransaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'description' => 'Due Today, Paused',
            'status' => 'paused',
            'next_due_date' => $today,
            'end_date' => null,
        ]);

        // 5. Transaction with end date in the past (should NOT be returned)
        $tx5 = RecurringTransaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'description' => 'Due Today, Ended Yesterday',
            'status' => 'active',
            'next_due_date' => $today,
            'end_date' => $yesterday,
        ]);

        // 6. Transaction with end date in the future (should be returned)
        $tx6 = RecurringTransaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'description' => 'Due Today, Ends Next Week',
            'status' => 'active',
            'next_due_date' => $today,
            'end_date' => $nextWeek,
        ]);

        // Get due transactions
        $dueTransactions = $this->repository->getDueRecurringTransactions();

        // Should return 3 transactions: #1, #2, and #6
        $this->assertCount(3, $dueTransactions);

        // All returned transactions should have 'active' status
        foreach ($dueTransactions as $transaction) {
            $this->assertEquals('active', $transaction->status);
            // Convert Carbon object to string for proper comparison
            $this->assertLessThanOrEqual($today, $transaction->next_due_date->toDateString());
        }

        // Test with a specific date in the future that includes tomorrow's transaction
        $specificDate = $tomorrow;

        // Create a transaction due on the specific date
        $tx7 = RecurringTransaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'description' => 'Due Tomorrow Also',
            'status' => 'active',
            'next_due_date' => $tomorrow,
            'end_date' => null,
        ]);

        // Get transactions due by the specific date (tomorrow)
        // Should include the 3 previous transactions (#1, #2, #6) plus tx3 and tx7 (both due tomorrow)
        $dueBySpecificDate = $this->repository->getDueRecurringTransactions($specificDate);

        // Should be 5 transactions: the 3 from earlier plus the 2 due tomorrow
        $this->assertCount(5, $dueBySpecificDate);

        // Verify all returned transactions have correct properties
        foreach ($dueBySpecificDate as $transaction) {
            $this->assertEquals('active', $transaction->status);
            // All due dates should be less than or equal to tomorrow
            $this->assertLessThanOrEqual($specificDate, $transaction->next_due_date->toDateString());
        }
    }
}
