<?php

namespace Tests;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Disable CSRF verification for feature tests to simplify form submissions
        // Tests exercise validation and controller logic directly.
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }
}
