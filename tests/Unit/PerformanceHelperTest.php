<?php

namespace Tests\Unit;

use App\Helpers\PerformanceHelper;
use PHPUnit\Framework\TestCase;

class PerformanceHelperTest extends TestCase
{
    public function test_get_cache_key_without_params(): void
    {
        $key = PerformanceHelper::getCacheKey('stats_overview');

        $this->assertEquals('dashboard_widget_stats_overview', $key);
    }

    public function test_get_cache_key_with_params_includes_hash(): void
    {
        $key = PerformanceHelper::getCacheKey('chart', ['period' => 'weekly']);

        $this->assertStringStartsWith('dashboard_widget_chart_', $key);
        $this->assertNotEquals('dashboard_widget_chart', $key);
    }

    public function test_get_cache_key_different_params_produce_different_keys(): void
    {
        $key1 = PerformanceHelper::getCacheKey('chart', ['period' => 'weekly']);
        $key2 = PerformanceHelper::getCacheKey('chart', ['period' => 'monthly']);

        $this->assertNotEquals($key1, $key2);
    }

    public function test_get_cache_ttl_returns_known_values(): void
    {
        $this->assertEquals(300, PerformanceHelper::getCacheTtl('stats'));
        $this->assertEquals(600, PerformanceHelper::getCacheTtl('charts'));
        $this->assertEquals(120, PerformanceHelper::getCacheTtl('recent_activity'));
        $this->assertEquals(900, PerformanceHelper::getCacheTtl('winners_history'));
    }

    public function test_get_cache_ttl_returns_default_for_unknown_type(): void
    {
        $this->assertEquals(300, PerformanceHelper::getCacheTtl('unknown_widget'));
    }

    public function test_optimize_chart_data_returns_original_when_under_limit(): void
    {
        $data = range(1, 10);
        $result = PerformanceHelper::optimizeChartData($data, 50);

        $this->assertCount(10, $result);
        $this->assertEquals($data, $result);
    }

    public function test_optimize_chart_data_returns_original_when_at_limit(): void
    {
        $data = range(1, 50);
        $result = PerformanceHelper::optimizeChartData($data, 50);

        $this->assertCount(50, $result);
    }

    public function test_optimize_chart_data_reduces_points_over_limit(): void
    {
        $data = range(1, 200);
        $result = PerformanceHelper::optimizeChartData($data, 50);

        $this->assertLessThanOrEqual(50, count($result));
        $this->assertGreaterThan(0, count($result));
    }

    public function test_optimize_chart_data_preserves_first_element(): void
    {
        $data = range(100, 300);
        $result = PerformanceHelper::optimizeChartData($data, 10);

        $this->assertEquals(100, $result[0]);
    }
}
