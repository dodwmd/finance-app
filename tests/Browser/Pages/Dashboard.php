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
            '@current-balance' => '.balance-card, .card:contains("Current Balance")',
            '@monthly-income' => '.income-card, .card:contains("Income")',
            '@monthly-expenses' => '.expense-card, .card:contains("Expenses")',
            '@income-expense-chart' => '#income-expense-chart, div:contains("Income vs Expenses")',
            '@expense-category-chart' => '#expense-category-chart, div:contains("Expense Breakdown")',
            '@recent-transactions' => '.transaction-list, div:contains("Recent Transactions")',
            '@view-all-transactions' => 'a[href="/transactions"], a:contains("View All")',
        ];
    }
}
