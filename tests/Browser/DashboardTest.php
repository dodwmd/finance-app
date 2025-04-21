<?php

namespace Tests\Browser;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Dashboard;
use Tests\Browser\Pages\Transactions;
use Tests\DuskTestCase;

class DashboardTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test the dashboard displays all financial components
     */
    public function test_dashboard_shows_financial_components(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create some test transactions
        Transaction::factory()->count(10)->create([
            'user_id' => $user->id,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(new Dashboard)
                    // Check for financial summary cards
                ->assertVisible('@current-balance')
                ->assertVisible('@monthly-income')
                ->assertVisible('@monthly-expenses')
                    // Check for charts
                ->assertVisible('@income-expense-chart')
                ->assertVisible('@expense-category-chart')
                    // Check for recent transactions
                ->assertVisible('@recent-transactions');
        });
    }

    /**
     * Test transactions can be viewed from the dashboard
     */
    public function test_view_transactions_from_dashboard(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create some test transactions
        $transactions = Transaction::factory()->count(5)->create([
            'user_id' => $user->id,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(new Dashboard)
                ->click('@view-all-transactions')
                ->on(new Transactions);
        });
    }
}
