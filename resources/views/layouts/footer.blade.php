<footer id="footer" class="w-full bg-black bg-opacity-70 text-white py-2 sm:py-3 z-10 rtl">
    <div class="container mx-auto px-4">
        <!-- Single row footer with tight spacing -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-2 sm:gap-4 text-xs sm:text-sm">
            <!-- Left: Branding -->
            <div class="text-center sm:text-start">
                <p class="opacity-90 font-tajawal">
                    {{ config('app.ar_translations.footer_title') ?? 'برنامج الســارية ©️ ' . date('Y') }}
                </p>
            </div>

            <!-- Center: Stats -->
            @if(isset($hits))
            <div class="flex gap-3 justify-center opacity-90">
                <span class="text-orange-300">المشاركات: {{ $totalHits ?? 0 }}</span>
                <span class="text-gray-500">•</span>
                <span class="text-orange-300">الزيارات: {{ $hits ?? 0 }}</span>
            </div>
            @endif

            <!-- Right: Links and copyright -->
            <div class="flex gap-3 justify-center sm:justify-end items-center">
                <a href="{{ route('privacy') }}" class="text-indigo-300 hover:text-indigo-100 transition-colors">
                    سياسة الخصوصية
                </a>
                <span class="text-gray-500">•</span>
                <span class="opacity-90">تلفزيون البحرين © {{ date('Y') }}</span>
            </div>
        </div>

        <!-- Bottom: Single copyright line -->
        <div class="border-t border-gray-700 border-opacity-30 mt-1.5 pt-1.5 text-center">
            <p class="text-xs opacity-80">
                تصميم وبرمجة فريق عمل برنامج السارية | الإصدار <span class="text-indigo-300">{{ config('app.version', 'v1.0') }}</span>
            </p>
        </div>
    </div>
</footer>