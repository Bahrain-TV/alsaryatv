<?php

namespace Tests\Unit;

use App\Models\Caller;
use App\Providers\HitsCounter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HitsCounterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_get_hits_returns_zero_when_no_callers(): void
    {
        $this->assertEquals(0, HitsCounter::getHits());
    }

    public function test_get_hits_returns_sum_of_all_caller_hits(): void
    {
        Caller::factory()->create(['hits' => 5]);
        Caller::factory()->create(['hits' => 10]);

        Cache::flush();
        $this->assertEquals(15, HitsCounter::getHits());
    }

    public function test_get_total_hits_returns_sum_without_cache(): void
    {
        Caller::factory()->create(['hits' => 3]);
        Caller::factory()->create(['hits' => 7]);

        $this->assertEquals(10, HitsCounter::getTotalHits());
    }

    public function test_increment_hits_invalidates_cache(): void
    {
        Caller::factory()->create(['hits' => 5]);
        Cache::flush();

        // Warm cache
        HitsCounter::getHits();

        // Add more data
        Caller::factory()->create(['hits' => 10]);

        // Increment should invalidate cache and return fresh count
        $result = HitsCounter::incrementHits();

        $this->assertEquals(15, $result);
    }

    public function test_get_user_hits_returns_hits_for_specific_cpr(): void
    {
        Caller::factory()->create(['cpr' => '111111111', 'hits' => 7]);
        Caller::factory()->create(['cpr' => '222222222', 'hits' => 12]);

        $this->assertEquals(7, HitsCounter::getUserHits('111111111'));
        $this->assertEquals(12, HitsCounter::getUserHits('222222222'));
    }

    public function test_get_user_hits_returns_zero_for_nonexistent_cpr(): void
    {
        $this->assertEquals(0, HitsCounter::getUserHits('nonexistent'));
    }

    public function test_get_user_hits_returns_zero_for_null_cpr(): void
    {
        $this->assertEquals(0, HitsCounter::getUserHits(null));
    }
}
