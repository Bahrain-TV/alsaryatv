<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AlSaryaTV') }} - {{ $isDirtyFile ? 'تم التسجيل بنجاح' : 'جاري التحضير' }}</title>
    <meta name="description" content="{{ $isDirtyFile ? 'تم تسجيل مشاركتك بنجاح' : 'يرجى الانتظار' }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=tajawal:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Third-party libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

    <style>
        :root {
            --primary-gold: #D4AF37;
            --primary-red: #A81C2E;
            --glass-bg: rgba(20, 20, 20, 0.65);
            --glass-border: rgba(212, 175, 55, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-image: url("{{ asset('images/alsarya-bg-2026-by-gemini-compressed.jpeg') }}");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            font-family: 'Tajawal', sans-serif;
            overflow-x: hidden;
            overflow-y: auto;
            position: relative;
            min-height: 100vh;
            padding: 0;
            color: #ffffff;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("{{ asset('images/alsarya-bg-2026-by-gemini-compressed.jpeg') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            z-index: -1;
        }

        .success-container {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(16px, 4vw, 32px);
            background: rgba(0, 0, 0, 0.4); /* Overlay to ensure text readability */
            padding-top: max(env(safe-area-inset-top), 16px);
            padding-bottom: max(env(safe-area-inset-bottom), 16px);
        }

        .success-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-top: 1px solid rgba(212, 175, 55, 0.5);
            border-radius: 24px;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.5),
                0 0 30px rgba(168, 28, 46, 0.2),
                inset 0 1px 1px rgba(255, 255, 255, 0.1);
            animation: slideIn 0.8s cubic-bezier(0.2, 0.8, 0.2, 1);
            width: 100%;
            max-width: min(500px, 95vw);
            padding: clamp(32px, 6vw, 48px) clamp(24px, 5vw, 36px);
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .success-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--primary-red), var(--primary-gold), var(--primary-red));
        }

        .success-card > * {
            position: relative;
            z-index: 1;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(40px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes pulse-glow {
            0%, 100% { filter: drop-shadow(0 0 10px rgba(212, 175, 55, 0.3)); }
            50% { filter: drop-shadow(0 0 20px rgba(212, 175, 55, 0.6)); }
        }

        .lottie-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 24px;
        }

        .check-mark-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 24px;
            animation: pulse-glow 3s ease-in-out infinite;
        }

        .check-mark {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            padding: 16px;
            border: 1px solid rgba(212, 175, 55, 0.2);
            animation: bounceIn 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .check-mark svg {
            width: 100%;
            height: 100%;
            color: var(--primary-gold);
            filter: drop-shadow(0 2px 8px rgba(212, 175, 55, 0.4));
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0) rotateZ(-45deg);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1) rotateZ(0deg);
                opacity: 1;
            }
        }

        h2 {
            font-size: clamp(2rem, 5vw, 2.75rem);
            font-weight: 800;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #ffffff 20%, var(--primary-gold) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }

        .subtitle {
            font-size: clamp(1rem, 2.8vw, 1.1rem);
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 32px;
            line-height: 1.6;
        }

        .subtitle .highlight {
            color: var(--primary-gold);
            font-weight: 700;
        }

        .bapco-section {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.02) 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 32px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .bapco-section:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.08);
        }

        .bapco-section p {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 12px;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .bapco-section .app-name {
            color: var(--primary-gold);
            font-weight: 700;
            font-size: 1.1rem;
        }

        .app-store-link {
            display: inline-block;
            transition: all 0.3s ease;
            margin-top: 12px;
        }

        .app-store-link:hover {
            transform: scale(1.05);
            filter: brightness(1.1);
        }

        .app-store-link img {
            height: 48px;
            width: auto;
            border-radius: 8px;
        }

        .stats-container {
            background: rgba(168, 28, 46, 0.1);
            border: 1px solid rgba(168, 28, 46, 0.3);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            text-align: center;
        }

        .stat-label {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ffffff 0%, var(--primary-gold) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-variant-numeric: tabular-nums;
            margin-bottom: 4px;
            line-height: 1;
        }

        .stat-total {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .warning-box {
            background: rgba(168, 28, 46, 0.2);
            border: 1px solid rgba(168, 28, 46, 0.4);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 32px;
            text-align: center;
        }

        .warning-box .label {
            color: #ff8a8a;
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 4px;
        }

        .warning-box .text {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .countdown-section {
            margin-top: 32px;
            text-align: center;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-gold) 0%, var(--primary-red) 100%);
            border-radius: 3px;
            transition: width 1s linear;
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.4);
        }

        .countdown-text {
            display: flex;
            align-items: baseline;
            justify-content: center;
            gap: 8px;
            margin-bottom: 24px;
        }

        .countdown-number {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--primary-gold);
            font-variant-numeric: tabular-nums;
        }

        .countdown-label {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .action-button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 280px;
            margin: 0 auto;
            padding: 16px 32px;
            background: linear-gradient(135deg, var(--primary-gold) 0%, #B8860B 100%);
            color: #000;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4);
            filter: brightness(1.1);
        }

        /* Rate limit styles */
        .rate-limit-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(168, 28, 46, 0.15);
            border-radius: 50%;
            border: 2px solid rgba(168, 28, 46, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }

        .rate-limit-icon svg {
            width: 50px;
            height: 50px;
            color: var(--primary-red);
        }

        .timer-circle {
            width: 140px;
            height: 140px;
            margin: 0 auto 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: conic-gradient(var(--primary-gold), var(--primary-red));
            padding: 4px;
            position: relative;
            animation: rotate 4s linear infinite;
        }

        .timer-circle::after {
            content: '';
            position: absolute;
            inset: 4px;
            border-radius: 50%;
            background: rgba(20, 20, 20, 0.95);
        }

        .timer-content {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 480px) {
            .success-container {
                align-items: flex-start;
                padding: 12px;
            }
            .success-card {
                padding: 24px 20px;
                border-radius: 20px;
                margin-top: 20px;
            }
            .check-mark { width: 60px; height: 60px; }
            h2 { font-size: 1.75rem; }
            .stat-value { font-size: 2.5rem; }
            .action-button { font-size: 1rem; padding: 14px 24px; }
        }
    </style>
</head>

<body class="antialiased text-white">
    <div class="success-container">
        <div class="success-card">
            @if($isDirtyFile)
                <!-- ===== SUCCESS SCREEN ===== -->

                <!-- Lottie animation -->
                <div class="lottie-wrapper">
                    <lottie-player src="{{ asset('lottie/crecent-moon-ramadan.json') }}" background="transparent"
                        speed="0.5" style="width: 160px; height: 160px;" count="1" autoplay>
                    </lottie-player>
                </div>

                <!-- Success check mark -->
                <div class="check-mark-wrapper">
                    <div class="check-mark" id="celebration-target">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>

                <h2>تم التسجيل بنجاح!</h2>

                <p class="subtitle">
                    شكراً <span class="highlight">{{ session('name', 'المشارك الكريم') }}</span> لمشاركتك في برنامج السارية
                </p>

                <!-- Bapco Energies section -->
                <div class="bapco-section">
                    <p>لاستلام الجائزة يجب على جميع المشاركين تحميل تطبيق</p>
                    <p class="app-name">Bapco Energies</p>
                    <a href="#" class="app-store-link" target="_blank" rel="noopener noreferrer">
                        <img src="{{ asset('images/dl-from-app-stores.png') }}" alt="تحميل التطبيق" />
                    </a>
                </div>

                <!-- Statistics -->
                @if(($userHits ?? null) !== null || ($totalHits ?? null) !== null)
                <div class="stats-container">
                    <div class="stat-label">عدد مرات مشاركتك</div>
                    <div class="stat-value" id="hits-counter">{{ $userHits ?? 1 }}</div>
                    <div class="stat-total">من إجمالي <strong>{{ number_format($totalHits ?? 0) }}</strong> مشارك</div>
                </div>

                <div class="warning-box">
                    <div class="label">⚠️ ملاحظة مهمة</div>
                    <div class="text">المشاركة المتكررة لا تزيد من فرصتك للفوز</div>
                </div>
                @endif

                <!-- Countdown to auto-redirect -->
                <div class="countdown-section">
                    <div class="progress-bar">
                        <div class="progress-bar-fill" id="progress"></div>
                    </div>

                    <div class="countdown-text">
                        <span class="countdown-number" id="countdown">{{ $seconds ?? 30 }}</span>
                        <span class="countdown-label" id="seconds-word">ثوانٍ</span>
                    </div>

                    <a href="/" class="action-button">العودة للصفحة الرئيسية</a>
                </div>

            @else
                <!-- ===== RATE LIMIT COUNTDOWN SCREEN ===== -->
                <!-- (Kept largely the same but with new styling classes) -->
                <div class="rate-limit-container">
                    <div class="rate-limit-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>

                    <h2>انتظر قليلاً</h2>

                    <p class="subtitle">عاد تحاول تسجيل بسرعة كثير! 😊</p>
                    <p class="subtitle" style="font-size: 0.9rem; margin-top: -20px;">يمكنك التسجيل مجدداً بعد انتهاء المدة المحددة أدناه</p>

                    <div class="timer-circle" id="timer-circle">
                        <div class="timer-content">
                            <div class="timer-value" id="timer-countdown">300</div>
                            <div class="timer-label">ثانية</div>
                        </div>
                    </div>

                    <div class="countdown-section">
                        <div class="countdown-text">
                            <span class="countdown-label">الوقت المتبقي:</span>
                            <span class="countdown-number" id="countdown-detailed">5</span>
                            <span class="countdown-label" id="minutes-word">دقائق</span>
                        </div>
                    </div>

                    <a href="/" class="action-button" style="margin-top: 24px;">العودة للصفحة الرئيسية</a>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Trigger Confetti on Load
            if (typeof fireConfetti === 'function') {
                const target = document.getElementById('celebration-target');
                if (target) {
                    setTimeout(() => {
                        fireConfetti(target, ['#D4AF37', '#A81C2E', '#FFFFFF']);
                    }, 500);
                }
            }

            const isDirtyFile = @json($isDirtyFile);
            const cpr = @json($cpr);

            if (isDirtyFile) {
                // ===== SUCCESS PAGE COUNTDOWN =====
                const userHits = {{ (int) ($userHits ?? 1) }};
                const hitsCounter = document.getElementById('hits-counter');
                const progressBar = document.getElementById('progress');
                const countdownEl = document.getElementById('countdown');
                const secondsWordEl = document.getElementById('seconds-word');
                
                // Animate hits counter
                if (hitsCounter && userHits > 0) {
                    let currentCount = Math.max(1, Math.floor(userHits * 0.1));
                    hitsCounter.textContent = currentCount;
                    
                    const duration = 1500;
                    const interval = 30;
                    const steps = duration / interval;
                    const increment = (userHits - currentCount) / steps;
                    
                    const counterInterval = setInterval(() => {
                        currentCount += increment;
                        if (currentCount >= userHits) {
                            currentCount = userHits;
                            clearInterval(counterInterval);
                        }
                        hitsCounter.textContent = Math.floor(currentCount);
                    }, interval);
                }

                // Update Arabic seconds text
                function updateSecondsWord(seconds) {
                    if (seconds === 0) {
                        secondsWordEl.textContent = "";
                    } else if (seconds === 1) {
                        secondsWordEl.textContent = " ثانية واحدة";
                    } else if (seconds === 2) {
                        secondsWordEl.textContent = " ثانيتان";
                    } else if (seconds >= 3 && seconds <= 10) {
                        secondsWordEl.textContent = " ثوانٍ";
                    } else {
                        secondsWordEl.textContent = " ثانية";
                    }
                }

                let secondsLeft = {{ (int) ($seconds ?? 30) }};
                const totalSeconds = secondsLeft;
                let isRunning = true;
                
                progressBar.style.width = '100%';
                updateSecondsWord(secondsLeft);
                
                const countdownInterval = setInterval(() => {
                    if (!isRunning) return;
                    
                    secondsLeft -= 1;
                    
                    countdownEl.textContent = Math.max(0, secondsLeft);
                    updateSecondsWord(secondsLeft);
                    
                    const percentage = (secondsLeft / totalSeconds) * 100;
                    progressBar.style.width = `${percentage}%`;
                    
                    if (secondsLeft <= 0) {
                        isRunning = false;
                        clearInterval(countdownInterval);
                        setTimeout(() => {
                            window.location.href = '/';
                        }, 500);
                    }
                }, 1000);

                // Allow manual navigation
                document.querySelector('.action-button').addEventListener('click', () => {
                    isRunning = false;
                    clearInterval(countdownInterval);
                });

            } else {
                // ===== RATE LIMIT COUNTDOWN SCREEN =====
                const timerCircle = document.getElementById('timer-circle');
                const timerCountdown = document.getElementById('timer-countdown');
                const countdownDetailed = document.getElementById('countdown-detailed');
                const minutesWord = document.getElementById('minutes-word');
                
                // Start with 5 minutes (300 seconds)
                let timeRemaining = 300;
                
                function updateDisplay() {
                    const minutes = Math.floor(timeRemaining / 60);
                    const seconds = timeRemaining % 60;
                    
                    timerCountdown.textContent = timeRemaining;
                    countdownDetailed.textContent = minutes;
                    
                    // Update minutes word based on number
                    if (minutes === 1) {
                        minutesWord.textContent = "دقيقة";
                    } else if (minutes === 2) {
                        minutesWord.textContent = "دقيقتان";
                    } else if (minutes >= 3 && minutes <= 10) {
                        minutesWord.textContent = "دقائق";
                    } else {
                        minutesWord.textContent = "دقيقة";
                    }
                }
                
                updateDisplay();
                
                const timerInterval = setInterval(() => {
                    timeRemaining--;
                    updateDisplay();
                    
                    if (timeRemaining <= 0) {
                        clearInterval(timerInterval);
                        // Optionally redirect or show that they can register
                        window.location.href = '/';
                    }
                }, 1000);

                // Allow manual navigation
                document.querySelector('.action-button').addEventListener('click', () => {
                    clearInterval(timerInterval);
                });
            }
        });
    </script>
</body>

</html>