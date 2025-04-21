<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\FinancialGoal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class FinancialGoalTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected $user;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create a user
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create categories
        $savingsCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Savings',
            'type' => 'income',
            'color' => '#4CAF50',
            'icon' => 'piggy-bank',
        ]);

        $educationCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Education',
            'type' => 'expense',
            'color' => '#2196F3',
            'icon' => 'graduation-cap',
        ]);

        // Create a financial goal
        FinancialGoal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $savingsCategory->id,
            'name' => 'Emergency Fund',
            'target_amount' => 5000.00,
            'current_amount' => 1000.00,
            'type' => 'saving',
            'start_date' => Carbon::now()->startOfMonth(),
            'target_date' => Carbon::now()->addMonths(6),
            'is_active' => true,
        ]);
    }

    /**
     * Test user can view financial goals.
     */
    public function test_user_can_view_financial_goals(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/dashboard')
                ->assertSee('Dashboard')
                ->visit('/goals')
                ->assertPathIs('/goals')
                ->assertSee('Financial Goals')
                ->assertSee('Emergency Fund');
        });
    }

    /**
     * Test user can create a new financial goal.
     */
    public function test_user_can_create_financial_goal(): void
    {
        $this->browse(function (Browser $browser) {
            $goal = [
                'name' => 'College Fund',
                'category_id' => '2', // Education category
                'target_amount' => '10000',
                'type' => 'saving',
                'start_date' => Carbon::now()->format('Y-m-d'),
                'target_date' => Carbon::now()->addYears(2)->format('Y-m-d'),
                'is_active' => true,
            ];

            $browser->loginAs($this->user)
                ->visit('/goals')
                ->waitForText('Financial Goals')
                ->assertSee('Financial Goals')
                ->visit('/goals/create')
                ->waitForLocation('/goals/create')
                ->assertPathIs('/goals/create');

            // Fill out the form fields
            $browser->waitFor('input[name="name"]')
                ->type('name', $goal['name'])
                ->select('category_id', $goal['category_id'])
                ->type('target_amount', $goal['target_amount'])
                ->select('type', $goal['type'])
                ->type('start_date', $goal['start_date'])
                ->type('target_date', $goal['target_date']);

            if ($goal['is_active']) {
                $browser->check('is_active');
            }

            // Submit the form
            $browser->scrollIntoView('button[type="submit"]')
                ->pause(1000) // Give the page time to stabilize
                ->click('button[type="submit"]')
                ->pause(5000); // Wait for form processing

            // Verify we're back on the goals index or view page
            $browser->assertSee($goal['name'])
                ->assertSee('$10,000.00');
        });
    }

    /**
     * Test user can edit a financial goal.
     */
    public function test_user_can_edit_financial_goal(): void
    {
        $this->browse(function (Browser $browser) {
            // Get existing test goal
            $goal = $this->ensureFinancialGoalExists();

            $updatedGoal = [
                'name' => 'Updated Emergency Fund',
                'target_amount' => '6000',
            ];

            // Go directly to the edit page for the goal
            $browser->loginAs($this->user)
                ->visit("/goals/{$goal->id}/edit")
                ->waitFor('input[name="name"]', 10)
                ->assertInputValue('name', $goal->name);

            // Update the goal fields
            $browser->type('name', '')  // Clear first
                ->type('name', $updatedGoal['name'])
                ->type('target_amount', '')    // Clear first
                ->type('target_amount', $updatedGoal['target_amount'])
                ->scrollIntoView('button[type="submit"]')
                ->pause(1000) // Give time for the page to stabilize
                ->click('button[type="submit"]')
                ->pause(3000); // Wait for form processing

            // Go to the goals index page to verify
            $browser->visit('/goals')
                ->waitForText($updatedGoal['name'], 10)
                ->assertSee($updatedGoal['name'])
                ->assertSee('$6,000.00');
        });
    }

    /**
     * Test user can view financial goal progress.
     */
    public function test_user_can_view_financial_goal_progress(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/goals')
                ->assertSee('Financial Goals')
                ->click('.view-goal-button')
                ->assertPathBeginsWith('/goals/')
                ->assertSee('Goal Details')
                ->assertSee('Emergency Fund')
                ->visit('/goals/1/progress')
                ->assertSee('Goal Progress');
        });
    }

    /**
     * Helper method to ensure a financial goal exists for testing
     */
    private function ensureFinancialGoalExists(): FinancialGoal
    {
        // Find an existing test goal
        $testGoal = FinancialGoal::where('user_id', $this->user->id)->first();

        // If no goal exists, create one directly in the database
        if (! $testGoal) {
            $testGoal = FinancialGoal::create([
                'user_id' => $this->user->id,
                'name' => 'Test Goal for Editing',
                'category_id' => 1,
                'target_amount' => 5000,
                'current_amount' => 1000,
                'type' => 'saving',
                'start_date' => Carbon::now()->format('Y-m-d'),
                'target_date' => Carbon::now()->addMonths(6)->format('Y-m-d'),
                'is_active' => true,
            ]);
        }

        return $testGoal;
    }
}
