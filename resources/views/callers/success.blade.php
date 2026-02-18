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
            background-repeat: no-repeat;
            font-family: 'Tajawal', sans-serif;
            overflow-x: hidden;
            overflow-y: auto;
            position: relative;
            min-height: 100vh;
            padding: 0;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("{{ asset('images/seef-district-from-sea.jpg') }}");
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
            align-items: flex-start;
            justify-content: center;
            padding: clamp(16px, 4vw, 32px);
            background: linear-gradient(135deg, rgba(9, 12, 18, 0.9) 0%, rgba(16, 24, 40, 0.85) 100%);
            padding-top: max(env(safe-area-inset-top), 16px);
            padding-bottom: max(env(safe-area-inset-bottom), 16px);
        }

        .success-card {
            background: rgba(10, 12, 20, 0.7);
            backdrop-filter: blur(15px);
            border: 2px solid rgba(251, 191, 36, 0.35);
            border-radius: clamp(16px, 4vw, 24px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5),
                        0 0 50px rgba(251, 191, 36, 0.2),
                        inset 0 1px 1px rgba(255, 255, 255, 0.08);
            animation: slideIn 0.7s cubic-bezier(0.34, 1.56, 0.64, 1);
            width: 100%;
            max-width: min(450px, 95vw);
            padding: clamp(24px, 6vw, 40px) clamp(20px, 5vw, 28px);
            position: relative;
            overflow: hidden;
            margin-top: clamp(20px, 8vh, 60px);
        }

        .success-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 20%, rgba(251, 191, 36, 0.15) 0%, transparent 50%);
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
            0%, 100% { filter: drop-shadow(0 0 10px rgba(251, 191, 36, 0.5)); }
            50% { filter: drop-shadow(0 0 20px rgba(251, 191, 36, 0.85)); }
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
            font-size: clamp(1.5rem, 5vw, 2.5rem);
            font-weight: 800;
            text-align: center;
            margin-bottom: clamp(16px, 4vw, 24px);
            background: linear-gradient(135deg, #ffffff 0%, #fde68a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }

        .subtitle {
            font-size: clamp(0.9rem, 2.8vw, 1rem);
            text-align: center;
            color: #cbd5e1;
            margin-bottom: clamp(24px, 6vw, 32px);
            line-height: 1.6;
        }

        .bapco-section {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.12) 0%, rgba(16, 185, 129, 0.12) 100%);
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: clamp(12px, 3vw, 16px);
            padding: clamp(16px, 4vw, 24px);
            margin-bottom: clamp(24px, 6vw, 32px);
            text-align: center;
        }

        .bapco-section p {
            color: #e2e8f0;
            margin-bottom: clamp(8px, 2vw, 16px);
            font-size: clamp(0.9rem, 2.5vw, 0.95rem);
            line-height: 1.5;
        }

        .bapco-section .app-name {
            color: #fbbf24;
            font-weight: 700;
            font-size: clamp(1rem, 3vw, 1.1rem);
        }

        .app-store-link {
            display: inline-block;
            transition: all 0.3s ease;
            transform: scale(1);
            margin-top: clamp(8px, 2vw, 12px);
        }

        .app-store-link:hover {
            transform: scale(1.05);
        }

        .app-store-link img {
            width: clamp(180px, 25vw, 240px);
            height: auto;
            border-radius: clamp(6px, 1.5vw, 8px);
            max-width: 100%;
        }

        .stats-container {
            background: rgba(251, 191, 36, 0.08);
            border-radius: clamp(8px, 2vw, 12px);
            padding: clamp(16px, 4vw, 24px);
            margin-bottom: clamp(16px, 4vw, 24px);
            border: 1px solid rgba(251, 191, 36, 0.2);
            text-align: center;
        }

        .stat-label {
            font-size: clamp(0.8rem, 2.5vw, 0.875rem);
            color: #94a3b8;
            margin-bottom: clamp(8px, 2.5vw, 12px);
        }

        .stat-value {
            font-size: clamp(2rem, 8vw, 3rem);
            font-weight: 900;
            text-align: center;
            background: linear-gradient(135deg, #fbbf24 0%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum";
            margin-bottom: clamp(4px, 1.5vw, 8px);
            line-height: 1;
        }

        .stat-total {
            font-size: clamp(0.75rem, 2.5vw, 0.875rem);
            color: #cbd5e1;
            text-align: center;
            line-height: 1.4;
        }

        .warning-box {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(234, 88, 12, 0.15) 100%);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: clamp(8px, 2vw, 12px);
            padding: clamp(12px, 3vw, 16px);
            margin-bottom: clamp(24px, 6vw, 32px);
            text-align: center;
        }

        .warning-box .label {
            color: #fca5a5;
            font-weight: 700;
            font-size: clamp(0.85rem, 2.5vw, 0.95rem);
            margin-bottom: clamp(2px, 1vw, 4px);
        }

        .warning-box .text {
            color: #fecaca;
            font-size: clamp(0.8rem, 2.5vw, 0.875rem);
            line-height: 1.5;
        }

        .countdown-section {
            margin-top: clamp(24px, 6vw, 32px);
            text-align: center;
        }

        .progress-bar {
            width: 100%;
            height: clamp(6px, 2vw, 8px);
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: clamp(3px, 1vw, 4px);
            overflow: hidden;
            margin-bottom: clamp(16px, 4vw, 24px);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.5);
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #fbbf24 0%, #34d399 60%, #0ea5e9 100%);
            border-radius: clamp(3px, 1vw, 4px);
            transition: width 0.1s linear;
            box-shadow: 0 0 10px rgba(251, 191, 36, 0.5);
        }

        .countdown-text {
            display: flex;
            align-items: baseline;
            justify-content: center;
            gap: clamp(4px, 1.5vw, 8px);
            margin-bottom: clamp(16px, 4vw, 24px);
            flex-wrap: wrap;
        }

        .countdown-number {
            font-size: clamp(1.8rem, 6vw, 2.5rem);
            font-weight: 900;
            color: #fbbf24;
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum";
            min-width: clamp(60px, 15vw, 90px);
        }

        .countdown-label {
            font-size: clamp(0.85rem, 2.5vw, 1rem);
            color: #cbd5e1;
        }

        .action-button {
            display: inline-block;
            padding: clamp(12px, 3vw, 14px) clamp(24px, 6vw, 32px);
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #0f172a;
            border: none;
            border-radius: clamp(10px, 2.5vw, 12px);
            font-size: clamp(14px, 3.5vw, 16px);
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.35);
            text-decoration: none;
            font-family: 'Tajawal', sans-serif;
            text-align: center;
            min-height: 44px; /* Touch-friendly minimum */
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 280px;
            margin: 0 auto;
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(251, 191, 36, 0.45);
        }

        .action-button:active {
            transform: translateY(0);
        }

        /* Touch device optimizations */
        @media (hover: none) and (pointer: coarse) {
            .action-button {
                padding: 16px 32px;
                font-size: 16px; /* Prevents zoom on iOS */
            }

            .action-button:hover {
                transform: none;
            }

            .app-store-link {
                min-height: 44px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }

        .rate-limit-container {
            text-align: center;
        }

        .rate-limit-icon {
            width: clamp(80px, 20vw, 100px);
            height: clamp(80px, 20vw, 100px);
            margin: 0 auto clamp(16px, 4vw, 24px);
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(234, 88, 12, 0.15) 100%);
            border-radius: 50%;
            border: 2px solid rgba(239, 68, 68, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }

        .rate-limit-icon svg {
            width: clamp(50%, 15vw, 60%);
            height: clamp(50%, 15vw, 60%);
            color: #fca5a5;
        }

        .rate-limit-message {
            color: #e2e8f0;
            margin-bottom: clamp(8px, 2vw, 12px);
            font-size: clamp(1rem, 3vw, 1.1rem);
        }

        .rate-limit-submessage {
            color: #cbd5e1;
            font-size: clamp(0.9rem, 2.5vw, 0.95rem);
            margin-bottom: clamp(24px, 6vw, 32px);
            line-height: 1.5;
        }

        .timer-circle {
            width: clamp(100px, 25vw, 140px);
            height: clamp(100px, 25vw, 140px);
            margin: 0 auto clamp(16px, 4vw, 24px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: conic-gradient(#fbbf24, #f97316, #34d399);
            padding: clamp(3px, 1vw, 4px);
            position: relative;
            animation: rotate 3s linear infinite;
        }

        .timer-circle::after {
            content: '';
            position: absolute;
            width: calc(100% - clamp(6px, 2vw, 8px));
            height: calc(100% - clamp(6px, 2vw, 8px));
            border-radius: 50%;
            background: rgba(15, 23, 42, 0.95);
            top: clamp(3px, 1vw, 4px);
            left: clamp(3px, 1vw, 4px);
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
            font-size: clamp(1.8rem, 5vw, 2.5rem);
            font-weight: 900;
            color: #fbbf24;
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum";
        }

        .timer-label {
            font-size: clamp(0.6rem, 2vw, 0.75rem);
            color: #cbd5e1;
            text-transform: uppercase;
            letter-spacing: clamp(0.5px, 1.5vw, 1px);
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Enhanced Responsive Design */
        @media (max-width: 480px) {
            .success-container {
                padding: 12px;
                align-items: flex-start;
            }

            .success-card {
                margin-top: 20px;
                padding: 20px 16px;
                border-radius: 16px;
                max-width: 100%;
            }

            .lottie-wrapper lottie-player {
                width: 120px !important;
                height: 120px !important;
            }

            .check-mark {
                width: 60px;
                height: 60px;
            }

            h2 {
                font-size: 1.5rem;
                margin-bottom: 16px;
            }

            .subtitle {
                font-size: 0.9rem;
                margin-bottom: 24px;
            }

            .bapco-section {
                padding: 16px;
                margin-bottom: 24px;
            }

            .app-store-link img {
                width: 180px;
            }

            .stats-container {
                padding: 16px;
                margin-bottom: 16px;
            }

            .stat-value {
                font-size: 2rem;
            }

            .warning-box {
                padding: 12px;
                margin-bottom: 24px;
            }

            .countdown-section {
                margin-top: 24px;
            }

            .timer-circle {
                width: 100px;
                height: 100px;
            }

            .timer-value {
                font-size: 1.8rem;
            }

            .rate-limit-icon {
                width: 80px;
                height: 80px;
            }

            .rate-limit-icon svg {
                width: 50%;
                height: 50%;
            }
        }

        @media (min-width: 481px) and (max-width: 768px) {
            .success-card {
                max-width: 500px;
                padding: 32px 24px;
            }

            .lottie-wrapper lottie-player {
                width: 150px !important;
                height: 150px !important;
            }

            .check-mark {
                width: 70px;
                height: 70px;
            }

            h2 {
                font-size: 2rem;
            }

            .app-store-link img {
                width: 220px;
            }

            .stat-value {
                font-size: 2.8rem;
            }

            .timer-circle {
                width: 120px;
                height: 120px;
            }

            .timer-value {
                font-size: 2.2rem;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .success-card {
                max-width: 550px;
                padding: 36px 32px;
            }

            .lottie-wrapper lottie-player {
                width: 160px !important;
                height: 160px !important;
            }

            .check-mark {
                width: 75px;
                height: 75px;
            }

            h2 {
                font-size: 2.2rem;
            }

            .app-store-link img {
                width: 240px;
            }

            .stat-value {
                font-size: 3.2rem;
            }
        }

        @media (min-width: 1025px) {
            .success-card {
                max-width: 450px;
            }

            .lottie-wrapper lottie-player {
                width: 180px !important;
                height: 180px !important;
            }
        }

        /* Landscape orientation adjustments */
        @media (max-height: 600px) and (orientation: landscape) {
            .success-container {
                align-items: center;
                padding: 16px;
            }

            .success-card {
                margin-top: 0;
                max-height: calc(100vh - 32px);
                overflow-y: auto;
            }

            .lottie-wrapper lottie-player {
                width: 100px !important;
                height: 100px !important;
            }

            h2 {
                font-size: 1.4rem;
                margin-bottom: 12px;
            }

            .subtitle {
                font-size: 0.85rem;
                margin-bottom: 16px;
            }

            .bapco-section {
                padding: 12px;
                margin-bottom: 16px;
            }

            .stats-container {
                padding: 12px;
                margin-bottom: 12px;
            }

            .warning-box {
                padding: 10px;
                margin-bottom: 16px;
            }

            .countdown-section {
                margin-top: 16px;
            }
        }

        /* High DPI displays */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .success-card {
                border-width: 1px;
            }

            .app-store-link img {
                image-rendering: -webkit-optimize-contrast;
                image-rendering: crisp-edges;
            }
        }

        /* Reduced motion preferences */
        @media (prefers-reduced-motion: reduce) {
            .success-card {
                animation: none;
            }

            .check-mark {
                animation: none;
            }

            .timer-circle {
                animation: none;
            }

            .rate-limit-icon {
                animation: none;
            }

            * {
                transition-duration: 0.01ms !important;
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
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
                        speed="0.5" style="width: clamp(120px, 20vw, 180px); height: clamp(120px, 20vw, 180px);" count="1" autoplay>
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

                <!-- Bapco Energies section -->
                <div class="bapco-section">
                    <p>لاستلام الجائزة يجب على جميع المشاركين تحميل تطبيق</p>
                    <p class="app-name">Bapco Energies</p>
                    <a href="#" class="app-store-link" target="_blank" rel="noopener noreferrer">
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