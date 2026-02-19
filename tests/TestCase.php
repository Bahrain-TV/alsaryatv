<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Disable CSRF verification for feature tests to simplify form submissions
        // Tests exercise validation and controller logic directly.
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }
}
