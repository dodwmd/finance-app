<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Dashboard extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/dashboard';
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
            '@current-balance' => '.balance-card, div:has(h3:contains("Current Balance"))',
            '@monthly-income' => '.income-card, div:has(h3:contains("Income"))',
            '@monthly-expenses' => '.expense-card, div:has(h3:contains("Expenses"))',
            '@income-expense-chart' => '#income-expense-chart, div:has(h3:contains("Income vs Expenses"))',
            '@expense-category-chart' => '#expense-category-chart, div:has(h3:contains("Expense Breakdown"))',
            '@recent-transactions' => '.transaction-list, div:has(h3:contains("Recent Transactions"))',
            '@view-all-transactions' => 'a[href="/transactions"]',
        ];
    }
}
