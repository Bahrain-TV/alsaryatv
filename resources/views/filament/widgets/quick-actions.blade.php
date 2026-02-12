<x-filament-widgets::widget>
    <div class="bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 rounded-2xl p-8 text-white shadow-2xl relative overflow-hidden">
        <!-- Animated background elements -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>

        <div class="relative z-10">
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-2">
                    <div class="text-4xl">⚡</div>
                    <h2 class="text-3xl font-bold bg-gradient-to-r from-purple-200 via-pink-200 to-purple-200 bg-clip-text text-transparent">الإجراءات السريعة</h2>
                </div>
                <p class="text-purple-200 text-sm font-medium">الوصول السريع للعمليات الشائعة والمهمة</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($this->getQuickActions() as $action)
                    <a href="{{ $action['url'] }}"
                       class="group relative bg-white/10 hover:bg-white/20 backdrop-blur-xl rounded-xl p-6 transition-all duration-300 transform hover:-translate-y-1 border border-white/20 hover:border-white/40 overflow-hidden">
                        <!-- Shine effect -->
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 group-hover:opacity-20 -translate-x-full group-hover:translate-x-full transition-all duration-500"></div>

                        <div class="relative z-10">
                            <div class="text-5xl mb-4 transition-transform duration-300 group-hover:scale-125 group-hover:rotate-12 origin-center">{{ $action['icon'] }}</div>
                            <h3 class="font-bold text-lg mb-2 text-white group-hover:text-transparent group-hover:bg-gradient-to-r group-hover:from-purple-200 group-hover:to-pink-200 group-hover:bg-clip-text transition-all">{{ $action['title'] }}</h3>
                            <p class="text-sm text-purple-100 leading-relaxed">{{ $action['description'] }}</p>
                            <div class="mt-5 flex items-center text-xs font-semibold text-purple-200 group-hover:text-white group-hover:translate-x-2 transition-all duration-300">
                                <span>انتقل</span>
                                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-8 pt-6 border-t border-white/20">
                <div class="flex items-center gap-2 text-sm text-purple-200">
                    <span>💡</span>
                    <p class="font-medium">نصيحة: استخدم البحث السريع <kbd class="bg-white/20 px-2 py-1 rounded text-xs font-mono ml-1">⌘K</kbd> للوصول لأي صفحة</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
    </style>
</x-filament-widgets::widget>
