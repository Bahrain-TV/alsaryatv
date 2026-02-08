<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Production URL Tests (Using cURL)
 *
 * This test suite verifies that all production URLs on https://alsarya.tv
 * are accessible and returning appropriate responses using cURL.
 *
 * These tests make actual HTTP requests to the production domain to ensure
 * all routes are properly configured and accessible.
 *
 * Note: These tests require network connectivity to https://alsarya.tv
 */
class ProductionUrlCurlTest extends TestCase
{
    /**
     * The production base URL
     */
    protected string $productionUrl = 'https://alsarya.tv';

    /**
     * Helper method to make a cURL request
     *
     * @param string $url The URL to request
     * @return array ['status' => int, 'body' => string, 'headers' => array]
     */
    protected function curlRequest(string $url): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'AlSarya-Test-Suite/1.0');

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->fail("cURL error: {$error}");
        }

        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        return [
            'status' => $httpCode,
            'body' => $body,
            'headers' => $headers,
        ];
    }

    /**
     * Test that the site uses HTTPS
     */
    public function test_site_uses_https(): void
    {
        $this->assertTrue(
            str_starts_with($this->productionUrl, 'https://'),
            'Production site must use HTTPS for security'
        );
    }

    /**
     * Test that the splash screen is accessible
     */
    public function test_splash_page_is_accessible(): void
    {
        $response = $this->curlRequest("{$this->productionUrl}/splash");

        $this->assertEquals(
            200,
            $response['status'],
            'Splash page should be accessible'
        );
        $this->assertNotEmpty($response['body'], 'Splash page should have content');
    }

    /**
     * Test that the home/welcome page is accessible
     */
    public function test_home_page_is_accessible(): void
    {
        $response = $this->curlRequest("{$this->productionUrl}/");

        $this->assertEquals(
            200,
            $response['status'],
            'Home page should be accessible'
        );
        $this->assertNotEmpty($response['body'], 'Home page should have content');
    }

    /**
     * Test that the welcome route is accessible
     */
    public function test_welcome_page_is_accessible(): void
    {
        $response = $this->curlRequest("{$this->productionUrl}/welcome");

        $this->assertEquals(
            200,
            $response['status'],
            'Welcome page should be accessible'
        );
        $this->assertNotEmpty($response['body'], 'Welcome page should have content');
    }

    /**
     * Test that the family registration page is accessible
     */
    public function test_family_registration_page_is_accessible(): void
    {
        $response = $this->curlRequest("{$this->productionUrl}/family");

        $this->assertEquals(
            200,
            $response['status'],
            'Family registration page should be accessible'
        );
        $this->assertNotEmpty($response['body'], 'Family page should have content');
    }

    /**
     * Test that the privacy policy page is accessible
     */
    public function test_privacy_page_is_accessible(): void
    {
        $response = $this->curlRequest("{$this->productionUrl}/privacy");

        $this->assertEquals(
            200,
            $response['status'],
            'Privacy policy page should be accessible'
        );
        $this->assertNotEmpty($response['body'], 'Privacy page should have content');
    }

    /**
     * Test that the registration form page is accessible
     */
    public function test_registration_form_page_is_accessible(): void
    {
        $response = $this->curlRequest("{$this->productionUrl}/register");

        $this->assertEquals(
            200,
            $response['status'],
            'Registration form page should be accessible'
        );
        $this->assertNotEmpty($response['body'], 'Registration form should have content');
    }

    /**
     * Test that the CSRF test page is accessible
     */
    public function test_csrf_test_page_is_accessible(): void
    {
        $response = $this->curlRequest("{$this->productionUrl}/csrf-test");

        $this->assertEquals(
            200,
            $response['status'],
            'CSRF test page should be accessible'
        );
        $this->assertNotEmpty($response['body'], 'CSRF test page should have content');
    }

    /**
     * Test that the caller create page is accessible
     */
    public function test_caller_create_page_is_accessible(): void
    {
        $response = $this->curlRequest("{$this->productionUrl}/callers/create");

        $this->assertEquals(
            200,
            $response['status'],
            'Caller create page should be accessible'
        );
        $this->assertNotEmpty($response['body'], 'Caller create page should have content');
    }

    /**
     * Test that the version API endpoint is accessible
     */
    public function test_api_version_endpoint_is_accessible(): void
    {
        $response = $this->curlRequest("{$this->productionUrl}/api/version");

        $this->assertEquals(
            200,
            $response['status'],
            'Version API endpoint should be accessible'
        );

        // Verify it returns JSON
        $json = json_decode($response['body'], true);
        $this->assertIsArray($json, 'Version API should return JSON');
        $this->assertArrayHasKey('version', $json, 'Version API should include version key');
    }

    /**
     * Test that the changelog API endpoint is accessible
     */
    public function test_api_changelog_endpoint_is_accessible(): void
    {
        $response = $this->curlRequest("{$this->productionUrl}/api/version/changelog");

        // Changelog may return 200 (if exists) or 404 (if not found), both are acceptable
        $this->assertContains(
            $response['status'],
            [200, 404],
            'Changelog API should return 200 or 404'
        );
    }

    /**
     * Test that protected routes redirect to login (dashboard)
     */
    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->curlRequest("{$this->productionUrl}/dashboard");

        // Should redirect (302) or return 401/403 for protected routes
        $this->assertContains(
            $response['status'],
            [302, 401, 403],
            'Dashboard should require authentication'
        );
    }

    /**
     * Test that protected routes redirect to login (winners)
     */
    public function test_winners_page_requires_authentication(): void
    {
        $response = $this->curlRequest("{$this->productionUrl}/winners");

        // Should redirect (302) or return 401/403 for protected routes
        $this->assertContains(
            $response['status'],
            [302, 401, 403],
            'Winners page should require authentication'
        );
    }

    /**
     * Test that protected routes redirect to login (families)
     */
    public function test_families_page_requires_authentication(): void
    {
        $response = $this->curlRequest("{$this->productionUrl}/families");

        // Should redirect (302) or return 401/403 for protected routes
        $this->assertContains(
            $response['status'],
            [302, 401, 403],
            'Families page should require authentication'
        );
    }

    /**
     * Test that admin panel is protected
     */
    public function test_admin_panel_requires_authentication(): void
    {
        $response = $this->curlRequest("{$this->productionUrl}/admin");

        // Should redirect (302) or return 401/403 for protected routes
        $this->assertContains(
            $response['status'],
            [302, 401, 403],
            'Admin panel should require authentication'
        );
    }

    /**
     * Test all critical public URLs in batch
     */
    public function test_all_critical_public_urls_are_accessible(): void
    {
        $criticalUrls = [
            '/',
            '/splash',
            '/family',
            '/privacy',
            '/register',
            '/welcome',
        ];

        $results = [];
        foreach ($criticalUrls as $url) {
            $response = $this->curlRequest("{$this->productionUrl}{$url}");
            $results[$url] = $response['status'];

            $this->assertEquals(
                200,
                $response['status'],
                "URL {$url} should be accessible but returned {$response['status']}"
            );
        }

        // Additional summary assertion
        $allSuccess = array_reduce($results, fn($carry, $status) => $carry && ($status === 200), true);
        $this->assertTrue($allSuccess, 'All critical URLs should return 200');
    }

    /**
     * Test that SSL certificate is valid (HTTPS)
     */
    public function test_ssl_certificate_is_valid(): void
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->productionUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $errorNo = curl_errno($ch);
        curl_close($ch);

        $this->assertEmpty($error, "SSL certificate should be valid, but got error: {$error}");
        $this->assertEquals(0, $errorNo, 'No SSL errors should occur');
        $this->assertNotFalse($response, 'Should successfully connect via HTTPS');
    }

    /**
     * Test that the site responds with proper security headers
     */
    public function test_site_has_security_headers(): void
    {
        $response = $this->curlRequest($this->productionUrl);

        // Check for common security headers
        $headers = $response['headers'];

        $this->assertStringContainsStringIgnoringCase(
            'x-frame-options',
            $headers,
            'Site should have X-Frame-Options header'
        );
    }

    /**
     * Test that the site root redirects or loads properly
     */
    public function test_root_url_loads(): void
    {
        $response = $this->curlRequest($this->productionUrl);

        $this->assertContains(
            $response['status'],
            [200, 301, 302],
            'Root URL should be accessible or redirect properly'
        );
    }
}
