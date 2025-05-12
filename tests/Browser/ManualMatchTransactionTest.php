<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ManualMatchTransactionTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * A basic test to verify the login page works
     */
    public function test_login_page_works(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertPathIs('/login');
        });
    }
}
