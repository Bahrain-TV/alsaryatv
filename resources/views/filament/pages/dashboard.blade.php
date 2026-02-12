@php
    $totalCallers = \App\Models\Caller::count();
    $totalWinners = \App\Models\Caller::where('is_winner', true)->count();
    $eligibleCallers = \App\Models\Caller::where('is_winner', false)->count();
    $todayRegistrations = \App\Models\Caller::whereDate('created_at', today())->count();
@endphp

<div class="bg-gradient-to-br from-amber-500/10 via-emerald-500/10 to-amber-500/10 dark:from-amber-500/5 dark:via-emerald-500/5 dark:to-amber-500/5 rounded-3xl p-8 md:p-12 mb-8 border-2 border-amber-500/20 dark:border-amber-500/30">
    <!-- Hero Section -->
    <div class="text-center mb-8">
        <h1 class="text-4xl md:text-5xl text-amber-500 dark:text-amber-400 mb-4 font-black drop-shadow-lg">
            🎯 لوحة التحكم - برنامج السارية
        </h1>
        <p class="text-xl md:text-2xl text-emerald-500 dark:text-emerald-400 mb-8 font-semibold">
            إدارة المتصلين والفائزين بسهولة وأمان
        </p>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-slate-900/60 dark:bg-slate-800/80 border-2 border-amber-500/30 dark:border-amber-500/40 rounded-2xl p-6 backdrop-blur-sm">
                <div class="text-4xl text-amber-500 dark:text-amber-400 font-black">{{ $totalCallers }}</div>
                <div class="text-slate-400 dark:text-slate-300 text-sm mt-2">إجمالي المتصلين</div>
            </div>
            <div class="bg-slate-900/60 dark:bg-slate-800/80 border-2 border-emerald-500/30 dark:border-emerald-500/40 rounded-2xl p-6 backdrop-blur-sm">
                <div class="text-4xl text-emerald-400 dark:text-emerald-300 font-black">{{ $eligibleCallers }}</div>
                <div class="text-slate-400 dark:text-slate-300 text-sm mt-2">مؤهلين للفوز</div>
            </div>
            <div class="bg-slate-900/60 dark:bg-slate-800/80 border-2 border-green-500/30 dark:border-green-500/40 rounded-2xl p-6 backdrop-blur-sm">
                <div class="text-4xl text-green-400 dark:text-green-300 font-black">{{ $totalWinners }}</div>
                <div class="text-slate-400 dark:text-slate-300 text-sm mt-2">الفائزون</div>
            </div>
            <div class="bg-slate-900/60 dark:bg-slate-800/80 border-2 border-purple-500/30 dark:border-purple-500/40 rounded-2xl p-6 backdrop-blur-sm">
                <div class="text-4xl text-purple-400 dark:text-purple-300 font-black">{{ $todayRegistrations }}</div>
                <div class="text-slate-400 dark:text-slate-300 text-sm mt-2">تسجيلات اليوم</div>
            </div>
        </div>

        <!-- HUGE RED WINNER SELECTION BUTTON -->
        @if ($eligibleCallers > 0)
            <button id="select-winner-btn"
                    class="w-full max-w-2xl mx-auto py-8 px-6 bg-gradient-to-br from-red-600 to-red-800 dark:from-red-500 dark:to-red-700 text-white text-3xl md:text-4xl font-black border-4 border-red-900 dark:border-red-600 rounded-3xl cursor-pointer shadow-2xl hover:shadow-red-500/50 dark:hover:shadow-red-400/50 transition-all duration-300 transform hover:scale-105 active:scale-98 drop-shadow-2xl">
                🎡 اختر الفائز الآن!
            </button>
            <p class="text-amber-500 dark:text-amber-400 mt-4 text-base md:text-lg text-center font-semibold">
                ⚠️ يرجى الضغط بحذر - سيتم حظر الفائز فوراً من الظهور مرة أخرى
            </p>
        @else
            <div class="bg-red-500/10 dark:bg-red-500/20 border-2 border-red-500/30 dark:border-red-500/40 rounded-2xl p-8 backdrop-blur-sm">
                <p class="text-red-400 dark:text-red-300 text-xl md:text-2xl font-bold text-center">❌ لا يوجد متصلون مؤهلون للفوز حالياً</p>
            </div>
        @endif
    </div>
</div>

<!-- Winner Selection Modal (Hidden by default) -->
<div id="winner-modal" class="hidden fixed inset-0 bg-black/90 dark:bg-black/95 z-50 flex items-center justify-center p-4">
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 border-4 border-amber-500 dark:border-amber-400 rounded-3xl p-8 md:p-12 max-w-2xl w-full text-center shadow-2xl">
        <h2 class="text-amber-500 dark:text-amber-400 text-3xl md:text-4xl mb-8 font-black">🎯 الفائز الأسبوع هذا</h2>

        <!-- Spinning Names Animation Container -->
        <div id="names-spin-container" class="bg-black/30 dark:bg-black/50 border-2 border-dashed border-amber-500/50 dark:border-amber-400/50 rounded-2xl p-12 mb-8 min-h-[200px] flex items-center justify-center relative overflow-hidden">
            <div id="spinning-text" class="text-4xl md:text-5xl text-emerald-400 dark:text-emerald-300 font-black text-center whitespace-nowrap animate-spin-slow">
                جاري الاختيار...
            </div>
        </div>

        <div id="winner-result" class="hidden">
            <div id="winner-name" class="text-4xl md:text-5xl text-green-400 dark:text-green-300 font-black mb-6 drop-shadow-lg"></div>
            <button id="confirm-winner-btn" class="w-full py-4 bg-gradient-to-r from-green-500 to-green-600 dark:from-green-400 dark:to-green-500 text-white text-xl font-bold border-none rounded-2xl cursor-pointer mb-4 hover:shadow-xl transition-all transform hover:scale-105 active:scale-95">
                ✅ تأكيد الفائز
            </button>
            <button id="close-modal-btn" class="w-full py-4 bg-transparent text-amber-500 dark:text-amber-400 text-lg font-bold border-2 border-amber-500 dark:border-amber-400 rounded-2xl cursor-pointer hover:bg-amber-500/10 dark:hover:bg-amber-400/10 transition-all">
                ❌ إلغاء
            </button>
        </div>

        <button id="start-spin-btn" class="w-full py-4 bg-gradient-to-r from-amber-500 to-amber-600 dark:from-amber-400 dark:to-amber-500 text-slate-900 dark:text-slate-950 text-xl font-bold border-none rounded-2xl cursor-pointer hover:shadow-xl transition-all transform hover:scale-105 active:scale-95">
            🎡 ابدأ الدوران
        </button>
    </div>
