<style>
    @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@200;300;400;500;700;800;900&display=swap');

    .obs-overlay-wrap {
        position: fixed;
        left: 50%;
        bottom: 10vh;
        transform: translateX(-50%);
        width: min(90vw, 100%);
        padding: 0 1.5rem;
        z-index: 50;
    }

    .obs-overlay-float {
        will-change: transform;
    }

    .obs-overlay-panel {
        position: relative;
        overflow: hidden;
        font-family: 'Tajawal', sans-serif;
        height: 220px;
        display: flex;
        flex-direction: column;
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(17, 24, 39, 0.86) 55%, rgba(31, 41, 55, 0.88) 100%);
        border: 1px solid rgba(255, 255, 255, 0.12);
        box-shadow: 0 14px 40px rgba(0, 0, 0, 0.45);
    }

    .obs-overlay-panel::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 16% 22%, rgba(245, 222, 179, 0.12) 0%, transparent 45%),
                    radial-gradient(circle at 85% 70%, rgba(197, 157, 95, 0.12) 0%, transparent 40%);
        pointer-events: none;
        z-index: 0;
    }

    .obs-overlay-panel::after {
        content: '';
        position: absolute;
        inset: 1px;
        border-radius: 0.95rem;
        border: 1px solid rgba(255, 255, 255, 0.05);
        pointer-events: none;
        z-index: 0;
    }

    .obs-overlay-panel [dir="auto"] {
        font-family: 'Tajawal', sans-serif;
    }

    .obs-overlay-pulse {
        animation: obsOverlayPulse 1.8s ease-in-out infinite;
    }

    /* Individual Card Display Container */
    .stat-card-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        pointer-events: none;
        z-index: 40;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-card-container.active {
        opacity: 1;
        pointer-events: auto;
    }

    .stat-card-individual {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        z-index: 50;
        padding-top: 1rem;
        text-align: center;
    }

    .stat-card-individual .card-label {
        font-size: 1.5rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.7);
        text-transform: uppercase;
        letter-spacing: 3px;
        margin-bottom: 0.5rem;
    }

    .stat-card-individual .card-value {
        font-size: 5rem;
        font-weight: 900;
        background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1;
        width: 100%;
        text-align: center;
        direction: ltr;
        font-variant-numeric: tabular-nums;
        font-feature-settings: 'tnum' 1, 'lnum' 1;
        text-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }

    /* Combined Cards Grid */
    .stat-cards-grid {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) translateY(10px);
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        width: 90%;
        opacity: 0;
        pointer-events: none;
        z-index: 1;
    }
    .stat-cards-grid.visible {
        opacity: 1;
        pointer-events: auto;
    }

    .stat-cards-grid .stat-card-small {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 1.5rem;
        border-radius: 0.75rem;
        min-height: 128px;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.14);
    }

    .stat-card-small .card-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.6);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 0.75rem;
        width: 100%;
        text-align: center;
    }

    .stat-card-small .card-value {
        font-size: 2.5rem;
        font-weight: bold;
        background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1;
        width: 100%;
        text-align: center;
        direction: ltr;
        font-variant-numeric: tabular-nums;
        font-feature-settings: 'tnum' 1, 'lnum' 1;
    }

    /* Animations */
    @keyframes obsOverlayPulse {
        0%, 100% {
            opacity: 0.7;
            transform: scale(1);
        }
        50% {
            opacity: 1;
            transform: scale(1.25);
        }
    }

</style>

