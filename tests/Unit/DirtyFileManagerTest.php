<?php

namespace Tests\Unit;

use App\Services\DirtyFileManager;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DirtyFileManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_mark_successful_stores_data_in_cache(): void
    {
        DirtyFileManager::markSuccessful('123456789');

        $this->assertTrue(DirtyFileManager::exists('123456789'));
    }

    public function test_exists_returns_false_when_not_marked(): void
    {
        $this->assertFalse(DirtyFileManager::exists('nonexistent'));
    }

    public function test_get_returns_data_with_expected_keys(): void
    {
        DirtyFileManager::markSuccessful('123456789');

        $data = DirtyFileManager::get('123456789');

        $this->assertNotNull($data);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertArrayHasKey('session_id', $data);
        $this->assertArrayHasKey('marked_at', $data);
    }

    public function test_get_returns_null_when_not_marked(): void
    {
        $this->assertNull(DirtyFileManager::get('nonexistent'));
    }

    public function test_remove_clears_cache_entry(): void
    {
        DirtyFileManager::markSuccessful('123456789');
        $this->assertTrue(DirtyFileManager::exists('123456789'));

        DirtyFileManager::remove('123456789');
        $this->assertFalse(DirtyFileManager::exists('123456789'));
    }

    public function test_get_time_remaining_returns_positive_for_recent_entry(): void
    {
        DirtyFileManager::markSuccessful('123456789');

        $remaining = DirtyFileManager::getTimeRemaining('123456789');

        $this->assertGreaterThan(0, $remaining);
        $this->assertLessThanOrEqual(60, $remaining);
    }

    public function test_get_time_remaining_returns_zero_when_not_marked(): void
    {
        $this->assertEquals(0, DirtyFileManager::getTimeRemaining('nonexistent'));
    }

    public function test_is_rate_limited_returns_true_when_not_marked(): void
    {
        $this->assertTrue(DirtyFileManager::isRateLimited('nonexistent'));
    }

    public function test_is_rate_limited_returns_false_when_marked(): void
    {
        DirtyFileManager::markSuccessful('123456789');

        $this->assertFalse(DirtyFileManager::isRateLimited('123456789'));
    }

    public function test_different_cprs_are_independent(): void
    {
        DirtyFileManager::markSuccessful('111111111');

        $this->assertTrue(DirtyFileManager::exists('111111111'));
        $this->assertFalse(DirtyFileManager::exists('222222222'));
    }
}
