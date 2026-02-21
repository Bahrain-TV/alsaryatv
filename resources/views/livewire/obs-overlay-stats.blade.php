<style>
    @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@200;300;400;500;600;700;800;900&display=swap');

    /* ============================================
       ROOT STYLES & VARIABLES
       ============================================ */
    :root {
        --primary-gold: #FFD700;
        --primary-gold-dark: #FFA500;
        --accent-emerald: #10B981;
        --bg-dark: rgba(15, 23, 42, 0.98);
        --bg-darker: rgba(5, 10, 20, 0.95);
        --text-primary: #FFFFFF;
        --text-secondary: rgba(255, 255, 255, 0.85);
        --text-tertiary: rgba(255, 255, 255, 0.65);
        --border-light: rgba(255, 255, 255, 0.15);
    }

    * {
        box-sizing: border-box;
    }

    /* ============================================
       MAIN WRAPPER & CONTAINER
       ============================================ */
    .obs-overlay-wrap {
        position: fixed;
        left: 50%;
        bottom: 12vh;
        transform: translateX(-50%);
        width: min(90vw, 1400px);
        z-index: 50;
    }

    .obs-overlay-float {
        will-change: transform;
    }

    /* Main panel - Premium dark theme */
    .obs-overlay-panel {
        position: relative;
        overflow: hidden;
        font-family: 'Tajawal', 'Segoe UI', sans-serif;
        height: auto;
        min-height: 200px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        
        /* Premium gradient background */
        background: linear-gradient(135deg, 
            rgba(15, 23, 42, 0.98) 0%, 
            rgba(20, 30, 55, 0.96) 50%, 
            rgba(25, 35, 60, 0.97) 100%);
        
        /* Enhanced border and shadow */
        border: 1.5px solid rgba(255, 215, 0, 0.25);
        box-shadow: 
            0 0 60px rgba(255, 215, 0, 0.15),
            0 20px 60px rgba(0, 0, 0, 0.6),
            inset 0 1px 0 rgba(255, 255, 255, 0.1);
        
        border-radius: 24px;
        padding: 2rem 2.5rem;
        backdrop-filter: blur(10px);
    }

    /* Animated background glow */
    .obs-overlay-panel::before {
        content: '';
        position: absolute;
        inset: 0;
        background: 
            radial-gradient(circle at 15% 25%, rgba(255, 215, 0, 0.12) 0%, transparent 50%),
            radial-gradient(circle at 85% 75%, rgba(16, 185, 129, 0.08) 0%, transparent 45%);
        pointer-events: none;
        z-index: 0;
        animation: glowPulse 6s ease-in-out infinite;
    }

    /* Inner premium border */
    .obs-overlay-panel::after {
        content: '';
        position: absolute;
        inset: 1px;
        border-radius: 23px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        pointer-events: none;
        z-index: 0;
    }

    /* ============================================
       LIVE INDICATOR
       ============================================ */
    .obs-overlay-pulse {
        animation: livePulse 2s ease-in-out infinite;
    }

    /* ============================================
       CARD CONTAINERS
       ============================================ */
    /* Individual card display (rotating view) */
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

    /* Individual stat card styling */
    .stat-card-individual {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        z-index: 50;
        text-align: center;
        gap: 0.5rem;
    }

    .stat-card-individual .card-label {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-tertiary);
        text-transform: uppercase;
        letter-spacing: 3px;
        margin: 0;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
    }

    .stat-card-individual .card-value {
        font-size: 5.5rem;
        font-weight: 900;
        background: linear-gradient(135deg, #FFD700 0%, #FF9500 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1.1;
        width: 100%;
        text-align: center;
        direction: ltr;
        font-variant-numeric: tabular-nums;
        font-feature-settings: 'tnum' 1, 'lnum' 1;
        filter: drop-shadow(0 8px 20px rgba(255, 215, 0, 0.3));
        letter-spacing: -2px;
    }

    /* Grid display (all cards visible) */
    .stat-cards-grid {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        width: 95%;
        opacity: 0;
        pointer-events: none;
        z-index: 1;
    }

    .stat-cards-grid.visible {
        opacity: 1;
        pointer-events: auto;
    }

    .stat-card-small {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 1.25rem 1rem;
        border-radius: 16px;
        min-height: 120px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.06) 0%, rgba(255, 255, 255, 0.03) 100%);
        border: 1.5px solid rgba(255, 215, 0, 0.2);
        backdrop-filter: blur(8px);
        transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        transform-origin: center;
    }

    .stat-card-small:hover {
        background: linear-gradient(135deg, rgba(255, 215, 0, 0.12) 0%, rgba(255, 215, 0, 0.06) 100%);
        border-color: rgba(255, 215, 0, 0.4);
        transform: scale(1.08) translateY(-4px);
        box-shadow: 0 12px 30px rgba(255, 215, 0, 0.2);
    }

    .stat-card-small .card-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--text-tertiary);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 0.75rem;
        width: 100%;
        text-align: center;
        line-height: 1.3;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .stat-card-small .card-value {
        font-size: 2.75rem;
        font-weight: 900;
        background: linear-gradient(135deg, #FFD700 0%, #FF9500 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1;
        width: 100%;
        text-align: center;
        direction: ltr;
        font-variant-numeric: tabular-nums;
        font-feature-settings: 'tnum' 1, 'lnum' 1;
        filter: drop-shadow(0 4px 12px rgba(255, 215, 0, 0.25));
    }

    /* ============================================
       HEADER
       ============================================ */
    .overlay-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        position: relative;
        z-index: 2;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(255, 215, 0, 0.15);
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .header-left .live-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-primary);
        text-transform: uppercase;
        letter-spacing: 1px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .header-right {
        font-size: 0.75rem;
        color: var(--text-tertiary);
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    /* ============================================
       FOOTER
       ============================================ */
    .overlay-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.75rem;
        color: var(--text-tertiary);
        position: relative;
        z-index: 2;
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 215, 0, 0.15);
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .footer-left {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .footer-left span:last-child {
        color: var(--primary-gold);
        font-weight: 800;
        font-size: 0.85rem;
    }

    /* ============================================
       ANIMATIONS
       ============================================ */
    @keyframes livePulse {
        0%, 100% {
            opacity: 1;
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        }
        50% {
            opacity: 0.8;
            transform: scale(1.15);
            box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
        }
    }

    @keyframes glowPulse {
        0%, 100% {
            opacity: 0.8;
        }
        50% {
            opacity: 1.2;
        }
    }

    @keyframes slideInFromTop {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideInFromBottom {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ============================================
       RESPONSIVE
       ============================================ */
    @media (max-width: 1024px) {
        .obs-overlay-panel {
            padding: 1.5rem 2rem;
        }

        .stat-card-individual .card-value {
            font-size: 4.5rem;
        }

        .stat-card-small .card-value {
            font-size: 2.25rem;
        }
    }

    @media (max-width: 768px) {
        .obs-overlay-wrap {
            width: min(95vw, 100%);
            bottom: 10vh;
        }

        .obs-overlay-panel {
            padding: 1.25rem 1.5rem;
            min-height: 160px;
        }

        .stat-card-individual .card-label {
            font-size: 1rem;
            letter-spacing: 2px;
        }

        .stat-card-individual .card-value {
            font-size: 3.5rem;
        }

        .stat-cards-grid {
            gap: 1rem;
        }

        .stat-card-small {
            padding: 1rem 0.75rem;
            min-height: 100px;
        }

        .stat-card-small .card-label {
            font-size: 0.7rem;
            letter-spacing: 0.5px;
        }

        .stat-card-small .card-value {
            font-size: 1.8rem;
        }

        .overlay-header, .overlay-footer {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            margin-bottom: 1rem;
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
                        <span class="obs-overlay-pulse inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                        <span class="live-badge">{{ __('🔴 LIVE', [], 'en') }}</span>
                    </div>
                    <span class="header-right">Updated: {{ $lastUpdatedAt }}</span>
                </div>

                <!-- Individual Card Display Area - Full screen stats view -->
                <div class="stat-card-container" id="cardContainer">
                    <div class="stat-card-individual">
                        <div class="card-label" id="cardLabel">Total Callers</div>
                        <div class="card-value" id="cardValue">0</div>
                    </div>
                </div>

                <!-- Combined Cards Grid - All stats in 3-column view -->
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
                    <div class="footer-left">
                        <span>{{ __('Win Ratio', [], 'en') }}</span>:
                        <span>{{ $winRatio }}%</span>
                    </div>
                    <div>
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
// SPRING PHYSICS ENGINE
// ============================================
class SpringValue {
    constructor(initialValue = 0, stiffness = 100, damping = 18) {
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
// OBS OVERLAY ANIMATION CONTROLLER
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    const cardContainer = document.getElementById('cardContainer');
    const cardLabel = document.getElementById('cardLabel');
    const cardValue = document.getElementById('cardValue');
    const cardsGrid = document.getElementById('cardsGrid');

    // Spring physics for smooth number animation
    const numberSpring = new SpringValue(0, 95, 20);
    let currentDisplayValue = 0;

    // Enhanced card data with English labels
    const cardsEnglish = [
        {
            label: '{{ __("Total Callers", [], "en") }}',
            value: {{ $totalCallers }},
            delay: 0,
            displayDuration: 3500
        },
        {
            label: '{{ __("Today Callers", [], "en") }}',
            value: {{ $todayCallers }},
            delay: 4500,
            displayDuration: 3500
        },
        {
            label: '{{ __("Total Hits", [], "en") }}',
            value: {{ $totalHits }},
            delay: 9000,
            displayDuration: 3500
        }
    ];

    // Arabic label versions for the flip animation
    const cardsArabic = [
        {
            label: '{{ __("Total Callers", [], "ar") }}',
            value: {{ $totalCallers }},
            delay: 0,
            displayDuration: 3500
        },
        {
            label: '{{ __("Today Callers", [], "ar") }}',
            value: {{ $todayCallers }},
            delay: 4500,
            displayDuration: 3500
        },
        {
            label: '{{ __("Total Hits", [], "ar") }}',
            value: {{ $totalHits }},
            delay: 9000,
            displayDuration: 3500
        }
    ];

    // Optimized animation configuration (in milliseconds)
    const ANIM_CONFIG = {
        entryDuration: 0.8,        // Card entrance animation
        numberCountDuration: 2.3,   // Number count-up animation
        exitDuration: 0.6,          // Card exit animation
        languageFlipDuration: 0.6,  // Language flip/transition animation
        gridDisplayDuration: 8000,  // Show all cards together
        pauseBetweenCards: 400,     // Pause between card transitions
        pauseBetweenLanguages: 800, // Pause before language flip
        totalCycleDuration: 42000   // Full cycle time (English + Arabic + grid)
    };

    // Format number with thousand separators
    function formatNumber(num) {
        return Math.floor(num).toLocaleString('en-US');
    }

    // Animate card entrance with premium scaling
    function animateCardEnter(card, onComplete) {
        numberSpring.setTarget(0);
        cardLabel.textContent = card.label;
        currentDisplayValue = 0;

        gsap.timeline()
            // Entrance animation
            .to(cardContainer, {
                scale: 0.8,
                opacity: 1,
                duration: ANIM_CONFIG.entryDuration,
                ease: 'elastic.out(1.2, 0.8)'
            }, 0)
            // Label animation
            .to(cardLabel, {
                y: 0,
                opacity: 1,
                duration: 0.5,
                ease: 'power3.out'
            }, 0.2)
            // Value animation
            .to(cardValue, {
                y: 0,
                opacity: 1,
                duration: 0.5,
                ease: 'power3.out'
            }, 0.3)
            // Start number animation
            .call(() => {
                numberSpring.setTarget(card.value);
            }, null, 0.4)
            // Hold final value
            .to({}, {
                duration: ANIM_CONFIG.numberCountDuration,
                onComplete: () => {
                    setTimeout(() => {
                        if (onComplete) onComplete();
                    }, 800);
                }
            }, 0.4);
    }

    function animateCardExit() {
        return new Promise((resolve) => {
            gsap.to(cardContainer, {
                scale: 1.2,
                opacity: 0,
                duration: ANIM_CONFIG.exitDuration,
                ease: 'power3.in',
                onComplete: resolve
            });
        });
    }

    function showGridCards() {
        gsap.to(cardsGrid, {
            opacity: 1,
            duration: 0.7,
            ease: 'power2.out'
        });
    }

    function hideGridCards() {
        gsap.to(cardsGrid, {
            opacity: 0,
            duration: 0.7,
            ease: 'power2.in'
        });
    }

    // Language flip animation - flip the panel and swap labels
    function animateLanguageFlip() {
        return new Promise((resolve) => {
            gsap.to(cardContainer, {
                rotationY: 360,
                duration: ANIM_CONFIG.languageFlipDuration * 2,
                ease: 'power2.inOut',
                onComplete: resolve
            });
        });
    }

    // Run animation sequence with specific cards
    async function runAnimationSequenceWithCards(cards, languageName) {
        // Display Card 1
        cardContainer.classList.add('active');
        await new Promise(resolve => animateCardEnter(cards[0], resolve));
        await animateCardExit();
        await new Promise(resolve => setTimeout(resolve, ANIM_CONFIG.pauseBetweenCards));

        // Display Card 2
        await new Promise(resolve => animateCardEnter(cards[1], resolve));
        await animateCardExit();
        await new Promise(resolve => setTimeout(resolve, ANIM_CONFIG.pauseBetweenCards));

        // Display Card 3
        await new Promise(resolve => animateCardEnter(cards[2], resolve));
        await animateCardExit();
        await new Promise(resolve => setTimeout(resolve, ANIM_CONFIG.pauseBetweenCards));
    }

    // Main animation sequence - shows English, grid, then Arabic, grid again
    async function runAnimationSequence() {
        // === ENGLISH SEQUENCE ===
        await runAnimationSequenceWithCards(cardsEnglish, 'English');

        // Show all cards in grid (English)
        cardContainer.classList.remove('active');
        cardsGrid.classList.add('visible');
        showGridCards();
        await new Promise(resolve => setTimeout(resolve, ANIM_CONFIG.gridDisplayDuration));
        hideGridCards();
        await new Promise(resolve => setTimeout(resolve, ANIM_CONFIG.pauseBetweenLanguages));

        // === LANGUAGE FLIP ANIMATION ===
        await animateLanguageFlip();
        await new Promise(resolve => setTimeout(resolve, 300));

        // === ARABIC SEQUENCE ===
        await runAnimationSequenceWithCards(cardsArabic, 'Arabic');

        // Show all cards in grid (Arabic)
        cardContainer.classList.remove('active');
        cardsGrid.classList.add('visible');
        showGridCards();
        await new Promise(resolve => setTimeout(resolve, ANIM_CONFIG.gridDisplayDuration));
        hideGridCards();
        await new Promise(resolve => setTimeout(resolve, ANIM_CONFIG.pauseBetweenLanguages));

        // Restart animation sequence
        runAnimationSequence();
    }

    // Smooth spring animation loop
    let animationFrameId = null;
    
    function updateSpringAnimation() {
        const deltaTime = 0.016; // 60fps target
        const newValue = numberSpring.update(deltaTime);

        if (Math.abs(newValue - currentDisplayValue) > 0.1) {
            currentDisplayValue = newValue;
            cardValue.textContent = formatNumber(currentDisplayValue);
        }

        animationFrameId = requestAnimationFrame(updateSpringAnimation);
    }

    // Initialize and start animations
    updateSpringAnimation();
    runAnimationSequence();

    // Cleanup function
    function cleanupAnimations() {
        if (animationFrameId) {
            cancelAnimationFrame(animationFrameId);
        }
        gsap.killTweensOf([cardContainer, cardLabel, cardValue, cardsGrid]);
    }

    window.addEventListener('beforeunload', cleanupAnimations);
    window.addEventListener('pagehide', cleanupAnimations);

    // Livewire hooks - cleanup and reinit on component update
    if (window.Livewire) {
        Livewire.on('refreshStats', () => {
            cleanupAnimations();
            setTimeout(() => {
                updateSpringAnimation();
            }, 100);
        });
    }
});
</script>
