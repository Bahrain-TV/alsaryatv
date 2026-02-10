<x-app-layout>
    <x-slot name="header">
        <div class="space-y-1">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ __('Operational overview for callers, winners, and live registration activity.') }}
            </p>
        </div>
    </x-slot>

    @php
        $isRtl = app()->getLocale() === 'ar';
        $tableReady = \Illuminate\Support\Facades\Schema::hasTable('callers');

        $stats = [
            [
                'label' => __('Total Callers'),
                'value' => $tableReady ? \App\Models\Caller::count() : 0,
                'hint' => __('All registrations recorded so far.'),
                'tone' => 'indigo',
            ],
            [
                'label' => __('Total Winners'),
                'value' => $tableReady ? \App\Models\Caller::where('is_winner', true)->count() : 0,
                'hint' => __('Marked winners across all draws.'),
                'tone' => 'emerald',
            ],
            [
                'label' => __('Today\'s Callers'),
                'value' => $tableReady ? \App\Models\Caller::whereDate('created_at', today())->count() : 0,
                'hint' => __('Registrations received today.'),
                'tone' => 'amber',
            ],
            [
                'label' => __('Family Registrations'),
                'value' => $tableReady ? \App\Models\Caller::where('is_family', true)->count() : 0,
                'hint' => __('Households registered as families.'),
                'tone' => 'sky',
            ],
        ];

        $toneStyles = [
            'indigo' => 'bg-indigo-50 text-indigo-700 ring-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-200 dark:ring-indigo-500/30',
            'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-500/30',
            'amber' => 'bg-amber-50 text-amber-700 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/30',
            'sky' => 'bg-sky-50 text-sky-700 ring-sky-100 dark:bg-sky-500/10 dark:text-sky-200 dark:ring-sky-500/30',
        ];

        $recentCallers = $tableReady ? \App\Models\Caller::latest()->take(6)->get() : collect();
        $recentWinners = $tableReady ? \App\Models\Caller::where('is_winner', true)->latest()->take(4)->get() : collect();
        $todayLabel = now()->translatedFormat('j F Y');
        $dataStatus = $tableReady ? __('Live data connected') : __('Database not migrated');
    @endphp

    <div class="py-10">
        <div
            @class([
                'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6',
                'text-right' => $isRtl,
            ])
            dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
        >
            <div class="grid gap-6 lg:grid-cols-3">
                <section class="lg:col-span-2 space-y-6">
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 auto-rows-fr">
                        @foreach ($stats as $stat)
                            @php
                                $toneClass = $toneStyles[$stat['tone']] ?? $toneStyles['indigo'];
                            @endphp
                            <div class="relative flex h-full flex-col justify-between rounded-2xl border border-gray-200 bg-white p-5 shadow-sm ring-1 ring-inset ring-gray-100 transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-800 dark:bg-gray-900 dark:ring-gray-800">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        {{ $stat['label'] }}
                                    </p>
                                    <span
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-full ring-1 ring-inset {{ $toneClass }}"
                                        aria-hidden="true"
                                    ></span>
                                </div>
                                <div class="mt-4 text-3xl font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($stat['value']) }}
                                </div>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $stat['hint'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ __('Recent Registrations') }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('Latest callers captured as of :date.', ['date' => $todayLabel]) }}
                                </p>
                            </div>
                            <span class="inline-flex items-center rounded-full border border-gray-200 px-3 py-1 text-xs font-semibold text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                {{ $dataStatus }}
                            </span>
                        </div>

                        @if (! $tableReady)
                            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('Caller data is not available yet. Run the migrations to enable statistics.') }}
                            </p>
                        @elseif ($recentCallers->isEmpty())
                            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No callers have been registered yet.') }}
                            </p>
                        @else
                            <ul role="list" class="mt-4 divide-y divide-gray-200 dark:divide-gray-800">
                                @foreach ($recentCallers as $caller)
                                    <li class="flex flex-col gap-3 py-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $caller->name ?: __('Unnamed caller') }}
                                            </p>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                <span class="font-mono">{{ $caller->phone ?: '---' }}</span>
                                                <span class="mx-1">•</span>
                                                <span class="font-mono">{{ $caller->cpr ?: '---' }}</span>
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-3 text-xs">
                                            <span
                                                class="inline-flex items-center rounded-full px-2.5 py-1 font-semibold"
                                                @class([
                                                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200' => $caller->is_winner,
                                                    'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' => ! $caller->is_winner,
                                                ])
                                            >
                                                {{ $caller->is_winner ? __('Winner') : __('Active') }}
                                            </span>
                                            <span class="text-gray-500 dark:text-gray-400">
                                                {{ $caller->created_at?->diffForHumans() ?? __('Unknown time') }}
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </section>

                <aside class="space-y-6">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('Quick Actions') }}
                        </h3>
                        <div class="mt-4 grid gap-3">
                            <a
                                href="{{ route('callers.create') }}"
                                class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700"
                            >
                                {{ __('Add Caller') }}
                            </a>
                            <a
                                href="{{ route('winners') }}"
                                class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
                            >
                                {{ __('View Winners') }}
                            </a>
                            <a
                                href="{{ route('families') }}"
                                class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
                            >
                                {{ __('Family Registrations') }}
                            </a>
                            <a
                                href="{{ route('obs.overlay') }}"
                                target="_blank"
                                class="inline-flex items-center justify-center rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200 dark:hover:bg-emerald-500/20"
                            >
                                {{ __('Open OBS Overlay') }}
                            </a>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('Operator Notes') }}
                        </h3>
                        <ul class="mt-3 space-y-2 text-xs text-gray-600 dark:text-gray-300 list-disc ps-4">
                            <li>{{ __('Verify new registrations before announcing winners.') }}</li>
                            <li>{{ __('Keep the dashboard open to maintain session access.') }}</li>
                            <li>{{ __('Refresh the OBS overlay if stats stop updating.') }}</li>
                            <li>{{ __('Use the Random Winner tool only after eligibility checks.') }}</li>
                        </ul>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('Recent Winners') }}
                        </h3>
                        @if (! $tableReady)
                            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('Winners will appear here once data is available.') }}
                            </p>
                        @elseif ($recentWinners->isEmpty())
                            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No winners have been selected yet.') }}
                            </p>
                        @else
                            <ul role="list" class="mt-3 space-y-3">
                                @foreach ($recentWinners as $winner)
                                    <li class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 dark:border-emerald-500/30 dark:bg-emerald-500/10">
                                        <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-200">
                                            {{ $winner->name ?: __('Unnamed caller') }}
                                        </p>
                                        <p class="mt-1 text-xs text-emerald-700 dark:text-emerald-300 font-mono">
                                            {{ $winner->phone ?: '---' }}
                                        </p>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