<div wire:poll.2s="refreshStats">
    <div class="obs-overlay-wrap">
        <div class="obs-overlay-float">
            <div class="obs-overlay-panel rounded-2xl p-6 backdrop-blur">

                <!-- Content wrapper -->
                <div style="position: relative; z-index: 1;">
                    <!-- Header -->
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2">
                            <span class="obs-overlay-pulse inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                            <p class="text-xs font-semibold text-white">{{ __('Live statistics', [], 'en') }}</p>
                        </div>
                        <span class="text-xs text-white/70">{{ __('Updated') }} {{ $lastUpdatedAt }}</span>
                    </div>

                    <!-- Individual Card Display Area -->
                    <div class="stat-card-container" id="cardContainer">
                        <div class="stat-card-individual">
                            <div class="card-label" id="cardLabel">Total Callers</div>
                            <div class="card-value" id="cardValue">0</div>
                        </div>
                    </div>

                    <!-- Combined Cards Grid -->
                    <div class="stat-cards-grid" id="cardsGrid">
                        <div class="rounded-xl bg-white/5 p-4 stat-card-small">
                            <p class="card-label">{{ __('Total Callers', [], 'en') }}</p>
                            <p class="card-value">{{ number_format($totalCallers) }}</p>
                        </div>
                        <div class="rounded-xl bg-white/5 p-4 stat-card-small">
                            <p class="card-label">{{ __('Today Callers', [], 'en') }}</p>
                            <p class="card-value">{{ number_format($todayCallers) }}</p>
                        </div>
                        <div class="rounded-xl bg-white/5 p-4 stat-card-small">
                            <p class="card-label">{{ __('Total Hits', [], 'en') }}</p>
                            <p class="card-value">{{ number_format($totalHits) }}</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-2 flex justify-between items-center text-xs text-white/70">
                        <div class="text-xs">
                            <span>{{ __('Win ratio', [], 'en') }}</span>:
                            <span>{{ $winRatio }}%</span>
                        </div>
                        <div class="text-xs text-white/50 font-mono">
                            v{{ config('alsarya.version', '1.0.0') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script>
// ============================================
// SPRING PHYSICS IMPLEMENTATION
// ============================================
class SpringValue {
    constructor(initialValue = 0, stiffness = 120, damping = 15) {
        this.position = initialValue;
        this.velocity = 0;
        this.target = initialValue;
        this.stiffness = stiffness;
        this.damping = damping;
    }

    setTarget(value) {
        this.target = value;
    }

    update(deltaTime) {
        const displacement = this.position - this.target;
        const springForce = -this.stiffness * displacement;
        const dampingForce = -this.damping * this.velocity;
        const acceleration = (springForce + dampingForce) / 1;

        this.velocity += acceleration * deltaTime;
        this.position += this.velocity * deltaTime;

        return this.position;
    }

    getValue() {
        return this.position;
    }
}

// ============================================
// OBS STATS CARD ANIMATION
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    const cardContainer = document.getElementById('cardContainer');
    const cardLabel = document.getElementById('cardLabel');
    const cardValue = document.getElementById('cardValue');
    const cardsGrid = document.getElementById('cardsGrid');

    // Spring physics for number animation
    const numberSpring = new SpringValue(0, 150, 20);
    let currentDisplayValue = 0;

    // Card data
    const cards = [
        {
            label: '{{ __("Total Callers", [], "en") }}',
            value: {{ $totalCallers }},
            delay: 0,
            duration: 2000
        },
        {
            label: '{{ __("Today Callers", [], "en") }}',
            value: {{ $todayCallers }},
            delay: 2200,
            duration: 2000
        },
        {
            label: '{{ __("Total Hits", [], "en") }}',
            value: {{ $totalHits }},
            delay: 4400,
            duration: 2000
        }
    ];

    // Format number with thousand separators
    function formatNumber(num) {
        return Math.floor(num).toLocaleString('en-US');
    }

    // Animate card entrance/exit with GSAP
    function animateCardEnter(card, callback) {
        numberSpring.setTarget(0);
        cardLabel.textContent = card.label;
        currentDisplayValue = 0;

        gsap.timeline()
            .to(cardContainer, {
                scale: 1,
                opacity: 1,
                duration: 0.8,
                ease: 'back.out'
            }, 0)
            .to(cardLabel, {
                y: 0,
                opacity: 1,
                duration: 0.6,
                ease: 'power2.out'
            }, 0.1)
            .to(cardValue, {
                y: 0,
                opacity: 1,
                duration: 0.6,
                ease: 'power2.out'
            }, 0.2)
            .call(() => {
                // Start spring animation for numbers
                numberSpring.setTarget(card.value);
            }, null, 0.4)
            .to({}, { duration: card.duration / 1000 }, 0)
            .call(() => {
                callback();
            });
    }

    function animateCardExit() {
        return gsap.to(cardContainer, {
            scale: 0.5,
            opacity: 0,
            duration: 0.8,
            ease: 'back.in'
        });
    }

    function showGridCards() {
        gsap.to(cardsGrid, {
            opacity: 1,
            duration: 0.6,
            ease: 'power2.out'
        });
    }

    function hideGridCards() {
        gsap.to(cardsGrid, {
            opacity: 0,
            duration: 0.6,
            ease: 'power2.in'
        });
    }

    // Main animation sequence
    function runAnimationSequence() {
        const timeline = gsap.timeline();

        // Card 1
        timeline.call(() => {
            cardContainer.classList.add('active');
            animateCardEnter(cards[0], () => {});
        }, null, 0);

        // Card 2
        timeline.call(() => {
            animateCardExit().then(() => {
                animateCardEnter(cards[1], () => {});
            });
        }, null, 2);

        // Card 3
        timeline.call(() => {
            animateCardExit().then(() => {
                animateCardEnter(cards[2], () => {});
            });
        }, null, 4);

        // Show grid
        timeline.call(() => {
            animateCardExit();
            setTimeout(() => {
                cardContainer.classList.remove('active');
                cardsGrid.classList.add('visible');
                showGridCards();
            }, 800);
        }, null, 6.4);

        // Wait and restart
        timeline.to({}, { duration: 8 }, 6.6);
        timeline.call(() => {
            cardsGrid.classList.remove('visible');
            hideGridCards();
            runAnimationSequence();
        });
    }

    // Animation loop for spring physics
    let animationFrameId = null;
    function updateSpringAnimation() {
        const deltaTime = 0.016; // 60fps
        const newValue = numberSpring.update(deltaTime);

        if (Math.abs(newValue - currentDisplayValue) > 0.1) {
            currentDisplayValue = newValue;
            cardValue.textContent = formatNumber(currentDisplayValue);
        }

        animationFrameId = requestAnimationFrame(updateSpringAnimation);
    }

    // Start animations
    updateSpringAnimation();
    runAnimationSequence();

    function cleanupAnimations() {
        cancelAnimationFrame(animationFrameId);
        window.removeEventListener('beforeunload', cleanupAnimations);
        window.removeEventListener('pagehide', cleanupAnimations);
    }

    // Cleanup
    window.addEventListener('beforeunload', cleanupAnimations);
    window.addEventListener('pagehide', cleanupAnimations);
});
</script>
