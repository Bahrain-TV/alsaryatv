<!-- Statistics Panel Wrapper -->
<div class="stats-panel-wrapper">
    <!-- Statistics Panel Toggle Button -->
    <button
        class="stats-toggle-btn"
        @click="toggleStats()"
        title="Toggle Statistics Panel"
        x-show="true"
        x-transition
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
    </button>

    <!-- Statistics Panel Container -->
    <div class="stats-panel-container" style="opacity: 1; transform: translateY(0);">
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
        gsap: null,
        showStats: true,
        animationRunning: false,

        async init() {
            // Import GSAP
            const { default: gsapLib } = await import('gsap');
            this.gsap = gsapLib;

            // Wait for DOM to be ready
            this.$nextTick(() => {
                this.resetValues();
                this.setupAnimations();
            });
        },

        resetValues() {
            // Reset all values to 0 before animation
            this.totalCallers = 0;
            this.totalWinners = 0;
            this.todayCallers = 0;
            this.totalHits = 0;
            this.activeCallers = 0;
            this.uniqueCprs = 0;
            this.winRatio = 0;
            this.todayTrend = 0;
            this.averageHits = 0;

            // Reset DOM values
            const valueElements = this.$el.querySelectorAll('[data-value]');
            valueElements.forEach(el => {
                el.textContent = '0';
            });
        },

        setupAnimations() {
            if (this.animationRunning) return;
            this.animationRunning = true;

            const cards = this.$el.querySelectorAll('.stat-card');
            const cardDelay = 0.15; // Delay between each card

            // Create GSAP timeline
            const tl = this.gsap.timeline();

            // Get the animation targets
            const targets = [
                { selector: '.stat-card-0', property: 'totalCallers', value: {{ $totalCallers ?? 0 }} },
                { selector: '.stat-card-1', property: 'totalWinners', value: {{ $totalWinners ?? 0 }} },
                { selector: '.stat-card-2', property: 'todayCallers', value: {{ $todayCallers ?? 0 }} },
                { selector: '.stat-card-3', property: 'totalHits', value: {{ $totalHits ?? 0 }} },
                { selector: '.stat-card-4', property: 'activeCallers', value: {{ $activeCallers ?? 0 }} },
                { selector: '.stat-card-5', property: 'uniqueCprs', value: {{ $uniqueCprs ?? 0 }} },
            ];

            // Animate each card
            targets.forEach((target, index) => {
                const card = this.$el.querySelector(target.selector);
                if (!card) return;

                // Card reveal animation - staggered
                tl.fromTo(card,
                    {
                        opacity: 0,
                        y: 30,
                        scale: 0.95
                    },
                    {
                        opacity: 1,
                        y: 0,
                        scale: 1,
                        duration: 0.6,
                        ease: 'expo.out'
                    },
                    index * cardDelay // Stagger timing
                );

                // Number counter animation - starts when card appears
                const valueElement = card.querySelector('[data-value]');
                if (valueElement) {
                    const counterObj = { value: 0 };

                    tl.to(counterObj,
                        {
                            value: target.value,
                            duration: 2.5,
                            ease: 'power2.out',
                            onUpdate: () => {
                                valueElement.textContent = Math.floor(counterObj.value).toLocaleString();
                            }
                        },
                        index * cardDelay + 0.1 // Start counter slightly after card appears
                    );
                }
            });

            // Calculate and set derived values after animations
            tl.call(() => {
                this.winRatio = {{ $totalCallers > 0 ? round(($totalWinners ?? 0) / $totalCallers * 100, 1) : 0 }};
                this.todayTrend = {{ $previousDayCallers > 0 ? round((($todayCallers ?? 0) - $previousDayCallers) / $previousDayCallers * 100, 1) : 0 }};
                this.averageHits = {{ $totalCallers > 0 ? round(($totalHits ?? 0) / $totalCallers, 1) : 0 }};
                this.animationRunning = false;
            }, null, '-=1.5'); // Start calculating halfway through final animation
        },

        toggleStats() {
            const panel = this.$el.closest('.stats-panel-container');
            if (!panel) return;

            if (this.showStats) {
                // Hide panel
                this.gsap.to(panel, {
                    opacity: 0,
                    y: 50,
                    duration: 0.4,
                    ease: 'power2.inOut',
                    pointerEvents: 'none'
                });
            } else {
                // Show panel
                this.gsap.to(panel, {
                    opacity: 1,
                    y: 0,
                    duration: 0.4,
                    ease: 'power2.inOut',
                    pointerEvents: 'auto'
                });
            }
            this.showStats = !this.showStats;
        }
    }"
    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-7 w-full"
