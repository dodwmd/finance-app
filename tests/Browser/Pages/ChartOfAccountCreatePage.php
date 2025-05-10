<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page as BasePage;

class ChartOfAccountCreatePage extends BasePage
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/chart-of-accounts/create';
    }

    /**
     * Assert that the browser is on the page.
     *
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser->assertPathIs($this->url())
            ->assertSee('Create New Account');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@account_code' => 'input[name="account_code"]',
            '@account_name' => 'input[name="name"]',
            '@account_type' => 'select[name="type"]',
            '@description' => 'textarea[name="description"]',
            '@parent_account_id' => 'select[name="parent_account_id"]',
            '@is_active' => 'input[name="is_active"]',
            '@allow_direct_posting' => 'input[name="allow_direct_posting"]',
            '@system_account_tag' => 'input[name="system_account_tag"]',
            '@submit-button' => 'button[type="submit"][dusk="submit-button"]',
        ];
    }

    /**
     * Fill out and submit the create account form.
     *
     * @return void
     */
    public function createAccount(Browser $browser, array $data)
    {
        $browser->type('@account_code', $data['account_code'] ?? '')
            ->type('@account_name', $data['name'] ?? '')
            ->select('@account_type', $data['type'] ?? '')
            ->type('@description', $data['description'] ?? '');

        if (isset($data['parent_account_id'])) {
            $browser->select('@parent_account_id', $data['parent_account_id']);
        }

        if (isset($data['is_active']) && $data['is_active']) {
            $browser->check('@is_active');
        } else {
            $browser->uncheck('@is_active');
        }

        if (isset($data['allow_direct_posting']) && $data['allow_direct_posting']) {
            $browser->check('@allow_direct_posting');
        } else {
            $browser->uncheck('@allow_direct_posting');
        }

        if (isset($data['system_account_tag'])) {
            $browser->type('@system_account_tag', $data['system_account_tag']);
        }

        $browser->click('@submit-button');
    }
}
