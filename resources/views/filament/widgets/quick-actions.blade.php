<x-filament-widgets::widget>
    <div class="quick-actions-panel bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-xl p-4 md:p-8 text-white shadow-lg">
        <div class="mb-4 md:mb-6">
            <h2 class="text-lg md:text-2xl font-bold mb-1">⚡ الإجراءات السريعة</h2>
            <p class="text-indigo-100 text-xs md:text-base hidden sm:block">الوصول السريع للعمليات الشائعة</p>
        </div>

        {{-- Mobile: horizontal scroll strip, Desktop: grid --}}
        <div class="hidden md:grid md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($this->getQuickActions() as $action)
                <a href="{{ $action['url'] }}"
                   class="group bg-white bg-opacity-10 hover:bg-opacity-20 backdrop-blur-md rounded-lg p-6 transition-all transform hover:scale-105 hover:shadow-xl">
                    <div class="text-4xl mb-3 group-hover:scale-110 transition-transform">{{ $action['icon'] }}</div>
                    <h3 class="font-bold text-lg mb-1">{{ $action['title'] }}</h3>
                    <p class="text-sm text-indigo-100">{{ $action['description'] }}</p>
                    <div class="mt-4 flex items-center text-sm font-semibold group-hover:translate-x-2 transition-transform">
                        انتقل ←
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Mobile: compact horizontal list --}}
        <div class="flex md:hidden gap-3 overflow-x-auto pb-2 -mx-1 px-1 snap-x snap-mandatory scrollbar-hide">
            @foreach($this->getQuickActions() as $action)
                <a href="{{ $action['url'] }}"
                   class="snap-start flex-shrink-0 w-28 bg-white bg-opacity-10 backdrop-blur-md rounded-lg p-3 text-center transition-all active:scale-95">
                    <div class="text-2xl mb-1">{{ $action['icon'] }}</div>
                    <h3 class="font-semibold text-xs leading-tight">{{ $action['title'] }}</h3>
                </a>
            @endforeach
        </div>

        <div class="mt-4 md:mt-6 pt-4 md:pt-6 border-t border-white border-opacity-20 hidden sm:block">
            <p class="text-sm text-indigo-100">💡 نصيحة: استخدم البحث السريع (⌘+K) للوصول لأي صفحة</p>
        </div>
    </div>

    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</x-filament-widgets::widget>
