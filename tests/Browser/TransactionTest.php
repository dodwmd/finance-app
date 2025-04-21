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
     * Login a user using the login form.
     */
    protected function loginUser(Browser $browser, $email = 'test@example.com', $password = 'password'): Browser
    {
        return $browser->visit('/login')
            ->type('email', $email)
            ->type('password', $password)
            ->screenshot('login-form-filled')
            ->press('button[type="submit"]') // Use CSS selector instead of text
            ->waitForLocation('/dashboard')
            ->screenshot('after-login');
    }

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

        // Create test categories
        $incomeCategory = Category::factory()->create([
            'user_id' => $user->id,
            'name' => 'Salary',
            'type' => 'income',
            'color' => '#4CAF50',
            'icon' => 'fa-money-bill'
        ]);

        // Create some test transactions
        Transaction::factory()->count(5)->create([
            'user_id' => $user->id,
            'category_id' => $incomeCategory->id,
            'type' => 'income',
            'description' => 'Test Salary Payment',
            'amount' => 1000
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginUser($browser)
                ->assertPathIs('/dashboard')
                ->visit('/transactions')
                ->screenshot('transaction-list')
                // Look for text in the header
                ->assertSee('Add Transaction')
                ->assertSee('Test Salary Payment');
        });
    }
    
    /**
     * Test transactions page loads after login.
     */
    public function test_transactions_page_loads(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginUser($browser)
                ->assertPathIs('/dashboard')
                ->visit('/transactions')
                ->screenshot('transactions-page')
                ->assertSee('Add Transaction')
                ->assertPathIs('/transactions');
        });
    }

    /**
     * Test creating a transaction after login.
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
            'color' => '#F44336',
            'icon' => 'fa-shopping-cart'
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginUser($browser)
                ->visit('/transactions')
                ->screenshot('before-add-transaction')
                ->clickLink('Add Transaction')
                ->screenshot('add-transaction-form')
                ->type('description', 'Test Transaction')
                ->type('amount', '50.00')
                ->select('category_id', (string)$category->id)
                ->select('type', 'expense')
                ->type('date', date('Y-m-d'))
                ->press('Save')
                ->screenshot('after-create')
                ->assertPathIs('/transactions')
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

        // Create test categories
        $expenseCategory = Category::factory()->create([
            'user_id' => $user->id,
            'name' => 'Groceries',
            'type' => 'expense',
            'color' => '#F44336',
            'icon' => 'fa-shopping-cart'
        ]);
        
        $incomeCategory = Category::factory()->create([
            'user_id' => $user->id,
            'name' => 'Salary',
            'type' => 'income',
            'color' => '#4CAF50',
            'icon' => 'fa-money-bill'
        ]);

        // Create a test transaction
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'description' => 'Original Transaction',
            'amount' => 100.00,
            'category_id' => $expenseCategory->id,
            'type' => 'expense'
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginUser($browser)
                ->visit('/transactions')
                ->screenshot('before-edit')
                ->assertSee('Original Transaction')
                ->clickLink('Edit')
                ->waitFor('form')
                ->screenshot('edit-form')
                ->assertInputValue('description', 'Original Transaction')
                ->type('description', 'Updated Transaction')
                ->type('amount', '150.00')
                ->select('category_id', (string)$incomeCategory->id)
                ->select('type', 'income')
                ->press('Save')
                ->screenshot('after-edit')
                ->assertPathIs('/transactions')
                ->assertSee('Updated Transaction');
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

        // Create a test category
        $category = Category::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Category',
            'type' => 'expense',
            'color' => '#F44336', 
            'icon' => 'fa-shopping-cart'
        ]);

        // Create a test transaction
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'description' => 'Transaction to Delete',
            'category_id' => $category->id,
            'type' => 'expense'
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginUser($browser)
                ->visit('/transactions')
                ->screenshot('before-delete')
                ->assertSee('Transaction to Delete')
                ->press('Delete') // This assumes there's a Delete button
                ->waitForDialog()
                ->acceptDialog()
                ->screenshot('after-delete')
                ->assertDontSee('Transaction to Delete');
        });
    }
}
