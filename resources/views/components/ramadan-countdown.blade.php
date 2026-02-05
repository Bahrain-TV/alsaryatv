@props([
    'targetDate' => null,
    'id' => 'ramadan-countdown'
])

@php
    $defaultDate = config('alsarya.ramadan.start_date', '2026-02-28');
    $timezone = config('alsarya.ramadan.timezone', 'Asia/Bahrain');
    $actualTargetDate = $targetDate ?? ($defaultDate . 'T00:00:00+03:00');
@endphp

<div id="{{ $id }}" class="rc-countdown" data-target="{{ $actualTargetDate }}">
    <div class="rc-grid">
        {{-- Days - First (leftmost in LTR grid) --}}
        <div class="rc-unit">
            <div class="rc-flip-card">
                <div class="rc-card-inner">
                    <div class="rc-card-top">
                        <span class="rc-number" data-days>00</span>
                    </div>
                    <div class="rc-card-bottom">
                        <span class="rc-number" data-days-bottom>00</span>
                    </div>
                    <div class="rc-card-divider"></div>
                </div>
            </div>
            <span class="rc-label">يوم</span>
        </div>

        <div class="rc-separator">:</div>

        {{-- Hours --}}
        <div class="rc-unit">
            <div class="rc-flip-card">
                <div class="rc-card-inner">
                    <div class="rc-card-top">
                        <span class="rc-number" data-hours>00</span>
                    </div>
                    <div class="rc-card-bottom">
                        <span class="rc-number" data-hours-bottom>00</span>
                    </div>
                    <div class="rc-card-divider"></div>
                </div>
            </div>
            <span class="rc-label">ساعة</span>
        </div>

        <div class="rc-separator">:</div>

        {{-- Minutes --}}
        <div class="rc-unit">
            <div class="rc-flip-card">
                <div class="rc-card-inner">
                    <div class="rc-card-top">
                        <span class="rc-number" data-minutes>00</span>
                    </div>
                    <div class="rc-card-bottom">
                        <span class="rc-number" data-minutes-bottom>00</span>
                    </div>
                    <div class="rc-card-divider"></div>
                </div>
            </div>
            <span class="rc-label">دقيقة</span>
        </div>

        <div class="rc-separator">:</div>

        {{-- Seconds --}}
        <div class="rc-unit">
            <div class="rc-flip-card">
                <div class="rc-card-inner">
                    <div class="rc-card-top">
                        <span class="rc-number" data-seconds>00</span>
                    </div>
                    <div class="rc-card-bottom">
                        <span class="rc-number" data-seconds-bottom>00</span>
                    </div>
                    <div class="rc-card-divider"></div>
                </div>
            </div>
            <span class="rc-label">ثانية</span>
        </div>
    </div>
</div>

<style>
/* Ramadan Countdown - Scoped Styles */
.rc-countdown {
    --rc-gold: #d4af37;
    --rc-gold-light: #f5d97e;
    --rc-emerald: #10b981;
    --rc-bg-dark: rgba(15, 23, 42, 0.95);
    --rc-bg-card: rgba(30, 41, 59, 0.9);

    width: 100%;
    padding: 1.5rem 0;
    direction: ltr;
}

.rc-grid {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: clamp(0.25rem, 1.5vw, 1rem);
    flex-wrap: nowrap;
}

.rc-unit {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: clamp(0.5rem, 1.5vw, 0.875rem);
}

.rc-flip-card {
    position: relative;
    width: clamp(50px, 15vw, 100px);
    height: clamp(60px, 18vw, 110px);
    perspective: 1000px;
}

.rc-card-inner {
    position: relative;
    width: 100%;
    height: 100%;
    border-radius: clamp(8px, 2vw, 14px);
    background: linear-gradient(180deg,
        var(--rc-bg-card) 0%,
        var(--rc-bg-dark) 49.9%,
        var(--rc-bg-dark) 50.1%,
        var(--rc-bg-card) 100%);
    border: 1px solid rgba(212, 175, 55, 0.25);
    box-shadow:
        0 8px 32px rgba(0, 0, 0, 0.4),
        0 0 0 1px rgba(255, 255, 255, 0.05) inset,
        0 4px 16px rgba(212, 175, 55, 0.08);
    overflow: hidden;
}

.rc-card-top,
.rc-card-bottom {
    position: absolute;
    left: 0;
    right: 0;
    height: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.rc-card-top {
    top: 0;
    background: linear-gradient(180deg,
        rgba(255, 255, 255, 0.06) 0%,
        transparent 100%);
    border-radius: clamp(8px, 2vw, 14px) clamp(8px, 2vw, 14px) 0 0;
}

.rc-card-top .rc-number {
    transform: translateY(50%);
}

.rc-card-bottom {
    bottom: 0;
    border-radius: 0 0 clamp(8px, 2vw, 14px) clamp(8px, 2vw, 14px);
}

.rc-card-bottom .rc-number {
    transform: translateY(-50%);
}

.rc-card-divider {
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg,
        transparent 0%,
        rgba(0, 0, 0, 0.5) 10%,
        rgba(0, 0, 0, 0.6) 50%,
        rgba(0, 0, 0, 0.5) 90%,
        transparent 100%);
    transform: translateY(-50%);
    z-index: 10;
}

.rc-card-divider::before {
    content: '';
    position: absolute;
    top: -1px;
    left: 5%;
    right: 5%;
    height: 1px;
    background: linear-gradient(90deg,
        transparent,
        rgba(212, 175, 55, 0.3),
        transparent);
}

