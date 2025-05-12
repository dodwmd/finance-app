<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class BankStatementColumnMappingTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Set up before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * A basic test to verify the bank accounts page works
     */
    public function test_bank_accounts_page_works(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/bank-accounts')
                ->assertPathIs('/bank-accounts');
        });
    }
}
