<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page as BasePage;

class BankAccountIndexPage extends BasePage
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/bank-accounts';
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url());
    }

    /**
     * Get the element shortcuts for the page.
     */
    public function elements(): array
    {
        return [
            '@addNewAccountButton' => 'a[dusk="add-new-account-button"]',
            '@bankAccountsTable' => 'table[dusk="bank-accounts-table"]',
            // General selector for success messages, assuming it's in the main layout
            '@successMessage' => 'div[dusk="success-message"]',
        ];
    }

    /**
     * Get the selector for the view link of a specific account.
     */
    public function viewAccountLink(int $accountId): string
    {
        return "a[dusk='view-account-{$accountId}-link']";
    }

    /**
     * Get the selector for the edit link of a specific account.
     */
    public function editAccountLink(int $accountId): string
    {
        return "a[dusk='edit-account-{$accountId}-link']";
    }

    /**
     * Get the selector for the delete button of a specific account.
     */
    public function deleteAccountButton(int $accountId): string
    {
        return "button[dusk='delete-account-{$accountId}-button']";
    }
}
