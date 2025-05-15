<?php

namespace Tests\Browser;

use App\Models\ChartOfAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\ChartOfAccountCreatePage;
use Tests\Browser\Pages\ChartOfAccountEditPage;
use Tests\Browser\Pages\ChartOfAccountIndexPage;
use Tests\DuskTestCase;

class ChartOfAccountManagementDuskTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        // Seed the COA for the user, so there are parent accounts to select from
        $this->artisan('db:seed', ['--class' => 'ChartOfAccountSeeder']);
    }

    /**
     * Test navigation to the chart of accounts index page.
     *
     * @return void
     */
    public function test_user_can_navigate_to_chart_of_accounts_index()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit(new ChartOfAccountIndexPage)
                ->assertSee('Chart of Accounts');
        });
    }

    /**
     * Test creating a new chart of account.
     *
     * @return void
     */
    public function test_user_can_create_new_account()
    {
        // Pre-fetch parent_account_id outside the browser callback
        $parentAccount = ChartOfAccount::where('user_id', $this->user->id)
            ->where('name', 'Cash and Cash Equivalents')
            ->first();
        $parentId = $parentAccount ? $parentAccount->id : null;

        $this->browse(function (Browser $browser) use ($parentId) {
            $browser->loginAs($this->user)
                ->visit(new ChartOfAccountIndexPage)
                ->clickAddAccountButton()
                ->on(new ChartOfAccountCreatePage)
                ->createAccount([
                    'account_code' => '11100',
                    'name' => 'Test Bank Account',
                    'type' => 'asset',
                    'description' => 'A test bank account for Dusk.',
                    'parent_id' => $parentId, // Use pre-fetched ID
                    'is_active' => true,
                    'allow_direct_posting' => true,
                ])
                ->screenshot('post-create-account-submission')
                // Try to wait for redirect back to index page
                ->pause(1000)
                // Manually navigate back to index page if redirect doesn't happen
                ->visit(new ChartOfAccountIndexPage)
                ->screenshot('back-to-index-after-create')
                // Verify the account was created by looking for it on the index page
                ->assertSeeAccount('Test Bank Account', '11100');
        });
    }

    /**
     * Test editing an existing chart of account.
     *
     * @return void
     */
    public function test_user_can_edit_existing_account()
    {
        // First, create an account to edit
        $account = ChartOfAccount::factory()->for($this->user)->create([
            'account_code' => '11110',
            'name' => 'Original Name',
            'type' => 'asset',
        ]);

        $this->browse(function (Browser $browser) use ($account) {
            $browser->loginAs($this->user)
                ->visit(new ChartOfAccountIndexPage)
                ->clickEditAccountButton($account->id)
                ->screenshot('debug_before_edit_page_assertion') // Moved here
                ->dump() // Moved here
                ->on(new ChartOfAccountEditPage($account->id))
                ->updateAccount([
                    'account_code' => $account->account_code,
                    'name' => 'Updated Test Account Name',
                    'type' => $account->type,
                    'description' => 'Updated description.',
                    'is_active' => false, // Test changing a checkbox
                ])
                ->waitForText('Account updated successfully.', 5) // Wait for redirect
                ->on(new ChartOfAccountIndexPage)
                ->assertSeeAccount('Updated Test Account Name', $account->account_code)
                ->assertSee('Account updated successfully.');

            // Assert the account is inactive in the database
            $updatedAccount = ChartOfAccount::find($account->id);
            $this->assertFalse($updatedAccount->is_active, 'Account should be inactive in the database.');

            $browser->assertSee('Inactive'); // Assuming 'Inactive' is shown for inactive accounts
        });
    }

    /**
     * Test deleting an existing chart of account.
     *
     * @return void
     */
    public function test_user_can_delete_account()
    {
        $accountToDelete = ChartOfAccount::factory()->for($this->user)->create([
            'account_code' => '00001-delete', // Changed for early sorting
            'name' => 'Account to Delete',
            'type' => 'expense',
            'allow_direct_posting' => true, // Make sure it can be deleted (no children initially)
        ]);

        $this->browse(function (Browser $browser) use ($accountToDelete) {
            $browser->loginAs($this->user)
                ->visit(new ChartOfAccountIndexPage)
                ->assertSeeAccount($accountToDelete->name, $accountToDelete->account_code)
                ->clickDeleteAccountButton($accountToDelete->id)
                ->acceptDialog() // Confirm deletion
                ->waitForText('Account deleted successfully.', 5) // Wait for redirect and message
                ->screenshot('debug_before_delete_redirect_assertion') // Added here
                ->dump() // Added here
                ->on(new ChartOfAccountIndexPage)
                ->assertDontSeeAccount($accountToDelete->name, $accountToDelete->account_code);
        });
    }

    /**
     * Test that a user cannot delete an account with child accounts.
     *
     * @return void
     */
    public function test_user_cannot_delete_account_with_children()
    {
        $parentAccount = ChartOfAccount::where('user_id', $this->user->id)->where('name', 'Assets')->first();
        // Ensure parent account exists from seeder
        if (! $parentAccount) {
            $parentAccount = ChartOfAccount::factory()->for($this->user)->create(['name' => 'Assets', 'account_code' => '10000', 'type' => 'asset']);
        }

        // Create a child account for the 'Assets' account (or any other seeded parent)
        ChartOfAccount::factory()->for($this->user)->create([
            'account_code' => '10001',
            'name' => 'Child of Assets',
            'type' => 'asset',
            'parent_id' => $parentAccount->id,
        ]);

        $this->browse(function (Browser $browser) use ($parentAccount) {
            $browser->loginAs($this->user)
                ->visit(new ChartOfAccountIndexPage)
                ->clickDeleteAccountButton($parentAccount->id)
                ->acceptDialog()
                ->on(new ChartOfAccountIndexPage)
                ->assertSee('Cannot delete account: It has child accounts. Please reassign or delete them first.') // Ensure this message is set in controller
                ->assertSeeAccount($parentAccount->name, $parentAccount->account_code); // Account should still exist
        });
    }

    // TODO: Add tests for validation (required fields, unique account code, etc.)
    // TODO: Add tests for system account tag restrictions if any (e.g., cannot change once set for certain tags)
}
