<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CallersTrendsWidget extends ChartWidget
{
    protected static ?string $heading = 'Caller Registration Trends';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        return Cache::remember('chart.callers.trends', now()->addMinutes(10), function () {
            $data = Caller::select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
            ])
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return [
                'datasets' => [
                    [
                        'label' => 'Caller Registrations',
                        'data' => $data->pluck('count')->toArray(),
                        'backgroundColor' => '#36A2EB',
                        'borderColor' => '#36A2EB',
                    ],
                ],
                'labels' => $data->pluck('date')->toArray(),
            ];
        });
    }

    protected function getType(): string
    {
        return 'line';
    }
}