</div>

<!-- Confetti Container -->
<div id="confetti-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 10000;"></div>

<!-- Widgets Grid -->
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

<style>
    @keyframes spin {
        0% { transform: rotateY(0deg) rotateZ(0deg); }
        100% { transform: rotateY(360deg) rotateZ(360deg); }
    }
    
    .animate-spin-slow {
        animation: spin 0.1s linear infinite;
    }
    
    /* Ensure modal visibility states */
    #winner-modal.hidden {
        display: none !important;
    }
    
    #winner-modal:not(.hidden) {
        display: flex !important;
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/TextPlugin.min.js"></script>

<script>
    const selectWinnerBtn = document.getElementById('select-winner-btn');
    const winnerModal = document.getElementById('winner-modal');
    const startSpinBtn = document.getElementById('start-spin-btn');
    const confirmWinnerBtn = document.getElementById('confirm-winner-btn');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const spinningText = document.getElementById('spinning-text');
    const winnerResult = document.getElementById('winner-result');
    const winnerNameDisplay = document.getElementById('winner-name');
    const nameSpinContainer = document.getElementById('names-spin-container');

    let selectedWinner = null;
    let callersList = [];
    let isSpinning = false;

    // Fetch eligible callers
    async function fetchCallers() {
        try {
            const response = await fetch('/api/callers/eligible');
            callersList = await response.json();
        } catch (error) {
            console.error('Error fetching callers:', error);
            alert('حدث خطأ في جلب البيانات');
        }
    }

    // Open modal
    selectWinnerBtn?.addEventListener('click', async () => {
        await fetchCallers();
        if (callersList.length < 20) {
            alert('عدد المتصلين غير كافٍ (نحتاج 20 على الأقل)');
            return;
        }
        winnerModal.classList.remove('hidden');
        winnerResult.classList.add('hidden');
        startSpinBtn.classList.remove('hidden');
    });

    // Close modal
    closeModalBtn?.addEventListener('click', () => {
        winnerModal.classList.add('hidden');
        isSpinning = false;
    });

    // Start spinning animation
    startSpinBtn?.addEventListener('click', () => {
        if (isSpinning) return;
        isSpinning = true;

        // Get random 20 unique names
        const shuffled = [...callersList].sort(() => Math.random() - 0.5).slice(0, 20);
        const names = shuffled.map(c => c.name);

        // Spin animation - rapidly cycle through names
        let index = 0;
        const spinInterval = setInterval(() => {
            spinningText.textContent = names[index % names.length];
            index++;
        }, 100);

        // Stop spinning after 3 seconds
        setTimeout(() => {
            clearInterval(spinInterval);
            selectedWinner = shuffled[Math.floor(Math.random() * 20)];
            winnerNameDisplay.textContent = selectedWinner.name;
            spinningText.classList.add('hidden');
            winnerResult.classList.remove('hidden');
            startSpinBtn.classList.add('hidden');
            isSpinning = false;

            // Show confetti
            showConfetti();
        }, 3000);
    });

    // Confirm winner and block
    confirmWinnerBtn?.addEventListener('click', async () => {
        if (!selectedWinner) return;

        try {
            const response = await fetch('/api/callers/' + selectedWinner.id + '/mark-winner', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ block_from_future: true })
            });

            if (response.ok) {
                alert('تم تحديد الفائز بنجاح وحظره من الظهور مرة أخرى!');
                winnerModal.classList.add('hidden');
                location.reload();
            } else {
                alert('حدث خطأ في حفظ البيانات');
            }
        } catch (error) {
            console.error('Error marking winner:', error);
            alert('حدث خطأ');
        }
    });

    // Confetti animation
    function showConfetti() {
        const container = document.getElementById('confetti-container');
        for (let i = 0; i < 100; i++) {
            const confetti = document.createElement('div');
            confetti.style.position = 'fixed';
            confetti.style.width = '10px';
            confetti.style.height = '10px';
            confetti.style.left = Math.random() * 100 + '%';
            confetti.style.top = '-10px';
            confetti.style.backgroundColor = ['#fbbf24', '#34d399', '#22c55e', '#f59e0b', '#ec4899'][Math.floor(Math.random() * 5)];
            confetti.style.borderRadius = '50%';
            confetti.style.pointerEvents = 'none';
            confetti.style.zIndex = '10000';
            container.appendChild(confetti);

            gsap.to(confetti, {
                y: window.innerHeight + 20,
                x: Math.random() * 200 - 100,
                opacity: 0,
                duration: 2 + Math.random() * 1,
                ease: 'power2.in',
                onComplete: () => confetti.remove()
            });
        }
    }
</script>
