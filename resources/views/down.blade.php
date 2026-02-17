<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="صيانة مجدولة - برنامج السارية من تلفزيون البحرين">
    <title>صيانة مجدولة - برنامج السارية</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=changa:400,600,700&display=swap" rel="stylesheet">
    <link href="https://fonts.bunny.net/css?family=tajawal:400,500,700&display=swap" rel="stylesheet">

    <!-- External Libraries (Fallback CDN) -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js" async></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg: #0c0f14;
            --panel: rgba(12, 15, 20, 0.82);
            --panel-border: rgba(255, 255, 255, 0.08);
            --ink: #f8fafc;
            --muted: rgba(248, 250, 252, 0.7);
            --accent: #ffb703;
            --accent-2: #fb8500;
            --cool: #8ecae6;
            --transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        html, body {
            width: 100%;
            height: 100%;
        }

        body {
            background-color: var(--bg);
            background-image:
                linear-gradient(120deg, rgba(12, 15, 20, 0.88), rgba(12, 15, 20, 0.65)),
                radial-gradient(circle at 20% 20%, rgba(251, 133, 0, 0.18), transparent 45%),
                radial-gradient(circle at 80% 10%, rgba(142, 202, 230, 0.16), transparent 40%);
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--ink);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Fallback background image */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("{{ asset('images/bahrain-bay.jpg') }}");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            z-index: -1;
            opacity: 0.3;
        }

        .down-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: clamp(24px, 5vw, 48px);
            position: relative;
            z-index: 1;
        }

        .down-card {
            width: min(920px, 100%);
            background: var(--panel);
            border: 1px solid var(--panel-border);
            border-radius: 28px;
            backdrop-filter: blur(14px);
            box-shadow:
                0 30px 80px rgba(0, 0, 0, 0.45),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            padding: clamp(24px, 4vw, 48px);
            display: grid;
            gap: clamp(20px, 3vw, 32px);
            animation: fadeIn 0.6s ease-out;
        }

        .down-head {
            display: flex;
            align-items: center;
            gap: clamp(16px, 3vw, 32px);
            flex-wrap: wrap;
        }

        .down-brand {
            display: flex;
            align-items: center;
            gap: clamp(12px, 2vw, 20px);
            flex: 1 1 260px;
            min-width: 0;
        }

        .lottie-wrapper {
            flex-shrink: 0;
            width: clamp(100px, 15vw, 140px);
            height: clamp(100px, 15vw, 140px);
            perspective: 1000px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .lottie-wrapper lottie-player {
            width: 100%;
            height: 100%;
        }

        /* Logo 3D Rotation Container */
        .logo-3d-container {
            width: 100%;
            height: 100%;
            perspective: 1000px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .logo-3d-rotating {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: rotate3D 8s linear infinite;
            transform-style: preserve-3d;
            position: relative;
        }

        .logo-3d-rotating img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 10px 30px rgba(255, 183, 3, 0.3));
        }

        /* Smooth 3D Rotation Animation */
        @keyframes rotate3D {
            0% {
                transform: rotateX(0deg) rotateY(0deg) rotateZ(0deg);
            }
            25% {
                transform: rotateX(20deg) rotateY(90deg) rotateZ(0deg);
            }
            50% {
                transform: rotateX(0deg) rotateY(180deg) rotateZ(20deg);
            }
            75% {
                transform: rotateX(-20deg) rotateY(270deg) rotateZ(0deg);
            }
            100% {
                transform: rotateX(0deg) rotateY(360deg) rotateZ(0deg);
            }
        }

        /* Slow rotation variant for more subtle effect */
        @keyframes rotate3D-slow {
            0% {
                transform: rotateX(0deg) rotateY(0deg) rotateZ(0deg);
            }
            20% {
                transform: rotateX(10deg) rotateY(72deg) rotateZ(0deg);
            }
            40% {
                transform: rotateX(0deg) rotateY(144deg) rotateZ(10deg);
            }
            60% {
                transform: rotateX(-10deg) rotateY(216deg) rotateZ(0deg);
            }
            80% {
                transform: rotateX(0deg) rotateY(288deg) rotateZ(-10deg);
            }
            100% {
                transform: rotateX(0deg) rotateY(360deg) rotateZ(0deg);
            }
        }

        /* Glow effect around logo */
        .logo-glow {
            position: absolute;
            width: 120%;
            height: 120%;
            border-radius: 50%;
            background: radial-gradient(circle at center, rgba(255, 183, 3, 0.15), transparent 70%);
            animation: pulseGlow 3s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes pulseGlow {
            0%, 100% {
                opacity: 0.5;
                transform: scale(1);
            }
            50% {
                opacity: 1;
                transform: scale(1.1);
            }
        }

        .down-text {
            flex: 1;
            min-width: 0;
        }

        .down-title {
            font-family: 'Changa', sans-serif;
            font-size: clamp(1.6rem, 3vw, 2.4rem);
            font-weight: 700;
            margin: 12px 0 8px 0;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }

        .down-subtitle {
            color: var(--muted);
            font-size: clamp(0.95rem, 1.5vw, 1.1rem);
            margin: 0;
        }

        .status-pill {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 183, 3, 0.12);
            border: 1px solid rgba(255, 183, 3, 0.35);
            color: var(--accent);
            font-weight: 600;
            font-size: 0.85rem;
            white-space: nowrap;
        }

        .countdown-card {
            border-radius: 20px;
            padding: clamp(16px, 2vw, 24px);
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            display: grid;
            gap: 12px;
            text-align: center;
            min-width: 200px;
        }

        .countdown-value {
            font-family: 'Changa', sans-serif;
            font-size: clamp(2.2rem, 4vw, 3.2rem);
            font-weight: 700;
            color: var(--accent);
            line-height: 1;
            font-feature-settings: "tnum";
            font-variant-numeric: tabular-nums;
        }

        .countdown-label {
            color: var(--muted);
            font-size: 0.9rem;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background-color: rgba(255, 255, 255, 0.12);
            border-radius: 3px;
            overflow: hidden;
            position: relative;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent), var(--accent-2));
            border-radius: 3px;
            transition: width 0.4s ease-out;
            width: 0%;
        }

        .down-grid {
            display: grid;
            gap: clamp(16px, 2vw, 24px);
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }

        .fun-message {
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            animation: slideIn 0.6s ease-out;
            font-size: clamp(1rem, 1.8vw, 1.25rem);
            line-height: 1.6;
            text-align: center;
            color: var(--muted);
        }

        .emoji-bounce {
            display: inline-block;
            margin-left: 8px;
            animation: bounce 0.8s ease-in-out infinite;
            font-size: 1.3em;
        }

        .hits-display {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .footer-link {
            color: var(--cool);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color var(--transition);
        }

        .footer-link:hover {
            color: var(--accent);
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-8px);
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .down-head {
                flex-direction: column;
            }

            .down-brand {
                flex: 1 1 100%;
                justify-content: center;
            }

            .countdown-card {
                min-width: 100%;
            }

            .fun-message {
                grid-column: 1 / -1;
            }

            /* Slower rotation on mobile for better performance */
            .logo-3d-rotating {
                animation: rotate3D-slow 10s linear infinite;
            }

            /* Reduce glow intensity on mobile */
            .logo-glow {
                opacity: 0.4;
            }
        }

        /* Medium screens optimization */
        @media (min-width: 769px) and (max-width: 1024px) {
            .lottie-wrapper {
                width: clamp(90px, 12vw, 130px);
                height: clamp(90px, 12vw, 130px);
            }

            /* Moderate rotation speed for tablets */
            .logo-3d-rotating {
                animation: rotate3D-slow 9s linear infinite;
            }
        }

        /* Large screens - fast rotation */
        @media (min-width: 1025px) {
            .logo-3d-rotating {
                animation: rotate3D 8s linear infinite;
            }

            .logo-glow {
                opacity: 0.7;
            }
        }

        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* High contrast support */
        @media (prefers-contrast: more) {
            .down-card {
                border-color: rgba(255, 255, 255, 0.3);
                background: rgba(12, 15, 20, 0.95);
            }

            .status-pill {
                border-color: rgba(255, 183, 3, 0.7);
            }
        }
    </style>
