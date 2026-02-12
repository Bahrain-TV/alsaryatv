@php
    $totalCallers = \App\Models\Caller::count();
    $totalWinners = \App\Models\Caller::where('is_winner', true)->count();
    $eligibleCallers = \App\Models\Caller::where('is_winner', false)->count();
    $todayRegistrations = \App\Models\Caller::whereDate('created_at', today())->count();
@endphp

<div class="dashboard-hero" style="background: linear-gradient(135deg, rgba(251, 191, 36, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%); border-radius: 20px; padding: 3rem; margin-bottom: 3rem; border: 2px solid rgba(251, 191, 36, 0.2);">
    <!-- Hero Section -->
    <div style="text-align: center; margin-bottom: 2rem;">
        <h1 style="font-size: 3rem; color: #fbbf24; margin-bottom: 1rem; font-weight: 900; text-shadow: 0 0 30px rgba(251, 191, 36, 0.3);">
            🎯 لوحة التحكم - برنامج السارية
        </h1>
        <p style="font-size: 1.25rem; color: #34d399; margin-bottom: 2rem;">
            إدارة المتصلين والفائزين بسهولة وأمان
        </p>

        <!-- Quick Stats -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="stat-card" style="background: rgba(15, 23, 42, 0.6); border: 2px solid rgba(251, 191, 36, 0.3); border-radius: 15px; padding: 1.5rem;">
                <div style="font-size: 2.5rem; color: #fbbf24; font-weight: 900;">{{ $totalCallers }}</div>
                <div style="color: #94a3b8; font-size: 0.9rem; margin-top: 0.5rem;">إجمالي المتصلين</div>
            </div>
            <div class="stat-card" style="background: rgba(15, 23, 42, 0.6); border: 2px solid rgba(16, 185, 129, 0.3); border-radius: 15px; padding: 1.5rem;">
                <div style="font-size: 2.5rem; color: #34d399; font-weight: 900;">{{ $eligibleCallers }}</div>
                <div style="color: #94a3b8; font-size: 0.9rem; margin-top: 0.5rem;">مؤهلين للفوز</div>
            </div>
            <div class="stat-card" style="background: rgba(15, 23, 42, 0.6); border: 2px solid rgba(34, 197, 94, 0.3); border-radius: 15px; padding: 1.5rem;">
                <div style="font-size: 2.5rem; color: #22c55e; font-weight: 900;">{{ $totalWinners }}</div>
                <div style="color: #94a3b8; font-size: 0.9rem; margin-top: 0.5rem;">الفائزون</div>
            </div>
            <div class="stat-card" style="background: rgba(15, 23, 42, 0.6); border: 2px solid rgba(147, 51, 234, 0.3); border-radius: 15px; padding: 1.5rem;">
                <div style="font-size: 2.5rem; color: #a855f7; font-weight: 900;">{{ $todayRegistrations }}</div>
                <div style="color: #94a3b8; font-size: 0.9rem; margin-top: 0.5rem;">تسجيلات اليوم</div>
            </div>
        </div>

        <!-- HUGE RED WINNER SELECTION BUTTON -->
        @if ($eligibleCallers > 0)
            <button id="select-winner-btn"
                    style="
                        width: 100%;
                        max-width: 600px;
                        padding: 2rem;
                        background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
                        color: white;
                        font-size: 2rem;
                        font-weight: 900;
                        border: 4px solid #991b1b;
                        border-radius: 25px;
                        cursor: pointer;
                        box-shadow: 0 10px 40px rgba(220, 38, 38, 0.5), inset 0 -4px 0 rgba(0,0,0,0.3);
                        transition: all 0.3s ease;
                        transform: scale(1);
                        text-shadow: 0 2px 10px rgba(0,0,0,0.3);
                    "
                    onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 15px 50px rgba(220, 38, 38, 0.7), inset 0 -4px 0 rgba(0,0,0,0.3)'"
                    onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 10px 40px rgba(220, 38, 38, 0.5), inset 0 -4px 0 rgba(0,0,0,0.3)'"
                    onmousedown="this.style.transform='scale(0.98)'"
                    onmouseup="this.style.transform='scale(1.05)'">
                🎡 اختر الفائز الآن!
            </button>
            <p style="color: #fbbf24; margin-top: 1rem; font-size: 0.9rem;">
                ⚠️ يرجى الضغط بحذر - سيتم حظر الفائز فوراً من الظهور مرة أخرى
            </p>
        @else
            <div style="background: rgba(239, 68, 68, 0.1); border: 2px solid rgba(239, 68, 68, 0.3); border-radius: 15px; padding: 2rem; color: #fca5a5;">
                <p style="font-size: 1.25rem; margin: 0;">❌ لا يوجد متصلون مؤهلون للفوز حالياً</p>
            </div>
        @endif
    </div>
