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
        $browser->assertPathIs($this->url())
            ->assertSee('Transactions');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@transaction-list' => '.transaction-list',
            '@add-transaction-btn' => '[data-testid="add-transaction-btn"]',
            '@transaction-item' => '.transaction-item',
            '@edit-transaction' => function ($id) {
                return "[data-testid=\"edit-transaction-{$id}\"]";
            },
            '@delete-transaction' => function ($id) {
                return "[data-testid=\"delete-transaction-{$id}\"]";
            },
            '@transaction-form' => 'form.transaction-form',
            '@description-input' => 'input[name="description"]',
            '@amount-input' => 'input[name="amount"]',
            '@category-select' => 'select[name="category_id"]',
            '@type-select' => 'select[name="type"]',
            '@date-input' => 'input[name="date"]',
            '@save-btn' => 'button[type="submit"]',
        ];
    }
}
