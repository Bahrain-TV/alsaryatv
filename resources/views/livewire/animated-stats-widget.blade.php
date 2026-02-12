<div
    x-data="{
        totalCallers: 0,
        totalWinners: 0,
        todayCallers: 0,
        totalHits: 0,
        activeCallers: 0,
        uniqueCprs: 0,
        winRatio: 0,
        todayTrend: 0,
        averageHits: 0,

        init() {
            // Animate values when component is initialized
            this.animateValue('totalCallers', 0, {{ $totalCallers ?? 0 }});
            this.animateValue('totalWinners', 0, {{ $totalWinners ?? 0 }});
            this.animateValue('todayCallers', 0, {{ $todayCallers ?? 0 }});
            this.animateValue('totalHits', 0, {{ $totalHits ?? 0 }});
            this.animateValue('activeCallers', 0, {{ $activeCallers ?? 0 }});
            this.animateValue('uniqueCprs', 0, {{ $uniqueCprs ?? 0 }});
            
            // Calculate derived values
            this.winRatio = {{ $totalCallers > 0 ? round(($totalWinners ?? 0) / $totalCallers * 100, 1) : 0 }};
            this.todayTrend = {{ $previousDayCallers > 0 ? round((($todayCallers ?? 0) - $previousDayCallers) / $previousDayCallers * 100, 1) : 0 }};
            this.averageHits = {{ $totalCallers > 0 ? round(($totalHits ?? 0) / $totalCallers, 1) : 0 }};
        },

        animateValue(property, start, end) {
            if (start === end) {
                this[property] = end;
                return;
            }

            let startTime = null;
            const duration = 2000; // Animation duration in ms

            const animate = (currentTime) => {
                if (!startTime) startTime = currentTime;
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);

                // Ease-out function for smooth animation
                const easeOut = 1 - Math.pow(1 - progress, 2);

                const currentValue = Math.floor(start + (end - start) * easeOut);
                this[property] = currentValue;

                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    this[property] = end; // Ensure final value is exact
                }
            };

            requestAnimationFrame(animate);
        }
    }"
    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 w-full"
>
    <!-- Total Callers Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-md transition-all duration-300 ease-in-out transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">إجمالي المتصلين</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2" x-text="totalCallers.toLocaleString()"></p>
            </div>
            <div class="p-3 rounded-lg bg-amber-100 dark:bg-amber-500/20">
                <svg class="w-6 h-6 text-amber-500" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">📞 جميع المتصلين المسجلين في النظام</p>
    </div>

    <!-- Winners Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-md transition-all duration-300 ease-in-out transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">الفائزون</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2" x-text="totalWinners.toLocaleString()"></p>
            </div>
            <div class="p-3 rounded-lg bg-emerald-100 dark:bg-emerald-500/20">
                <svg class="w-6 h-6 text-emerald-500" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-3" x-text="'🏆 ' + winRatio + '% من إجمالي المتصلين'"></p>
    </div>

    <!-- Today's Callers Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-md transition-all duration-300 ease-in-out transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">متصلو اليوم</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2" x-text="todayCallers.toLocaleString()"></p>
            </div>
            <div class="p-3 rounded-lg bg-sky-100 dark:bg-sky-500/20">
                <svg class="w-6 h-6 text-sky-500" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-3" x-text="todayTrend >= 0 ? '📈 زيادة ' + todayTrend + '% عن الأمس' : '📉 انخفاض ' + Math.abs(todayTrend) + '% عن الأمس'"></p>
    </div>

    <!-- Total Hits Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-md transition-all duration-300 ease-in-out transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">إجمالي المشاركات</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2" x-text="totalHits.toLocaleString()"></p>
            </div>
            <div class="p-3 rounded-lg bg-purple-100 dark:bg-purple-500/20">
                <svg class="w-6 h-6 text-purple-500" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-3" x-text="'👋 متوسط ' + averageHits + ' مشاركة لكل متصل'"></p>
    </div>

    <!-- Active Callers Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-md transition-all duration-300 ease-in-out transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">المتصلون النشطون</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2" x-text="activeCallers.toLocaleString()"></p>
            </div>
            <div class="p-3 rounded-lg bg-green-100 dark:bg-green-500/20">
                <svg class="w-6 h-6 text-green-500" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-3" x-text="'✅ ' + (totalCallers > 0 ? Math.round((activeCallers / totalCallers) * 100, 1) : 0) + '% من المتصلين'"></p>
    </div>

    <!-- Unique CPRs Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-md transition-all duration-300 ease-in-out transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">أرقام فريدة (CPR)</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2" x-text="uniqueCprs.toLocaleString()"></p>
            </div>
            <div class="p-3 rounded-lg bg-rose-100 dark:bg-rose-500/20">
                <svg class="w-6 h-6 text-rose-500" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">🔐 عدد المتصلين الفريدين حسب رقم المواطن</p>
    </div>
</div>