</div>

<!-- Winner Selection Modal (Hidden by default) -->
<div id="winner-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; display: flex; align-items: center; justify-content: center;">
    <div style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.95)); border: 3px solid #fbbf24; border-radius: 30px; padding: 3rem; max-width: 600px; width: 90%; text-align: center; box-shadow: 0 25px 100px rgba(0,0,0,0.5);">
        <h2 style="color: #fbbf24; font-size: 2rem; margin-bottom: 2rem; font-weight: 900;">🎯 الفائز الأسبوع هذا</h2>

        <!-- Spinning Names Animation Container -->
        <div id="names-spin-container" style="
            background: rgba(0,0,0,0.3);
            border: 2px dashed rgba(251, 191, 36, 0.5);
            border-radius: 20px;
            padding: 3rem 2rem;
            margin-bottom: 2rem;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        ">
            <div id="spinning-text" style="
                font-size: 3rem;
                color: #34d399;
                font-weight: 900;
                text-align: center;
                white-space: nowrap;
                animation: spin 0.1s linear infinite;
            ">
                جاري الاختيار...
            </div>
        </div>

        <div id="winner-result" style="display: none;">
            <div id="winner-name" style="
                font-size: 2.5rem;
                color: #22c55e;
                font-weight: 900;
                margin-bottom: 1rem;
                text-shadow: 0 0 20px rgba(34, 197, 94, 0.5);
            "></div>
            <button id="confirm-winner-btn" style="
                width: 100%;
                padding: 1rem;
                background: linear-gradient(135deg, #22c55e, #16a34a);
                color: white;
                font-size: 1.25rem;
                font-weight: 700;
                border: none;
                border-radius: 15px;
                cursor: pointer;
                margin-bottom: 1rem;
            ">
                ✅ تأكيد الفائز
            </button>
            <button id="close-modal-btn" style="
                width: 100%;
                padding: 1rem;
                background: transparent;
                color: #fbbf24;
                font-size: 1rem;
                font-weight: 700;
                border: 2px solid #fbbf24;
                border-radius: 15px;
                cursor: pointer;
            ">
                ❌ إلغاء
            </button>
        </div>

        <button id="start-spin-btn" style="
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #0f172a;
            font-size: 1.25rem;
            font-weight: 700;
            border: none;
            border-radius: 15px;
            cursor: pointer;
        ">
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

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes pulse-glow {
        0%, 100% {
            box-shadow: 0 0 20px rgba(251, 191, 36, 0.3);
        }
        50% {
            box-shadow: 0 0 40px rgba(251, 191, 36, 0.6);
        }
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-10px);
        }
    }

    @keyframes shimmer {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.7;
        }
    }

    .dashboard-hero {
        animation: fadeInUp 0.8s ease-out;
    }

    .stat-card {
        animation: fadeInUp 0.6s ease-out backwards;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .stat-card:nth-child(1) { animation-delay: 0.1s; }
    .stat-card:nth-child(2) { animation-delay: 0.2s; }
    .stat-card:nth-child(3) { animation-delay: 0.3s; }
    .stat-card:nth-child(4) { animation-delay: 0.4s; }

    .stat-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 15px 40px rgba(251, 191, 36, 0.3) !important;
    }

    #select-winner-btn {
        animation: pulse-glow 2s ease-in-out infinite;
        transition: all 0.3s ease;
    }

    #select-winner-btn:hover {
        animation: pulse-glow 1s ease-in-out infinite;
    }

    #select-winner-btn:active {
        animation: none;
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/TextPlugin.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.0/dist/confetti.browser.min.js"></script>

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

    // Open modal with animation
    selectWinnerBtn?.addEventListener('click', async () => {
        await fetchCallers();
        if (callersList.length < 20) {
            alert('عدد المتصلين غير كافٍ (نحتاج 20 على الأقل)');
            return;
        }

        // Animate modal entrance
        winnerModal.style.display = 'flex';
        winnerResult.style.display = 'none';
        startSpinBtn.style.display = 'block';

        gsap.from(winnerModal.querySelector('div'), {
            scale: 0.8,
            opacity: 0,
            duration: 0.5,
            ease: 'back.out'
        });
    });

    // Close modal
    closeModalBtn?.addEventListener('click', () => {
        gsap.to(winnerModal.querySelector('div'), {
            scale: 0.8,
            opacity: 0,
            duration: 0.3,
            ease: 'back.in',
            onComplete: () => {
                winnerModal.style.display = 'none';
                isSpinning = false;
            }
        });
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

        // Animate spin container
        gsap.to(nameSpinContainer, {
            scale: 1.1,
            boxShadow: '0 0 40px rgba(251, 191, 36, 0.6)',
            duration: 0.3
        });

        // Stop spinning after 3 seconds
        setTimeout(() => {
            clearInterval(spinInterval);
            selectedWinner = shuffled[Math.floor(Math.random() * 20)];

            // Animate winner reveal
            gsap.to(nameSpinContainer, {
                scale: 1,
                boxShadow: '0 0 20px rgba(251, 191, 36, 0.3)',
                duration: 0.3
            });

            winnerNameDisplay.textContent = selectedWinner.name;
            spinningText.style.display = 'none';

            gsap.from(winnerResult, {
                opacity: 0,
                y: 20,
                duration: 0.5,
                onStart: () => {
                    winnerResult.style.display = 'block';
                }
            });

            startSpinBtn.style.display = 'none';
            isSpinning = false;

            // Show confetti
            showConfetti();
        }, 3000);
    });

    // Confirm winner and block
    confirmWinnerBtn?.addEventListener('click', async () => {
        if (!selectedWinner) return;

        // Disable button during request
        confirmWinnerBtn.disabled = true;
        confirmWinnerBtn.style.opacity = '0.7';

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
                // Celebrate with extra confetti
                showConfetti();
                showConfetti();

                // Close modal with animation
                setTimeout(() => {
                    gsap.to(winnerModal.querySelector('div'), {
                        scale: 0.8,
                        opacity: 0,
                        duration: 0.3,
                        ease: 'back.in',
                        onComplete: () => {
                            alert('تم تحديد الفائز بنجاح وحظره من الظهور مرة أخرى!');
                            winnerModal.style.display = 'none';
                            location.reload();
                        }
                    });
                }, 500);
            } else {
                alert('حدث خطأ في حفظ البيانات');
                confirmWinnerBtn.disabled = false;
                confirmWinnerBtn.style.opacity = '1';
            }
        } catch (error) {
            console.error('Error marking winner:', error);
            alert('حدث خطأ');
            confirmWinnerBtn.disabled = false;
            confirmWinnerBtn.style.opacity = '1';
        }
    });

    // Enhanced confetti animation using canvas-confetti
    function showConfetti() {
        if (typeof confetti === 'undefined') {
            console.warn('canvas-confetti not loaded');
            return;
        }

        // Multiple bursts for more celebration
        confetti({
            particleCount: 100,
            spread: 70,
            origin: { y: 0.6 },
            colors: ['#fbbf24', '#34d399', '#22c55e', '#f59e0b', '#ec4899', '#3b82f6'],
        });

        // Second burst from sides
        confetti({
            particleCount: 60,
            angle: 60,
            spread: 55,
            origin: { x: 0, y: 0.6 },
            colors: ['#fbbf24', '#f59e0b', '#ec4899'],
        });

        confetti({
            particleCount: 60,
            angle: 120,
            spread: 55,
            origin: { x: 1, y: 0.6 },
            colors: ['#34d399', '#22c55e', '#3b82f6'],
        });
    }
</script>
