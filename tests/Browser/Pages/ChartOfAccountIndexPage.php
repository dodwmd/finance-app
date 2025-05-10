<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page as BasePage;

class ChartOfAccountIndexPage extends BasePage
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/chart-of-accounts';
    }

    /**
     * Assert that the browser is on the page.
     *
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser->assertPathIs($this->url())
            ->assertSee('Chart of Accounts');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@add-account-button' => 'a[dusk="add-account-button"]',
            // Add other elements like table, specific rows, edit/delete buttons for specific accounts if needed
        ];
    }

    /**
     * Click the add new account button.
     *
     * @return void
     */
    public function clickAddAccountButton(Browser $browser)
    {
        $browser->click('@add-account-button');
    }

    /**
     * Assert that a specific account is visible in the list.
     *
     * @return void
     */
    public function assertSeeAccount(Browser $browser, string $accountName, string $accountCode)
    {
        $browser->assertSee($accountName)
            ->assertSee($accountCode);
    }

    /**
     * Assert that a specific account is not visible in the list.
     *
     * @return void
     */
    public function assertDontSeeAccount(Browser $browser, string $accountName, string $accountCode)
    {
        $browser->assertDontSee($accountName)
            ->assertDontSee($accountCode);
    }

    /**
     * Click the edit button for a specific account.
     *
     * @return void
     */
    public function clickEditAccountButton(Browser $browser, int $accountId)
    {
        $browser->click("a[dusk='edit-account-{$accountId}-button']");
    }

    /**
     * Click the delete button for a specific account.
     *
     * @return void
     */
    public function clickDeleteAccountButton(Browser $browser, int $accountId)
    {
        $browser->click("button[dusk='delete-account-{$accountId}-button']");
    }
}
