<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Login extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/login';
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        // Only check for form presence, not path, since we might be redirected
        $browser->assertPresent('form');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@email' => 'input[name="email"]',
            '@password' => 'input[name="password"]',
            '@login-button' => 'button[type="submit"]',
            '@remember-me' => 'input[name="remember"]',
            '@forgot-password' => 'a[href*="password/reset"]',
        ];
    }

    /**
     * Login with the given credentials.
     *
     * @param  string  $email
     * @param  string  $password
     * @param  bool  $remember
     * @return void
     */
    public function loginAs(Browser $browser, $email = 'test@example.com', $password = 'password', $remember = false)
    {
        $browser->type('@email', $email)
            ->type('@password', $password);

        if ($remember) {
            $browser->check('@remember-me');
        }

        $browser->click('@login-button')
            ->waitForLocation('/dashboard');
    }
}
