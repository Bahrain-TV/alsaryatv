<style>
    @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@200;300;400;500;700;800;900&display=swap');

    /* Main wrapper - centered horizontally, positioned higher from bottom */
    .obs-overlay-wrap {
        position: fixed;
        left: 50%;
        bottom: 15vh;
        transform: translateX(-50%);
        width: min(85vw, 1200px);
        z-index: 50;
    }

    .obs-overlay-float {
        will-change: transform;
    }

    /* Main panel - compact, symmetric layout */
    .obs-overlay-panel {
        position: relative;
        overflow: hidden;
        font-family: 'Tajawal', sans-serif;
        height: 180px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(17, 24, 39, 0.92) 55%, rgba(31, 41, 55, 0.94) 100%);
        border: 1px solid rgba(255, 255, 255, 0.15);
        box-shadow: 0 10px 50px rgba(0, 0, 0, 0.5);
        border-radius: 16px;
        padding: 1rem 1.5rem;
    }

    /* Subtle background glow effects */
    .obs-overlay-panel::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 20% 30%, rgba(245, 222, 179, 0.08) 0%, transparent 50%),
                    radial-gradient(circle at 80% 60%, rgba(197, 157, 95, 0.08) 0%, transparent 45%);
        pointer-events: none;
        z-index: 0;
    }

    /* Inner border for polish */
    .obs-overlay-panel::after {
        content: '';
        position: absolute;
        inset: 1px;
        border-radius: 15px;
        border: 1px solid rgba(255, 255, 255, 0.06);
        pointer-events: none;
        z-index: 0;
    }

    .obs-overlay-panel [dir="auto"] {
        font-family: 'Tajawal', sans-serif;
    }

    /* Live indicator pulse */
    .obs-overlay-pulse {
        animation: obsOverlayPulse 1.8s ease-in-out infinite;
    }

    /* Individual Card Display Container - perfectly centered */
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

    /* Individual stat card - centered content */
    .stat-card-individual {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        z-index: 50;
        text-align: center;
        gap: 0.25rem;
    }

    .stat-card-individual .card-label {
        font-size: 1.1rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.75);
        text-transform: uppercase;
        letter-spacing: 2px;
        margin: 0;
    }

    .stat-card-individual .card-value {
        font-size: 4.5rem;
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
        text-shadow: 0 8px 24px rgba(0,0,0,0.4);
    }

    /* Combined Cards Grid - symmetric 3-column layout */
    .stat-cards-grid {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        width: 95%;
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
        padding: 0.875rem 0.75rem;
        border-radius: 10px;
        min-height: 100px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.12);
        transition: all 0.3s ease;
    }

    .stat-cards-grid .stat-card-small:hover {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.2);
    }

    .stat-card-small .card-label {
        font-size: 0.65rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.65);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
        width: 100%;
        text-align: center;
        line-height: 1.2;
    }

    .stat-card-small .card-value {
        font-size: 2.25rem;
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
    }

    /* Header - compact and centered */
    .overlay-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0;
        position: relative;
        z-index: 2;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .header-right {
        font-size: 0.65rem;
        color: rgba(255, 255, 255, 0.6);
        font-weight: 500;
    }

    /* Footer - compact */
    .overlay-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.6rem;
        color: rgba(255, 255, 255, 0.65);
        position: relative;
        z-index: 2;
    }

    /* Animations */
    @keyframes obsOverlayPulse {
        0%, 100% {
            opacity: 0.7;
            transform: scale(1);
        }
        50% {
            opacity: 1;
            transform: scale(1.2);
        }
    }

</style>

