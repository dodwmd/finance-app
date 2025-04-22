<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\TransactionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected TransactionRepository $repository;

    protected User $user;

    protected Category $incomeCategory;

    protected Category $expenseCategory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TransactionRepository(new Transaction());
        $this->user = User::factory()->create();
        $this->incomeCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Salary',
            'type' => 'income',
        ]);
        $this->expenseCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Groceries',
            'type' => 'expense',
        ]);
    }

    public function test_can_get_transactions_by_user_id(): void
    {
        // Create transactions for the user
        Transaction::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
            'type' => 'income',
        ]);

        Transaction::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'type' => 'expense',
        ]);

        // Create transactions for another user
        $anotherUser = User::factory()->create();
        $anotherCategory = Category::factory()->create(['user_id' => $anotherUser->id]);
        Transaction::factory()->count(2)->create([
            'user_id' => $anotherUser->id,
            'category_id' => $anotherCategory->id,
        ]);

        // Get transactions by user ID
        $transactions = $this->repository->getByUserId($this->user->id);

        // Should only return the current user's transactions
        $this->assertCount(5, $transactions);
        foreach ($transactions as $transaction) {
            $this->assertEquals($this->user->id, $transaction->user_id);
        }
    }

    public function test_can_get_transactions_by_user_id_and_type(): void
    {
        // Create income transactions
        Transaction::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
            'type' => 'income',
        ]);

        // Create expense transactions
        Transaction::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'type' => 'expense',
        ]);

        // Get only income transactions
        $incomeTransactions = $this->repository->getByUserIdAndType($this->user->id, 'income');
        $this->assertCount(3, $incomeTransactions);
        foreach ($incomeTransactions as $transaction) {
            $this->assertEquals('income', $transaction->type);
        }

        // Get only expense transactions
        $expenseTransactions = $this->repository->getByUserIdAndType($this->user->id, 'expense');
        $this->assertCount(2, $expenseTransactions);
        foreach ($expenseTransactions as $transaction) {
            $this->assertEquals('expense', $transaction->type);
        }
    }

    public function test_can_get_transactions_by_date_range(): void
    {
        // Create transactions with different dates
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $lastWeek = now()->subWeek()->toDateString();
        $nextWeek = now()->addWeek()->toDateString();

        // Transaction from last week
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
            'transaction_date' => $lastWeek,
            'description' => 'Last Week Transaction',
        ]);

        // Transaction from yesterday
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
            'transaction_date' => $yesterday,
            'description' => 'Yesterday Transaction',
        ]);

        // Transaction from today
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'transaction_date' => $today,
            'description' => 'Today Transaction',
        ]);

        // Transaction for next week
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'transaction_date' => $nextWeek,
            'description' => 'Next Week Transaction',
        ]);

        // Debug the transactions to make sure they're created with correct dates
        $allTransactions = Transaction::all();
        $this->assertCount(4, $allTransactions);
        
        // Get transactions between yesterday and today
        $transactions = $this->repository->getByDateRange(
            $this->user->id,
            $yesterday,
            $today
        );

        // Verify each transaction in the date range
        $transactionDates = $transactions->pluck('transaction_date')->map(function($date) {
            return $date->toDateString();
        })->toArray();
        
        // Should include both yesterday and today
        $this->assertTrue(in_array($yesterday, $transactionDates), "Yesterday's transaction is missing from the results");
        $this->assertTrue(in_array($today, $transactionDates), "Today's transaction is missing from the results");
        
        // Should return 2 transactions
        $this->assertCount(2, $transactions);
        
        foreach ($transactions as $transaction) {
            $transactionDate = $transaction->transaction_date->toDateString();
            $this->assertTrue(
                $transactionDate === $yesterday || $transactionDate === $today,
                "Transaction date {$transactionDate} is not within the expected range"
            );
        }
    }

    public function test_can_get_sum_by_type(): void
    {
        // Create income transactions with specific amounts
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
            'type' => 'income',
            'amount' => 1000.00,
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
            'type' => 'income',
            'amount' => 500.00,
        ]);

        // Create expense transactions with specific amounts
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'type' => 'expense',
            'amount' => 200.00,
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'type' => 'expense',
            'amount' => 300.00,
        ]);

        // Get sum of income transactions
        $incomeSum = $this->repository->getSumByType($this->user->id, 'income');
        $this->assertEquals(1500.00, $incomeSum);

        // Get sum of expense transactions
        $expenseSum = $this->repository->getSumByType($this->user->id, 'expense');
        $this->assertEquals(500.00, $expenseSum);
    }
}
