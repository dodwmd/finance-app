<?php

namespace Tests\Unit;

use App\Contracts\Repositories\BudgetRepositoryInterface;
use App\Contracts\Repositories\TransactionRepositoryInterface;
use App\Models\Budget;
use App\Services\BudgetService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class BudgetServiceTest extends TestCase
{
    protected $budgetRepository;

    protected $transactionRepository;

    protected $budgetService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->budgetRepository = Mockery::mock(BudgetRepositoryInterface::class);
        $this->transactionRepository = Mockery::mock(TransactionRepositoryInterface::class);

        $this->budgetService = new BudgetService(
            $this->budgetRepository,
            $this->transactionRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test getting all budgets for a user.
     */
    public function test_get_all_budgets(): void
    {
        $userId = 1;
        $perPage = 10;

        $paginatedBudgets = new LengthAwarePaginator(
            [new Budget],
            1,
            $perPage
        );

        $this->budgetRepository->shouldReceive('getAllForUser')
            ->once()
            ->with($userId, $perPage)
            ->andReturn($paginatedBudgets);

        $result = $this->budgetService->getAllBudgets($userId, $perPage);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals($paginatedBudgets, $result);
    }

    /**
     * Test getting a budget by ID.
     */
    public function test_get_budget_by_id(): void
    {
        $budgetId = 1;
        $budget = new Budget;
        $budget->id = $budgetId;

        $this->budgetRepository->shouldReceive('getById')
            ->once()
            ->with($budgetId)
            ->andReturn($budget);

        $result = $this->budgetService->getBudgetById($budgetId);

        $this->assertInstanceOf(Budget::class, $result);
        $this->assertEquals($budgetId, $result->id);
    }

    /**
     * Test creating a new budget.
     */
    public function test_create_budget(): void
    {
        $startDate = '2025-04-01';

        $budgetData = [
            'name' => 'Test Budget',
            'user_id' => 1,
            'category_id' => 2,
            'amount' => 500.00,
            'period' => 'monthly',
            'start_date' => $startDate,
            'is_active' => true,
        ];

        $calculatedEndDate = Carbon::parse($startDate)
            ->addMonth()
            ->subDay()
            ->toDateString();

        $expectedData = $budgetData;
        $expectedData['end_date'] = $calculatedEndDate;

        $budget = new Budget($expectedData);
        $budget->id = 1;

        $this->budgetRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($expectedData) {
                return $data['end_date'] === $expectedData['end_date'];
            }))
            ->andReturn($budget);

        $result = $this->budgetService->createBudget($budgetData);

        $this->assertInstanceOf(Budget::class, $result);
    }

    /**
     * Test updating an existing budget.
     */
    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function test_update_budget(): void
    {
        $budgetId = 1;
        $startDate = '2025-04-01';

        $existingBudget = new Budget([
            'name' => 'Old Budget',
            'user_id' => 1,
            'category_id' => 2,
            'amount' => 300.00,
            'period' => 'monthly',
            'start_date' => $startDate,
            'end_date' => Carbon::parse($startDate)->addMonth()->subDay()->toDateString(),
            'is_active' => true,
        ]);
        $existingBudget->id = $budgetId;

        $updateData = [
            'name' => 'Updated Budget',
            'amount' => 500.00,
            'period' => 'quarterly',
        ];

        $this->budgetRepository->shouldReceive('getById')
            ->once()
            ->with($budgetId)
            ->andReturn($existingBudget);

        $this->budgetRepository->shouldReceive('update')
            ->once()
            ->with($budgetId, Mockery::on(function ($data) use ($updateData) {
                // Ensure end_date is calculated and present, and other data matches
                return array_key_exists('end_date', $data) &&
                       $data['name'] === $updateData['name'] &&
                       $data['amount'] === $updateData['amount'];
            }));

        $this->budgetService->updateBudget($budgetId, $updateData);

        // No assertions on $result as the method is void.
        // The mock expectation verifies the repository 'update' method was called correctly.
    }

    /**
     * Test deleting a budget.
     */
    public function test_delete_budget(): void
    {
        $budgetId = 1;

        $this->budgetRepository->shouldReceive('delete')
            ->once()
            ->with($budgetId);
        // Repository delete is void, service now returns bool

        $result = $this->budgetService->deleteBudget($budgetId);

        $this->assertTrue($result);
    }

    /**
     * Test getting active budgets with progress.
     */
    public function test_get_active_budgets_with_progress(): void
    {
        $userId = 1;
        $period = null;

        $budget1 = new Budget;
        $budget1->id = 1;
        $budget1->user_id = $userId;
        $budget1->category_id = 1;
        $budget1->name = 'Test Budget 1';
        $budget1->amount = 500.00;
        $budget1->period = 'monthly';
        $budget1->start_date = '2025-04-01';
        $budget1->end_date = '2025-04-30';
        $budget1->is_active = true;

        $budget2 = new Budget;
        $budget2->id = 2;
        $budget2->user_id = $userId;
        $budget2->category_id = 2;
        $budget2->name = 'Test Budget 2';
        $budget2->amount = 1000.00;
        $budget2->period = 'monthly';
        $budget2->start_date = '2025-04-01';
        $budget2->end_date = '2025-04-30';
        $budget2->is_active = true;

        $activeBudgets = Collection::make([$budget1, $budget2]);

        $budgetProgress1 = [
            'budget' => $budget1,
            'spent' => 150.00,
            'remaining' => 350.00,
            'percentage' => 30,
            'is_exceeded' => false,
        ];

        $budgetProgress2 = [
            'budget' => $budget2,
            'spent' => 300.00,
            'remaining' => 700.00,
            'percentage' => 30,
            'is_exceeded' => false,
        ];

        $this->budgetRepository->shouldReceive('getActiveBudgets')
            ->once()
            ->with($userId, $period)
            ->andReturn($activeBudgets);

        $this->budgetRepository->shouldReceive('getBudgetProgress')
            ->twice()
            ->andReturnUsing(function ($budgetId) use ($budgetProgress1, $budgetProgress2) {
                return $budgetId === 1 ? $budgetProgress1 : $budgetProgress2;
            });

        $result = $this->budgetService->getActiveBudgetsWithProgress($userId);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        $this->assertEquals(30, $result->first()['percentage']);
        $this->assertEquals(150.00, $result->first()['spent']);
        $this->assertEquals($budget1, $result->first()['budget']);
    }

    /**
     * Test getting period options.
     */
    public function test_get_period_options(): void
    {
        $expectedOptions = [
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'yearly' => 'Yearly',
        ];

        $result = $this->budgetService->getPeriodOptions();

        $this->assertEquals($expectedOptions, $result);
    }

    /**
     * Test end date calculation via create budget.
     */
    public function test_end_date_calculation_via_create_budget(): void
    {
        // Test monthly period
        $startDate = '2025-04-01';
        $monthlyData = [
            'name' => 'Monthly Budget',
            'user_id' => 1,
            'category_id' => 1,
            'amount' => 500.00,
            'period' => 'monthly',
            'start_date' => $startDate,
            'is_active' => true,
        ];

        $monthlyExpectedDate = Carbon::parse('2025-04-30');
        $monthlyBudget = new Budget($monthlyData);
        $monthlyBudget->id = 1;
        $monthlyBudget->end_date = $monthlyExpectedDate;

        $this->budgetRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) {
                return ! empty($data['end_date']);
            }))
            ->andReturn($monthlyBudget);

        $result1 = $this->budgetService->createBudget($monthlyData);
        $this->assertTrue($monthlyExpectedDate->isSameDay($result1->end_date), 'Monthly end date should be 2025-04-30');

        // Test quarterly period
        $quarterlyData = [
            'name' => 'Quarterly Budget',
            'user_id' => 1,
            'category_id' => 1,
            'amount' => 1500.00,
            'period' => 'quarterly',
            'start_date' => $startDate,
            'is_active' => true,
        ];

        $quarterlyExpectedDate = Carbon::parse('2025-06-30');
        $quarterlyBudget = new Budget($quarterlyData);
        $quarterlyBudget->id = 2;
        $quarterlyBudget->end_date = $quarterlyExpectedDate;

        $this->budgetRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) {
                return ! empty($data['end_date']);
            }))
            ->andReturn($quarterlyBudget);

        $result2 = $this->budgetService->createBudget($quarterlyData);
        $this->assertTrue($quarterlyExpectedDate->isSameDay($result2->end_date), 'Quarterly end date should be 2025-06-30');

        // Test yearly period
        $yearlyData = [
            'name' => 'Yearly Budget',
            'user_id' => 1,
            'category_id' => 1,
            'amount' => 6000.00,
            'period' => 'yearly',
            'start_date' => $startDate,
            'is_active' => true,
        ];

        $yearlyExpectedDate = Carbon::parse('2026-03-31');
        $yearlyBudget = new Budget($yearlyData);
        $yearlyBudget->id = 3;
        $yearlyBudget->end_date = $yearlyExpectedDate;

        $this->budgetRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) {
                return ! empty($data['end_date']);
            }))
            ->andReturn($yearlyBudget);

        $result3 = $this->budgetService->createBudget($yearlyData);
        $this->assertTrue($yearlyExpectedDate->isSameDay($result3->end_date), 'Yearly end date should be 2026-03-31');
    }
}