<div wire:poll.2s="refreshStats">
    <div class="obs-overlay-wrap">
        <div class="obs-overlay-float">
            <div class="obs-overlay-panel">

                <!-- Header - Live indicator and timestamp -->
                <div class="overlay-header">
                    <div class="header-left">
                        <span class="obs-overlay-pulse inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                        <span class="text-xs font-semibold text-white">{{ __('Live statistics', [], 'en') }}</span>
                    </div>
                    <span class="header-right">{{ __('Updated') }} {{ $lastUpdatedAt }}</span>
                </div>

                <!-- Individual Card Display Area - centered -->
                <div class="stat-card-container" id="cardContainer">
                    <div class="stat-card-individual">
                        <div class="card-label" id="cardLabel">Total Callers</div>
                        <div class="card-value" id="cardValue">0</div>
                    </div>
                </div>

                <!-- Combined Cards Grid - symmetric 3 columns -->
                <div class="stat-cards-grid" id="cardsGrid">
                    <div class="stat-card-small">
                        <div class="card-label">{{ __('Total Callers', [], 'en') }}</div>
                        <div class="card-value">{{ number_format($totalCallers) }}</div>
                    </div>
                    <div class="stat-card-small">
                        <div class="card-label">{{ __('Today Callers', [], 'en') }}</div>
                        <div class="card-value">{{ number_format($todayCallers) }}</div>
                    </div>
                    <div class="stat-card-small">
                        <div class="card-label">{{ __('Total Hits', [], 'en') }}</div>
                        <div class="card-value">{{ number_format($totalHits) }}</div>
                    </div>
                </div>

                <!-- Footer - Win ratio and version -->
                <div class="overlay-footer">
                    <div>
                        <span>{{ __('Win ratio', [], 'en') }}</span>:
                        <span style="color: rgba(255,255,255,0.85); font-weight: 600;">{{ $winRatio }}%</span>
                    </div>
                    <div style="color: rgba(255, 255, 255, 0.45); font-weight: 500;">
                        v{{ config('alsarya.version', '1.0.0') }}
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

    // Spring physics for number animation - tuned for smooth counting
    // Lower stiffness and damping for smoother, more readable count-up
    const numberSpring = new SpringValue(0, 110, 19);
    let currentDisplayValue = 0;

    // Card data - Enhanced timing for better readability
    const cards = [
        {
            label: '{{ __("Total Callers", [], "en") }}',
            value: {{ $totalCallers }},
            delay: 0,
            displayDuration: 3500  // 3.5 seconds display time
        },
        {
            label: '{{ __("Today Callers", [], "en") }}',
            value: {{ $todayCallers }},
            delay: 4000,
            displayDuration: 3500  // 3.5 seconds display time
        },
        {
            label: '{{ __("Total Hits", [], "en") }}',
            value: {{ $totalHits }},
            delay: 8000,
            displayDuration: 3500  // 3.5 seconds display time
        }
    ];

    // Animation configuration
    const ANIM_CONFIG = {
        cardEnterDuration: 1000,      // 1s for card to slide in
        numberAnimationDuration: 2500, // 2.5s for number to count up
        cardHoldDuration: 1000,       // 1s hold after number completes
        cardExitDuration: 800,        // 0.8s for card to slide out
        gridDisplayDuration: 12000,   // 12s showing all cards together
        totalCycleDuration: 25000     // Total time before restart (~25s)
    };

    // Format number with thousand separators
    function formatNumber(num) {
        return Math.floor(num).toLocaleString('en-US');
    }

    // Animate card entrance with GSAP - Enhanced timing
    function animateCardEnter(card, onComplete) {
        numberSpring.setTarget(0);
        cardLabel.textContent = card.label;
        currentDisplayValue = 0;

        gsap.timeline()
            .to(cardContainer, {
                scale: 1,
                opacity: 1,
                duration: ANIM_CONFIG.cardEnterDuration / 1000,
                ease: 'back.out(1.7)'
            }, 0)
            .to(cardLabel, {
                y: 0,
                opacity: 1,
                duration: 0.6,
                ease: 'power2.out'
            }, 0.2)
            .to(cardValue, {
                y: 0,
                opacity: 1,
                duration: 0.6,
                ease: 'power2.out'
            }, 0.3)
            .call(() => {
                // Start spring animation for numbers with smoother transition
                numberSpring.setTarget(card.value);
            }, null, 0.5)
            .to({}, { 
                duration: ANIM_CONFIG.numberAnimationDuration / 1000,
                onComplete: () => {
                    // Hold the final value briefly before exit
                    setTimeout(() => {
                        if (onComplete) onComplete();
                    }, ANIM_CONFIG.cardHoldDuration);
                }
            }, 0.5);
    }

    function animateCardExit() {
        return new Promise((resolve) => {
            gsap.to(cardContainer, {
                scale: 0.5,
                opacity: 0,
                duration: ANIM_CONFIG.cardExitDuration / 1000,
                ease: 'back.in(1.7)',
                onComplete: resolve
            });
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

    // Main animation sequence - Enhanced timing for readability
    async function runAnimationSequence() {
        // Card 1: Total Callers
        cardContainer.classList.add('active');
        await new Promise(resolve => animateCardEnter(cards[0], resolve));
        
        // Exit Card 1
        await animateCardExit();
        
        // Card 2: Today Callers
        await new Promise(resolve => animateCardEnter(cards[1], resolve));
        
        // Exit Card 2
        await animateCardExit();
        
        // Card 3: Total Hits
        await new Promise(resolve => animateCardEnter(cards[2], resolve));
        
        // Exit Card 3
        await animateCardExit();
        
        // Show all cards together in grid
        cardContainer.classList.remove('active');
        cardsGrid.classList.add('visible');
        showGridCards();
        
        // Hold on grid view for extended time (allows full cycle read)
        await new Promise(resolve => setTimeout(resolve, ANIM_CONFIG.gridDisplayDuration));
        
        // Hide grid and restart
        cardsGrid.classList.remove('visible');
        hideGridCards();
        
        // Restart the animation cycle
        runAnimationSequence();
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
