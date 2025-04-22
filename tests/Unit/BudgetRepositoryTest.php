<?php

namespace Tests\Unit;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\BudgetRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected BudgetRepository $repository;
    protected User $user;
    protected Category $expenseCategory;
    protected Category $incomeCategory;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create the repository instance
        $this->repository = new BudgetRepository(new Budget());
        
        // Create a test user
        $this->user = User::factory()->create();
        
        // Create test categories
        $this->expenseCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Groceries',
            'type' => 'expense'
        ]);
        
        $this->incomeCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Salary',
            'type' => 'income'
        ]);
    }

    public function test_can_get_all_budgets_for_user(): void
    {
        // Create budgets for the user
        Budget::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
        ]);
        
        // Create budgets for another user
        $anotherUser = User::factory()->create();
        $anotherCategory = Category::factory()->create(['user_id' => $anotherUser->id]);
        Budget::factory()->count(2)->create([
            'user_id' => $anotherUser->id,
            'category_id' => $anotherCategory->id,
        ]);
        
        // Get budgets for the user
        $budgets = $this->repository->getAllForUser($this->user->id);
        
        // Should return only the user's budgets
        $this->assertEquals(3, $budgets->total());
        
        foreach ($budgets as $budget) {
            $this->assertEquals($this->user->id, $budget->user_id);
        }
    }
    
    public function test_can_get_budget_by_id(): void
    {
        // Create a budget
        $budget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'name' => 'Test Budget',
        ]);
        
        // Get the budget by ID
        $foundBudget = $this->repository->getById($budget->id);
        
        // Verify it's the correct budget
        $this->assertNotNull($foundBudget);
        $this->assertEquals($budget->id, $foundBudget->id);
        $this->assertEquals('Test Budget', $foundBudget->name);
        $this->assertTrue($foundBudget->relationLoaded('category'));
    }
    
    public function test_can_create_budget(): void
    {
        // Prepare budget data
        $budgetData = [
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'name' => 'New Budget',
            'amount' => 500.00,
            'period' => 'monthly',
            'start_date' => Carbon::now()->startOfMonth()->toDateString(),
            'is_active' => true,
        ];
        
        // Create the budget
        $budget = $this->repository->create($budgetData);
        
        // Verify the budget was created with correct data
        $this->assertInstanceOf(Budget::class, $budget);
        $this->assertEquals($this->user->id, $budget->user_id);
        $this->assertEquals($this->expenseCategory->id, $budget->category_id);
        $this->assertEquals('New Budget', $budget->name);
        $this->assertEquals(500.00, $budget->amount);
        $this->assertEquals('monthly', $budget->period);
        $this->assertTrue($budget->is_active);
    }
    
    public function test_can_update_budget(): void
    {
        // Create a budget
        $budget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'name' => 'Old Name',
            'amount' => 300.00,
        ]);
        
        // Update the budget
        $updatedBudget = $this->repository->update($budget->id, [
            'name' => 'New Name',
            'amount' => 400.00,
            'is_active' => false,
        ]);
        
        // Verify the budget was updated correctly
        $this->assertNotNull($updatedBudget);
        $this->assertEquals($budget->id, $updatedBudget->id);
        $this->assertEquals('New Name', $updatedBudget->name);
        $this->assertEquals(400.00, $updatedBudget->amount);
        $this->assertFalse($updatedBudget->is_active);
    }
    
    public function test_update_returns_null_for_nonexistent_budget(): void
    {
        // Try to update a nonexistent budget
        $updatedBudget = $this->repository->update(999, [
            'name' => 'New Name',
        ]);
        
        // Should return null
        $this->assertNull($updatedBudget);
    }
    
    public function test_can_delete_budget(): void
    {
        // Create a budget
        $budget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
        ]);
        
        // Delete the budget
        $result = $this->repository->delete($budget->id);
        
        // Should return true and budget should be deleted
        $this->assertTrue($result);
        $this->assertNull(Budget::find($budget->id));
    }
    
    public function test_delete_returns_false_for_nonexistent_budget(): void
    {
        // Try to delete a nonexistent budget
        $result = $this->repository->delete(999);
        
        // Should return false
        $this->assertFalse($result);
    }
    
    public function test_can_get_active_budgets(): void
    {
        // Create active budgets
        Budget::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'is_active' => true,
            'period' => 'monthly',
        ]);
        
        // Create inactive budget
        Budget::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'is_active' => false,
            'period' => 'monthly',
        ]);
        
        // Create active budget with different period
        Budget::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'is_active' => true,
            'period' => 'yearly',
        ]);
        
        // Get all active budgets
        $activeBudgets = $this->repository->getActiveBudgets($this->user->id);
        
        // Should return 3 active budgets (both monthly and yearly)
        $this->assertCount(3, $activeBudgets);
        foreach ($activeBudgets as $budget) {
            $this->assertTrue($budget->is_active);
        }
        
        // Get active budgets with monthly period
        $monthlyBudgets = $this->repository->getActiveBudgets($this->user->id, 'monthly');
        
        // Should return 2 active monthly budgets
        $this->assertCount(2, $monthlyBudgets);
        foreach ($monthlyBudgets as $budget) {
            $this->assertTrue($budget->is_active);
            $this->assertEquals('monthly', $budget->period);
        }
    }
    
    public function test_can_get_budget_progress(): void
    {
        // Create a budget
        $startDate = Carbon::now()->startOfMonth()->toDateString();
        $budget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 1000.00,
            'period' => 'monthly',
            'start_date' => $startDate,
            'end_date' => null, // Will be calculated based on period
        ]);
        
        // Create transactions within the budget period
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 250.00,
            'type' => 'expense',
            'transaction_date' => $startDate,
        ]);
        
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 300.00,
            'type' => 'expense',
            'transaction_date' => Carbon::parse($startDate)->addDays(5)->toDateString(),
        ]);
        
        // Create a transaction outside the budget's category
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id, // Different category
            'amount' => 200.00,
            'type' => 'expense',
            'transaction_date' => $startDate,
        ]);
        
        // Get budget progress
        $progress = $this->repository->getBudgetProgress($budget->id);
        
        // Total spent should be 550.00 (250 + 300)
        $this->assertEquals($budget->id, $progress['budget']->id);
        $this->assertEquals(550.00, $progress['spent']);
        $this->assertEquals(450.00, $progress['remaining']);
        $this->assertEquals(55.0, $progress['percentage']);
        $this->assertFalse($progress['is_exceeded']);
        
        // Handle the start_date which could be a string or Carbon object
        $startDateToTest = is_string($progress['start_date']) 
            ? $progress['start_date'] 
            : $progress['start_date']->toDateString();
        $this->assertEquals($startDate, $startDateToTest);
        
        // End date should be calculated based on monthly period (start_date + 1 month - 1 day)
        $expectedEndDate = Carbon::parse($startDate)->addMonth()->subDay()->toDateString();
        
        // Handle the end_date which could be a string or Carbon object
        $endDateToTest = is_string($progress['end_date']) 
            ? $progress['end_date'] 
            : $progress['end_date']->toDateString();
        $this->assertEquals($expectedEndDate, $endDateToTest);
    }
    
    public function test_get_budget_progress_with_explicit_end_date(): void
    {
        // Create a budget with explicit end date
        $startDate = Carbon::now()->startOfMonth()->toDateString();
        $endDate = Carbon::now()->endOfMonth()->toDateString(); // Explicit end date
        
        $budget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 1000.00,
            'period' => 'monthly',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
        
        // Create a transaction
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 1200.00, // Exceeds budget
            'type' => 'expense',
            'transaction_date' => $startDate,
        ]);
        
        // Get budget progress
        $progress = $this->repository->getBudgetProgress($budget->id);
        
        // Handle the end_date which could be a string or Carbon object
        $endDateToTest = is_string($progress['end_date']) 
            ? $progress['end_date'] 
            : $progress['end_date']->toDateString();
        $this->assertEquals($endDate, $endDateToTest);
        
        $this->assertEquals(1200.00, $progress['spent']);
        $this->assertEquals(0.00, $progress['remaining']); // Should be 0 when exceeded
        $this->assertEquals(100.0, $progress['percentage']); // Should be capped at 100
        $this->assertTrue($progress['is_exceeded']);
    }
    
    public function test_can_get_current_budgets(): void
    {
        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();
        $tomorrow = Carbon::tomorrow()->toDateString();
        $lastMonth = Carbon::today()->subMonth()->toDateString();
        $nextMonth = Carbon::today()->addMonth()->toDateString();
        
        // 1. Budget that started yesterday and has no end date (current)
        Budget::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'is_active' => true,
            'start_date' => $yesterday,
            'end_date' => null,
            'name' => 'Current Budget 1',
        ]);
        
        // 2. Budget that started last month and ends next month (current)
        Budget::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'is_active' => true,
            'start_date' => $lastMonth,
            'end_date' => $nextMonth,
            'name' => 'Current Budget 2',
        ]);
        
        // 3. Budget that starts tomorrow (not current)
        Budget::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'is_active' => true,
            'start_date' => $tomorrow,
            'end_date' => $nextMonth,
            'name' => 'Future Budget',
        ]);
        
        // 4. Budget that ended yesterday (not current)
        Budget::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'is_active' => true,
            'start_date' => $lastMonth,
            'end_date' => $yesterday,
            'name' => 'Past Budget',
        ]);
        
        // 5. Inactive budget that would be current by date (not current due to inactive)
        Budget::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'is_active' => false,
            'start_date' => $yesterday,
            'end_date' => $nextMonth,
            'name' => 'Inactive Budget',
        ]);
        
        // Get current budgets
        $currentBudgets = $this->repository->getCurrentBudgets($this->user->id);
        
        // Should return 2 current budgets
        $this->assertCount(2, $currentBudgets);
        
        // Verify they are the expected budgets
        $budgetNames = $currentBudgets->pluck('name')->toArray();
        $this->assertContains('Current Budget 1', $budgetNames);
        $this->assertContains('Current Budget 2', $budgetNames);
        $this->assertNotContains('Future Budget', $budgetNames);
        $this->assertNotContains('Past Budget', $budgetNames);
        $this->assertNotContains('Inactive Budget', $budgetNames);
    }
}
