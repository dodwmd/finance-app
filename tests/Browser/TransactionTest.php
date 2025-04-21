<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TransactionTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test basic transactions page loads after login.
     */
    public function test_transactions_page_loads(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user, 'web')
                ->visit('/transactions')
                ->screenshot('transactions-page')
                ->assertPathIs('/transactions');
        });
    }

    /**
     * Test viewing transaction list with actual transactions.
     */
    public function test_view_transaction_list(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create test categories
        $category = Category::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Category',
            'type' => 'expense',
        ]);

        // Create a specific transaction
        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'description' => 'Test Transaction Item',
            'amount' => 123.45,
            'type' => 'expense',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user, 'web')
                ->visit('/transactions')
                ->screenshot('transaction-list-with-items')
                ->assertSee('Test Transaction Item');
        });
    }
}
