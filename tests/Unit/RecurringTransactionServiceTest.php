<?php

namespace Tests\Unit;

use App\Contracts\Repositories\RecurringTransactionRepositoryInterface;
use App\Models\RecurringTransaction;
use App\Services\RecurringTransactionService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class RecurringTransactionServiceTest extends TestCase
{
    protected RecurringTransactionRepositoryInterface $repository;

    protected RecurringTransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(RecurringTransactionRepositoryInterface::class);
        $this->service = new RecurringTransactionService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_recurring_transaction(): void
    {
        $data = [
            'user_id' => 1,
            'description' => 'Monthly Rent',
            'amount' => 1500.00,
            'type' => 'expense',
            'category_id' => 5,
            'frequency' => 'monthly',
            'start_date' => '2025-01-01',
            'next_due_date' => '2025-01-01',
            'status' => 'active',
        ];

        $expectedTransaction = new RecurringTransaction($data);

        $this->repository->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($expectedTransaction);

        $result = $this->service->createRecurringTransaction($data);

        $this->assertEquals($expectedTransaction, $result);
    }

    public function test_update_recurring_transaction(): void
    {
        $transaction = Mockery::mock(RecurringTransaction::class);
        $data = [
            'description' => 'Updated Rent',
            'amount' => 1600.00,
        ];

        $this->repository->shouldReceive('updateInstance')
            ->once()
            ->with($transaction, $data)
            ->andReturn(true);

        $result = $this->service->updateRecurringTransaction($transaction, $data);

        $this->assertTrue($result);
    }

    public function test_get_user_recurring_transactions(): void
    {
        $userId = 1;
        $transactions = new Collection([
            new RecurringTransaction(['description' => 'Transaction 1']),
            new RecurringTransaction(['description' => 'Transaction 2']),
        ]);

        $this->repository->shouldReceive('getByUserId')
            ->once()
            ->with($userId)
            ->andReturn($transactions);

        $result = $this->service->getUserRecurringTransactions($userId);

        $this->assertCount(2, $result);
        $this->assertEquals($transactions, $result);
    }

    public function test_get_due_recurring_transactions(): void
    {
        $date = '2025-04-20';
        $transactions = new Collection([
            new RecurringTransaction(['description' => 'Due Transaction']),
        ]);

        $this->repository->shouldReceive('getDueRecurringTransactions')
            ->once()
            ->with($date)
            ->andReturn($transactions);

        $result = $this->service->getDueRecurringTransactions($date);

        $this->assertCount(1, $result);
        $this->assertEquals($transactions, $result);
    }

    public function test_process_recurring_transaction(): void
    {
        $transaction = Mockery::mock(RecurringTransaction::class);
        $transaction->shouldReceive('getAttribute')
            ->with('frequency')
            ->andReturn('monthly');
        $transaction->shouldReceive('getAttribute')
            ->with('next_due_date')
            ->andReturn('2025-04-15');

        Carbon::setTestNow(Carbon::parse('2025-04-15'));
        $currentDate = Carbon::now()->toDateString();
        $nextDueDate = '2025-05-15';

        $this->repository->shouldReceive('markAsProcessed')
            ->once()
            ->with($transaction, $currentDate, $nextDueDate)
            ->andReturn(true);

        $result = $this->service->processRecurringTransaction($transaction);

        $this->assertTrue($result);
        Carbon::setTestNow(); // Reset the mock time
    }

    public function test_toggle_status_active_to_paused(): void
    {
        $transaction = Mockery::mock(RecurringTransaction::class);
        $transaction->shouldReceive('getAttribute')
            ->with('status')
            ->andReturn('active');

        $this->repository->shouldReceive('updateInstance')
            ->once()
            ->with($transaction, ['status' => 'paused'])
            ->andReturn(true);

        $result = $this->service->toggleStatus($transaction);

        $this->assertTrue($result);
    }

    public function test_toggle_status_paused_to_active(): void
    {
        $transaction = Mockery::mock(RecurringTransaction::class);
        $transaction->shouldReceive('getAttribute')
            ->with('status')
            ->andReturn('paused');

        $this->repository->shouldReceive('updateInstance')
            ->once()
            ->with($transaction, ['status' => 'active'])
            ->andReturn(true);

        $result = $this->service->toggleStatus($transaction);

        $this->assertTrue($result);
    }

    public function test_has_ended_with_future_end_date(): void
    {
        $transaction = Mockery::mock(RecurringTransaction::class);
        $transaction->shouldReceive('getAttribute')
            ->with('end_date')
            ->andReturn(Carbon::now()->addDays(10)->toDateString());
        $transaction->shouldReceive('offsetExists')
            ->with('end_date')
            ->andReturn(true);

        $result = $this->service->hasEnded($transaction);

        $this->assertFalse($result);
    }

    public function test_has_ended_with_past_end_date(): void
    {
        $transaction = Mockery::mock(RecurringTransaction::class);
        $transaction->shouldReceive('getAttribute')
            ->with('end_date')
            ->andReturn(Carbon::now()->subDays(10)->toDateString());
        $transaction->shouldReceive('offsetExists')
            ->with('end_date')
            ->andReturn(true);

        $result = $this->service->hasEnded($transaction);

        $this->assertTrue($result);
    }

    public function test_has_ended_with_no_end_date(): void
    {
        $transaction = Mockery::mock(RecurringTransaction::class);
        $transaction->shouldReceive('getAttribute')
            ->with('end_date')
            ->andReturn(null);
        $transaction->shouldReceive('offsetExists')
            ->with('end_date')
            ->andReturn(false);

        $result = $this->service->hasEnded($transaction);

        $this->assertFalse($result);
    }
}
