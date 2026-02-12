<?php

namespace Tests\Feature;

use App\Services\CprHashingService;
use Tests\TestCase;

class CprHashingServiceTest extends TestCase
{
    protected CprHashingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CprHashingService;
    }

    public function test_hash_cpr_creates_hash(): void
    {
        $cpr = '12345678901';
        $hash = $this->service->hashCpr($cpr);

        // Hash should not be the same as plain text
        $this->assertNotEquals($cpr, $hash);

        // Hash should be a non-empty string
        $this->assertNotEmpty($hash);
    }

    public function test_verify_cpr_succeeds_with_correct_cpr(): void
    {
        $cpr = '12345678901';
        $hash = $this->service->hashCpr($cpr);

        $isValid = $this->service->verifyCpr($cpr, $hash);

        $this->assertTrue($isValid);
    }

    public function test_verify_cpr_fails_with_incorrect_cpr(): void
    {
        $cpr = '12345678901';
        $wrongCpr = '98765432109';
        $hash = $this->service->hashCpr($cpr);

        $isValid = $this->service->verifyCpr($wrongCpr, $hash);

        $this->assertFalse($isValid);
    }

    public function test_mask_cpr_hides_most_digits(): void
    {
        $cpr = '12345678901';
        $masked = $this->service->maskCpr($cpr);

        // First 3 digits should show
        $this->assertStringStartsWith('123', $masked);

        // Rest should be masked
        $this->assertStringContainsString('*', $masked);
    }

    public function test_mask_cpr_preserves_length(): void
    {
        $cpr = '12345678901';
        $masked = $this->service->maskCpr($cpr);

        $this->assertEquals(strlen($cpr), strlen($masked));
    }

    public function test_different_hashes_for_same_cpr(): void
    {
        $cpr = '12345678901';
        $hash1 = $this->service->hashCpr($cpr);
        $hash2 = $this->service->hashCpr($cpr);

        // Hashes should be different (different salts)
        $this->assertNotEquals($hash1, $hash2);

        // But both should verify correctly
        $this->assertTrue($this->service->verifyCpr($cpr, $hash1));
        $this->assertTrue($this->service->verifyCpr($cpr, $hash2));
    }

    public function test_mask_handles_short_cpr(): void
    {
        $cpr = '123';
        $masked = $this->service->maskCpr($cpr);

        $this->assertStringStartsWith('123', $masked);
    }

    public function test_hash_is_consistent_for_verification(): void
    {
        $cpr = '12345678901';
        $hash = $this->service->hashCpr($cpr);

        // Verify multiple times with same hash
        for ($i = 0; $i < 5; $i++) {
            $this->assertTrue($this->service->verifyCpr($cpr, $hash));
        }
    }
}
