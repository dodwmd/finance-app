<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_transactions_index(): void
    {
        $user = User::factory()->create();

        // Create a category for test transactions
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Test Category',
            'type' => 'expense',
            'color' => '#607D8B',
            'icon' => 'tag',
        ]);

        // Create transactions with the category_id
        Transaction::factory()->count(5)->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get('/transactions');

        $response->assertStatus(200);
        $response->assertViewIs('transactions.index');
    }

    public function test_user_can_create_a_transaction(): void
    {
        $user = User::factory()->create();

        // Create a category for the test
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Salary',
            'type' => 'income',
            'color' => '#4CAF50',
            'icon' => 'money-bill',
        ]);

        $transactionData = [
            'description' => 'New Test Transaction',
            'amount' => 150.75,
            'type' => 'income',
            'category_id' => $category->id,
            'transaction_date' => '2025-04-21',
        ];

        $response = $this->actingAs($user)->post('/transactions', $transactionData);

        $response->assertRedirect('/transactions');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'description' => 'New Test Transaction',
            'amount' => 150.75,
            'type' => 'income',
            'category_id' => $category->id,
        ]);
    }

    public function test_user_can_update_a_transaction(): void
    {
        $user = User::factory()->create();

        // Create categories for the test
        $foodCategory = Category::create([
            'user_id' => $user->id,
            'name' => 'Food',
            'type' => 'expense',
            'color' => '#4CAF50',
            'icon' => 'utensils',
        ]);

        $shoppingCategory = Category::create([
            'user_id' => $user->id,
            'name' => 'Shopping',
            'type' => 'expense',
            'color' => '#3F51B5',
            'icon' => 'shopping-cart',
        ]);

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'description' => 'Original Transaction',
            'amount' => 100.00,
            'type' => 'expense',
            'category_id' => $foodCategory->id,
            'transaction_date' => now(),
        ]);

        $updatedData = [
            'description' => 'Updated Transaction',
            'amount' => 150.00,
            'type' => 'expense',
            'category_id' => $shoppingCategory->id,
            'transaction_date' => '2025-04-21',
        ];

        $response = $this->actingAs($user)
            ->put("/transactions/{$transaction->id}", $updatedData);

        $response->assertRedirect('/transactions');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'user_id' => $user->id,
            'description' => 'Updated Transaction',
            'amount' => 150.00,
            'category_id' => $shoppingCategory->id,
        ]);
    }

    public function test_user_can_delete_a_transaction(): void
    {
        $user = User::factory()->create();

        // Create a category for the test
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Test Category',
            'type' => 'expense',
            'color' => '#607D8B',
            'icon' => 'tag',
        ]);

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'description' => 'Transaction to Delete',
            'amount' => 50.00,
            'type' => 'expense',
            'category_id' => $category->id,
            'transaction_date' => now(),
        ]);

        $response = $this->actingAs($user)
            ->delete("/transactions/{$transaction->id}");

        $response->assertRedirect('/transactions');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);
    }

    public function test_user_cannot_manage_another_users_transactions(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create a category for user1
        $category = Category::create([
            'user_id' => $user1->id,
            'name' => 'Test Category',
            'type' => 'expense',
            'color' => '#607D8B',
            'icon' => 'tag',
        ]);

        $transaction = Transaction::create([
            'user_id' => $user1->id,
            'description' => 'User 1 Transaction',
            'amount' => 75.00,
            'type' => 'expense',
            'category_id' => $category->id,
            'transaction_date' => now(),
        ]);

        // Create a category for user2 (needed for update attempt)
        $user2Category = Category::create([
            'user_id' => $user2->id,
            'name' => 'Other Category',
            'type' => 'expense',
            'color' => '#F44336',
            'icon' => 'question-circle',
        ]);

        // Ensure TransactionPolicy is registered
        $this->app->make(\Illuminate\Contracts\Auth\Access\Gate::class)
            ->policy(Transaction::class, \App\Policies\TransactionPolicy::class);

        // User2 tries to view user1's transaction (this will 404 since we can't access the detail page directly)
        $response = $this->actingAs($user2)->get("/transactions/{$transaction->id}/edit");
        $response->assertStatus(403);

        // User2 tries to update user1's transaction
        $response = $this->actingAs($user2)->put("/transactions/{$transaction->id}", [
            'description' => 'Unauthorized Update',
            'amount' => 999.99,
            'type' => 'expense',
            'category_id' => $user2Category->id,
            'transaction_date' => '2025-04-21',
        ]);
        $response->assertStatus(403);

        // User2 tries to delete user1's transaction
        $response = $this->actingAs($user2)->delete("/transactions/{$transaction->id}");
        $response->assertStatus(403);

        // Verify the transaction still exists and is unchanged
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'user_id' => $user1->id,
            'description' => 'User 1 Transaction',
        ]);
    }
}
