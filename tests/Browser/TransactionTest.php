<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Transactions;
use Tests\DuskTestCase;

class TransactionTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test viewing the transaction list.
     */
    public function test_view_transaction_list(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create some test transactions
        Transaction::factory()->count(5)->create([
            'user_id' => $user->id,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(new Transactions)
                ->assertVisible('@transaction-list');
        });
    }

    /**
     * Test creating a new transaction.
     */
    public function test_create_transaction(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create a test category
        $category = Category::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Category',
            'type' => 'expense',
        ]);

        $this->browse(function (Browser $browser) use ($user, $category) {
            $browser->loginAs($user)
                ->visit(new Transactions)
                ->click('@add-transaction-btn')
                ->waitFor('@transaction-form')
                ->type('@description-input', 'Test Transaction')
                ->type('@amount-input', '50.00')
                ->select('@category-select', $category->id)
                ->select('@type-select', 'expense')
                ->type('@date-input', date('Y-m-d'))
                ->click('@save-btn')
                ->waitForText('Transaction created successfully')
                ->assertSee('Test Transaction');
        });
    }

    /**
     * Test editing an existing transaction.
     */
    public function test_edit_transaction(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create a test transaction
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'description' => 'Original Transaction',
            'amount' => 100.00,
        ]);

        $this->browse(function (Browser $browser) use ($user, $transaction) {
            $transactionsPage = new Transactions;
            $editSelector = $transactionsPage->elements()['@edit-transaction']($transaction->id);

            $browser->loginAs($user)
                ->visit(new Transactions)
                ->click($editSelector)
                ->waitFor('@transaction-form')
                ->assertInputValue('@description-input', 'Original Transaction')
                ->type('@description-input', 'Updated Transaction')
                ->type('@amount-input', '150.00')
                ->click('@save-btn')
                ->waitForText('Transaction updated successfully')
                ->assertSee('Updated Transaction')
                ->assertSee('150.00');
        });
    }

    /**
     * Test deleting a transaction.
     */
    public function test_delete_transaction(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create a test transaction
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'description' => 'Transaction to Delete',
        ]);

        $this->browse(function (Browser $browser) use ($user, $transaction) {
            $transactionsPage = new Transactions;
            $deleteSelector = $transactionsPage->elements()['@delete-transaction']($transaction->id);

            $browser->loginAs($user)
                ->visit(new Transactions)
                ->assertSee('Transaction to Delete')
                ->click($deleteSelector)
                ->waitForDialog()
                ->acceptDialog()
                ->waitForText('Transaction deleted successfully')
                ->assertDontSee('Transaction to Delete');
        });
    }
}
