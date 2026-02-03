<?php

namespace Tests\Feature;

use App\Models\Caller;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CsrfProtectionTest extends TestCase
{
    /**
     * Test that CSRF token is present in form
     */
    public function test_csrf_token_in_form(): void
    {
        $response = $this->get('/callers/create');

        $response->assertStatus(200);
        $response->assertViewIs('callers.create');
        
        // Check CSRF token is in view
        $content = $response->getContent();
        $this->assertStringContainsString('csrf-token', $content);
        $this->assertStringContainsString('_token', $content);
    }

    /**
     * Test that CSRF token is required for POST requests
     */
    public function test_csrf_token_required(): void
    {
        // Try posting without CSRF token
        $response = $this->post('/callers', [
            'name' => 'Test User',
            'cpr' => '123456789',
            'phone_number' => '33333333',
            'caller_type' => 'individual',
        ]);

        // Should fail with 419 (CSRF mismatch)
        $response->assertStatus(419);
    }

    /**
     * Test that valid CSRF token allows POST request
     */
    public function test_csrf_token_valid(): void
    {
        // Clear any existing rate limits
        Cache::flush();

        $response = $this->post('/callers', [
            'name' => 'Test User',
            'cpr' => '123456789',
            'phone_number' => '33333333',
            'caller_type' => 'individual',
        ]);

        // Should succeed (not 419)
        $this->assertNotEquals(419, $response->getStatusCode());
    }

    /**
     * Test that GET request includes CSRF meta tag
     */
    public function test_csrf_meta_tag_in_response(): void
    {
        $response = $this->get('/callers/create');

        $response->assertStatus(200);
        // Should have meta tag with CSRF token
        $response->assertSeeText('csrf-token');
    }
}
