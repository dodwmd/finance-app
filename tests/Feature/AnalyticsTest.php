<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Category $expenseCategory;

    private Category $incomeCategory;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();

        // Create test categories
        $this->expenseCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'name' => 'Test Expense',
            'color' => '#FF5733',
        ]);

        $this->incomeCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income',
            'name' => 'Test Income',
            'color' => '#33FF57',
        ]);

        // Create some transactions for testing
        $this->createTestTransactions();
    }

    /**
     * Create test transactions for analytics
     */
    private function createTestTransactions(): void
    {
        // Create income transactions
        Transaction::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
            'type' => 'income',
            'amount' => 1000,
            'transaction_date' => now()->subDays(5)->format('Y-m-d'),
        ]);

        // Create expense transactions
        Transaction::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'type' => 'expense',
            'amount' => 200,
            'transaction_date' => now()->subDays(3)->format('Y-m-d'),
        ]);

        // Create a transaction from previous month for comparison
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'type' => 'expense',
            'amount' => 500,
            'transaction_date' => now()->subMonth()->format('Y-m-d'),
        ]);

        // Create a transaction from previous month for comparison
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
            'type' => 'income',
            'amount' => 2000,
            'transaction_date' => now()->subMonth()->format('Y-m-d'),
        ]);
    }

    /**
     * Test that the analytics index page can be accessed when authenticated
     */
    public function test_analytics_index_page_can_be_rendered_when_authenticated(): void
    {
        $response = $this->actingAs($this->user)->get(route('analytics.index'));

        $response->assertStatus(200);
        $response->assertViewIs('analytics.index');
        $response->assertViewHas('analyticsData');
        $response->assertViewHas('categories');
        $response->assertViewHas('period');
    }

    /**
     * Test that analytics index redirects when not authenticated
     */
    public function test_analytics_index_redirects_when_not_authenticated(): void
    {
        $response = $this->get(route('analytics.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test analytics index with period parameter
     */
    public function test_analytics_index_with_period_parameter(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('analytics.index', ['period' => 'week']));

        $response->assertStatus(200);
        $response->assertViewHas('period', 'week');

        // Validate the date range in analytics data is for a week
        $analyticsData = $response->viewData('analyticsData');
        $this->assertEquals('week', $analyticsData['period']);
        $this->assertArrayHasKey('dateRange', $analyticsData);
    }

    /**
     * Test analytics index with category filter
     */
    public function test_analytics_index_with_category_filter(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('analytics.index', ['category' => $this->expenseCategory->id]));

        $response->assertStatus(200);
        $response->assertViewHas('categoryId', $this->expenseCategory->id);
    }

    /**
     * Test that the expenses page can be accessed when authenticated
     */
    public function test_expenses_page_can_be_rendered_when_authenticated(): void
    {
        $response = $this->actingAs($this->user)->get(route('analytics.expenses'));

        $response->assertStatus(200);
        $response->assertViewIs('analytics.expenses');
        $response->assertViewHas('expenseTrends');
        $response->assertViewHas('categoryComparison');
        $response->assertViewHas('spendingPatterns');
    }

    /**
     * Test that expenses page redirects when not authenticated
     */
    public function test_expenses_page_redirects_when_not_authenticated(): void
    {
        $response = $this->get(route('analytics.expenses'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test expenses with period parameter
     */
    public function test_expenses_with_period_parameter(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('analytics.expenses', ['period' => 'quarter']));

        $response->assertStatus(200);
        $response->assertViewHas('period', 'quarter');

        // Verify category comparison has current and previous data
        $categoryComparison = $response->viewData('categoryComparison');
        $this->assertArrayHasKey('current', $categoryComparison);
        $this->assertArrayHasKey('previous', $categoryComparison);
    }

    /**
     * Test that the income page can be accessed when authenticated
     */
    public function test_income_page_can_be_rendered_when_authenticated(): void
    {
        $response = $this->actingAs($this->user)->get(route('analytics.income'));

        $response->assertStatus(200);
        $response->assertViewIs('analytics.income');
        $response->assertViewHas('incomeAnalysis');
        $response->assertViewHas('period');
    }

    /**
     * Test that income page redirects when not authenticated
     */
    public function test_income_page_redirects_when_not_authenticated(): void
    {
        $response = $this->get(route('analytics.income'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test income with period parameter
     */
    public function test_income_with_period_parameter(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('analytics.income', ['period' => 'year']));

        $response->assertStatus(200);
        $response->assertViewHas('period', 'year');
    }

    /**
     * Test that the comparison page can be accessed when authenticated
     */
    public function test_comparison_page_can_be_rendered_when_authenticated(): void
    {
        $response = $this->actingAs($this->user)->get(route('analytics.comparison'));

        $response->assertStatus(200);
        $response->assertViewIs('analytics.comparison');
        $response->assertViewHas('comparisonData');
        $response->assertViewHas('period');
        $response->assertViewHas('compareWith');
    }

    /**
     * Test that comparison page redirects when not authenticated
     */
    public function test_comparison_page_redirects_when_not_authenticated(): void
    {
        $response = $this->get(route('analytics.comparison'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test comparison with parameters
     */
    public function test_comparison_with_parameters(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('analytics.comparison', [
                'period' => 'month',
                'compare' => 'previous',
            ]));

        $response->assertStatus(200);
        $response->assertViewHas('period', 'month');
        $response->assertViewHas('compareWith', 'previous');

        // Verify comparison data structure
        $comparisonData = $response->viewData('comparisonData');
        $this->assertArrayHasKey('current', $comparisonData);
        $this->assertArrayHasKey('comparison', $comparisonData);
        $this->assertArrayHasKey('percentChanges', $comparisonData);
    }
}
