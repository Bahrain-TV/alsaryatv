<div wire:poll.2s="refreshStats" class="w-full max-w-4xl">
    <div class="rounded-2xl border border-white/10 bg-black/70 p-6 shadow-lg backdrop-blur">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                <p class="text-sm font-semibold text-white">{{ __('Live dashboard feed') }}</p>
            </div>
            <span class="text-xs text-white/70">{{ __('Updated') }} {{ $lastUpdatedAt }}</span>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-white/5 p-4">
                <p class="text-xs uppercase tracking-wide text-white/60">{{ __('Total callers') }}</p>
                <p class="mt-2 text-2xl font-semibold text-white">{{ number_format($totalCallers) }}</p>
            </div>
            <div class="rounded-xl bg-white/5 p-4">
                <p class="text-xs uppercase tracking-wide text-white/60">{{ __('Total winners') }}</p>
                <p class="mt-2 text-2xl font-semibold text-white">{{ number_format($totalWinners) }}</p>
            </div>
            <div class="rounded-xl bg-white/5 p-4">
                <p class="text-xs uppercase tracking-wide text-white/60">{{ __('Today callers') }}</p>
                <p class="mt-2 text-2xl font-semibold text-white">{{ number_format($todayCallers) }}</p>
            </div>
            <div class="rounded-xl bg-white/5 p-4">
                <p class="text-xs uppercase tracking-wide text-white/60">{{ __('Total hits') }}</p>
                <p class="mt-2 text-2xl font-semibold text-white">{{ number_format($totalHits) }}</p>
            </div>
        </div>

        <div class="mt-4 text-xs text-white/70">
            {{ __('Win ratio') }}: {{ $winRatio }}%
        </div>
    </div>
</div>
