<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_transaction_with_category()
    {
        // Create a user
        $user = User::factory()->create();
        
        // Create a category
        $category = Category::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Category',
            'type' => 'expense',
            'color' => '#FF0000',
            'icon' => 'fa-tag'
        ]);
        
        // Login the user
        $this->actingAs($user);
        
        // Create a transaction with the category
        $response = $this->post(route('transactions.store'), [
            'description' => 'Test Transaction',
            'amount' => 100.00,
            'transaction_date' => now()->format('Y-m-d'),
            'category_id' => $category->id,
            'type' => 'expense'
        ]);
        
        // Assert the transaction was created successfully
        $response->assertStatus(302); // Redirect status
        
        // Assert the transaction exists in the database with the correct category
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'description' => 'Test Transaction',
            'category_id' => $category->id,
            'type' => 'expense'
        ]);
    }
    
    public function test_update_transaction_category()
    {
        // Create a user
        $user = User::factory()->create();
        
        // Create categories
        $categoryExpense = Category::factory()->create([
            'user_id' => $user->id,
            'name' => 'Expense Category',
            'type' => 'expense',
            'color' => '#FF0000',
            'icon' => 'fa-tag'
        ]);
        
        $categoryIncome = Category::factory()->create([
            'user_id' => $user->id,
            'name' => 'Income Category',
            'type' => 'income',
            'color' => '#00FF00',
            'icon' => 'fa-money-bill'
        ]);
        
        // Create a transaction
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'description' => 'Original Transaction',
            'amount' => 100.00,
            'category_id' => $categoryExpense->id,
            'type' => 'expense'
        ]);
        
        // Login the user
        $this->actingAs($user);
        
        // Update the transaction with a new category
        $response = $this->put(route('transactions.update', $transaction), [
            'description' => 'Updated Transaction',
            'amount' => 200.00,
            'transaction_date' => now()->format('Y-m-d'),
            'category_id' => $categoryIncome->id,
            'type' => 'income'
        ]);
        
        // Assert the transaction was updated successfully
        $response->assertStatus(302); // Redirect status
        
        // Assert the transaction was updated in the database with the new category
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'description' => 'Updated Transaction',
            'category_id' => $categoryIncome->id,
            'type' => 'income'
        ]);
    }
    
    public function test_transaction_belongs_to_category()
    {
        // Create a user
        $user = User::factory()->create();
        
        // Create a category
        $category = Category::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Category',
            'type' => 'expense'
        ]);
        
        // Create a transaction with the category
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'expense'
        ]);
        
        // Assert the transaction belongs to the category
        $this->assertEquals($category->id, $transaction->category->id);
        $this->assertEquals($category->name, $transaction->category->name);
        $this->assertEquals($category->type, $transaction->category->type);
    }
    
    public function test_user_has_access_to_their_categories()
    {
        // Create a user
        $user = User::factory()->create();
        
        // Create categories for the user
        Category::factory()->count(3)->create([
            'user_id' => $user->id
        ]);
        
        // Login the user
        $this->actingAs($user);
        
        // Access the user's categories
        $categories = $user->categories;
        
        // Assert the user has access to their categories
        $this->assertCount(3, $categories);
    }
}
