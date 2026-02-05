<x-filament-panels::page>
    @php
        $analytics = $this->getAnalyticsData();
        $comparison = $this->getComparisonData();
    @endphp

    <div class="space-y-6">
        {{-- Overview Stats Grid --}}
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            {{-- Total Callers --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">إجمالي المتصلين</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($analytics['overview']['total_callers']) }}
                            </p>
                        </div>
                        <div class="rounded-full bg-primary-50 p-3 dark:bg-primary-500/10">
                            <x-heroicon-o-users class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        {{ number_format($analytics['overview']['unique_cprs']) }} رقم شخصي فريد
                    </p>
                </div>
            </div>

            {{-- Total Winners --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">إجمالي الفائزين</p>
                            <p class="mt-2 text-3xl font-bold text-success-600 dark:text-success-400">
                                {{ number_format($analytics['overview']['total_winners']) }}
                            </p>
                        </div>
                        <div class="rounded-full bg-success-50 p-3 dark:bg-success-500/10">
                            <x-heroicon-o-trophy class="h-6 w-6 text-success-600 dark:text-success-400" />
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        {{ $analytics['overview']['total_callers'] > 0 ? number_format(($analytics['overview']['total_winners'] / $analytics['overview']['total_callers']) * 100, 1) : 0 }}% من المتصلين
                    </p>
                </div>
            </div>

            {{-- Total Hits --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">إجمالي المشاركات</p>
                            <p class="mt-2 text-3xl font-bold text-warning-600 dark:text-warning-400">
                                {{ number_format($analytics['overview']['total_hits']) }}
                            </p>
                        </div>
                        <div class="rounded-full bg-warning-50 p-3 dark:bg-warning-500/10">
                            <x-heroicon-o-hand-raised class="h-6 w-6 text-warning-600 dark:text-warning-400" />
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        معدل {{ number_format($analytics['participation']['avg_hits_per_caller'], 2) }} مشاركة لكل متصل
                    </p>
                </div>
            </div>

            {{-- This Week --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">متصلو الأسبوع</p>
                            <p class="mt-2 text-3xl font-bold text-info-600 dark:text-info-400">
                                {{ number_format($analytics['overview']['this_week_callers']) }}
                            </p>
                        </div>
                        <div class="rounded-full bg-info-50 p-3 dark:bg-info-500/10">
                            <x-heroicon-o-calendar-days class="h-6 w-6 text-info-600 dark:text-info-400" />
                        </div>
                    </div>
                    @php
                        $growth = $analytics['growth']['weekly_growth_rate'];
                    @endphp
                    <p class="mt-2 text-sm {{ $growth >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                        {{ $growth >= 0 ? '↑' : '↓' }} {{ abs($growth) }}% عن الأسبوع الماضي
                    </p>
                </div>
            </div>
        </div>

        {{-- Status Distribution & Participation --}}
        <div class="grid gap-4 md:grid-cols-2">
            {{-- Status Breakdown --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">توزيع الحالات</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="h-3 w-3 rounded-full bg-success-500"></div>
                                <span class="text-sm text-gray-700 dark:text-gray-300">نشط</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ number_format($analytics['status']['active']) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="h-3 w-3 rounded-full bg-warning-500"></div>
                                <span class="text-sm text-gray-700 dark:text-gray-300">غير نشط</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ number_format($analytics['status']['inactive']) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="h-3 w-3 rounded-full bg-danger-500"></div>
                                <span class="text-sm text-gray-700 dark:text-gray-300">محظور</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ number_format($analytics['status']['blocked']) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Participation Breakdown --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">تفاعل المشاركة</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-fire class="h-5 w-5 text-warning-500" />
                                <span class="text-sm text-gray-700 dark:text-gray-300">مشاركة عالية (&gt; 5)</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ number_format($analytics['participation']['high_participation']) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-arrow-trending-down class="h-5 w-5 text-gray-400" />
                                <span class="text-sm text-gray-700 dark:text-gray-300">مشاركة منخفضة (≤ 2)</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ number_format($analytics['participation']['low_participation']) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">المتوسط لكل متصل</span>
                            <span class="text-lg font-bold text-primary-600 dark:text-primary-400">
                                {{ number_format($analytics['participation']['avg_hits_per_caller'], 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Performers --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <x-heroicon-o-star class="h-5 w-5 text-warning-500" />
                    أفضل 10 مشاركين
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-right">#</th>
                                <th class="px-4 py-2 text-right">الاسم</th>
                                <th class="px-4 py-2 text-right">الهاتف</th>
                                <th class="px-4 py-2 text-right">المشاركات</th>
                                <th class="px-4 py-2 text-right">فائز</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($analytics['top_performers'] as $index => $performer)
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <td class="px-4 py-3">
                                        @if($index < 3)
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full {{ $index === 0 ? 'bg-warning-100 text-warning-700' : ($index === 1 ? 'bg-gray-100 text-gray-700' : 'bg-orange-100 text-orange-700') }}">
                                                {{ $index + 1 }}
                                            </span>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-medium">{{ $performer->name }}</td>
                                    <td class="px-4 py-3">{{ $performer->phone }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400">
                                            {{ $performer->hits }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($performer->is_winner)
                                            <x-heroicon-s-trophy class="h-5 w-5 text-success-500" />
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">لا توجد بيانات</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Peak Hours --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <x-heroicon-o-clock class="h-5 w-5 text-info-500" />
                    أوقات الذروة (أعلى 5 ساعات)
                </h3>
                <div class="grid gap-3 md:grid-cols-5">
                    @foreach($analytics['peak_hours'] as $hour)
                        <div class="text-center p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                                {{ sprintf('%02d:00', $hour->hour) }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                {{ number_format($hour->count) }} تسجيل
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Recent Winners --}}
        @if(count($analytics['recent_winners']) > 0)
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <x-heroicon-o-trophy class="h-5 w-5 text-success-500" />
                        آخر الفائزين
                    </h3>
                    <div class="space-y-3">
                        @foreach($analytics['recent_winners'] as $winner)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-success-50 dark:bg-success-500/10">
                                <div class="flex items-center gap-3">
                                    <x-heroicon-s-trophy class="h-6 w-6 text-success-600 dark:text-success-400" />
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $winner->name }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $winner->phone }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">{{ $winner->updated_at->diffForHumans() }}</p>
                                    <p class="text-xs text-gray-400">{{ $winner->hits }} مشاركة</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
