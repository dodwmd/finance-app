<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page as BasePage;

class BankAccountEditPage extends BasePage
{
    protected $accountId;

    public function __construct($accountId)
    {
        $this->accountId = $accountId;
    }

    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return "/bank-accounts/{$this->accountId}/edit";
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
            '@accountNameInput' => 'input[dusk="account-name-input"]',
            '@accountTypeSelect' => 'select[dusk="account-type-select"]',
            '@accountNumberInput' => 'input[dusk="account-number-input"]',
            '@bsbInput' => 'input[dusk="bsb-input"]',
            '@currentBalanceInput' => 'input[dusk="current-balance-input"]', // Note: Opening balance is not editable
            '@updateAccountButton' => 'button[dusk="update-account-button"]',
        ];
    }
}
