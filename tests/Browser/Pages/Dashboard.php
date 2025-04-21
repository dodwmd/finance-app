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
        $browser->assertPathIs($this->url())
            ->assertSee('Financial Overview');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@current-balance' => '.balance-card',
            '@monthly-income' => '.income-card',
            '@monthly-expenses' => '.expense-card',
            '@income-expense-chart' => '#income-expense-chart',
            '@expense-category-chart' => '#expense-category-chart',
            '@recent-transactions' => '.transaction-list',
            '@view-all-transactions' => 'a[href="/transactions"]',
        ];
    }
}
