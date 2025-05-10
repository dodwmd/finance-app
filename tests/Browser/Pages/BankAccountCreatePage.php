<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page as BasePage;

class BankAccountCreatePage extends BasePage
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/bank-accounts/create';
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
            '@openingBalanceInput' => 'input[dusk="opening-balance-input"]',
            '@createAccountButton' => 'button[dusk="create-account-button"]',
        ];
    }
}