.rc-number {
    font-family: 'Tajawal', system-ui, -apple-system, sans-serif;
    font-size: clamp(1.5rem, 5vw, 3rem);
    font-weight: 700;
    color: #ffffff;
    text-shadow:
        0 0 20px rgba(212, 175, 55, 0.4),
        0 2px 4px rgba(0, 0, 0, 0.3);
    line-height: 1;
    letter-spacing: 0.05em;
    transition: color 0.2s ease;
}

.rc-unit.pulse .rc-number {
    color: var(--rc-gold);
    text-shadow: 0 0 30px rgba(212, 175, 55, 0.7);
}

.rc-separator {
    font-family: 'Tajawal', sans-serif;
    font-size: clamp(1.25rem, 4vw, 2.5rem);
    font-weight: 700;
    color: var(--rc-gold);
    opacity: 0.7;
    align-self: flex-start;
    margin-top: clamp(1rem, 3vw, 1.75rem);
    animation: rc-pulse 1s ease-in-out infinite;
}

@keyframes rc-pulse {
    0%, 100% { opacity: 0.7; }
    50% { opacity: 0.3; }
}

.rc-label {
    font-family: 'Tajawal', sans-serif;
    font-size: clamp(0.65rem, 2vw, 0.95rem);
    font-weight: 600;
    color: var(--rc-gold);
    text-shadow: 0 0 10px rgba(212, 175, 55, 0.3);
    letter-spacing: 0.05em;
    text-align: center;
}

/* Hover effect */
.rc-flip-card:hover .rc-card-inner {
    border-color: rgba(212, 175, 55, 0.5);
    box-shadow:
        0 8px 32px rgba(0, 0, 0, 0.4),
        0 0 0 1px rgba(255, 255, 255, 0.08) inset,
        0 4px 24px rgba(212, 175, 55, 0.15);
}

/* Tablet */
@media (max-width: 768px) {
    .rc-countdown {
        padding: 1rem 0;
    }

    .rc-grid {
        gap: clamp(0.2rem, 2vw, 0.75rem);
    }
}

/* Mobile */
@media (max-width: 480px) {
    .rc-countdown {
        padding: 0.75rem 0;
    }

    .rc-grid {
        gap: 0.15rem;
    }

    .rc-flip-card {
        width: clamp(48px, 20vw, 65px);
        height: clamp(55px, 22vw, 75px);
    }

    .rc-separator {
        font-size: 1.1rem;
        margin-top: 0.9rem;
    }

    .rc-unit {
        gap: 0.4rem;
    }

    .rc-label {
        font-size: 0.6rem;
    }
}

/* Small Mobile */
@media (max-width: 360px) {
    .rc-grid {
        gap: 0.1rem;
    }

    .rc-flip-card {
        width: 44px;
        height: 52px;
    }

    .rc-number {
        font-size: 1.25rem;
    }

    .rc-separator {
        font-size: 1rem;
        margin-top: 0.75rem;
    }

    .rc-label {
        font-size: 0.55rem;
    }

    .rc-card-inner {
        border-radius: 6px;
    }
}

/* Countdown ended */
.rc-countdown.ended .rc-card-inner {
    border-color: rgba(16, 185, 129, 0.4);
    box-shadow:
        0 8px 32px rgba(0, 0, 0, 0.3),
        0 4px 16px rgba(16, 185, 129, 0.15);
}

.rc-countdown.ended .rc-number {
    color: var(--rc-emerald);
    text-shadow: 0 0 20px rgba(16, 185, 129, 0.5);
}

.rc-countdown.ended .rc-label,
.rc-countdown.ended .rc-separator {
    color: var(--rc-emerald);
}
</style>

<script>
(function() {
    const wrapper = document.getElementById('{{ $id }}');
    if (!wrapper || wrapper.dataset.initialized) return;
    wrapper.dataset.initialized = 'true';

    const targetDate = new Date(wrapper.dataset.target).getTime();

    const elements = {
        days: wrapper.querySelector('[data-days]'),
        daysBottom: wrapper.querySelector('[data-days-bottom]'),
        hours: wrapper.querySelector('[data-hours]'),
        hoursBottom: wrapper.querySelector('[data-hours-bottom]'),
        minutes: wrapper.querySelector('[data-minutes]'),
        minutesBottom: wrapper.querySelector('[data-minutes-bottom]'),
        seconds: wrapper.querySelector('[data-seconds]'),
        secondsBottom: wrapper.querySelector('[data-seconds-bottom]')
    };

    let lastValues = { days: -1, hours: -1, minutes: -1, seconds: -1 };

    function updateUnit(key, value) {
        const paddedValue = String(value).padStart(2, '0');
        const el = elements[key];
        const elBottom = elements[key + 'Bottom'];

        if (lastValues[key] !== value) {
            // Add pulse animation
            const unit = el.closest('.rc-unit');
            unit.classList.add('pulse');
            setTimeout(() => unit.classList.remove('pulse'), 300);
            lastValues[key] = value;
        }

        el.textContent = paddedValue;
        if (elBottom) elBottom.textContent = paddedValue;
    }

    function updateCountdown() {
        const now = Date.now();
        const distance = targetDate - now;

        if (distance <= 0) {
            wrapper.classList.add('ended');
            ['days', 'hours', 'minutes', 'seconds'].forEach(key => {
                updateUnit(key, 0);
            });
            wrapper.dispatchEvent(new CustomEvent('countdownEnded'));
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        updateUnit('days', days);
        updateUnit('hours', hours);
        updateUnit('minutes', minutes);
        updateUnit('seconds', seconds);
    }

    // Initial update
    updateCountdown();

    // Update every second
    setInterval(updateCountdown, 1000);
})();
</script>
