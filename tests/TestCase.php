<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Setup default configurations for tests
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Disable CSRF verification for all tests
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }
}
