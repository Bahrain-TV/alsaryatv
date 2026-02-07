<x-filament-widgets::widget>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-bold text-gray-800">❓ مركز المساعدة</h3>
                <p class="text-sm text-gray-600 mt-1">نصائح سريعة لتحسين استخدام الإدارة</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($this->getHelpTopics() as $topic)
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="text-3xl mb-2">{{ $topic['icon'] }}</div>
                    <h4 class="font-semibold text-gray-800 text-sm mb-1">{{ $topic['title'] }}</h4>
                    <p class="text-xs text-gray-600 leading-relaxed">{{ $topic['description'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-6 pt-6 border-t border-gray-200 bg-amber-50 rounded-lg p-4">
            <p class="text-sm text-amber-800">
                <span class="font-semibold">⚡ اختصار لوحة المفاتيح:</span>
                استخدم <kbd class="bg-white border border-gray-300 rounded px-2 py-1 text-xs font-mono">Cmd/Ctrl + K</kbd>
                للبحث السريع في أي مكان
            </p>
        </div>
    </div>
</x-filament-widgets::widget>
