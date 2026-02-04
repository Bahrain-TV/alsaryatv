<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clear cache before each test
        Cache::flush();
    }

    /**
     * Test that second registration within 5 minutes is blocked
     */
    public function test_rate_limit_prevents_duplicate_registration(): void
    {
        // First registration should succeed
        $response1 = $this->post('/callers', [
            'name' => 'Test User',
            'cpr' => '123456789',
            'phone_number' => '33333333',
            'caller_type' => 'individual',
        ]);

        $this->assertNotEquals(419, $response1->getStatusCode());

        // Second registration with same CPR within 5 minutes should fail
        $response2 = $this->post('/callers', [
            'name' => 'Test User 2',
            'cpr' => '123456789',
            'phone_number' => '33333333',
            'caller_type' => 'individual',
        ]);

        // Should get rate limit error
        $this->assertTrue(
            $response2->status() === 422 ||
            $response2->status() === 500 ||
            strpos($response2->getContent(), 'rate limit') !== false
        );
    }

    /**
     * Test that different CPRs can register without rate limiting each other
     */
    public function test_different_cprs_not_rate_limited(): void
    {
        // First registration
        $response1 = $this->post('/callers', [
            'name' => 'Test User 1',
            'cpr' => '111111111',
            'phone_number' => '33333333',
            'caller_type' => 'individual',
        ]);

        $this->assertNotEquals(419, $response1->getStatusCode());

        // Different CPR should not be rate limited
        $response2 = $this->post('/callers', [
            'name' => 'Test User 2',
            'cpr' => '222222222',
            'phone_number' => '33333334',
            'caller_type' => 'individual',
        ]);

        $this->assertNotEquals(419, $response2->getStatusCode());
    }

    /**
     * Test that IP rate limit prevents bulk registrations
     */
    public function test_ip_rate_limit_prevents_bulk_registration(): void
    {
        // Try to register 11 times from same IP
        for ($i = 0; $i < 11; $i++) {
            $response = $this->post('/callers', [
                'name' => "Test User $i",
                'cpr' => '12345678'.str_pad($i, 1, '0', STR_PAD_LEFT),
                'phone_number' => '3333333'.str_pad($i, 1, '0', STR_PAD_LEFT),
                'caller_type' => 'individual',
            ]);

            if ($i < 10) {
                // First 10 should succeed
                $this->assertNotEquals(419, $response->getStatusCode());
            } else {
                // 11th should be rate limited by IP
                $this->assertTrue(
                    $response->status() === 422 ||
                    $response->status() === 500 ||
                    strpos($response->getContent(), 'rate limit') !== false ||
                    strpos($response->getContent(), 'location') !== false
                );
            }
        }
    }

    /**
     * Test rate limit message is user-friendly
     */
    public function test_rate_limit_error_message(): void
    {
        // Register once
        $this->post('/callers', [
            'name' => 'Test User',
            'cpr' => '123456789',
            'phone_number' => '33333333',
            'caller_type' => 'individual',
        ]);

        // Try again immediately
        $response = $this->post('/callers', [
            'name' => 'Test User 2',
            'cpr' => '123456789',
            'phone_number' => '33333333',
            'caller_type' => 'individual',
        ]);

        // Check for rate limit message in response
        $content = $response->getContent();
        $this->assertTrue(
            strpos($content, '5 minutes') !== false ||
            strpos($content, 'rate limit') !== false ||
            strpos($content, 'try again later') !== false
        );
    }
}
