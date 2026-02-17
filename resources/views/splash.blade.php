<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AlSaryaTV') }}</title>
    <meta name="description" content="برنامج السارية - مسابقة رمضانية">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=tajawal:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Lottie Animation -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 25%, #2d1b69 50%, #1e1b4b 75%, #0f172a 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Animated background elements */
        .bg-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
            z-index: -1;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.3;
        }

        .orb-1 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, #A81C2E 0%, rgba(168, 28, 46, 0) 70%);
            top: -100px;
            left: -100px;
            animation: float 20s ease-in-out infinite;
        }

        .orb-2 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, #E8D7C3 0%, rgba(232, 215, 195, 0) 70%);
            bottom: -150px;
            right: -150px;
            animation: float 25s ease-in-out infinite reverse;
        }

        .orb-3 {
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, #7A1422 0%, rgba(122, 20, 34, 0) 70%);
            top: 50%;
            right: 10%;
            animation: float 22s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            33% { transform: translate(30px, -30px); }
            66% { transform: translate(-20px, 30px); }
        }

        /* Container */
        .splash-container {
            position: relative;
            z-index: 10;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2rem;
            max-width: 600px;
            width: 100%;
            padding: 2rem;
        }

        /* Logo wrapper with animation */
        .logo-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            perspective: 1200px;
            width: 320px;
            height: 320px;
            animation: slideInDown 1s cubic-bezier(0.34, 1.56, 0.64, 1) 2s backwards;
        }

        .logo-flip-card {
            position: relative;
            width: 100%;
            height: 100%;
            transition: transform 0.6s;
            transform-style: preserve-3d;
        }

        .logo-wrapper.flip-active .logo-flip-card {
            transform: rotateY(180deg);
        }

        .logo-face {
            position: absolute;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            backface-visibility: hidden;
        }

        .logo-face.front {
            z-index: 2;
        }

        .logo-face.back {
            transform: rotateY(180deg);
            z-index: 1;
        }

        .logo-glow {
            position: absolute;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(168, 28, 46, 0.4) 0%, rgba(232, 215, 195, 0.3) 50%, transparent 70%);
            animation: pulse-glow 3s ease-in-out infinite;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
        }

        .logo-glow.front {
            animation: pulse-glow 3s ease-in-out infinite;
        }

        .logo-glow.back {
            animation: pulse-glow 3s ease-in-out infinite, glow-expand 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) 4s forwards;
        }

        @keyframes pulse-glow {
            0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.3; }
            50% { transform: translate(-50%, -50%) scale(1.1); opacity: 0.5; }
        }

        @keyframes glow-expand {
            0% { transform: translate(-50%, -50%) scale(1); }
            100% { transform: translate(-50%, -50%) scale(1.3); opacity: 0.6; }
        }

        .logo-image {
            position: relative;
            z-index: 2;
            width: 280px;
            height: auto;
            filter: drop-shadow(0 20px 40px rgba(168, 28, 46, 0.5))
                    drop-shadow(0 0 30px rgba(232, 215, 195, 0.4));
            animation: bounce 3s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
        }

        .logo-image.back-image {
            animation: none;
            filter: drop-shadow(0 20px 40px rgba(168, 28, 46, 0.7))
                    drop-shadow(0 0 40px rgba(232, 215, 195, 0.6));
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        /* Logo flip animation for brand transition */
        @keyframes flipCard {
            0% {
                transform: rotateY(0deg);
            }
            50% {
                transform: rotateY(90deg);
            }
            100% {
                transform: rotateY(180deg);
            }
        }

        @keyframes fadeOutFront {
            0% { opacity: 1; }
            50% { opacity: 0; }
            100% { opacity: 0; }
        }

        @keyframes fadeInBack {
            0% { opacity: 0; }
            50% { opacity: 0; }
            100% { opacity: 1; }
        }

        @keyframes expandGlow {
            0% { transform: translate(-50%, -50%) scale(1); opacity: 0.3; }
            50% { transform: translate(-50%, -50%) scale(1.1); opacity: 0.5; }
            100% { transform: translate(-50%, -50%) scale(1.5); opacity: 0.8; }
        }

        .logo-wrapper.flip-animation .logo-flip-card {
            animation: flipCard 1.5s ease-in-out 0s forwards !important;
        }

        .logo-wrapper.flip-animation .logo-glow.front {
            animation: fadeOutFront 1.5s ease-in-out 0s forwards !important;
        }

        .logo-wrapper.flip-animation .logo-glow.back {
            animation: expandGlow 1.5s ease-in-out 0s forwards !important;
        }

        .logo-wrapper.flip-animation .logo-image.front-image {
            animation: fadeOutFront 1.5s ease-in-out 0s forwards !important;
        }

        .logo-wrapper.flip-animation .logo-image.back-image {
            animation: fadeInBack 1.5s ease-in-out 0s forwards !important;
        }

        /* Text content */
        .splash-content {
            animation: slideInUp 1s cubic-bezier(0.34, 1.56, 0.64, 1) 2.2s backwards;
        }

        .splash-title {
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 800;
            background: linear-gradient(135deg, #E8D7C3 0%, #F5DEB3 50%, #A81C2E 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .splash-subtitle {
            font-size: clamp(1rem, 3vw, 1.25rem);
            color: #cbd5e1;
            font-weight: 500;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .splash-description {
            font-size: 0.95rem;
            color: #94a3b8;
            margin-bottom: 2rem;
            line-height: 1.8;
            max-width: 400px;
        }

        /* Loading animation */
        .loading-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            animation: slideInUp 1s cubic-bezier(0.34, 1.56, 0.64, 1) 2.4s backwards;
        }

        .loading-bar {
            width: 200px;
            height: 4px;
            background: rgba(232, 215, 195, 0.2);
            border-radius: 2px;
            overflow: hidden;
            box-shadow: inset 0 0 10px rgba(168, 28, 46, 0.2);
        }

        .loading-progress {
            height: 100%;
            background: linear-gradient(90deg, #A81C2E 0%, #E8D7C3 50%, #F5DEB3 100%);
            border-radius: 2px;
            width: 0%;
            animation: loading 9s ease-in-out forwards;
            box-shadow: 0 0 10px rgba(168, 28, 46, 0.9);
        }

        @keyframes loading {
            0% { width: 0%; }
            80% { width: 80%; }
            100% { width: 100%; }
        }

        .loading-text {
            font-size: 0.85rem;
            color: #94a3b8;
            font-weight: 500;
            letter-spacing: 1px;
        }

        /* Animated text */
        .dot {
            display: inline-block;
            animation: blink 1.4s infinite;
        }

        .dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes blink {
            0%, 60%, 100% { opacity: 0.3; }
            30% { opacity: 1; }
        }

        /* Slide animations */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Star decorations */
        .stars {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .star {
            position: absolute;
            width: 2px;
            height: 2px;
            background: white;
            border-radius: 50%;
            opacity: 0;
            animation: twinkle 3s infinite;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0; }
            50% { opacity: 0.8; }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .logo-image {
                width: 180px;
            }

            .logo-glow {
                width: 220px;
                height: 220px;
            }

            .splash-title {
                font-size: 2.2rem;
            }

            .splash-subtitle {
                font-size: 1.05rem;
            }

            .splash-container {
                gap: 1.25rem;
                padding: 1.25rem;
            }

            .splash-description {
                font-size: 0.95rem;
                margin-bottom: 1.25rem;
                max-width: 320px;
            }

            .loading-bar {
                width: 160px;
            }

            .loading-text {
                font-size: 0.8rem;
            }

            .orb-1 { width: 300px; height: 300px; }
            .orb-2 { width: 350px; height: 350px; }
            .orb-3 { width: 250px; height: 250px; }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            body {
                background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 25%, #2d1b69 50%, #1e1b4b 75%, #0f172a 100%);
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
    </style>
</head>
<body>
    <!-- Background animated elements -->
    <div class="bg-elements">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <!-- Starfield -->
    <div class="stars" id="starfield"></div>

    <!-- Main splash screen -->
    <div class="splash-container">
        <!-- Logo with glow - Animated flip -->
        <div class="logo-wrapper">
            <div class="logo-flip-card">
                <!-- Front face -->
                <div class="logo-face front">
                    <div class="logo-glow front"></div>
                    @if(file_exists(public_path('images/branding/logo.png')))
                        <img src="{{ asset('images/branding/logo.png') }}" alt="برنامج السارية" class="logo-image front-image" title="Al-Sarya TV Show">
                    @else
                        <img src="{{ asset('images/branding/logo.png') }}" alt="برنامج السارية" class="logo-image front-image" title="Al-Sarya TV Show">
                    @endif
                </div>
                <!-- Back face -->
                <div class="logo-face back">
                    <div class="logo-glow back"></div>
                    @if(file_exists(public_path('images/branding/logo.png')))
                        <img src="{{ asset('images/branding/logo.png') }}" alt="برنامج السارية" class="logo-image back-image" title="Al-Sarya TV Show">
                    @else
                        <img src="{{ asset('images/branding/logo.png') }}" alt="برنامج السارية" class="logo-image back-image" title="Al-Sarya TV Show">
                    @endif
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="splash-content">
            <h1 class="splash-title">برنامج السارية</h1>
            <p class="splash-subtitle">مسابقة رمضانية حصرية</p>
            <p class="splash-description">
                انضم إلينا في رحلة مثيرة مليئة بالجوائز والمفاجآت الرائعة
            </p>
        </div>

        <!-- Loading section -->
        <div class="loading-section">
            <div class="loading-bar">
                <div class="loading-progress"></div>
            </div>
            <p class="loading-text">
                جاري التحضير<span class="dot">.</span><span class="dot">.</span><span class="dot">.</span>
            </p>
        </div>
    </div>

    <script>
        // Dark mode detection with 1-second processing time
        function detectAndApplyTheme() {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const prefersLight = window.matchMedia('(prefers-color-scheme: light)').matches;

            // Set CSS variables for theme detection (used throughout the app)
            document.documentElement.style.setProperty('--user-theme', prefersDark ? 'dark' : 'light');
            document.documentElement.style.setProperty('--theme-is-dark', prefersDark ? '1' : '0');

            // Apply theme-specific class for accessibility
            if (prefersDark) {
                document.documentElement.classList.add('dark-mode-detected');
            } else if (prefersLight) {
                document.documentElement.classList.add('light-mode-detected');
            }

            // Log for debugging
            console.log('🎨 Theme detected:', prefersDark ? 'Dark' : 'Light');
        }

        // Initialize theme detection immediately (allows 1 second for processing)
        detectAndApplyTheme();

        // Listen for real-time theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', detectAndApplyTheme);

        // Generate random stars
        function generateStars() {
            const starfield = document.getElementById('starfield');
            const starCount = window.innerWidth > 768 ? 50 : 20;

            for (let i = 0; i < starCount; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                star.style.left = Math.random() * 100 + '%';
                star.style.top = Math.random() * 100 + '%';
                star.style.animationDelay = Math.random() * 3 + 's';
                starfield.appendChild(star);
            }
        }

        // Initialize stars on load
        generateStars();

        // Trigger logo flip animation after loading completes
        function triggerLogoFlip() {
            const logoWrapper = document.querySelector('.logo-wrapper');
            if (logoWrapper) {
                logoWrapper.classList.add('flip-animation');
            }
        }

        // Auto-redirect after loading and animation completes
        window.addEventListener('load', () => {
            // Trigger flip at 4 seconds (after initial logo display)
            setTimeout(() => {
                triggerLogoFlip();
            }, 4000);

            // Redirect after full animation: slide-in (2s) + first logo (2s) + flip (1.5s) + second logo (4s) = 9.5s
            setTimeout(() => {
                window.location.href = '/';
            }, 9500);
        });

        // Allow escape key or click to skip
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                window.location.href = '/';
            }
        });

        document.addEventListener('click', () => {
            window.location.href = '/';
        });

        // Handle visibility change (tab switching)
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                // When tab becomes visible again, redirect
                window.location.href = '/';
            }
        });
    </script>
</body>
</html>
