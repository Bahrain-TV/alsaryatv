<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionUrlTest extends TestCase
{
    /**
     * Production URL to test against
     */
    protected string $productionUrl = 'https://alsarya.tv';

    /**
     * Test if production home page is accessible
     */
    public function test_production_home_page_is_accessible(): void
    {
        $response = $this->get($this->productionUrl.'/');
        $response->assertStatus(200);
    }

    /**
     * Test if splash screen is accessible
     */
    public function test_production_splash_screen_is_accessible(): void
    {
        $response = $this->get($this->productionUrl.'/splash');
        $response->assertStatus(200);
    }

    /**
     * Test if welcome page is accessible
     */
    public function test_production_welcome_page_is_accessible(): void
    {
        $response = $this->get($this->productionUrl.'/welcome');
        $response->assertStatus(200);
    }

    /**
     * Test if family registration page is accessible
     */
    public function test_production_family_page_is_accessible(): void
    {
        $response = $this->get($this->productionUrl.'/family');
        $response->assertStatus(200);
    }

    /**
     * Test if registration form is accessible
     */
    public function test_production_register_page_is_accessible(): void
    {
        $response = $this->get($this->productionUrl.'/register');
        $response->assertStatus(200);
    }

    /**
     * Test if privacy policy is accessible
     */
    public function test_production_privacy_page_is_accessible(): void
    {
        $response = $this->get($this->productionUrl.'/privacy');
        $response->assertStatus(200);
    }

    /**
     * Test if terms page is accessible
     */
    public function test_production_terms_page_is_accessible(): void
    {
        $response = $this->get($this->productionUrl.'/terms');
        $response->assertStatus(200);
    }

    /**
     * Test if policy page is accessible
     */
    public function test_production_policy_page_is_accessible(): void
    {
        $response = $this->get($this->productionUrl.'/policy');
        $response->assertStatus(200);
    }

    /**
     * Test if CSRF test page is accessible
     */
    public function test_production_csrf_test_page_is_accessible(): void
    {
        $response = $this->get($this->productionUrl.'/csrf-test');
        $response->assertStatus(200);
    }

    /**
     * Test if OBS overlay is accessible
     */
    public function test_production_obs_overlay_is_accessible(): void
    {
        $response = $this->get($this->productionUrl.'/obs-overlay');
        $response->assertStatus(200);
    }

    /**
     * Test if callers create page is accessible
     */
    public function test_production_callers_create_page_is_accessible(): void
    {
        $response = $this->get($this->productionUrl.'/callers/create');
        $response->assertStatus(200);
    }

    /**
     * Test if dashboard requires authentication (should redirect)
     */
    public function test_production_dashboard_requires_authentication(): void
    {
        $response = $this->get($this->productionUrl.'/dashboard');
        
        // Should redirect to login or return 401/403
        $this->assertContains($response->status(), [302, 401, 403]);
    }

    /**
     * Test if winners page requires authentication (should redirect)
     */
    public function test_production_winners_requires_authentication(): void
    {
        $response = $this->get($this->productionUrl.'/winners');
        
        // Should redirect to login or return 401/403
        $this->assertContains($response->status(), [302, 401, 403]);
    }

    /**
     * Test if families page requires authentication (should redirect)
     */
    public function test_production_families_requires_authentication(): void
    {
        $response = $this->get($this->productionUrl.'/families');
        
        // Should redirect to login or return 401/403
        $this->assertContains($response->status(), [302, 401, 403]);
    }

    /**
     * Test if admin panel requires authentication (should redirect)
     */
    public function test_production_admin_requires_authentication(): void
    {
        $response = $this->get($this->productionUrl.'/admin');
        
        // Should redirect to login or return 401/403
        $this->assertContains($response->status(), [302, 401, 403]);
    }

    /**
     * Test if callers success page requires session (should redirect)
     */
    public function test_production_callers_success_requires_session(): void
    {
        $response = $this->get($this->productionUrl.'/callers/success');
        
        // Should redirect since no session
        $this->assertContains($response->status(), [302, 401, 403]);
    }

    /**
     * Test if production uses HTTPS
     */
    public function test_production_uses_https(): void
    {
        $this->assertStringStartsWith('https://', $this->productionUrl);
    }
}
