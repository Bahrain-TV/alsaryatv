<div id="countdown-container" class="w-full py-8 countdown-timer-wrapper flex justify-center" data-target-date="{{ $targetDate ?? '2025-03-01T20:00:00' }}">
    <div class="max-w-4xl bg-gradient-to-b from-black/60 to-black/40 rounded-xl backdrop-blur-sm p-6 shadow-2xl text-center">
        @if(isset($title) && $title)
            <h2 class="text-center text-white text-2xl font-bold mb-6 font-tajawal">{{ $title }}</h2>
        @endif

        <div class="flex justify-center items-center space-x-6 rtl:space-x-reverse">
            <div class="timer-segment" data-unit="days">
                <div class="timer-value days bg-opacity-80">00</div>
                <div class="timer-label">يوم</div>
            </div>
            <div class="timer-separator text-white">:</div>
            <div class="timer-segment" data-unit="hours">
                <div class="timer-value hours bg-opacity-80">00</div>
                <div class="timer-label">ساعة</div>
            </div>
            <div class="timer-separator text-white">:</div>
            <div class="timer-segment" data-unit="minutes">
                <div class="timer-value minutes bg-opacity-80">00</div>
                <div class="timer-label">دقيقة</div>
            </div>
            <div class="timer-separator text-white">:</div>
            <div class="timer-segment" data-unit="seconds">
                <div class="timer-value seconds bg-opacity-80">00</div>
                <div class="timer-label">ثانية</div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    #countdown-container {
        font-family: "Tajawal", sans-serif;
        transition: all 0.3s ease-in-out;
    }

    #countdown-container:hover {
        transform: translateY(-5px);
    }

    .timer-segment {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .timer-value {
        font-size: 3.5rem;
        font-weight: 700;
        color: white;
        text-shadow: 0 0 15px rgba(255, 255, 255, 0.6);
        position: relative;
        padding: 0.5rem 1rem;
        background: linear-gradient(to bottom, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.8));
        border-radius: 0.5rem;
        box-shadow: 0 10px 20px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.15);
        min-width: 5rem;
        text-align: center;
        display: flex;
        justify-content: center;
        transform-style: preserve-3d;
        transform: perspective(1000px);
    }

    .timer-value::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        height: 1px;
        top: 50%;
        background: rgba(255, 255, 255, 0.2);
    }

    .timer-label {
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.8);
        margin-top: 0.75rem;
        font-weight: 500;
        text-shadow: 0 0 5px rgba(255, 255, 255, 0.4);
    }

    .timer-separator {
        font-size: 3rem;
        font-weight: bold;
        display: inline-flex;
        align-items: center;
        height: 100%;
        margin-top: -1rem;
        animation: pulse 1s infinite alternate;
    }

    @keyframes pulse {
        0% { opacity: 0.5; }
        100% { opacity: 1; }
    }

    @keyframes flipTop {
        0% { transform: rotateX(0deg); }
        100% { transform: rotateX(-90deg); }
    }

    @keyframes flipBottom {
        0% { transform: rotateX(90deg); }
        100% { transform: rotateX(0deg); }
    }

    @media (max-width: 640px) {
        .timer-value {
            font-size: 2rem;
            min-width: 3rem;
            padding: 0.3rem 0.5rem;
        }

        .timer-separator {
            font-size: 1.8rem;
        }

        .timer-label {
            font-size: 0.8rem;
        }
    }
</style>
@endpush

@push('scripts')
<!-- Countdown script moved to external file -->
<script src="{{ asset('js/countdown.js') }}"></script>
@endpush
