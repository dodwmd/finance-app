<?php

namespace Tests\Feature;

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
        
        Transaction::factory()->count(5)->create([
            'user_id' => $user->id,
        ]);
        
        $response = $this->actingAs($user)->get('/transactions');
        
        $response->assertStatus(200);
        $response->assertViewIs('transactions.index');
    }
    
    public function test_user_can_create_a_transaction(): void
    {
        $user = User::factory()->create();
        
        $transactionData = [
            'description' => 'New Test Transaction',
            'amount' => 150.75,
            'type' => 'income',
            'category' => 'Salary',
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
            'category' => 'Salary',
        ]);
    }
    
    public function test_user_can_update_a_transaction(): void
    {
        $user = User::factory()->create();
        
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'description' => 'Original Transaction',
            'amount' => 100.00,
            'type' => 'expense',
            'category' => 'Food',
        ]);
        
        $updatedData = [
            'description' => 'Updated Transaction',
            'amount' => 150.00,
            'type' => 'expense',
            'category' => 'Shopping',
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
            'category' => 'Shopping',
        ]);
    }
    
    public function test_user_can_delete_a_transaction(): void
    {
        $user = User::factory()->create();
        
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
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
        
        $transaction = Transaction::factory()->create([
            'user_id' => $user1->id,
            'description' => 'User 1 Transaction',
            'transaction_date' => now(),
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
            'category' => 'Other',
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
