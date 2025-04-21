<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BudgetManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(); // Ensure we have standard seeded data
    }

    /**
     * Test the budget index page is displayed.
     */
    public function test_budget_index_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('budgets.index'));

        $response->assertStatus(200);
        $response->assertViewIs('budgets.index');
        $response->assertSee('Budget Planning');
    }

    /**
     * Test the budget creation page is displayed.
     */
    public function test_budget_create_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('budgets.create'));

        $response->assertStatus(200);
        $response->assertViewIs('budgets.create');
        $response->assertSee('Create Budget');
    }

    /**
     * Test a budget can be created.
     */
    public function test_budget_can_be_created(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $budgetData = [
            'name' => 'Test Monthly Budget',
            'category_id' => $category->id,
            'amount' => 500.00,
            'period' => 'monthly',
            'start_date' => now()->format('Y-m-d'),
            'is_active' => true,
        ];

        $response = $this->actingAs($user)
            ->post(route('budgets.store'), $budgetData);

        $response->assertRedirect();

        $this->assertDatabaseHas('budgets', [
            'name' => 'Test Monthly Budget',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 500.00,
            'period' => 'monthly',
            'is_active' => true,
        ]);
    }

    /**
     * Test a budget can be viewed.
     */
    public function test_budget_can_be_viewed(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $budget = Budget::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => 'Test Budget',
            'amount' => 500.00,
            'period' => 'monthly',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonth()->subDay()->format('Y-m-d'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->get(route('budgets.show', $budget->id));

        $response->assertStatus(200);
        $response->assertViewIs('budgets.show');
        $response->assertSee('Test Budget');
        $response->assertSee('$500.00');
    }

    /**
     * Test a budget can be edited.
     */
    public function test_budget_can_be_edited(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $budget = Budget::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => 'Test Budget',
            'amount' => 500.00,
            'period' => 'monthly',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonth()->subDay()->format('Y-m-d'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->get(route('budgets.edit', $budget->id));

        $response->assertStatus(200);
        $response->assertViewIs('budgets.edit');
        $response->assertSee('Test Budget');

        $updatedData = [
            'name' => 'Updated Budget',
            'category_id' => $category->id,
            'amount' => 800.00,
            'period' => 'monthly',
            'start_date' => now()->format('Y-m-d'),
            'is_active' => true,
        ];

        $response = $this->actingAs($user)
            ->put(route('budgets.update', $budget->id), $updatedData);

        $response->assertRedirect();

        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'name' => 'Updated Budget',
            'amount' => 800.00,
        ]);
    }

    /**
     * Test a budget can be deleted.
     */
    public function test_budget_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $budget = Budget::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => 'Test Budget',
            'amount' => 500.00,
            'period' => 'monthly',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonth()->subDay()->format('Y-m-d'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('budgets.destroy', $budget->id));

        $response->assertRedirect(route('budgets.index'));

        $this->assertDatabaseMissing('budgets', [
            'id' => $budget->id,
        ]);
    }

    /**
     * Test budget progress page is displayed.
     */
    public function test_budget_progress_page_is_displayed(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $budget = Budget::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => 'Test Budget',
            'amount' => 500.00,
            'period' => 'monthly',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonth()->subDay()->format('Y-m-d'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->get(route('budgets.progress', $budget->id));

        $response->assertStatus(200);
        $response->assertViewIs('budgets.progress');
        $response->assertSee('Budget Progress');
        $response->assertSee('Test Budget');
    }

    /**
     * Test a user cannot access another user's budget.
     */
    public function test_user_cannot_access_another_users_budget(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user1->id]);

        $budget = Budget::create([
            'user_id' => $user1->id,
            'category_id' => $category->id,
            'name' => 'User1 Budget',
            'amount' => 500.00,
            'period' => 'monthly',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonth()->subDay()->format('Y-m-d'),
            'is_active' => true,
        ]);

        // User2 tries to access User1's budget
        $response = $this->actingAs($user2)
            ->get(route('budgets.show', $budget->id));

        $response->assertStatus(403); // Forbidden

        // Trying to edit
        $response = $this->actingAs($user2)
            ->get(route('budgets.edit', $budget->id));

        $response->assertStatus(403);

        // Trying to update
        $response = $this->actingAs($user2)
            ->put(route('budgets.update', $budget->id), [
                'name' => 'Hacked Budget',
                'category_id' => $category->id,
                'amount' => 1.00,
                'period' => 'monthly',
                'start_date' => now()->format('Y-m-d'),
            ]);

        $response->assertStatus(403);

        // Trying to delete
        $response = $this->actingAs($user2)
            ->delete(route('budgets.destroy', $budget->id));

        $response->assertStatus(403);

        // Ensure the budget wasn't modified
        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'name' => 'User1 Budget',
            'amount' => 500.00,
        ]);
    }

    /**
     * Test validation errors are shown when creating a budget.
     */
    public function test_validation_errors_are_shown_when_creating_budget(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('budgets.store'), [
                // Missing required fields
                'name' => '',
                'amount' => 'not-a-number',
            ]);

        $response->assertSessionHasErrors(['name', 'amount', 'category_id', 'period', 'start_date']);
    }
}