>
    <!-- Total Callers Card -->
    <div class="stat-card stat-card-0 group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-lg hover:shadow-2xl transition-all duration-300 ease-out transform hover:-translate-y-2 overflow-hidden">
        <!-- Gradient accent bar -->
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 via-amber-500 to-transparent transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>

        <!-- Shine effect -->
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-all duration-700 pointer-events-none"></div>

        <div class="relative z-10">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">إجمالي المتصلين</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-3 bg-gradient-to-r from-amber-600 to-amber-500 bg-clip-text text-transparent" data-value>0</p>
                </div>
                <div class="p-4 rounded-xl bg-gradient-to-br from-amber-100 to-amber-50 dark:from-amber-900/30 dark:to-amber-800/20 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 font-medium">📞 جميع المتصلين المسجلين في النظام</p>
        </div>
    </div>

    <!-- Winners Card -->
    <div class="stat-card stat-card-1 group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-lg hover:shadow-2xl transition-all duration-300 ease-out transform hover:-translate-y-2 overflow-hidden">
        <!-- Gradient accent bar -->
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 via-emerald-500 to-transparent transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>

        <!-- Shine effect -->
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-all duration-700 pointer-events-none"></div>

        <div class="relative z-10">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">الفائزون</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-3 bg-gradient-to-r from-emerald-600 to-emerald-500 bg-clip-text text-transparent" data-value>0</p>
                </div>
                <div class="p-4 rounded-xl bg-gradient-to-br from-emerald-100 to-emerald-50 dark:from-emerald-900/30 dark:to-emerald-800/20 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 font-medium" x-text="'🏆 ' + winRatio + '% من إجمالي المتصلين'"></p>
        </div>
    </div>

    <!-- Today's Callers Card -->
    <div class="stat-card stat-card-2 group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-lg hover:shadow-2xl transition-all duration-300 ease-out transform hover:-translate-y-2 overflow-hidden">
        <!-- Gradient accent bar -->
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-sky-400 via-sky-500 to-transparent transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>

        <!-- Shine effect -->
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-all duration-700 pointer-events-none"></div>

        <div class="relative z-10">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">متصلو اليوم</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-3 bg-gradient-to-r from-sky-600 to-sky-500 bg-clip-text text-transparent" data-value>0</p>
                </div>
                <div class="p-4 rounded-xl bg-gradient-to-br from-sky-100 to-sky-50 dark:from-sky-900/30 dark:to-sky-800/20 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-sky-600 dark:text-sky-400" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 font-medium" x-text="todayTrend >= 0 ? '📈 زيادة ' + todayTrend + '% عن الأمس' : '📉 انخفاض ' + Math.abs(todayTrend) + '% عن الأمس'"></p>
        </div>
    </div>

    <!-- Total Hits Card -->
    <div class="stat-card stat-card-3 group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-lg hover:shadow-2xl transition-all duration-300 ease-out transform hover:-translate-y-2 overflow-hidden">
        <!-- Gradient accent bar -->
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-400 via-purple-500 to-transparent transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>

        <!-- Shine effect -->
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-all duration-700 pointer-events-none"></div>

        <div class="relative z-10">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">إجمالي المشاركات</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-3 bg-gradient-to-r from-purple-600 to-purple-500 bg-clip-text text-transparent" data-value>0</p>
                </div>
                <div class="p-4 rounded-xl bg-gradient-to-br from-purple-100 to-purple-50 dark:from-purple-900/30 dark:to-purple-800/20 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 font-medium" x-text="'👋 متوسط ' + averageHits + ' مشاركة لكل متصل'"></p>
        </div>
    </div>

    <!-- Active Callers Card -->
    <div class="stat-card stat-card-4 group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-lg hover:shadow-2xl transition-all duration-300 ease-out transform hover:-translate-y-2 overflow-hidden">
        <!-- Gradient accent bar -->
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-green-400 via-green-500 to-transparent transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>

        <!-- Shine effect -->
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-all duration-700 pointer-events-none"></div>

        <div class="relative z-10">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">المتصلون النشطون</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-3 bg-gradient-to-r from-green-600 to-green-500 bg-clip-text text-transparent" data-value>0</p>
                </div>
                <div class="p-4 rounded-xl bg-gradient-to-br from-green-100 to-green-50 dark:from-green-900/30 dark:to-green-800/20 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 font-medium" x-text="'✅ ' + (totalCallers > 0 ? Math.round((activeCallers / totalCallers) * 100, 1) : 0) + '% من المتصلين'"></p>
        </div>
    </div>

    <!-- Unique CPRs Card -->
    <div class="stat-card stat-card-5 group relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-lg hover:shadow-2xl transition-all duration-300 ease-out transform hover:-translate-y-2 overflow-hidden">
        <!-- Gradient accent bar -->
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-400 via-rose-500 to-transparent transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>

        <!-- Shine effect -->
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-all duration-700 pointer-events-none"></div>

        <div class="relative z-10">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">أرقام فريدة (CPR)</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-3 bg-gradient-to-r from-rose-600 to-rose-500 bg-clip-text text-transparent" data-value>0</p>
                </div>
                <div class="p-4 rounded-xl bg-gradient-to-br from-rose-100 to-rose-50 dark:from-rose-900/30 dark:to-rose-800/20 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-rose-600 dark:text-rose-400" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 font-medium">🔐 عدد المتصلين الفريدين حسب رقم المواطن</p>
        </div>
    </div>
</div>
    </div>
    <!-- End of Statistics Panel Container -->
</div>
<!-- End of Statistics Panel Wrapper -->