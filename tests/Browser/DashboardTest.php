<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Dashboard;
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

        // Create test categories
        $incomeCategory = Category::factory()->create([
            'user_id' => $user->id,
            'name' => 'Salary',
            'type' => 'income',
            'color' => '#4CAF50',
            'icon' => 'fa-money-bill',
        ]);

        $expenseCategory = Category::factory()->create([
            'user_id' => $user->id,
            'name' => 'Groceries',
            'type' => 'expense',
            'color' => '#F44336',
            'icon' => 'fa-shopping-cart',
        ]);

        // Create some test transactions
        Transaction::factory()->count(5)->create([
            'user_id' => $user->id,
            'category_id' => $incomeCategory->id,
            'type' => 'income',
            'amount' => 1000,
        ]);

        Transaction::factory()->count(5)->create([
            'user_id' => $user->id,
            'category_id' => $expenseCategory->id,
            'type' => 'expense',
            'amount' => 500,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user, 'web')
                ->visit('/dashboard')
                ->screenshot('dashboard')
                ->assertPathIs('/dashboard')
                ->assertSee('Financial Dashboard')
                ->assertSee('Current Balance')
                ->assertSee('Income')
                ->assertSee('Expenses')
                ->assertSee('Recent Transactions');
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

        // Create a test category
        $category = Category::factory()->create([
            'user_id' => $user->id,
            'name' => 'Salary',
            'type' => 'income',
        ]);

        // Create some test transactions
        Transaction::factory()->count(5)->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user, 'web')
                ->visit('/dashboard')
                ->screenshot('dashboard-before-navigation')
                ->assertPathIs('/dashboard')
                // Visit transactions directly
                ->visit('/transactions')
                ->screenshot('transactions-from-dashboard')
                ->assertPathIs('/transactions');
        });
    }
}
