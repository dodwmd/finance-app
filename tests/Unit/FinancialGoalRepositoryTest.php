<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\FinancialGoal;
use App\Models\User;
use App\Repositories\FinancialGoalRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialGoalRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected FinancialGoalRepository $repository;

    protected User $user;

    protected Category $savingsCategory;

    protected Category $investmentCategory;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the repository instance
        $this->repository = new FinancialGoalRepository(new FinancialGoal);

        // Create a test user
        $this->user = User::factory()->create();

        // Create test categories - use 'expense' type which is valid in the schema
        $this->savingsCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Savings',
            'type' => 'expense',
        ]);

        $this->investmentCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Investments',
            'type' => 'expense',
        ]);
    }

    public function test_can_get_all_goals_for_user(): void
    {
        // Create goals for the user
        FinancialGoal::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'category_id' => $this->savingsCategory->id,
        ]);

        // Create goals for another user
        $anotherUser = User::factory()->create();
        $anotherCategory = Category::factory()->create(['user_id' => $anotherUser->id]);
        FinancialGoal::factory()->count(2)->create([
            'user_id' => $anotherUser->id,
            'category_id' => $anotherCategory->id,
        ]);

        // Get goals for the user
        $goals = $this->repository->getAllForUser($this->user->id);

        // Should return only the user's goals with pagination
        $this->assertEquals(3, $goals->total());

        foreach ($goals as $goal) {
            $this->assertEquals($this->user->id, $goal->user_id);
        }
    }

    public function test_can_get_goal_by_id(): void
    {
        // Create a goal
        $goal = FinancialGoal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->savingsCategory->id,
            'name' => 'Test Goal',
        ]);

        // Get the goal by ID
        $foundGoal = $this->repository->getById($goal->id);

        // Verify it's the correct goal
        $this->assertNotNull($foundGoal);
        $this->assertEquals($goal->id, $foundGoal->id);
        $this->assertEquals('Test Goal', $foundGoal->name);
        $this->assertTrue($foundGoal->relationLoaded('category'));
    }

    public function test_can_create_goal(): void
    {
        // Prepare goal data
        $goalData = [
            'user_id' => $this->user->id,
            'category_id' => $this->savingsCategory->id,
            'name' => 'New Goal',
            'description' => 'My first savings goal',
            'target_amount' => 5000.00,
            'current_amount' => 500.00,
            'start_date' => Carbon::now()->toDateString(),
            'target_date' => Carbon::now()->addMonths(6)->toDateString(),
            'type' => 'savings',
            'is_active' => true,
            'is_completed' => false,
        ];

        // Create the goal
        $goal = $this->repository->create($goalData);

        // Verify the goal was created with correct data
        $this->assertInstanceOf(FinancialGoal::class, $goal);
        $this->assertEquals($this->user->id, $goal->user_id);
        $this->assertEquals($this->savingsCategory->id, $goal->category_id);
        $this->assertEquals('New Goal', $goal->name);
        $this->assertEquals(5000.00, $goal->target_amount);
        $this->assertEquals(500.00, $goal->current_amount);
        $this->assertEquals('savings', $goal->type);
        $this->assertTrue($goal->is_active);
        $this->assertFalse($goal->is_completed);
    }

    public function test_can_update_goal(): void
    {
        // Create a goal
        $goal = FinancialGoal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->savingsCategory->id,
            'name' => 'Old Name',
            'target_amount' => 3000.00,
            'current_amount' => 100.00,
        ]);

        // Update the goal
        $updatedGoal = $this->repository->update($goal->id, [
            'name' => 'New Name',
            'target_amount' => 4000.00,
            'is_active' => false,
        ]);

        // Verify the goal was updated correctly
        $this->assertNotNull($updatedGoal);
        $this->assertEquals($goal->id, $updatedGoal->id);
        $this->assertEquals('New Name', $updatedGoal->name);
        $this->assertEquals(4000.00, $updatedGoal->target_amount);
        $this->assertEquals(100.00, $updatedGoal->current_amount); // Unchanged
        $this->assertFalse($updatedGoal->is_active);
    }

    public function test_update_returns_null_for_nonexistent_goal(): void
    {
        // Try to update a nonexistent goal
        $updatedGoal = $this->repository->update(999, [
            'name' => 'New Name',
        ]);

        // Should return null
        $this->assertNull($updatedGoal);
    }

    public function test_can_delete_goal(): void
    {
        // Create a goal
        $goal = FinancialGoal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->savingsCategory->id,
        ]);

        // Delete the goal
        $result = $this->repository->delete($goal->id);

        // Should return true and goal should be deleted
        $this->assertTrue($result);
        $this->assertNull(FinancialGoal::find($goal->id));
    }

    public function test_delete_returns_false_for_nonexistent_goal(): void
    {
        // Try to delete a nonexistent goal
        $result = $this->repository->delete(999);

        // Should return false
        $this->assertFalse($result);
    }

    public function test_can_update_amount_with_increment(): void
    {
        // Create a goal with initial amount
        $goal = FinancialGoal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->savingsCategory->id,
            'target_amount' => 1000.00,
            'current_amount' => 200.00,
            'is_completed' => false,
        ]);

        // Update amount by incrementing
        $updatedGoal = $this->repository->updateAmount($goal->id, 300.00, true);

        // Current amount should increase by the amount
        $this->assertEquals(500.00, $updatedGoal->current_amount);
        $this->assertFalse($updatedGoal->is_completed);
    }

    public function test_can_update_amount_without_increment(): void
    {
        // Create a goal with initial amount
        $goal = FinancialGoal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->savingsCategory->id,
            'target_amount' => 1000.00,
            'current_amount' => 200.00,
            'is_completed' => false,
        ]);

        // Update amount by setting directly
        $updatedGoal = $this->repository->updateAmount($goal->id, 300.00, false);

        // Current amount should be set to the exact amount
        $this->assertEquals(300.00, $updatedGoal->current_amount);
        $this->assertFalse($updatedGoal->is_completed);
    }

    public function test_update_amount_marks_goal_as_completed_when_target_reached(): void
    {
        // Create a goal with initial amount
        $goal = FinancialGoal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->savingsCategory->id,
            'target_amount' => 1000.00,
            'current_amount' => 800.00,
            'is_completed' => false,
        ]);

        // Update amount to exceed target
        $updatedGoal = $this->repository->updateAmount($goal->id, 300.00, true);

        // Current amount should update and goal should be marked as completed
        $this->assertEquals(1100.00, $updatedGoal->current_amount);
        $this->assertTrue($updatedGoal->is_completed);
    }

    public function test_update_amount_returns_null_for_nonexistent_goal(): void
    {
        // Try to update amount for a nonexistent goal
        $updatedGoal = $this->repository->updateAmount(999, 100.00);

        // Should return null
        $this->assertNull($updatedGoal);
    }

    public function test_can_get_active_goals(): void
    {
        // Create active goals with different types
        FinancialGoal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->savingsCategory->id,
            'is_active' => true,
            'type' => 'savings',
        ]);

        FinancialGoal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->investmentCategory->id,
            'is_active' => true,
            'type' => 'investment',
        ]);

        // Create inactive goal
        FinancialGoal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->savingsCategory->id,
            'is_active' => false,
            'type' => 'savings',
        ]);

        // Get all active goals
        $activeGoals = $this->repository->getActiveGoals($this->user->id);

        // Should return 2 active goals (both savings and investment)
        $this->assertCount(2, $activeGoals);
        foreach ($activeGoals as $goal) {
            $this->assertTrue($goal->is_active);
        }

        // Get active goals with savings type
        $savingsGoals = $this->repository->getActiveGoals($this->user->id, 'savings');

        // Should return 1 active savings goal
        $this->assertCount(1, $savingsGoals);
        foreach ($savingsGoals as $goal) {
            $this->assertTrue($goal->is_active);
            $this->assertEquals('savings', $goal->type);
        }
    }

    public function test_can_get_goals_due_within_days(): void
    {
        $today = Carbon::today();

        // 1. Goal due in 5 days
        FinancialGoal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->savingsCategory->id,
            'is_active' => true,
            'is_completed' => false,
            'target_date' => $today->copy()->addDays(5)->toDateString(),
            'name' => 'Due Soon',
        ]);

        // 2. Goal due in 15 days
        FinancialGoal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->savingsCategory->id,
            'is_active' => true,
            'is_completed' => false,
            'target_date' => $today->copy()->addDays(15)->toDateString(),
            'name' => 'Due Later',
        ]);

        // 3. Goal due in 5 days but already completed
        FinancialGoal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->savingsCategory->id,
            'is_active' => true,
            'is_completed' => true,
            'target_date' => $today->copy()->addDays(5)->toDateString(),
            'name' => 'Completed Goal',
        ]);

        // 4. Goal due in 5 days but inactive
        FinancialGoal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->savingsCategory->id,
            'is_active' => false,
            'is_completed' => false,
            'target_date' => $today->copy()->addDays(5)->toDateString(),
            'name' => 'Inactive Goal',
        ]);

        // Get goals due within 10 days
        $goalsDueSoon = $this->repository->getGoalsDueWithin($this->user->id, 10);

        // Should only return the first goal (due in 5 days, active, not completed)
        $this->assertCount(1, $goalsDueSoon);
        $this->assertEquals('Due Soon', $goalsDueSoon->first()->name);

        // Get goals due within 20 days
        $goalsDueExtended = $this->repository->getGoalsDueWithin($this->user->id, 20);

        // Should return both due soon and due later goals
        $this->assertCount(2, $goalsDueExtended);
        $goalNames = $goalsDueExtended->pluck('name')->toArray();
        $this->assertContains('Due Soon', $goalNames);
        $this->assertContains('Due Later', $goalNames);
    }

    public function test_can_get_goal_progress(): void
    {
        // Create a goal that started 30 days ago and is due in 30 days (60 day period)
        $startDate = Carbon::today()->subDays(30);
        $targetDate = Carbon::today()->addDays(30);

        $goal = FinancialGoal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->savingsCategory->id,
            'name' => 'Progress Test Goal',
            'target_amount' => 1000.00,
            'current_amount' => 550.00, // 55% of target amount to safely exceed time percentage
            'start_date' => $startDate->toDateString(),
            'target_date' => $targetDate->toDateString(),
            'is_active' => true,
            'is_completed' => false,
        ]);

        // Get goal progress
        $progress = $this->repository->getGoalProgress($goal->id);

        // Basic checks
        $this->assertEquals($goal->id, $progress['goal']->id);
        $this->assertEquals(550.00, $progress['current_amount']);
        $this->assertEquals(450.00, $progress['remaining_amount']);

        // Amount percentage should be 55%
        $this->assertEquals(55.0, $progress['amount_percentage']);

        // Time calculations - allow for small variations due to test execution time differences
        $this->assertEquals(60, $progress['days_total']);

        // Days remaining should be approximately 30 (allow for small variations)
        $this->assertGreaterThanOrEqual(29, $progress['days_remaining']);
        $this->assertLessThanOrEqual(31, $progress['days_remaining']);

        // Days elapsed should be approximately 30 (allow for small variations)
        $this->assertGreaterThanOrEqual(29, $progress['days_elapsed']);
        $this->assertLessThanOrEqual(31, $progress['days_elapsed']);

        // Time percentage should be approximately 50% (allow for small variations)
        $this->assertGreaterThanOrEqual(48, $progress['time_percentage']);
        $this->assertLessThanOrEqual(52, $progress['time_percentage']);

        // Goal should be on track (amount percentage >= time percentage)
        $this->assertTrue($progress['is_on_track']);
        $this->assertFalse($progress['is_overdue']);
    }

    public function test_goal_progress_for_overdue_goal(): void
    {
        // Create a goal that started 60 days ago and was due 10 days ago
        $startDate = Carbon::today()->subDays(60);
        $targetDate = Carbon::today()->subDays(10);

        $goal = FinancialGoal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->savingsCategory->id,
            'name' => 'Overdue Goal',
            'target_amount' => 1000.00,
            'current_amount' => 800.00, // 80% of target amount
            'start_date' => $startDate->toDateString(),
            'target_date' => $targetDate->toDateString(),
            'is_active' => true,
            'is_completed' => false,
        ]);

        // Get goal progress
        $progress = $this->repository->getGoalProgress($goal->id);

        // Goal is overdue
        $this->assertTrue($progress['is_overdue']);

        // Remaining days should be 0 for an overdue goal
        $this->assertEquals(0, $progress['days_remaining']);

        // Time percentage should be capped at 100%
        $this->assertEquals(100.0, $progress['time_percentage']);

        // Amount percentage is 80%
        $this->assertEquals(80.0, $progress['amount_percentage']);

        // Goal is not on track (100% time elapsed, but only 80% amount saved)
        $this->assertFalse($progress['is_on_track']);
    }
}
