<?php

namespace Tests\Browser;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class BudgetTest extends DuskTestCase
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

        // Create expense categories
        $groceriesCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Groceries',
            'type' => 'expense',
            'color' => '#4CAF50',
            'icon' => 'shopping-cart',
        ]);

        $rentCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Rent',
            'type' => 'expense',
            'color' => '#2196F3',
            'icon' => 'home',
        ]);

        // Create a budget
        Budget::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $groceriesCategory->id,
            'name' => 'Monthly Groceries',
            'amount' => 500.00,
            'period' => 'monthly',
            'start_date' => Carbon::now()->startOfMonth(),
            'end_date' => Carbon::now()->endOfMonth(),
            'is_active' => true,
        ]);
    }

    /**
     * Test user can view budgets.
     */
    public function test_user_can_view_budgets(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/dashboard')
                ->assertSee('Dashboard')
                ->click('@nav-budgets')
                ->assertPathIs('/budgets')
                ->assertSee('Budgets')
                ->assertSee('Monthly Groceries');
        });
    }

    /**
     * Test user can create a new budget.
     */
    public function test_user_can_create_budget(): void
    {
        $this->browse(function (Browser $browser) {
            $budget = [
                'name' => 'Monthly Rent Budget',
                'category_id' => '2', // Rent category
                'amount' => '1200',
                'period' => 'monthly',
                'start_date' => Carbon::now()->format('Y-m-d'),
                'is_active' => true,
            ];

            $browser->loginAs($this->user)
                ->visit('/budgets')
                ->waitForText('Budgets')
                ->assertSee('Budgets')
                ->waitFor('@create-budget')
                ->click('@create-budget')
                ->waitForLocation('/budgets/create')
                ->assertPathIs('/budgets/create');

            // Fill out the form fields
            $browser->waitFor('input[name="name"]')
                ->type('name', $budget['name'])
                ->select('category_id', $budget['category_id'])
                ->type('amount', $budget['amount'])
                ->select('period', $budget['period'])
                ->type('start_date', $budget['start_date']);

            if ($budget['is_active']) {
                $browser->check('is_active');
            }

            // Submit the form
            $browser->scrollIntoView('@submit-create-budget')
                ->pause(1000) // Give the page time to stabilize
                ->click('@submit-create-budget')
                ->pause(5000); // Wait for form processing

            // Verify we're back on the budget index or view page
            $browser->assertSee($budget['name'])
                ->assertSee('$1,200.00');
        });
    }

    /**
     * Test user can edit a budget.
     */
    public function test_user_can_edit_budget(): void
    {
        $this->browse(function (Browser $browser) {
            // Create a test budget in the database or get an existing one
            $budget = $this->ensureBudgetExists();

            $updatedBudget = [
                'name' => 'Updated Groceries Budget',
                'amount' => '600',
            ];

            // Go directly to the edit page for the budget
            $browser->loginAs($this->user)
                ->visit("/budgets/{$budget->id}/edit")
                ->waitFor('input[name="name"]', 10)
                ->assertInputValue('name', $budget->name);

            // Update the budget fields
            $browser->type('name', '')  // Clear first
                ->type('name', $updatedBudget['name'])
                ->type('amount', '')    // Clear first
                ->type('amount', $updatedBudget['amount'])
                ->scrollIntoView('@submit-update-budget')
                ->pause(1000) // Give time for the page to stabilize
                ->click('@submit-update-budget')
                ->pause(3000); // Wait for form processing

            // Go to the budget index page to verify
            $browser->visit('/budgets')
                ->waitForText($updatedBudget['name'], 10)
                ->assertSee($updatedBudget['name'])
                ->assertSee('$600.00');
        });
    }

    /**
     * Helper method to ensure a budget exists for testing
     */
    private function ensureBudgetExists(): Budget
    {
        // Find an existing test budget
        $testBudget = Budget::where('user_id', $this->user->id)->first();

        // If no budget exists, create one directly in the database
        if (! $testBudget) {
            $testBudget = Budget::create([
                'user_id' => $this->user->id,
                'name' => 'Test Budget for Editing',
                'category_id' => 1,
                'amount' => 800,
                'period' => 'monthly',
                'start_date' => Carbon::now()->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonth()->format('Y-m-d'),
                'is_active' => true,
            ]);
        }

        return $testBudget;
    }

    /**
     * Test user can view budget progress.
     */
    public function test_user_can_view_budget_progress(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/budgets')
                ->assertSee('Budgets')
                ->click('@view-budget')
                ->assertPathBeginsWith('/budgets/1')
                ->assertSee('Monthly Groceries')
                ->assertSee('Budget Details')
                ->click('@view-progress')
                ->assertPathBeginsWith('/budgets/1/progress')
                ->assertSee('Budget Progress');
        });
    }

    /**
     * Test budget filters work.
     */
    public function test_budget_filters_work(): void
    {
        // Create an additional budget for testing filters
        Budget::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => 2, // Rent category
            'name' => 'Quarterly Rent Budget',
            'amount' => 3600.00,
            'period' => 'quarterly',
            'start_date' => Carbon::now()->startOfQuarter(),
            'end_date' => Carbon::now()->endOfQuarter(),
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/budgets')
                ->assertSee('Budgets')
                ->waitFor('table')
                ->assertSee('Monthly Groceries')
                ->assertSee('Quarterly Rent Budget');

            // Test filtering will be done when filter UI elements are available
        });
    }
}
