<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page as BasePage;

class ChartOfAccountEditPage extends BasePage
{
    protected $accountId;

    /**
     * Create a new page instance.
     *
     * @return void
     */
    public function __construct(int $accountId)
    {
        $this->accountId = $accountId;
    }

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return "/chart-of-accounts/{$this->accountId}/edit";
    }

    /**
     * Assert that the browser is on the page.
     *
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser->assertPathIs($this->url())
            ->assertSee('Edit Account');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        // These are the same as the create page, but could be different if needed
        return [
            '@account_code' => 'input[name="account_code"]',
            '@account_name' => 'input[name="name"]',
            '@account_type' => 'select[name="type"]',
            '@description' => 'textarea[name="description"]',
            '@parent_account_id' => 'select[name="parent_account_id"]',
            '@is_active' => 'input[dusk="is_active"]',
            '@allow_direct_posting' => 'input[dusk="allow_direct_posting"]',
            '@system_account_tag' => 'input[name="system_account_tag"]',
            '@submit-button' => 'button[type="submit"][dusk="submit-button"]',
        ];
    }

    /**
     * Fill out and submit the edit account form.
     *
     * @return void
     */
    public function updateAccount(Browser $browser, array $data)
    {
        // Clear existing values before typing new ones if they are set in $data
        if (isset($data['account_code'])) {
            $browser->clear('@account_code')->type('@account_code', $data['account_code']);
        }
        if (isset($data['name'])) {
            $browser->clear('@account_name')->type('@account_name', $data['name']);
        }
        if (isset($data['type'])) {
            $browser->select('@account_type', $data['type']);
        }
        if (isset($data['description'])) {
            $browser->clear('@description')->type('@description', $data['description']);
        }
        if (array_key_exists('parent_account_id', $data)) { // Check if key exists to allow setting to null/empty
            $browser->select('@parent_account_id', $data['parent_account_id']);
        }

        if (isset($data['is_active'])) {
            if ($data['is_active']) {
                $browser->check('@is_active');
            } else {
                $browser->uncheck('@is_active');
            }
        }

        if (isset($data['allow_direct_posting'])) {
            if ($data['allow_direct_posting']) {
                $browser->check('@allow_direct_posting');
            } else {
                $browser->uncheck('@allow_direct_posting');
            }
        }

        if (array_key_exists('system_account_tag', $data)) { // Check if key exists to allow setting to null/empty
            $browser->type('@system_account_tag', $data['system_account_tag']);
        }

        $browser->click('@submit-button');
    }
}
