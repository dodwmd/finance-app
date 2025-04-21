<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Transactions extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/transactions';
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
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@transaction-list' => '.transaction-list, #transactions-table, table',
            '@add-transaction-btn' => '[data-testid="add-transaction-btn"], .btn-primary:contains("Add"), a:contains("New Transaction")',
            '@transaction-item' => '.transaction-item, tr',
            '@edit-transaction' => function ($id) {
                return "[data-testid=\"edit-transaction-{$id}\"], [data-id=\"{$id}\"] .edit-btn, a[href*=\"transactions/{$id}/edit\"]";
            },
            '@delete-transaction' => function ($id) {
                return "[data-testid=\"delete-transaction-{$id}\"], [data-id=\"{$id}\"] .delete-btn, button.delete-transaction[data-id=\"{$id}\"]";
            },
            '@transaction-form' => 'form.transaction-form, form[action*="transactions"]',
            '@description-input' => 'input[name="description"]',
            '@amount-input' => 'input[name="amount"]',
            '@category-select' => 'select[name="category_id"]',
            '@type-select' => 'select[name="type"]',
            '@date-input' => 'input[name="date"]',
            '@save-btn' => 'button[type="submit"], input[type="submit"], button:contains("Save")',
        ];
    }
}
