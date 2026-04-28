<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Stats Overview -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-4 md:p-6 text-white">
                <div class="text-xs md:text-sm font-medium opacity-90">إجمالي المتصلين</div>
                <div class="text-2xl md:text-3xl font-bold mt-2">{{ $this->getTotalCallersCount() }}</div>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-4 md:p-6 text-white">
                <div class="text-xs md:text-sm font-medium opacity-90">المتصلين المؤهلين</div>
                <div class="text-2xl md:text-3xl font-bold mt-2">{{ $this->getEligibleCallersCount() }}</div>
            </div>
            <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg p-4 md:p-6 text-white col-span-2 md:col-span-1">
                <div class="text-xs md:text-sm font-medium opacity-90">الفائزون</div>
                <div class="text-2xl md:text-3xl font-bold mt-2">{{ $this->getWinnersCount() }}</div>
            </div>
        </div>

        <!-- Winner Selection Zone -->
        <div class="bg-white rounded-lg shadow-lg p-4 md:p-8">
            <h2 class="text-xl md:text-2xl font-bold text-center mb-4 md:mb-8 text-gray-800">منطقة اختيار الفائز</h2>

            @if($this->getEligibleCallersCount() > 0)
                <div class="space-y-4 md:space-y-6">
                    <!-- Spinner Display -->
                    <div class="flex flex-col items-center justify-center min-h-[280px] md:min-h-96 bg-gradient-to-b from-indigo-50 to-white rounded-lg border-2 border-indigo-200 relative overflow-hidden">
                        <!-- Decorative spinning ring -->
                        <div class="absolute inset-0 flex items-center justify-center opacity-10">
                            <div class="w-48 h-48 md:w-64 md:h-64 border-4 border-indigo-500 rounded-full animate-spin" style="animation-duration: 3s;"></div>
                        </div>

                        <!-- Names spinner container -->
                        <div id="namesSpinner" class="relative z-10 text-center px-4">
                            <div class="text-sm md:text-lg font-semibold text-gray-600 mb-4 md:mb-6">جاهز لاختيار الفائز؟</div>
                            <div id="spinnerText" class="text-3xl md:text-5xl font-bold text-indigo-600 min-h-[60px] md:min-h-24 flex items-center justify-center">
                                ✨
                            </div>
                            <div class="text-xs md:text-sm text-gray-500 mt-3 md:mt-4">اضغط الزر لبدء الدوران</div>
                        </div>
                    </div>

                    <!-- Control Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 md:gap-4 justify-center">
                        <button
                            type="button"
                            onclick="startSpinner()"
                            class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-6 md:px-8 py-3 md:py-4 rounded-lg font-bold text-base md:text-lg transition-all transform hover:scale-105 active:scale-95 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none touch-manipulation"
                            style="min-width: 180px;">
                            🎯 ابدأ الدوران
                        </button>
                        <button
                            type="button"
                            onclick="confirmWinner()"
                            class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-6 md:px-8 py-3 md:py-4 rounded-lg font-bold text-base md:text-lg transition-all transform hover:scale-105 active:scale-95 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none touch-manipulation"
                            style="min-width: 180px;"
                            id="confirmBtn"
                            disabled>
                            ✅ تأكيد الفائز
                        </button>
                    </div>

                    <!-- Selected Winner Info -->
                    <div id="winnerInfo" class="hidden bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-300 rounded-lg p-4 md:p-6">
                        <div class="text-center">
                            <div class="text-xs md:text-sm font-medium text-green-700 mb-2">🏆 الفائز المختار</div>
                            <div id="winnerName" class="text-2xl md:text-3xl font-bold text-green-600"></div>
                            <div id="winnerDetails" class="text-sm md:text-base text-gray-600 mt-2 md:mt-3 space-y-1"></div>
                        </div>
                    </div>
                </div>

                <!-- Eligible Callers List -->
                <div class="mt-8 md:mt-10">
                    <h3 class="text-base md:text-lg font-bold text-gray-800 mb-3 md:mb-4">قائمة المتصلين المؤهلين</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4 max-h-[300px] md:max-h-96 overflow-y-auto">
                        @foreach($this->getEligibleCallers() as $caller)
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 md:p-4 hover:shadow-md transition-shadow">
                                <div class="font-semibold text-sm md:text-base text-gray-800">{{ $caller['name'] }}</div>
                                <div class="text-xs md:text-sm text-gray-600 mt-1">☎️ {{ $caller['phone'] }}</div>
                                <div class="text-xs md:text-sm text-gray-600">🆔 {{ $caller['cpr'] }}</div>
                                <div class="text-xs md:text-sm font-medium text-indigo-600 mt-2">📊 {{ $caller['hits'] }} مشاركة</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-12 md:py-16">
                    <div class="text-5xl md:text-6xl mb-3 md:mb-4">📭</div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-2 px-4">لا يوجد متصلين مؤهلين</h3>
                    <p class="text-sm md:text-base text-gray-600 px-4">يجب أن يكون هناك متصلين نشطين لاختيار فائز</p>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/TextPlugin.min.js"></script>
    <script>
        gsap.registerPlugin(TextPlugin);

        const eligibleCallers = {!! json_encode($this->getEligibleCallers()) !!};
        let isSpinning = false;
        let selectedWinner = null;

        async function startSpinner() {
            if (isSpinning || eligibleCallers.length === 0) return;

            isSpinning = true;
            const spinBtn = event.target;
            spinBtn.disabled = true;
            spinBtn.style.opacity = '0.6';

            const spinnerText = document.getElementById('spinnerText');
            const confirmBtn = document.getElementById('confirmBtn');
            const winnerInfo = document.getElementById('winnerInfo');

            // Hide previous winner info
            winnerInfo.classList.add('hidden');
            selectedWinner = null;
            confirmBtn.disabled = true;

            // Shuffle names for spinning effect
            const shuffled = [...eligibleCallers].sort(() => Math.random() - 0.5);

            // Quick spinning animation with GSAP
            for (let i = 0; i < 20; i++) {
                const caller = shuffled[i % shuffled.length];
                await new Promise(resolve => {
                    gsap.to(spinnerText, {
                        duration: 0.1,
                        text: caller.name,
                        ease: 'none',
                        onComplete: resolve
                    });
                });
            }

            // Select random winner
            selectedWinner = eligibleCallers[Math.floor(Math.random() * eligibleCallers.length)];

            // Final slow reveal
            await new Promise(resolve => {
                gsap.to(spinnerText, {
                    duration: 0.5,
                    text: selectedWinner.name,
                    ease: 'power2.out',
                    onComplete: resolve
                });
            });

            // Show confetti effect
            showConfetti();

            // Show winner info
            displayWinnerInfo();

            // Enable confirm button
            confirmBtn.disabled = false;
            confirmBtn.style.opacity = '1';

            isSpinning = false;
            spinBtn.disabled = false;
            spinBtn.style.opacity = '1';
        }

        function displayWinnerInfo() {
            const winnerInfo = document.getElementById('winnerInfo');
            const winnerName = document.getElementById('winnerName');
            const winnerDetails = document.getElementById('winnerDetails');

            winnerName.textContent = selectedWinner.name;
            winnerDetails.innerHTML = `
                <div>☎️ <span class="font-mono">${selectedWinner.phone}</span></div>
                <div>🆔 <span class="font-mono">${selectedWinner.cpr}</span></div>
                <div>📊 عدد المشاركات: <span class="font-bold">${selectedWinner.hits}</span></div>
            `;

            winnerInfo.classList.remove('hidden');

            // Animate the appearance
            gsap.fromTo(winnerInfo,
                { opacity: 0, y: 20 },
                { opacity: 1, y: 0, duration: 0.5, ease: 'back.out' }
            );
        }

        function confirmWinner() {
            if (!selectedWinner) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                || decodeURIComponent(
                    document.cookie.split('; ').find(r => r.startsWith('XSRF-TOKEN='))?.split('=')[1] ?? ''
                )
                || '';

            const confirmBtn = document.getElementById('confirmBtn');
            confirmBtn.disabled = true;
            confirmBtn.style.opacity = '0.6';

            // Make API call to mark winner (sets is_winner=true and is_selected=true)
            fetch(`/api/callers/${selectedWinner.id}/toggle-winner`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ is_winner: true })
            })
            .then(response => response.json().then(data => ({ ok: response.ok, data })))
            .then(({ ok, data }) => {
                if (ok && data.success) {
                    // Remove confirmed winner from local eligible list so spinner can't re-select them
                    const idx = eligibleCallers.findIndex(c => c.id === selectedWinner.id);
                    if (idx !== -1) eligibleCallers.splice(idx, 1);

                    showSuccessMessage(`🏆 تم تحديد ${selectedWinner.name} كفائز`);
                    setTimeout(() => {
                        location.reload();
                    }, 1800);
                } else {
                    showErrorMessage(data.message || 'حدث خطأ أثناء تحديد الفائز');
                    confirmBtn.disabled = false;
                    confirmBtn.style.opacity = '1';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorMessage('حدث خطأ أثناء تحديد الفائز');
                confirmBtn.disabled = false;
                confirmBtn.style.opacity = '1';
            });
        }

        function showConfetti() {
            // Create multiple confetti bursts for a more celebratory feel
            const confettiEmojis = ['🎉', '🎊', '🏆', '⭐', '✨', '🌟', '💫', '🎯', '🥇', '🎁'];
            const colors = ['#FFD700', '#FF6B6B', '#4ECDC4', '#45B7D1', '#F7B731', '#FC427B', '#26DE81'];
            
            // Create 3 bursts of confetti
            for (let burst = 0; burst < 3; burst++) {
                setTimeout(() => {
                    // Create 30 confetti pieces per burst
                    for (let i = 0; i < 30; i++) {
                        const div = document.createElement('div');
                        const useEmoji = Math.random() > 0.5;
                        
                        if (useEmoji) {
                            div.innerHTML = confettiEmojis[Math.floor(Math.random() * confettiEmojis.length)];
                            div.style.fontSize = (Math.random() * 1.5 + 1.5) + 'rem';
                        } else {
                            div.style.width = (Math.random() * 10 + 8) + 'px';
                            div.style.height = (Math.random() * 10 + 8) + 'px';
                            div.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                            div.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
                        }
                        
                        div.style.position = 'fixed';
                        div.style.pointerEvents = 'none';
                        div.style.left = (Math.random() * 100) + '%';
                        div.style.top = '-50px';
                        div.style.zIndex = '9999';
                        document.body.appendChild(div);

                        // Animate with GSAP for smooth falling
                        gsap.to(div, {
                            duration: Math.random() * 1.5 + 2,
                            y: window.innerHeight + 100,
                            x: (Math.random() - 0.5) * 300,
                            rotation: Math.random() * 720 - 360,
                            opacity: 0,
                            ease: 'power2.in',
                            delay: Math.random() * 0.3,
                            onComplete: () => div.remove()
                        });
                    }
                }, burst * 200);
            }
        }

        function showSuccessMessage(message) {
            const msg = document.createElement('div');
            msg.className = 'fixed top-4 right-4 left-4 md:left-auto md:w-96 bg-green-500 text-white px-6 py-4 rounded-lg shadow-xl z-[10000] text-center font-bold text-sm md:text-base';
            msg.innerHTML = `🎉 ${message} 🎉`;
            document.body.appendChild(msg);

            gsap.fromTo(msg,
                { opacity: 0, y: -30, scale: 0.9 },
                { opacity: 1, y: 0, scale: 1, duration: 0.4, ease: 'back.out(1.7)' }
            );

            // Pulse animation
            gsap.to(msg, {
                scale: 1.02,
                duration: 0.2,
                yoyo: true,
                repeat: 2,
                delay: 0.5
            });

            setTimeout(() => {
                gsap.to(msg, {
                    opacity: 0,
                    y: -30,
                    scale: 0.9,
                    duration: 0.3,
                    onComplete: () => msg.remove()
                });
            }, 2500);
        }

        function showErrorMessage(message) {
            const msg = document.createElement('div');
            msg.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            msg.textContent = message;
            document.body.appendChild(msg);

            setTimeout(() => {
                msg.remove();
            }, 3000);
        }
    </script>
    @endpush
</x-filament-layouts::page>
