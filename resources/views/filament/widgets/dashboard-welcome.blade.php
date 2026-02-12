<div class="fi-wi-widget fi-wi-custom-welcome">
    <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden relative">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6 relative z-10">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-amber-500/10 rounded-full dark:bg-amber-500/20">
                    <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                        {{ $this->getGreeting() }}، {{ $this->getUserName() }}!
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        مرحباً بك في لوحة تحكم برنامج السارية. إليك نظرة سريعة على إحصائيات اليوم.
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">التاريخ الهجري</p>
                    <p class="text-sm font-bold text-amber-600 dark:text-amber-400">{{ env('RAMADAN_HIJRI_DATE', '1 رمضان 1447 هـ') }}</p>
                </div>
                <div class="h-10 w-px bg-gray-200 dark:bg-gray-800 mx-2 hidden sm:block"></div>
                <div class="text-right">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الحالة الحالية</p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-sm font-bold text-gray-950 dark:text-white">النظام يعمل بشكل ممتاز</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Decorative background elements -->
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-amber-500/5 rounded-full blur-3xl"></div>
        <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-sky-500/5 rounded-full blur-3xl"></div>
    </div>
</div>
