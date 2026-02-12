<x-filament-widgets::widget>
    <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 h-full">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">إجراءات سريعة</h3>
        </div>

        <div class="grid grid-cols-2 gap-3">
            @foreach($this->getQuickActions() as $action)
                <a href="{{ $action['url'] }}" 
                   class="flex flex-col items-center justify-center p-3 rounded-lg border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                    <div class="p-2 rounded-full bg-{{ $action['color'] }}-100 dark:bg-{{ $action['color'] }}-500/10 mb-2 group-hover:scale-110 transition-transform">
                        @svg($action['icon'], 'w-5 h-5 text-' . $action['color'] . '-600 dark:text-' . $action['color'] . '-400')
                    </div>
                    <span class="text-xs font-medium text-gray-900 dark:text-gray-100">{{ $action['title'] }}</span>
                </a>
            @endforeach
        </div>

        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
            <p class="text-[10px] text-gray-500 text-center uppercase tracking-widest">تحكم كامل في النظام</p>
        </div>
    </div>
</x-filament-widgets::widget>
