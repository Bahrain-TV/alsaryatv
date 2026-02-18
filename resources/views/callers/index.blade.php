@extends('layouts.app')

@section('content')
@php
    $obsOverlayUrl = url('/obs');
@endphp

<div class="py-8" x-data="callerDashboard()" x-init="init()">
    <div id="confetti-container" class="pointer-events-none fixed inset-0 z-50"></div>
    <div @class([
        'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6',
        'text-right' => app()->getLocale() === 'ar',
    ])>
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 space-y-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Callers Dashboard') }}</h1>
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200">
                            {{ __('Live') }}
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                        {{ __('Manage callers, winners, and family registrations with a real-time operational view.') }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a
                        href="{{ route('callers.create') }}"
                        class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700"
                    >
                        {{ __('Add Caller') }}
                    </a>
                    <a
                        href="{{ route('winners') }}"
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 dark:border-gray-700 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        {{ __('View Winners') }}
                    </a>
                    <a
                        href="{{ $obsOverlayUrl }}"
                        target="_blank"
                        class="inline-flex items-center justify-center rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200"
                    >
                        {{ __('Open OBS Overlay') }}
                    </a>
                    <a
                        href="{{ $obsOverlayUrl }}"
                        target="_blank"
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200"
                    >
                        {{ __('Statistics OBS Overlay') }}
                    </a>
                    <a
                        href="{{ url('/') }}"
                        target="_blank"
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200"
                    >
                        {{ __('Visit Front Page') }}
                    </a>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <div class="lg:col-span-2" x-data="{ collapsed: window.innerWidth <= 768 }">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900 overflow-hidden">
                        <div class="flex items-center justify-between px-4 py-2 border-b bg-gray-50 dark:bg-gray-900">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Live Stats') }}</h3>
                            <button type="button" @click="collapsed = !collapsed" :aria-expanded="!collapsed" class="text-xs px-2 py-1 rounded-md bg-gray-100 dark:bg-gray-800"> <span x-show="collapsed">{{ __('Show') }}</span><span x-show="!collapsed">{{ __('Hide') }}</span> </button>
                        </div>
                        <div x-show="!collapsed" x-transition class="p-4">
                            @livewire('dashboard-live-stats')
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div x-data="{ collapsed: window.innerWidth <= 768 }" class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900 overflow-hidden">
                        <div class="flex items-center justify-between px-4 py-2 border-b">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('OBS overlay') }}</h2>
                            <button type="button" @click="collapsed = !collapsed" class="text-xs px-2 py-1 rounded-md bg-gray-100 dark:bg-gray-800"> <span x-show="collapsed">{{ __('Show') }}</span><span x-show="!collapsed">{{ __('Hide') }}</span></button>
                        </div>
                        <div x-show="!collapsed" x-transition class="p-4">
                            <p class="mt-2 text-xs text-gray-600 dark:text-gray-300">
                                {{ __('Use this link in an OBS Browser Source to mirror the live dashboard stats in real time.') }}
                            </p>
                            <div class="mt-3 space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500" for="obs-overlay-url">
                                    {{ __('OBS Browser Source URL') }}
                                </label>
                                <input
                                    id="obs-overlay-url"
                                    type="text"
                                    readonly
                                    value="{{ $obsOverlayUrl }}"
                                    dir="ltr"
                                    onclick="this.select()"
                                    class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-xs font-mono text-gray-700 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                                >
                                <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                    <span>{{ __('Livewire updates every 2 seconds.') }}</span>
                                    <span class="hidden sm:inline">•</span>
                                    <span>{{ __('Overlay is publicly accessible — no login required.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div x-data="{ collapsed: window.innerWidth <= 768 }" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900 overflow-hidden">
                        <div class="flex items-center justify-between px-2 py-1 border-b">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Operator guidance') }}</h2>
                            <button type="button" @click="collapsed = !collapsed" class="text-xs px-2 py-1 rounded-md bg-gray-100 dark:bg-gray-800"> <span x-show="collapsed">{{ __('Show') }}</span><span x-show="!collapsed">{{ __('Hide') }}</span></button>
                        </div>
                        <div x-show="!collapsed" x-transition class="p-4">
                            <ul class="mt-3 space-y-2 text-xs text-gray-600 dark:text-gray-300 list-disc ps-4">
                                <li>{{ __('Confirm registrations appear here before going live on air.') }}</li>
                                <li>{{ __('Use the Random Winner tool only after verifying caller eligibility.') }}</li>
                                <li>{{ __('The OBS overlay works independently — no dashboard session needed.') }}</li>
                                <li>{{ __('If the overlay stops updating, refresh the OBS browser source.') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-data="{ collapsed: window.innerWidth <= 768 }" class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Random Winner Draw') }}</h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ __('Tap the button to pick a random winner and mark them.') }}</p>
                </div>
                <button type="button" @click="collapsed = !collapsed" :aria-expanded="!collapsed" class="text-xs px-2 py-1 rounded-md bg-gray-100 dark:bg-gray-800"> <span x-show="collapsed">{{ __('Show') }}</span><span x-show="!collapsed">{{ __('Hide') }}</span></button>
            </div>
            <div x-show="!collapsed" x-transition>
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-center">
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="randomWinner ? randomWinner.name : '{{ __('Ready to draw') }}'"></p>
                        <p class="mt-1 text-xs text-gray-500" x-text="randomWinner ? randomWinner.phone : '---'"></p>
                        <p class="text-xs text-gray-500" x-text="randomWinner ? randomWinner.cpr : '---'"></p>
                    </div>
                    <div class="flex flex-col items-start gap-2">
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-md bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-600 disabled:opacity-60"
                            @click="pickRandomWinner"
                            :disabled="isPicking || callers.length === 0"
                        >
                            {{ __('Select Random Winner') }}
                        </button>
                        <span x-show="pickError" class="text-sm text-red-600" x-text="pickError"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="w-full lg:max-w-sm">
                    <label class="sr-only" for="caller-search">{{ __('Search') }}</label>
                    <input
                        id="caller-search"
                        type="text"
                        x-model="search"
                        @input="onSearchInput"
                        :placeholder="searchMode === 'cpr_phone' ? searchPlaceholderCprPhone : searchPlaceholderAll"
                        :inputmode="searchMode === 'cpr_phone' ? 'numeric' : 'text'"
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button
                            type="button"
                            @click="searchMode = 'all'"
                            :class="searchMode === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'"
                            class="rounded-md px-3 py-2 text-xs font-semibold"
                        >
                            {{ __('Search All') }}
                        </button>
                        <button
                            type="button"
                            @click="searchMode = 'cpr_phone'"
                            :class="searchMode === 'cpr_phone' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'"
                            class="rounded-md px-3 py-2 text-xs font-semibold"
                        >
                            {{ __('CPR / Phone') }}
                        </button>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        @click="filterType = 'all'"
                        :class="filterType === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'"
                        class="rounded-md px-3 py-2 text-xs font-semibold"
                    >
                        {{ __('All') }}
                    </button>
                    <button
                        type="button"
                        @click="filterType = 'family'"
                        :class="filterType === 'family' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'"
                        class="rounded-md px-3 py-2 text-xs font-semibold"
                    >
                        {{ __('Families Only') }}
                    </button>
                    <button
                        type="button"
                        @click="filterType = 'individual'"
                        :class="filterType === 'individual' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'"
                        class="rounded-md px-3 py-2 text-xs font-semibold"
                    >
                        {{ __('Individuals') }}
                    </button>
                    <button
                        type="button"
                        @click="toggleWinnersOnly()"
                        :class="showWinnersOnly ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'"
                        class="rounded-md px-3 py-2 text-xs font-semibold"
                    >
                        {{ __('Winners Only') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="rounded-lg bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
            <div class="md:hidden space-y-3 p-4">
                <template x-for="caller in filteredCallers" :key="caller.id">
                    <div :data-caller-id="caller.id" class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        <div class="text-base font-semibold text-gray-900 dark:text-gray-100" x-text="caller.name"></div>
                        <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Phone') }}</p>
                                <p class="mt-1 text-gray-700 dark:text-gray-200 font-mono" x-text="caller.phone"></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('CPR') }}</p>
                                <p class="mt-1 text-gray-700 dark:text-gray-200 font-mono" x-text="caller.cpr"></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Hits') }}</p>
                                <span class="mt-1 inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800" x-text="caller.hits || 0"></span>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Type') }}</p>
                                <span
                                    class="mt-1 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    :class="caller.is_family ? 'bg-purple-100 text-purple-800' : 'bg-emerald-100 text-emerald-800'"
                                    x-text="caller.is_family ? '{{ __('Family') }}' : '{{ __('Individual') }}'"
                                ></span>
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Status') }}</p>
                                <span
                                    class="mt-1 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    :class="caller.is_winner ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600'"
                                    x-text="caller.is_winner ? '{{ __('Winner') }}' : '{{ __('Active') }}'"
                                ></span>
                            </div>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button
                                type="button"
                                @click="toggleWinner(caller)"
                                class="rounded-md px-3 py-1 text-xs font-semibold"
                                :class="caller.is_winner ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'"
                                x-text="caller.is_winner ? '{{ __('Unmark Winner') }}' : '{{ __('Mark Winner') }}'"
                            ></button>
                            <a
                                :href="`/callers/${caller.id}/edit`"
                                class="rounded-md bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700"
                            >
                                {{ __('Edit') }}
                            </a>
                            <button
                                type="button"
                                @click="confirmDelete(caller)"
                                class="rounded-md bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700"
                            >
                                {{ __('Delete') }}
                            </button>
                        </div>
                    </div>
                </template>

                <div x-show="filteredCallers.length === 0" class="py-10 text-center text-sm text-gray-500">
                    {{ __('No callers found matching your criteria.') }}
                </div>
            </div>

            <div class="hidden md:block overflow-x-auto touch-pan-x">
                <table @class([
                    'min-w-full divide-y divide-gray-200 dark:divide-gray-700',
                    'text-right' => app()->getLocale() === 'ar',
                ])>
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Name') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Phone') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('CPR') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Hits') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Type') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="caller in filteredCallers" :key="caller.id">
                            <tr :data-caller-id="caller.id">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100" x-text="caller.name"></td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300" x-text="caller.phone"></td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300" x-text="caller.cpr"></td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800" x-text="caller.hits || 0"></span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                        :class="caller.is_family ? 'bg-purple-100 text-purple-800' : 'bg-emerald-100 text-emerald-800'"
                                        x-text="caller.is_family ? '{{ __('Family') }}' : '{{ __('Individual') }}'"
                                    ></span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                        :class="caller.is_winner ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600'"
                                        x-text="caller.is_winner ? '{{ __('Winner') }}' : '{{ __('Active') }}'"
                                    ></span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex flex-wrap gap-2">
                                        <button
                                            type="button"
                                            @click="toggleWinner(caller)"
                                            class="rounded-md px-3 py-1 text-xs font-semibold"
                                            :class="caller.is_winner ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'"
                                            x-text="caller.is_winner ? '{{ __('Unmark Winner') }}' : '{{ __('Mark Winner') }}'"
                                        ></button>
                                        <a
                                            :href="`/callers/${caller.id}/edit`"
                                            class="rounded-md bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700"
                                        >
                                            {{ __('Edit') }}
                                        </a>
                                        <button
                                            type="button"
                                            @click="confirmDelete(caller)"
                                            class="rounded-md bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700"
                                        >
                                            {{ __('Delete') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="filteredCallers.length === 0">
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">
                                {{ __('No callers found matching your criteria.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <p class="text-xs text-gray-500">
            {{ __('Showing') }}
            <span x-text="filteredCallers.length"></span>
            {{ __('of') }}
            <span x-text="totalCallers"></span>
            {{ __('records') }}
        </p>

        <div class="mt-3">
            {!! $callers->links() !!}
        </div>
        <div class="mt-2 text-center">
            <button type="button" x-show="currentPage < lastPage" @click="loadPage(currentPage + 1, false)" :disabled="loading" class="inline-flex items-center justify-center rounded-md border px-4 py-2 text-sm font-semibold bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600">
                <span x-show="!loading">{{ __('Load more') }}</span>
                <span x-show="loading">{{ __('Loading...') }}</span>
            </button>
            <div id="infinite-sentinel" style="height:1px;"></div>
        </div>

        <form id="delete-form" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

<script>
    function callerDashboard() {
        return {
            search: '',
            searchMode: 'all',
            filterType: 'all',
            showWinnersOnly: false,
            callers: @json($callers->items()),
            totalCallers: @json($callers->total()),
            currentPage: @json($callers->currentPage()),
            lastPage: @json($callers->lastPage()),
            perPage: @json($callers->perPage()),
            randomWinner: null,
            isPicking: false,
            pickError: null,
            loading: false,
            searchPlaceholderAll: @json(__('Search by name, CPR, or phone')),
            searchPlaceholderCprPhone: @json(__('Search by CPR or phone')),
            deleteMessage: @json(__('Are you sure you want to delete :name?')),
            selectWinnerError: @json(__('Failed to select winner.')),
            selectWinnerFallback: @json(__('An error occurred while selecting a winner.')),
            statusUpdateError: @json(__('Failed to update status.')),
            statusUpdateFallback: @json(__('An error occurred while updating winner status.')),
            abortController: null,
            debounceTimer: null,
            init() {
                // Improve mobile scrolling for the table
                const tableContainer = document.querySelector('.hidden.md\\:block.overflow-x-auto.touch-pan-x');
                if (tableContainer) {
                    tableContainer.style['-webkit-overflow-scrolling'] = 'touch';
                }
                const input = document.getElementById('caller-search');
                if (input) input.addEventListener('input', () => this.onSearchInput());

                // Infinite scroll sentinel
                const sentinel = document.getElementById('infinite-sentinel');
                if (sentinel && 'IntersectionObserver' in window) {
                    const io = new IntersectionObserver(entries => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting && !this.loading && this.currentPage < this.lastPage) {
                                this.loadPage(this.currentPage + 1, false);
                            }
                        });
                    }, { root: null, rootMargin: '400px' });
                    io.observe(sentinel);
                }
            },

            get filteredCallers() {
                // Client-side filters are applied to the current page; server-side search is used for global results
                return this.callers.filter(caller => {
                    const rawSearch = this.search.trim();
                    const searchLower = rawSearch.toLowerCase();
                    const searchDigits = rawSearch.replace(/\D/g, '');

                    const nameValue = (caller.name || '').toLowerCase();
                    const cprValue = (caller.cpr || '').toString();
                    const phoneValue = (caller.phone || '').toString();
                    const cprDigits = cprValue.replace(/\D/g, '');
                    const phoneDigits = phoneValue.replace(/\D/g, '');

                    let matchesSearch = true;
                    if (rawSearch.length > 0) {
                        if (this.searchMode === 'cpr_phone') {
                            matchesSearch = cprValue.includes(rawSearch) || phoneValue.includes(rawSearch);
                            if (!matchesSearch && searchDigits.length > 0) {
                                matchesSearch = cprDigits.includes(searchDigits) || phoneDigits.includes(searchDigits);
                            }
                        } else {
                            matchesSearch =
                                nameValue.includes(searchLower) ||
                                cprValue.includes(rawSearch) ||
                                phoneValue.includes(rawSearch);
                            if (!matchesSearch && searchDigits.length > 0) {
                                matchesSearch = cprDigits.includes(searchDigits) || phoneDigits.includes(searchDigits);
                            }
                        }
                    }

                    const matchesType =
                        this.filterType === 'all' ? true :
                        this.filterType === 'family' ? caller.is_family :
                        !caller.is_family;

                    const matchesWinner = this.showWinnersOnly ? caller.is_winner : true;

                    return matchesSearch && matchesType && matchesWinner;
                });
            },

            onSearchInput() {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.loadPage(1, true);
                }, 300);
            },

            async loadPage(page = 1, replace = true) {
                if (this.abortController) this.abortController.abort();
                this.abortController = new AbortController();

                const params = new URLSearchParams();
                if (this.search) params.set('q', this.search);
                if (this.filterType && this.filterType !== 'all') params.set('type', this.filterType);
                if (this.showWinnersOnly) params.set('winners', '1');
                params.set('page', page);

                this.loading = true;
                try {
                    const response = await fetch(`/dashboard?${params.toString()}`, {
                        headers: { 'Accept': 'application/json' },
                        signal: this.abortController.signal,
                    });
                    const data = await response.json();
                    const items = data.data || data;
                    if (replace) this.callers = items; else this.callers = this.callers.concat(items);
                    this.currentPage = data.current_page || page;
                    this.lastPage = data.last_page || this.lastPage;
                    this.perPage = data.per_page || this.perPage;
                    this.totalCallers = data.total || this.totalCallers;
                } catch (e) {
                    if (e.name !== 'AbortError') console.error('Failed to load callers', e);
                } finally {
                    this.loading = false;
                }
            },

            async pickRandomWinner() {
                if (this.isPicking) return;
                this.pickError = null;
                this.isPicking = true;

                const candidates = this.callers.slice();
                if (candidates.length === 0) {
                    this.pickError = this.selectWinnerFallback;
                    this.isPicking = false;
                    return;
                }

                const minSpinMs = 2200;
                const start = Date.now();
                let spinner;

                if (window.gsap) {
                    let idx = 0;
                    spinner = setInterval(() => {
                        this.randomWinner = candidates[idx % candidates.length];
                        idx++;
                    }, 80);
                } else {
                    spinner = setInterval(() => {
                        this.randomWinner = candidates[Math.floor(Math.random() * candidates.length)];
                    }, 120);
                }

                try {
                    const response = await fetch('/callers/random-winner', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                    });
                    const resp = await response.json();
                    if (!response.ok || !resp.success) throw new Error(resp.message || this.selectWinnerError);
                    const winner = resp.winner;
                    const elapsed = Date.now() - start;
                    const remaining = Math.max(0, minSpinMs - elapsed);
                    setTimeout(() => {
                        clearInterval(spinner);
                        this.randomWinner = winner;
                        const idx = this.callers.findIndex(c => c.id === winner.id);
                        if (idx !== -1) this.callers[idx].is_winner = true;
                        this.isPicking = false;

                        try {
                            if (window.gsap && document.getElementById('confetti-container')) {
                                const el = document.querySelector(`[data-caller-id="${winner.id}"]`) || document.getElementById('confetti-container');
                                window.gsap.fromTo(el, { scale: 0.9, rotation: -6 }, { scale: 1.06, rotation: 0, duration: 0.6, ease: 'elastic.out(1,0.6)' });
                            }
                        } catch (e) {}

                        if (window.fireConfetti) {
                            try {
                                window.fireConfetti(document.getElementById('confetti-container'));
                            } catch (e) {}
                        }
                    }, remaining);
                } catch (err) {
                    clearInterval(spinner);
                    this.pickError = err.message || this.selectWinnerFallback;
                    this.isPicking = false;
                }
            },

            async toggleWinner(caller) {
                const originalState = caller.is_winner;
                try {
                    const response = await fetch(`/callers/${caller.id}/toggle-winner`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                    });
                    const data = await response.json();
                    if (data.success) {
                        caller.is_winner = data.is_winner;
                        return;
                    }
                    caller.is_winner = originalState;
                    alert(this.statusUpdateError);
                } catch (error) {
                    caller.is_winner = originalState;
                    alert(this.statusUpdateFallback);
                }
            },

            confirmDelete(caller) {
                const message = this.deleteMessage.replace(':name', caller.name);
                if (confirm(message)) {
                    const form = document.getElementById('delete-form');
                    form.action = `/callers/${caller.id}`;
                    form.submit();
                }
            },
        };
    }
</script>

@endsection