</head>
<body class="antialiased">
    <div class="down-shell" id="app">
        <div class="down-card">
            <!-- Header Section -->
            <div class="down-head">
                <div class="down-brand">
                    <div class="lottie-wrapper">
                        <!-- 3D Rotating Logo -->
                        <div class="logo-3d-container">
                            <div class="logo-glow"></div>
                            <div class="logo-3d-rotating">
                                @if(file_exists(public_path('images/alsarya-logo-2026-1.png')))
                                    <img
                                        src="{{ asset('images/alsarya-logo-2026-1.png') }}"
                                        alt="برنامج السارية"
                                        loading="lazy">
                                @else
                                    <img
                                        src="{{ asset('images/alsarya-logo-2026-tiny.png') }}"
                                        alt="برنامج السارية"
                                        loading="lazy">
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="down-text">
                        <div class="status-pill">صيانة مجدولة</div>
                        <h1 class="down-title">نجهز لكم تجربة أفضل</h1>
                        <p class="down-subtitle">نعمل حاليًا على تطوير وتحسين النظام لخدمتكم بشكل أفضل.</p>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="countdown-card" aria-label="حالة الخادم">
                    <div class="countdown-value" id="countdown" aria-live="polite">⏳</div>
                    <div class="countdown-label">سنعود قريبًا</div>
                    <div class="progress-bar" aria-hidden="true">
                        <div class="progress-bar-fill" id="progress"></div>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="down-grid">
                <!-- Message -->
                <div class="fun-message" id="funMessage" aria-live="polite" aria-atomic="true">
                    <span class="emoji-bounce">⚙️</span>
                    <span>نعمل على تحديثات دورية لضمان أفضل أداء للنظام.</span>
                </div>

                <!-- Hits Counter -->
                <div class="countdown-card">
                    <div class="countdown-label">شكرًا لزيارتكم</div>
                    <div class="hits-display">
                        <div class="countdown-value count-number" id="hits-counter" aria-live="polite">0</div>
                    </div>
                    <a href="/" class="footer-link">برنامج السارية - تلفزيون البحرين</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        'use strict';

        // Configuration
        const CONFIG = {
            hitCountDuration: 1500,
            hitCountInterval: 30,
            siteCheckInterval: 30000, // 30 seconds
            messages: [
                { text: 'نعمل على تحسين الأداء والاستقرار لخدمتكم بشكل أفضل.', emoji: '⚙️' },
                { text: 'جاري تطبيق التحديثات الأمنية والتقنية المجدولة.', emoji: '🔒' },
                { text: 'نقدر صبركم ونتطلع لخدمتكم قريبًا بتجربة محسّنة.', emoji: '🌟' },
            ]
        };

        /**
         * Set a random maintenance message on page load
         */
        function setRandomMessage() {
            const messageEl = document.getElementById('funMessage');
            if (!messageEl) return;

            const message = CONFIG.messages[
                Math.floor(Math.random() * CONFIG.messages.length)
            ];

            messageEl.innerHTML = `
                <span class="emoji-bounce">${message.emoji}</span>
                <span>${message.text}</span>
            `;
        }

        /**
         * Animate hit counter with smooth increment
         */
        function animateHitCounter(hits) {
            const hitsCounter = document.getElementById('hits-counter');
            if (!hitsCounter) return;

            let currentCount = 0;
            const steps = CONFIG.hitCountDuration / CONFIG.hitCountInterval;
            const increment = hits / steps;

            const counterInterval = setInterval(() => {
                currentCount += increment;
                if (currentCount >= hits) {
                    currentCount = hits;
                    clearInterval(counterInterval);
                }
                hitsCounter.textContent = Math.floor(currentCount).toLocaleString('ar-SA');
            }, CONFIG.hitCountInterval);
        }

        /**
         * Animate progress bar aesthetic effect
         */
        function animateProgressBar() {
            const progressBar = document.getElementById('progress');
            if (!progressBar) return;

            let progress = 0;
            setInterval(() => {
                progress = (progress + 1) % 100;
                progressBar.style.width = `${progress}%`;
            }, 200);
        }

        /**
         * Poll for site availability and reload when live
         */
        function pollSiteAvailability() {
            setInterval(() => {
                fetch('/', {
                    method: 'HEAD',
                    cache: 'no-cache'
                })
                    .then(response => {
                        if (response.ok || response.status === 200) {
                            window.location.reload();
                        }
                    })
                    .catch(() => {
                        // Still in maintenance, silently continue
                    });
            }, CONFIG.siteCheckInterval);
        }

        /**
         * Initialize when DOM is ready
         */
        document.addEventListener('DOMContentLoaded', () => {
            try {
                setRandomMessage();

                // Get hits from session (default to 1)
                const hits = {{ session('hits', 1) }};
                animateHitCounter(hits);
                animateProgressBar();

                // Start polling for site availability
                pollSiteAvailability();
            } catch (error) {
                console.error('Error initializing maintenance page:', error);
            }
        });

        // Fallback if DOMContentLoaded already fired (rare edge case)
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setRandomMessage();
                const hits = {{ session('hits', 1) }};
                animateHitCounter(hits);
                animateProgressBar();
                pollSiteAvailability();
            });
        } else {
            setRandomMessage();
            const hits = {{ session('hits', 1) }};
            animateHitCounter(hits);
            animateProgressBar();
            pollSiteAvailability();
        }
    </script>
</body>
</html>
