<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertSee('Financial Dashboard');
        $response->assertSee('Current Balance');
        $response->assertSee('Income vs Expenses');
        $response->assertSee('Expense Breakdown');
        $response->assertSee('Recent Transactions');
    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_dashboard_shows_user_transactions(): void
    {
        $user = User::factory()->create();

        // Create categories for the transactions
        $incomeCategory = Category::create([
            'user_id' => $user->id,
            'name' => 'Gift',
            'type' => 'income',
            'color' => '#4CAF50',
            'icon' => 'gift'
        ]);
        
        $expenseCategory = Category::create([
            'user_id' => $user->id,
            'name' => 'Shopping',
            'type' => 'expense',
            'color' => '#F44336',
            'icon' => 'shopping-cart'
        ]);

        // Create transactions for this user
        Transaction::factory()->count(3)->create([
            'user_id' => $user->id,
            'type' => 'income',
            'amount' => 100.00,
            'category_id' => $incomeCategory->id,
            'description' => 'Test Income',
            'transaction_date' => now(),
        ]);

        Transaction::factory()->count(2)->create([
            'user_id' => $user->id,
            'type' => 'expense',
            'amount' => 50.00,
            'category_id' => $expenseCategory->id,
            'description' => 'Test Expense',
            'transaction_date' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);

        // Check for balance instead of specific transactions
        // Balance should be 3*100 - 2*50 = 300 - 100 = 200
        $response->assertSee('$200.00');
    }
}
