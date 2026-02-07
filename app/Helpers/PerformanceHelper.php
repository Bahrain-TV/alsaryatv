<?php

namespace App\Helpers;

class PerformanceHelper
{
    /**
     * Cache key generator for dashboard widgets
     */
    public static function getCacheKey(string $widgetName, array $params = []): string
    {
        $key = 'dashboard_widget_'.$widgetName;
        if (! empty($params)) {
            $key .= '_'.md5(serialize($params));
        }

        return $key;
    }

    /**
     * Get cache TTL for different widget types
     */
    public static function getCacheTtl(string $widgetType): int
    {
        $ttlMap = [
            'stats' => 300, // 5 minutes
            'charts' => 600, // 10 minutes
            'recent_activity' => 120, // 2 minutes
            'winners_history' => 900, // 15 minutes
        ];

        return $ttlMap[$widgetType] ?? 300; // default 5 minutes
    }

    /**
     * Optimize data for charts by reducing points for large datasets
     */
    public static function optimizeChartData(array $data, int $maxPoints = 50): array
    {
        if (count($data) <= $maxPoints) {
            return $data;
        }

        $optimized = [];
        $step = floor(count($data) / $maxPoints);

        for ($i = 0; $i < count($data); $i += $step) {
            $optimized[] = $data[$i];
        }

        return $optimized;
    }
}
