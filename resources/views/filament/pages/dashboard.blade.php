<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-6">
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::DASHBOARD_STATS_START, scopes: $this->getRenderHookScopes()) }}

    @foreach ($this->getCachedWidgets() as $widget)
        @if ($widget->columnSpan === 'full')
            <div class="col-span-4">
                {{ $widget }}
            </div>
        @elseif ($widget->columnSpan === 2)
            <div class="col-span-2">
                {{ $widget }}
            </div>
        @else
            <div class="col-span-1">
                {{ $widget }}
            </div>
        @endif
    @endforeach

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::DASHBOARD_STATS_END, scopes: $this->getRenderHookScopes()) }}
</div>