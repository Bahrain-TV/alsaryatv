<x-filament-widgets::widget>
    <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-xl p-8 text-white shadow-lg">
        <div class="mb-6">
            <h2 class="text-2xl font-bold mb-1">⚡ الإجراءات السريعة</h2>
            <p class="text-indigo-100">الوصول السريع للعمليات الشائعة</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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

        <div class="mt-6 pt-6 border-t border-white border-opacity-20">
            <p class="text-sm text-indigo-100">💡 نصيحة: استخدم البحث السريع (⌘+K) للوصول لأي صفحة</p>
        </div>
    </div>
</x-filament-widgets::widget>
