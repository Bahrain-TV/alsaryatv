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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-image: url("{{ asset('images/seef-district-from-sea.jpg') }}");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: 'Tajawal', sans-serif;
            overflow: hidden;
        }

        .success-container {
            min-h-screen flex items-center justify-center px-4 py-12;
            background: linear-gradient(135deg, rgba(10, 10, 20, 0.85) 0%, rgba(30, 15, 40, 0.85) 100%);
        }

        .success-card {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(15px);
            border: 2px solid rgba(79, 70, 229, 0.3);
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5),
                        inset 0 1px 1px rgba(255, 255, 255, 0.1);
            animation: slideIn 0.7s cubic-bezier(0.34, 1.56, 0.64, 1);
            max-width: 500px;
            width: 100%;
            padding: 48px 32px;
            position: relative;
            overflow: hidden;
        }

        .success-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 20%, rgba(79, 70, 229, 0.1) 0%, transparent 50%);
            pointer-events: none;
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
            0%, 100% { filter: drop-shadow(0 0 10px rgba(79, 70, 229, 0.5)); }
            50% { filter: drop-shadow(0 0 20px rgba(79, 70, 229, 0.8)); }
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .lottie-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 24px;
        }

        .check-mark-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 32px;
            animation: pulse-glow 2s ease-in-out infinite;
        }

        .check-mark {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: bounceIn 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .check-mark svg {
            width: 100%;
            height: 100%;
            filter: drop-shadow(0 4px 12px rgba(34, 197, 94, 0.4));
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
            font-size: clamp(1.875rem, 5vw, 2.5rem);
            font-weight: 800;
            text-align: center;
            margin-bottom: 24px;
            background: linear-gradient(135deg, #fff 0%, #e0e7ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }

        .subtitle {
            font-size: 1rem;
            text-align: center;
            color: #cbd5e1;
            margin-bottom: 32px;
            line-height: 1.6;
        }

        .beyon-section {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.15) 0%, rgba(147, 51, 234, 0.15) 100%);
            border: 1px solid rgba(79, 70, 229, 0.3);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 32px;
            text-align: center;
        }

        .beyon-section p {
            color: #e2e8f0;
            margin-bottom: 16px;
            font-size: 0.95rem;
        }

        .beyon-section .app-name {
            color: #60a5fa;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .app-store-link {
            display: inline-block;
            transition: all 0.3s ease;
            transform: scale(1);
        }

        .app-store-link:hover {
            transform: scale(1.05);
        }

        .app-store-link img {
            width: 240px;
            height: auto;
            border-radius: 8px;
        }

        .stats-container {
            background: rgba(79, 70, 229, 0.1);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid rgba(79, 70, 229, 0.2);
        }

        .stat-label {
            font-size: 0.875rem;
            color: #94a3b8;
            margin-bottom: 12px;
            text-align: center;
        }

        .stat-value {
            font-size: 3rem;
            font-weight: 900;
            text-align: center;
            background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum";
            margin-bottom: 8px;
        }

        .stat-total {
            font-size: 0.875rem;
            color: #cbd5e1;
            text-align: center;
        }

        .warning-box {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(234, 88, 12, 0.15) 100%);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 32px;
            text-align: center;
        }

        .warning-box .label {
            color: #fca5a5;
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 4px;
        }

        .warning-box .text {
            color: #fecaca;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .countdown-section {
            margin-top: 32px;
            text-align: center;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 24px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.5);
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #4F46E5 0%, #9333EA 50%, #EC4899 100%);
            border-radius: 4px;
            transition: width 0.1s linear;
            box-shadow: 0 0 10px rgba(79, 70, 229, 0.5);
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
            color: #60a5fa;
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum";
            min-width: 90px;
        }

        .countdown-label {
            font-size: 1rem;
            color: #cbd5e1;
        }

        .action-button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
            text-decoration: none;
            font-family: 'Tajawal', sans-serif;
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.6);
        }

        .action-button:active {
            transform: translateY(0);
        }

        /* Rate limit countdown mode */
        .rate-limit-container {
            text-align: center;
        }

        .rate-limit-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(234, 88, 12, 0.15) 100%);
            border-radius: 50%;
            border: 2px solid rgba(239, 68, 68, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }

        .rate-limit-icon svg {
            width: 60%;
            height: 60%;
            color: #fca5a5;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .rate-limit-message {
            color: #e2e8f0;
            margin-bottom: 12px;
            font-size: 1.1rem;
        }

        .rate-limit-submessage {
            color: #cbd5e1;
            font-size: 0.95rem;
            margin-bottom: 32px;
            line-height: 1.5;
        }

        .timer-circle {
            width: 140px;
            height: 140px;
            margin: 0 auto 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: conic-gradient(#fca5a5, #fed7aa);
            padding: 4px;
            position: relative;
            animation: rotate 3s linear infinite;
        }

        .timer-circle::after {
            content: '';
            position: absolute;
            width: calc(100% - 8px);
            height: calc(100% - 8px);
            border-radius: 50%;
            background: rgba(15, 23, 42, 0.95);
            top: 4px;
            left: 4px;
        }

        .timer-content {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .timer-value {
            font-size: 2.5rem;
            font-weight: 900;
            color: #fca5a5;
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum";
        }

        .timer-label {
            font-size: 0.75rem;
            color: #cbd5e1;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @media (max-width: 640px) {
            .success-card {
                padding: 32px 20px;
            }

            h2 {
                font-size: 1.75rem;
            }

            .stat-value {
                font-size: 2.5rem;
            }

            .app-store-link img {
                width: 200px;
            }
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
                        speed="1.2" style="width: 180px; height: 180px;" loop autoplay>
                    </lottie-player>
                </div>

                <!-- Success check mark -->
                <div class="check-mark-wrapper">
                    <div class="check-mark">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: #22c55e;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>

                <h2>تم التسجيل بنجاح!</h2>

                <p class="subtitle">
                    شكراً <span class="font-bold">{{ session('name') ?: 'المشارك الكريم' }}</span> لمشاركتك في برنامج السارية
                </p>

                <!-- Beyon Money section -->
                <div class="beyon-section">
                    <p>لاستلام الجائزة يجب على جميع المشاركين تحميل تطبيق</p>
                    <p class="app-name">Beyon Money</p>
                    <a href="https://beyonmoney.onelink.me/XTfh/homepage"
                        class="app-store-link" target="_blank" rel="noopener noreferrer">
                        <img src="{{ asset('images/dl-from-app-stores.png') }}" alt="تحميل التطبيق" />
                    </a>
                </div>

                <!-- Statistics -->
                @if(session('userHits') !== null || session('totalHits') !== null)
                <div class="stats-container">
                    <div class="stat-label">عدد مرات مشاركتك</div>
                    <div class="stat-value" id="hits-counter">{{ session('userHits', 1) }}</div>
                    <div class="stat-total">من إجمالي <strong>{{ number_format(session('totalHits', 0)) }}</strong> مشارك</div>
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
                        <span class="countdown-number" id="countdown">{{ session('seconds', 30) }}</span>
                        <span class="countdown-label" id="seconds-word">ثوانٍ</span>
                    </div>

                    <a href="/" class="action-button">العودة للصفحة الرئيسية</a>
                </div>

            @else
                <!-- ===== RATE LIMIT COUNTDOWN SCREEN ===== -->

                <div class="rate-limit-container">
                    <!-- Clock icon -->
                    <div class="rate-limit-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>

                    <h2>انتظر قليلاً</h2>

                    <p class="rate-limit-message">عاد تحاول تسجيل بسرعة كثير! 😊</p>
                    <p class="rate-limit-submessage">يمكنك التسجيل مجدداً بعد انتهاء المدة المحددة أدناه</p>

                    <!-- Timer circle with animation -->
                    <div class="timer-circle" id="timer-circle">
                        <div class="timer-content">
                            <div class="timer-value" id="timer-countdown">300</div>
                            <div class="timer-label">ثانية</div>
                        </div>
                    </div>

                    <!-- Detailed countdown -->
                    <div class="countdown-section">
                        <div class="countdown-text">
                            <span class="countdown-label">الوقت المتبقي:</span>
                            <span class="countdown-number" id="countdown-detailed">5</span>
                            <span class="countdown-label" id="minutes-word">دقائق</span>
                        </div>
                    </div>

                    <!-- Help text -->
                    <p class="rate-limit-submessage" style="margin-top: 24px; color: #64748b;">
                        هذا الحد الزمني يحمي التطبيق من الاستخدام غير المسؤول
                    </p>

                    <a href="/" class="action-button" style="margin-top: 24px;">العودة للصفحة الرئيسية</a>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const isDirtyFile = @json($isDirtyFile);
            const cpr = @json($cpr);

            if (isDirtyFile) {
                // ===== SUCCESS PAGE COUNTDOWN =====
                const userHits = {{ session('userHits', 1) }};
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

                let secondsLeft = {{ session('seconds', 30) }};
                const totalSeconds = secondsLeft;
                let isRunning = true;
                
                progressBar.style.width = '100%';
                updateSecondsWord(secondsLeft);
                
                const countdownInterval = setInterval(() => {
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