<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Budgets extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/budgets';
    }

    /**
     * Assert that the browser is on the page.
     *
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser->assertPathIs($this->url())
            ->assertSee('Budget Planning');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@create-budget-button' => 'a[href="'.route('budgets.create').'"]',
            '@filter-button' => 'button[type="submit"]',
            '@period-filter' => 'select[name="period"]',
            '@budget-cards' => '.budget-card',
            '@budget-table' => 'table',
        ];
    }

    /**
     * Create a new budget.
     *
     * @param  string  $name
     * @param  string|int  $categoryId
     * @param  float  $amount
     * @param  string  $period
     * @param  string  $startDate
     * @param  bool  $isActive
     * @return void
     */
    public function createBudget(Browser $browser, $name, $categoryId, $amount, $period, $startDate, $isActive = true)
    {
        $browser->clickLink('Create Budget')
            ->type('name', $name)
            ->select('category_id', $categoryId)
            ->type('amount', $amount)
            ->select('period', $period)
            ->type('start_date', $startDate);

        if ($isActive) {
            $browser->check('is_active');
        } else {
            $browser->uncheck('is_active');
        }

        $browser->press('Create Budget');
    }

    /**
     * View a budget's details.
     *
     * @param  int  $rowIndex  The row index (1-based) of the budget in the table
     * @return void
     */
    public function viewBudget(Browser $browser, $rowIndex)
    {
        $browser->click('table tbody tr:nth-child('.$rowIndex.') a:contains("View")');
    }

    /**
     * Edit a budget.
     *
     * @param  int  $rowIndex  The row index (1-based) of the budget in the table
     * @return void
     */
    public function editBudget(Browser $browser, $rowIndex)
    {
        $browser->click('table tbody tr:nth-child('.$rowIndex.') a:contains("Edit")');
    }

    /**
     * Filter budgets by period.
     *
     * @param  string  $period
     * @return void
     */
    public function filterByPeriod(Browser $browser, $period)
    {
        $browser->select('@period-filter', $period)
            ->click('@filter-button');
    }

    /**
     * Clear filters.
     *
     * @return void
     */
    public function clearFilters(Browser $browser)
    {
        $browser->clickLink('Clear');
    }
}
