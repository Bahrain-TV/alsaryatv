<div wire:poll.5s="refreshStats" class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Live system stats') }}</p>
        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200">
            {{ __('Livewire') }} • {{ __('Updated') }} {{ $lastUpdatedAt }}
        </span>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Total callers') }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($totalCallers) }}</p>
            <p class="mt-2 text-xs text-gray-500">{{ __('Unique CPRs') }}: {{ number_format($uniqueCprs) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Total winners') }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($totalWinners) }}</p>
            <p class="mt-2 text-xs text-gray-500">{{ __('Win ratio') }}: {{ $winRatio }}%</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Today callers') }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($todayCallers) }}</p>
            <p class="mt-2 text-xs text-gray-500">
                {{ $todayTrend >= 0 ? __('Trend up') : __('Trend down') }}: {{ $todayTrend }}%
            </p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Total hits') }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($totalHits) }}</p>
            <p class="mt-2 text-xs text-gray-500">{{ __('Average hits per caller') }}: {{ $averageHits }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Active callers') }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($activeCallers) }}</p>
            <p class="mt-2 text-xs text-gray-500">{{ __('Status') }}: {{ __('Active') }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Latest activity') }}</p>
            <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">
                {{ $lastCallerName ?: __('No callers yet') }}
            </p>
            <p class="mt-1 text-xs text-gray-500">
                {{ $lastCallerAt ? __('Registered') . ' ' . $lastCallerAt : __('Waiting for first registration') }}
            </p>
            <p class="mt-2 text-xs text-gray-500">
                {{ __('Latest winner') }}: {{ $lastWinnerName ?: __('None selected') }}
            </p>
        </div>
    </div>
</div>
