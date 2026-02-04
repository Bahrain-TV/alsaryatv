@props([
    'targetDate' => '2026-02-18T00:00:00+03:00',
    'id' => 'ramadan-countdown'
])

<div id="{{ $id }}" class="countdown-wrapper" data-target="{{ $targetDate }}">
    <div class="countdown-grid">
        <!-- Seconds -->
        <div class="countdown-item">
            <div class="countdown-value-container">
                <span class="countdown-value" data-seconds>00</span>
                <div class="countdown-glow"></div>
            </div>
            <span class="countdown-label">ثانية</span>
        </div>

        <!-- Minutes -->
        <div class="countdown-item">
            <div class="countdown-value-container">
                <span class="countdown-value" data-minutes>00</span>
                <div class="countdown-glow"></div>
            </div>
            <span class="countdown-label">دقيقة</span>
        </div>

        <!-- Hours -->
        <div class="countdown-item">
            <div class="countdown-value-container">
                <span class="countdown-value" data-hours>00</span>
                <div class="countdown-glow"></div>
            </div>
            <span class="countdown-label">ساعة</span>
        </div>

        <!-- Days -->
        <div class="countdown-item">
            <div class="countdown-value-container">
                <span class="countdown-value" data-days>00</span>
                <div class="countdown-glow"></div>
            </div>
            <span class="countdown-label">يوم</span>
        </div>
    </div>
</div>

<style>
    .countdown-wrapper {
        width: 100%;
        padding: 1rem 0;
    }

    .countdown-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: clamp(0.5rem, 2vw, 1.5rem);
        max-width: 600px;
        margin: 0 auto;
        direction: ltr;
    }

    .countdown-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
    }

    .countdown-value-container {
        position: relative;
        width: clamp(60px, 18vw, 120px);
        height: clamp(70px, 20vw, 130px);
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(145deg,
            rgba(15, 23, 42, 0.9) 0%,
            rgba(30, 41, 59, 0.8) 50%,
            rgba(15, 23, 42, 0.95) 100%);
        border-radius: clamp(12px, 3vw, 20px);
        border: 1px solid rgba(212, 175, 55, 0.3);
        box-shadow:
            0 10px 40px rgba(0, 0, 0, 0.4),
            0 0 30px rgba(212, 175, 55, 0.1),
            inset 0 1px 0 rgba(255, 255, 255, 0.1),
            inset 0 -1px 0 rgba(0, 0, 0, 0.2);
        overflow: hidden;
    }

    .countdown-value-container::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg,
            transparent,
            rgba(212, 175, 55, 0.3),
            transparent);
    }

    .countdown-value-container::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 50%;
        background: linear-gradient(180deg,
            rgba(255, 255, 255, 0.08) 0%,
            transparent 100%);
        border-radius: clamp(12px, 3vw, 20px) clamp(12px, 3vw, 20px) 0 0;
    }

    .countdown-glow {
        position: absolute;
        inset: 0;
        background: radial-gradient(
            ellipse at center,
            rgba(212, 175, 55, 0.15) 0%,
            transparent 70%
        );
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .countdown-value-container:hover .countdown-glow,
    .countdown-value-container.pulse .countdown-glow {
        opacity: 1;
    }

    .countdown-value {
        font-family: 'Tajawal', system-ui, sans-serif;
        font-size: clamp(1.75rem, 6vw, 3.5rem);
        font-weight: 700;
        color: #ffffff;
        text-shadow:
            0 0 20px rgba(212, 175, 55, 0.5),
            0 2px 4px rgba(0, 0, 0, 0.3);
        position: relative;
        z-index: 1;
        line-height: 1;
        letter-spacing: 0.02em;
        transition: transform 0.15s ease, color 0.15s ease;
    }

    .countdown-value.changing {
        animation: valueChange 0.3s ease;
    }

    @keyframes valueChange {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); color: #d4af37; }
        100% { transform: scale(1); }
    }

    .countdown-label {
        font-family: 'Tajawal', sans-serif;
        font-size: clamp(0.75rem, 2.5vw, 1.1rem);
        font-weight: 600;
        color: #d4af37;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        text-shadow: 0 0 15px rgba(212, 175, 55, 0.4);
    }

    /* Separator dots between items */
    .countdown-item:not(:last-child)::after {
        content: ':';
        position: absolute;
        right: calc(-0.5rem - 1vw);
        top: 50%;
        transform: translateY(-70%);
        font-size: clamp(1.5rem, 4vw, 2.5rem);
        font-weight: bold;
        color: rgba(212, 175, 55, 0.6);
        animation: separatorPulse 1s ease-in-out infinite;
    }

    .countdown-item {
        position: relative;
    }

    @keyframes separatorPulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }

    /* Responsive adjustments */
    @media (max-width: 480px) {
        .countdown-grid {
            gap: 0.4rem;
            padding: 0 0.5rem;
        }

        .countdown-item::after {
            font-size: 1.25rem;
            right: -0.35rem;
        }

        .countdown-label {
            letter-spacing: 0.05em;
        }
    }

    @media (max-width: 360px) {
        .countdown-value-container {
            width: 55px;
            height: 65px;
            border-radius: 10px;
        }

        .countdown-value {
            font-size: 1.5rem;
        }

        .countdown-label {
            font-size: 0.65rem;
        }

        .countdown-item::after {
            display: none;
        }
    }

    /* Countdown ended state */
    .countdown-wrapper.ended .countdown-value-container {
        background: linear-gradient(145deg,
            rgba(16, 185, 129, 0.2) 0%,
            rgba(5, 150, 105, 0.15) 100%);
        border-color: rgba(16, 185, 129, 0.5);
    }

    .countdown-wrapper.ended .countdown-value {
        color: #10b981;
        text-shadow: 0 0 20px rgba(16, 185, 129, 0.5);
    }

    .countdown-wrapper.ended .countdown-label {
        color: #10b981;
    }
</style>

<script>
(function() {
    const wrapper = document.getElementById('{{ $id }}');
    if (!wrapper || wrapper.dataset.initialized) return;
    wrapper.dataset.initialized = 'true';

    const targetDate = new Date(wrapper.dataset.target).getTime();
    const daysEl = wrapper.querySelector('[data-days]');
    const hoursEl = wrapper.querySelector('[data-hours]');
    const minutesEl = wrapper.querySelector('[data-minutes]');
    const secondsEl = wrapper.querySelector('[data-seconds]');

    let lastValues = { days: -1, hours: -1, minutes: -1, seconds: -1 };

    function updateValue(element, value, key) {
        const paddedValue = String(value).padStart(2, '0');
        if (lastValues[key] !== value) {
            element.classList.add('changing');
            element.parentElement.classList.add('pulse');
            setTimeout(() => {
                element.classList.remove('changing');
                element.parentElement.classList.remove('pulse');
            }, 300);
            lastValues[key] = value;
        }
        element.textContent = paddedValue;
    }

    function updateCountdown() {
        const now = new Date().getTime();
        const distance = targetDate - now;

        if (distance < 0) {
            wrapper.classList.add('ended');
            daysEl.textContent = '00';
            hoursEl.textContent = '00';
            minutesEl.textContent = '00';
            secondsEl.textContent = '00';

            // Dispatch event for countdown end
            wrapper.dispatchEvent(new CustomEvent('countdownEnded'));
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        updateValue(daysEl, days, 'days');
        updateValue(hoursEl, hours, 'hours');
        updateValue(minutesEl, minutes, 'minutes');
        updateValue(secondsEl, seconds, 'seconds');
    }

    // Initial update
    updateCountdown();

    // Update every second
    setInterval(updateCountdown, 1000);
})();
</script>
