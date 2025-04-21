<?php

namespace Tests\Browser;

use App\Models\User;
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
    }

    /**
     * Test user can view financial goals.
     */
    public function test_user_can_view_financial_goals(): void
    {
        $this->markTestSkipped('Financial Goals feature not implemented yet.');
    }

    /**
     * Test user can create financial goal.
     */
    public function test_user_can_create_financial_goal(): void
    {
        $this->markTestSkipped('Financial Goals feature not implemented yet.');
    }

    /**
     * Test user can edit financial goal.
     */
    public function test_user_can_edit_financial_goal(): void
    {
        $this->markTestSkipped('Financial Goals feature not implemented yet.');
    }

    /**
     * Test user can view financial goal progress.
     */
    public function test_user_can_view_financial_goal_progress(): void
    {
        $this->markTestSkipped('Financial Goals feature not implemented yet.');
    }
}
