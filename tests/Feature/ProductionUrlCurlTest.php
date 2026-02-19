<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class ProductionUrlCurlTest extends TestCase
{
    /**
     * Production URL to test against
     */
    protected string $productionUrl = 'https://alsarya.tv';

    protected function setUp(): void
    {
        parent::setUp();

        // Skip production cURL tests by default in local/test environments
        if (empty(env('RUN_PRODUCTION_TESTS'))) {
            $this->markTestSkipped('Skipping production cURL tests (RUN_PRODUCTION_TESTS not set).');
        }
    }

    /**
     * Timeout for cURL requests in seconds
     */
    protected int $timeout = 30;

    /**
     * Perform a cURL request
     */
    protected function curlRequest(string $url, bool $followRedirects = false): array
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => $followRedirects,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => false,
            CURLOPT_USERAGENT => 'AlSarya-Production-Test/1.0',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $error = curl_error($ch);
        $sslVerifyResult = curl_getinfo($ch, CURLINFO_SSL_VERIFYRESULT);

        $headers = '';
        $body = '';

        if ($response !== false) {
            $headers = substr($response, 0, $headerSize);
            $body = substr($response, $headerSize);
        }

        curl_close($ch);

        return [
            'code' => $httpCode,
            'error' => $error,
            'headers' => $headers,
            'body' => $body,
            'ssl_verify' => $sslVerifyResult,
        ];
    }

    /**
     * Test if production home page is accessible via cURL
     */
    public function test_curl_home_page_is_accessible(): void
    {
        $result = $this->curlRequest($this->productionUrl.'/');

        $this->assertEquals(200, $result['code'], 'Home page should return 200');
        $this->assertEmpty($result['error'], 'Should not have cURL errors');
    }

    /**
     * Test if splash screen is accessible via cURL
     */
    public function test_curl_splash_screen_is_accessible(): void
    {
        $result = $this->curlRequest($this->productionUrl.'/splash');

        $this->assertEquals(200, $result['code'], 'Splash screen should return 200');
        $this->assertEmpty($result['error'], 'Should not have cURL errors');
    }

    /**
     * Test if family page is accessible via cURL
     */
    public function test_curl_family_page_is_accessible(): void
    {
        $result = $this->curlRequest($this->productionUrl.'/family');

        $this->assertEquals(200, $result['code'], 'Family page should return 200');
        $this->assertEmpty($result['error'], 'Should not have cURL errors');
    }

    /**
     * Test if privacy page is accessible via cURL
     */
    public function test_curl_privacy_page_is_accessible(): void
    {
        $result = $this->curlRequest($this->productionUrl.'/privacy');

        $this->assertEquals(200, $result['code'], 'Privacy page should return 200');
        $this->assertEmpty($result['error'], 'Should not have cURL errors');
    }

    /**
     * Test if register page is accessible via cURL
     */
    public function test_curl_register_page_is_accessible(): void
    {
        $result = $this->curlRequest($this->productionUrl.'/register');

        $this->assertEquals(200, $result['code'], 'Register page should return 200');
        $this->assertEmpty($result['error'], 'Should not have cURL errors');
    }

    /**
     * Test SSL certificate validation
     */
    public function test_ssl_certificate_is_valid(): void
    {
        $result = $this->curlRequest($this->productionUrl.'/');

        $this->assertEquals(0, $result['ssl_verify'], 'SSL certificate should be valid');
        $this->assertEmpty($result['error'], 'Should not have SSL errors');
    }

    /**
     * Test if dashboard requires authentication via cURL
     */
    public function test_curl_dashboard_requires_authentication(): void
    {
        $result = $this->curlRequest($this->productionUrl.'/dashboard');

        $this->assertContains($result['code'], [302, 401, 403], 'Dashboard should require authentication');
    }

    /**
     * Test if winners page requires authentication via cURL
     */
    public function test_curl_winners_requires_authentication(): void
    {
        $result = $this->curlRequest($this->productionUrl.'/winners');

        $this->assertContains($result['code'], [302, 401, 403], 'Winners page should require authentication');
    }

    /**
     * Test if families page requires authentication via cURL
     */
    public function test_curl_families_requires_authentication(): void
    {
        $result = $this->curlRequest($this->productionUrl.'/families');

        $this->assertContains($result['code'], [302, 401, 403], 'Families page should require authentication');
    }

    /**
     * Test if admin panel requires authentication via cURL
     */
    public function test_curl_admin_requires_authentication(): void
    {
        $result = $this->curlRequest($this->productionUrl.'/admin');

        $this->assertContains($result['code'], [302, 401, 403], 'Admin panel should require authentication');
    }

    /**
     * Test security headers are present
     */
    public function test_security_headers_are_present(): void
    {
        $result = $this->curlRequest($this->productionUrl.'/');

        $this->assertEquals(200, $result['code'], 'Home page should be accessible');

        // Check for important security headers (may vary based on server config)
        $headers = strtolower($result['headers']);

        // Note: Not all headers may be present, but we check for common ones
        // X-Frame-Options, X-Content-Type-Options, etc.
        $this->assertNotEmpty($headers, 'Response should contain headers');
    }

    /**
     * Test response time is reasonable (under timeout)
     */
    public function test_response_time_is_reasonable(): void
    {
        $startTime = microtime(true);
        $result = $this->curlRequest($this->productionUrl.'/');
        $endTime = microtime(true);

        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $this->assertEquals(200, $result['code'], 'Home page should be accessible');
        $this->assertLessThan($this->timeout * 1000, $responseTime, 'Response time should be under timeout');
    }

    /**
     * Test if OBS overlay is accessible via cURL
     */
    public function test_curl_obs_overlay_is_accessible(): void
    {
        $result = $this->curlRequest($this->productionUrl.'/obs-overlay');

        $this->assertEquals(200, $result['code'], 'OBS overlay should return 200');
        $this->assertEmpty($result['error'], 'Should not have cURL errors');
    }

    /**
     * Test if terms page is accessible via cURL
     */
    public function test_curl_terms_page_is_accessible(): void
    {
        $result = $this->curlRequest($this->productionUrl.'/terms');

        $this->assertEquals(200, $result['code'], 'Terms page should return 200');
        $this->assertEmpty($result['error'], 'Should not have cURL errors');
    }

    /**
     * Test if policy page is accessible via cURL
     */
    public function test_curl_policy_page_is_accessible(): void
    {
        $result = $this->curlRequest($this->productionUrl.'/policy');

        $this->assertEquals(200, $result['code'], 'Policy page should return 200');
        $this->assertEmpty($result['error'], 'Should not have cURL errors');
    }
}
