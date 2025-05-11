<?php

namespace Tests\Browser;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Testing\Attributes\Test;
use Tests\Browser\Pages\BankAccountCreatePage;
use Tests\Browser\Pages\BankAccountEditPage;
use Tests\Browser\Pages\BankAccountIndexPage;
use Tests\DuskTestCase;

class BankAccountManagementDuskTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test that a user can view the bank account index page.
     */
    #[Test]
    public function test_user_can_view_bank_account_index_page(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit(new BankAccountIndexPage)
                ->assertSee('Bank Accounts');
        });
    }

    #[Test]
    public function user_can_navigate_to_create_bank_account_page(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit(new BankAccountIndexPage)
                ->click('@addNewAccountButton')
                ->on(new BankAccountCreatePage)
                ->assertSee('Add New Bank Account');
        });
    }

    #[Test]
    public function user_can_create_a_bank_account(): void
    {
        $this->browse(function (Browser $browser) {
            $accountName = 'My Test Checking Account With BSB';
            $inputBsb = '654321';
            $expectedFormattedBsb = '654-321';

            $browser->loginAs($this->user)
                ->visit(new BankAccountCreatePage)
                ->type('@accountNameInput', $accountName)
                ->select('@accountTypeSelect', 'bank')
                ->type('@bsbInput', $inputBsb)
                ->type('@openingBalanceInput', '1000.50')
                ->click('@createAccountButton')
                ->on(new BankAccountIndexPage)
                ->assertSee($accountName)
                ->assertSeeIn("table[dusk='bank-accounts-table'] tbody tr:contains('{$accountName}') td:nth-child(4)", $expectedFormattedBsb)
                ->assertSeeIn('@successMessage', 'Bank account created successfully.');
        });
    }

    #[Test]
    public function user_can_edit_a_bank_account(): void
    {
        $bankAccount = BankAccount::factory()->for($this->user)->create(['name' => 'Original Name', 'current_balance' => 500]);
        $updatedName = 'Updated Account Name';
        $updatedBalance = '1234.56';

        $this->browse(function (Browser $browser) use ($bankAccount, $updatedName, $updatedBalance) {
            $browser->loginAs($this->user)
                ->visit(new BankAccountIndexPage)
                ->click((new BankAccountIndexPage)->editAccountLink($bankAccount->id))
                ->on(new BankAccountEditPage($bankAccount->id))
                ->assertInputValue('@accountNameInput', $bankAccount->name)
                ->type('@accountNameInput', $updatedName)
                ->type('@currentBalanceInput', $updatedBalance)
                ->click('@updateAccountButton')
                ->on(new BankAccountIndexPage)
                ->assertSee($updatedName)
                ->assertSeeIn('@successMessage', 'Bank account updated successfully.');
        });
    }

    #[Test]
    public function user_can_delete_a_bank_account(): void
    {
        $bankAccountToDelete = BankAccount::factory()->for($this->user)->create(['name' => 'Account To Delete']);

        $this->browse(function (Browser $browser) use ($bankAccountToDelete) {
            $browser->loginAs($this->user)
                ->visit(new BankAccountIndexPage)
                ->assertSee($bankAccountToDelete->name)
                ->click((new BankAccountIndexPage)->deleteAccountButton($bankAccountToDelete->id))
                ->acceptDialog()
                ->on(new BankAccountIndexPage)
                ->waitUntilMissingText($bankAccountToDelete->name)
                ->assertDontSee($bankAccountToDelete->name)
                ->assertSeeIn('@successMessage', 'Bank account deleted successfully.');
        });
    }
}
