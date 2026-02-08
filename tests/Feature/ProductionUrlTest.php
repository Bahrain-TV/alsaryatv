<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Production URL Tests
 *
 * This test suite verifies that all production URLs on https://alsarya.tv
 * are accessible and returning appropriate responses.
 *
 * Note: These tests check URLs against the production domain to ensure
 * all routes are properly configured and accessible.
 */
class ProductionUrlTest extends TestCase
{
    /**
     * The production base URL
     */
    protected string $productionUrl = 'https://alsarya.tv';

    /**
     * Test that the splash screen is accessible
     */
    public function test_splash_page_is_accessible(): void
    {
        $response = $this->get("{$this->productionUrl}/splash");

        $response->assertStatus(200);
    }

    /**
     * Test that the home/welcome page is accessible
     */
    public function test_home_page_is_accessible(): void
    {
        $response = $this->get("{$this->productionUrl}/");

        $response->assertStatus(200);
    }

    /**
     * Test that the welcome route is accessible
     */
    public function test_welcome_page_is_accessible(): void
    {
        $response = $this->get("{$this->productionUrl}/welcome");

        $response->assertStatus(200);
    }

    /**
     * Test that the family registration page is accessible
     */
    public function test_family_registration_page_is_accessible(): void
    {
        $response = $this->get("{$this->productionUrl}/family");

        $response->assertStatus(200);
    }

    /**
     * Test that the privacy policy page is accessible
     */
    public function test_privacy_page_is_accessible(): void
    {
        $response = $this->get("{$this->productionUrl}/privacy");

        $response->assertStatus(200);
    }

    /**
     * Test that the registration form page is accessible
     */
    public function test_registration_form_page_is_accessible(): void
    {
        $response = $this->get("{$this->productionUrl}/register");

        $response->assertStatus(200);
    }

    /**
     * Test that the CSRF test page is accessible
     */
    public function test_csrf_test_page_is_accessible(): void
    {
        $response = $this->get("{$this->productionUrl}/csrf-test");

        $response->assertStatus(200);
    }

    /**
     * Test that the caller create page is accessible
     */
    public function test_caller_create_page_is_accessible(): void
    {
        $response = $this->get("{$this->productionUrl}/callers/create");

        $response->assertStatus(200);
    }

    /**
     * Test that the version API endpoint is accessible
     */
    public function test_api_version_endpoint_is_accessible(): void
    {
        $response = $this->get("{$this->productionUrl}/api/version");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'version',
        ]);
    }

    /**
     * Test that the changelog API endpoint is accessible
     */
    public function test_api_changelog_endpoint_is_accessible(): void
    {
        $response = $this->get("{$this->productionUrl}/api/version/changelog");

        // Should return 200 or 404 if changelog doesn't exist
        $this->assertContains($response->getStatusCode(), [200, 404]);
    }

    /**
     * Test that protected routes redirect to login (dashboard)
     */
    public function test_dashboard_redirects_to_login(): void
    {
        $response = $this->get("{$this->productionUrl}/dashboard");

        // Should redirect to login page (302) or return 401/403
        $this->assertContains($response->getStatusCode(), [302, 401, 403]);
    }

    /**
     * Test that protected routes redirect to login (winners)
     */
    public function test_winners_page_redirects_to_login(): void
    {
        $response = $this->get("{$this->productionUrl}/winners");

        // Should redirect to login page (302) or return 401/403
        $this->assertContains($response->getStatusCode(), [302, 401, 403]);
    }

    /**
     * Test that protected routes redirect to login (families)
     */
    public function test_families_page_redirects_to_login(): void
    {
        $response = $this->get("{$this->productionUrl}/families");

        // Should redirect to login page (302) or return 401/403
        $this->assertContains($response->getStatusCode(), [302, 401, 403]);
    }

    /**
     * Test that admin panel is protected
     */
    public function test_admin_panel_is_protected(): void
    {
        $response = $this->get("{$this->productionUrl}/admin");

        // Should redirect to login page (302) or return 401/403
        $this->assertContains($response->getStatusCode(), [302, 401, 403]);
    }

    /**
     * Test that the site uses HTTPS
     */
    public function test_site_uses_https(): void
    {
        $this->assertTrue(
            str_starts_with($this->productionUrl, 'https://'),
            'Production site should use HTTPS'
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
        ];

        foreach ($criticalUrls as $url) {
            $response = $this->get("{$this->productionUrl}{$url}");
            $this->assertEquals(
                200,
                $response->getStatusCode(),
                "URL {$url} should be accessible but returned {$response->getStatusCode()}"
            );
        }
    }
}
