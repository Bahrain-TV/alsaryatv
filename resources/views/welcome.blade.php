<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="description"
        content="{{ config('app.ar_translations.description') ?? 'البرنامج الرئيسي المباشر على شاشة تلفزيون البحرين خلال شهر رمضان المبارك.' }}" />
    <meta name="theme-color" content="#ffffff" />
    <meta property="og:title" content="{{ config('app.ar_translations.title') ?? 'السارية' }}" />
    <meta property="og:description"
        content="{{ config('app.ar_translations.description') ?? 'البرنامج الرئيسي المباشر على شاشة تلفزيون البحرين خلال شهر رمضان المبارك.' }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url('/') }}" />
    <meta property="og:image" content="{{ asset('images/og-banner.jpg') }}" />
    <meta name="twitter:card" content="summary_large_image" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon/favicon.ico') }}" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/favicon-32x32.png') }}" />
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon/favicon-16x16.png') }}" />

    <title>{{ config('app.ar_translations.title') ?? 'السارية' }} - قريباً في رمضان</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=tajawal:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- FlipDown Countdown Timer -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flipdown@0.3.2/dist/flipdown.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/flipdown@0.3.2/dist/flipdown.min.js"></script>

    <!-- Additional styles for flip cards and particles -->
    <style>
        /* Light mode body override */
        body:not(.dark-mode) {
            background: linear-gradient(135deg,
                oklch(0.99 0.02 85) 0%,
                oklch(0.98 0.03 75) 25%,
                oklch(0.97 0.04 65) 50%,
                oklch(0.96 0.03 55) 75%,
                oklch(0.98 0.02 45) 100%);
            animation: gentle-float 20s ease-in-out infinite;
        }

        @keyframes gentle-float {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        /* Enhanced Typography Gradients for Light Mode */
        .gold-text {
            background: linear-gradient(135deg,
                oklch(0.65 0.18 70.0804) 0%,
                oklch(0.7 0.15 75.0804) 30%,
                oklch(0.75 0.12 80.0804) 60%,
                oklch(0.65 0.18 70.0804) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0px 2px 15px oklch(0.65 0.18 70.0804 / 0.2);
            filter: drop-shadow(0 0 10px oklch(0.65 0.18 70.0804 / 0.1));
        }

        /* Dark Glassmorphism — seamlessly blends with dark background */
        .glass-panel {
            background: rgba(10, 14, 26, 0.76);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(197, 157, 95, 0.12);
            box-shadow:
                0 32px 64px -16px rgba(0, 0, 0, 0.6),
                0 0 0 1px rgba(197, 157, 95, 0.05) inset;
        }

        .input-gradient-border {
            background: linear-gradient(rgba(255,255,255,0.95), rgba(255,255,255,0.95)) padding-box,
                        linear-gradient(135deg,
                            oklch(0.65 0.18 70.0804 / 0.3),
                            oklch(0.75 0.12 155.0 / 0.2),
                            oklch(0.65 0.18 70.0804 / 0.3)) border-box;
            border: 2px solid transparent;
            border-radius: 12px;
        }

        /* Elegant Pattern for Light Mode */
        .pattern-overlay {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23C59D5F' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.6;
        }

        /* Enhanced 3D Transform Utilities */
        .perspective-1000 { perspective: 1000px; }
        .transform-style-3d { transform-style: preserve-3d; }
        .backface-hidden {
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }
        .face-front { transform: rotateY(0deg); }
        .face-back { transform: rotateY(180deg); }

        /* Beautiful Background layers for Light Mode */
        .background-layers {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .background-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .light-mode-bg {
            background:
                radial-gradient(circle at 20% 80%, oklch(0.75 0.12 155.0 / 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, oklch(0.65 0.18 70.0804 / 0.08) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, oklch(0.7 0.15 75.0804 / 0.06) 0%, transparent 50%);
        }

        /* Enhanced button styles for light mode */
        .btn-primary {
            background: linear-gradient(135deg,
                oklch(0.65 0.18 70.0804) 0%,
                oklch(0.7 0.15 75.0804) 50%,
                oklch(0.65 0.18 70.0804) 100%);
            border: none;
            color: white;
            box-shadow:
                0 4px 15px oklch(0.65 0.18 70.0804 / 0.3),
                0 2px 8px oklch(0.65 0.18 70.0804 / 0.2);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow:
                0 8px 25px oklch(0.65 0.18 70.0804 / 0.4),
                0 4px 15px oklch(0.65 0.18 70.0804 / 0.3);
        }

        /* Enhanced form styling */
        .form-input {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .form-input:focus {
            border-color: oklch(0.65 0.18 70.0804);
            box-shadow: 0 0 0 3px oklch(0.65 0.18 70.0804 / 0.1);
            background: rgba(255, 255, 255, 0.95);
        }

        /* Ramadan crescent moon effect */
        .moon-glow {
            position: absolute;
            top: 10%;
            right: 10%;
            width: 80px;
            height: 80px;
            background: radial-gradient(circle,
                oklch(0.9 0.05 70) 0%,
                oklch(0.8 0.08 75) 30%,
                transparent 70%);
            border-radius: 50%;
            opacity: 0.6;
            animation: moon-pulse 4s ease-in-out infinite;
        }

        @keyframes moon-pulse {
            0%, 100% { opacity: 0.4; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.1); }
        }

        .spinning-circle {
            position: absolute;
            border-radius: 50%;
            border: 1px solid;
            pointer-events: none;
        }

        .circle-1 {
            top: -20%;
            left: -10%;
            width: 800px;
            height: 800px;
            border-color: rgba(255, 255, 255, 0.05);
            opacity: 0.2;
            animation: spin-slow 60s linear infinite;
        }

        .circle-2 {
            top: 10%;
            right: -10%;
            width: 600px;
            height: 600px;
            border-color: rgba(197, 157, 95, 0.1);
            opacity: 0.2;
            animation: spin-slow 60s linear infinite reverse;
        }

        .pattern-overlay-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.3;
        }

        .gradient-overlay {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            height: 400px;
            background: linear-gradient(to bottom, rgba(168, 28, 46, 0.12) 0%, transparent 100%);
        }

        @keyframes spin-slow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Animation classes */
        .gsap-entry, .gsap-card, .gsap-item {
            opacity: 0;
            transform: translateY(30px);
        }

        /* ── Basmala — fixed at top of every page (CLAUDE.md requirement) ── */
        #basmala {
            position: fixed;
            top: 18px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 200;
            font-family: 'Tajawal', sans-serif;
            font-size: 0.85rem;
            font-weight: 600;
            color: #F5DEB3;
            text-shadow: 0 0 14px rgba(168, 28, 46, 0.7), 0 1px 3px rgba(0,0,0,0.9);
            letter-spacing: 0.05em;
            pointer-events: none;
            white-space: nowrap;
        }
        @media (max-width: 480px) {
            #basmala { font-size: 0.72rem; top: 12px; }
        }

        /* ── Mobile performance: disable heavy decorations ── */
        @media (max-width: 767px) {
            .spinning-circle { display: none !important; }
            .lottie-background { display: none !important; }
            .moon-glow { display: none !important; }
            #threejs-bg { display: none !important; }
            .background-layers { display: none !important; }
        }

        /* ── MOBILE RESPONSIVENESS ── */
        @media (max-width: 767px) {
            /* Disable all background animations for mobile */
            body:not(.dark-mode) {
                background: linear-gradient(135deg,
                    oklch(0.97 0.02 85) 0%,
                    oklch(0.96 0.02 75) 50%,
                    oklch(0.95 0.02 65) 100%) !important;
                animation: none !important;
            }

            /* Main content wrapper - less padding, full width */
            #main-content {
                padding: 0.75rem 0.5rem !important;
                min-height: 100vh;
            }

            /* Theme toggle - smaller and closer to edge */
            #theme-toggle {
                top: 8px !important;
                right: 8px !important;
                width: 36px !important;
                height: 36px !important;
            }
            #theme-toggle svg {
                width: 18px !important;
                height: 18px !important;
            }

            /* Header - smaller text */
            header h1 {
                font-size: 1.5rem !important;
                line-height: 1.2 !important;
                margin-bottom: 0.25rem !important;
            }
            header p {
                font-size: 0.7rem !important;
            }
            header .h-\[1px\] {
                width: 6px !important;
            }

            /* Logo - smaller on mobile */
            .logo-section img {
                width: 120px !important;
                margin-bottom: 0.5rem !important;
            }

            /* Glass panel - less padding, full width */
            .glass-panel {
                padding: 0.75rem !important;
                border-radius: 1rem !important;
            }

            /* Registration announcement panel */
            .gsap-item.relative.overflow-hidden {
                padding: 0.5rem !important;
                margin-bottom: 0.5rem !important;
            }
            .gsap-item.relative.overflow-hidden h2 {
                font-size: 0.9rem !important;
            }
            .gsap-item.relative.overflow-hidden .gold-text {
                font-size: 1.1rem !important;
            }
            .gsap-item.relative.overflow-hidden p {
                font-size: 0.6rem !important;
            }

            /* Tab toggle - smaller */
            .flex.bg-black\/40 {
                padding: 0.25rem !important;
                margin-bottom: 0.5rem !important;
                border-radius: 0.75rem !important;
            }
            .flex.bg-black\/40 button {
                padding: 0.45rem 0.25rem !important;
                font-size: 0.65rem !important;
            }
            .flex.bg-black\/40 button svg {
                width: 11px !important;
                height: 11px !important;
            }
            #tab-bg {
                left: 2px !important;
            }

            /* Form fields - compact */
            .flip-scene {
                height: 64px !important;
                margin-bottom: 0.4rem !important;
            }
            .form-input {
                padding: 0.45rem 0.5rem !important;
                font-size: 0.8rem !important;
                border-radius: 0.5rem !important;
            }
            .form-input::placeholder {
                font-size: 0.7rem !important;
            }
            label {
                font-size: 0.55rem !important;
                padding-right: 0.125rem !important;
            }

            /* Submit button */
            .flip-scene.h-\[48px\] {
                height: 40px !important;
            }
            .btn-primary span {
                font-size: 0.75rem !important;
            }
            .btn-primary svg {
                width: 12px !important;
                height: 12px !important;
            }

            /* Ramadan info panel */
            .gsap-item.mt-4.mx-2 {
                margin-top: 0.5rem !important;
                margin-left: 0.5rem !important;
                margin-right: 0.5rem !important;
            }
            .gsap-item.mt-4.mx-2 h4 {
                font-size: 0.6rem !important;
                margin-bottom: 0.2rem !important;
            }
            .gsap-item.mt-4.mx-2 .text-\[10px\] {
                font-size: 0.5rem !important;
            }

            /* Footer */
            footer {
                margin-top: 0.75rem !important;
                padding-bottom: 0.5rem !important;
            }
            footer p {
                font-size: 5px !important;
            }
            footer a {
                font-size: 5px !important;
            }
            footer .inline-flex {
                padding: 0.2rem 0.4rem !important;
            }
            footer .inline-flex span {
                font-size: 5px !important;
            }

            /* Disable GSAP animations on mobile - use CSS transitions instead */
            .gsap-entry,
            .gsap-card,
            .gsap-item {
                opacity: 1 !important;
                transform: none !important;
                animation: none !important;
            }

            /* Disable flip animations on mobile */
            .flip-card {
                transition: none !important;
            }

            /* Disable preloader animations on mobile */
            .preloader-logo {
                animation: none !important;
            }
            .preloader-ring,
            .preloader-ring-2 {
                animation: none !important;
            }
            .particle {
                animation: none !important;
            }
        }

        /* Extra small phones */
        @media (max-width: 380px) {
            #main-content {
                padding: 0.5rem 0.5rem !important;
            }
            header h1 {
                font-size: 1.5rem !important;
            }
            header p {
                font-size: 0.65rem !important;
            }
            .glass-panel {
                padding: 0.5rem !important;
            }
            .flip-scene {
                height: 64px !important;
            }
            .form-input {
                padding: 0.45rem 0.5rem !important;
                font-size: 0.8rem !important;
            }
            label {
                font-size: 0.55rem !important;
            }
            .flex.bg-black\/40 button {
                font-size: 0.65rem !important;
                gap: 2px !important;
            }
            .flex.bg-black\/40 button svg {
                width: 10px !important;
                height: 10px !important;
            }
        }

        /* Respect reduced-motion preference */
        @media (prefers-reduced-motion: reduce) {
            .spinning-circle { animation: none !important; }
            .moon-glow { animation: none !important; }
            body:not(.dark-mode) { animation: none !important; }
        }
    </style>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body dir="rtl">
    <!-- Basmala — fixed at top per project requirement -->
    <div id="basmala">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ</div>

    <!-- Background Layers -->
    <div class="background-layers">
        <!-- Three.js cinematic particle canvas (renders below all other layers) -->
        <canvas id="threejs-bg" style="position:absolute;top:0;left:0;width:100%;height:100%;z-index:0;pointer-events:none;"></canvas>

        <!-- Light Mode Beautiful Background -->
        <div class="background-layer light-mode-bg" style="z-index:1;"></div>

        <!-- Ramadan Moon Glow Effect -->
        <div class="moon-glow"></div>

        <!-- Animated Elements -->
        <div class="background-layer">
            <div class="spinning-circle circle-1"></div>
        </div>
        <div class="background-layer">
            <div class="spinning-circle circle-2"></div>
        </div>

        <!-- Pattern and Gradient Overlays -->
        <div class="background-layer pattern-overlay-bg"></div>
        <div class="background-layer gradient-overlay"></div>
    </div>

    <!-- ==================== PRELOADER ==================== -->
    <div class="preloader" id="preloader">
        <div class="preloader-stars" id="preloaderStars"></div>
        <div class="preloader-content">
            <div class="preloader-logo-container">
                <div class="preloader-particles">
                    <div class="particle"></div>
                    <div class="particle"></div>
                    <div class="particle"></div>
                    <div class="particle"></div>
                    <div class="particle"></div>
                    <div class="particle"></div>
                    <div class="particle"></div>
                    <div class="particle"></div>
                </div>
                <div class="preloader-ring"></div>
                <div class="preloader-ring-2"></div>
                <img src="{{ asset('images/alsarya-logo-2026-1.png') }}" alt="AlSarya TV" class="preloader-logo" />
            </div>
            <div class="preloader-text">جاري التحميل</div>
        </div>
    </div>
    <!-- Ramadan decorative background (CSS-only, replaces heavy Lottie player) -->
    <div class="lottie-background" aria-hidden="true"></div>

    <!-- Main Content Wrapper -->
    <div id="main-content" class="relative z-10 container mx-auto px-3 sm:px-4 py-4 sm:py-8 min-h-screen flex flex-col justify-center items-center">
        <!-- Theme Toggle Button -->
        <button id="theme-toggle" class="fixed top-4 sm:top-6 right-4 sm:right-6 z-50 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-white/20 backdrop-blur-md border border-black/10 hover:bg-white/30 dark:bg-black/20 dark:border-white/10 dark:hover:bg-black/30 transition-all duration-300 flex items-center justify-center group shadow-lg" onclick="window.ThemeManager?.toggleTheme()" title="تبديل السمة">
            <svg id="theme-icon-sun" class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-400 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <svg id="theme-icon-moon" class="w-5 h-5 sm:w-6 sm:h-6 text-blue-400 transition-opacity duration-300 absolute inset-0 m-auto opacity-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
            </svg>
        </button>

        <!-- Logo -->
        <div class="logo-section gsap-entry mb-4 sm:mb-6">
            <img src="{{ asset('images/alsarya-logo-2026-1.png') }}" alt="السارية" class="mx-auto w-40 sm:w-56 md:w-64 lg:w-72 h-auto" />
        </div>

        <!-- Header Section (Animates as one block) -->
        <header class="gsap-entry text-center mb-4 sm:mb-8 relative w-full max-w-lg">
            <h1 class="text-3xl sm:text-5xl md:text-6xl font-black mb-1 sm:mb-2 tracking-tight gold-text drop-shadow-2xl leading-tight pb-1 sm:pb-2">
                برنامج السارية
            </h1>
            <div class="flex items-center justify-center gap-2 sm:gap-3 opacity-90">
                <div class="h-[1px] w-8 sm:w-12 bg-gradient-to-l from-transparent to-gold-500/50"></div>
                <p class="text-gray-300 text-xs sm:text-base md:text-lg font-light tracking-wide">على شاشة تلفزيون البحرين</p>
                <div class="h-[1px] w-8 sm:w-12 bg-gradient-to-r from-transparent to-gold-500/50"></div>
            </div>
        </header>

        <!-- Main Glass Card Container (Animates in separately) -->
        <main class="w-full max-w-lg relative group">
            <!-- Glow effect -->
            <div class="absolute -inset-1 bg-gradient-to-r from-bahrain-red to-gold-700 rounded-[2.2rem] blur opacity-25 group-hover:opacity-40 transition duration-1000"></div>

            <div class="gsap-card glass-panel rounded-[2rem] p-4 sm:p-6 md:p-8 relative overflow-hidden">
                    @if(config('alsarya.registration.enabled', false) || auth()->check())
                        {{-- Registration is enabled - show registration form --}}
                        <div class="gsap-item relative overflow-hidden rounded-2xl mb-4 sm:mb-8 border border-white/5">
                            <div class="absolute inset-0 bg-gradient-to-r from-bahrain-dark to-[#4a0808] opacity-80"></div>
                            <div class="absolute -right-6 -top-6 w-24 h-24 bg-gold-500/20 rounded-full blur-2xl"></div>

                            <div class="relative p-3 sm:p-5 text-center">
                                <h2 class="text-lg sm:text-2xl font-bold text-white mb-1 flex items-center justify-center gap-2 sm:gap-3">
                                    <span class="gold-text text-2xl sm:text-3xl drop-shadow-lg">☪</span>
                                    <span class="bg-clip-text text-transparent bg-gradient-to-b from-white to-gray-300">رمضان كريم!</span>
                                </h2>
                                <p class="text-white/70 text-xs sm:text-sm font-light">التسجيل مفتوح الآن - سجّل للمشاركة في المسابقة</p>
                            </div>
                        </div>

                        {{-- Registration Form for Logged-in Users --}}
                        <div class="gsap-item">
                            {{-- Registration Type Toggle --}}
                            @if(env('ENABLE_FAMILY_REGISTRATION', false))
                            <div class="flex bg-black/40 p-1 sm:p-1.5 rounded-2xl mb-4 sm:mb-8 border border-white/5 relative">
                                <!-- Start at Left (roughly 4px) to align with "Individual" (Left in RTL layout) -->
                                <div id="tab-bg" class="w-1/2 h-full absolute top-0 bottom-0 rounded-xl bg-gradient-to-br from-bahrain-red to-bahrain-dark border border-white/10 shadow-lg left-[4px]">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent rounded-xl"></div>
                                </div>

                                <!-- Family (Visually Right in RTL) -->
                                <button onclick="switchTab('family')" id="tab-family" class="flex-1 relative z-10 py-2 sm:py-3 text-xs sm:text-sm font-bold text-gray-400 flex justify-center items-center gap-1 sm:gap-2 transition-colors duration-300">
                                    <i data-lucide="users" class="w-3 h-3 sm:w-4 sm:h-4"></i>
                                    <span class="truncate">تسجيل عائلي</span>
                                </button>

                                <!-- Individual (Visually Left in RTL) -->
                                <button onclick="switchTab('individual')" id="tab-individual" class="flex-1 relative z-10 py-2 sm:py-3 text-xs sm:text-sm font-bold text-white flex justify-center items-center gap-1 sm:gap-2 transition-colors duration-300">
                                    <i data-lucide="user" class="w-3 h-3 sm:w-4 sm:h-4"></i>
                                    <span class="truncate">تسجيل فردي</span>
                                </button>
                            </div>
                            @endif

                            <form method="POST" action="{{ route('callers.store') }}" dir="rtl">
                                @csrf

                                {{-- Hidden field to track registration type --}}
                                <input type="hidden" id="registration_type" name="registration_type" value="individual">

                                {{-- Name --}}
                                <div class="gsap-item flip-scene perspective-1000 h-[72px] sm:h-[86px] mb-3 sm:mb-5">
                                    <div class="flip-card w-full h-full relative transform-style-3d" id="field-1">
                                        <!-- Front -->
                                        <div class="face-front absolute inset-0 backface-hidden">
                                            <div class="space-y-1 sm:space-y-2">
                                                <label for="name" class="text-gold-100/80 text-[10px] sm:text-xs font-bold mr-1 block uppercase tracking-wider">الاسم الكامل</label>
                                                <input type="text" id="name" name="name" required value="{{ old('name') }}"
                                                       class="w-full form-input text-gray-800 placeholder-gray-500 rounded-lg sm:rounded-xl py-2.5 sm:py-4 px-3 sm:px-4 text-sm sm:text-base text-right focus:outline-none transition-all duration-300"
                                                       placeholder="أدخل اسمك الكامل">
                                                @error('name') <span class="text-red-400 text-xs sm:text-sm">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <!-- Back -->
                                        <div class="face-back absolute inset-0 backface-hidden">
                                            <div class="space-y-1 sm:space-y-2">
                                                <label class="text-gold-100/80 text-[10px] sm:text-xs font-bold mr-1 block uppercase tracking-wider text-bahrain-red">اسم رب العائلة</label>
                                                <input type="text" id="family_name" name="family_name" value="{{ old('family_name') }}"
                                                       class="w-full form-input text-gray-800 placeholder-gray-500 rounded-lg sm:rounded-xl py-2.5 sm:py-4 px-3 sm:px-4 text-sm sm:text-base text-right focus:outline-none transition-all duration-300"
                                                       placeholder="أدخل اسم العائلة">
                                                @error('family_name') <span class="text-red-400 text-xs sm:text-sm">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- CPR --}}
                                <div class="gsap-item flip-scene perspective-1000 h-[72px] sm:h-[86px] mb-3 sm:mb-5">
                                    <div class="flip-card w-full h-full relative transform-style-3d" id="field-2">
                                        <!-- Front -->
                                        <div class="face-front absolute inset-0 backface-hidden">
                                            <div class="space-y-1 sm:space-y-2">
                                                <label for="cpr" class="text-gold-100/80 text-[10px] sm:text-xs font-bold mr-1 block uppercase tracking-wider">رقم الهوية (CPR)</label>
                                                <input type="text" id="cpr" name="cpr" required value="{{ old('cpr') }}" pattern="\d*"
                                                       class="w-full form-input text-gray-800 placeholder-gray-500 rounded-lg sm:rounded-xl py-2.5 sm:py-4 px-3 sm:px-4 text-sm sm:text-base text-right focus:outline-none transition-all duration-300"
                                                       placeholder="أدخل رقم الهوية">
                                                @error('cpr') <span class="text-red-400 text-xs sm:text-sm">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <!-- Back -->
                                        <div class="face-back absolute inset-0 backface-hidden">
                                            <div class="space-y-1 sm:space-y-2">
                                                <label class="text-gold-100/80 text-[10px] sm:text-xs font-bold mr-1 block uppercase tracking-wider text-bahrain-red">رقم الهوية (CPR) لرب العائلة</label>
                                                <input type="text" id="responsible_cpr" name="responsible_cpr" value="{{ old('responsible_cpr') }}" pattern="\d*"
                                                       class="w-full form-input text-gray-800 placeholder-gray-500 rounded-lg sm:rounded-xl py-2.5 sm:py-4 px-3 sm:px-4 text-sm sm:text-base text-right focus:outline-none transition-all duration-300"
                                                       placeholder="أدخل رقم الهوية">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Phone --}}
                                <div class="gsap-item flip-scene perspective-1000 h-[72px] sm:h-[86px] mb-3 sm:mb-5">
                                    <div class="flip-card w-full h-full relative transform-style-3d" id="field-3">
                                        <!-- Front -->
                                        <div class="face-front absolute inset-0 backface-hidden">
                                            <div class="space-y-1 sm:space-y-2">
                                                <label for="phone_number" class="text-gold-100/80 text-[10px] sm:text-xs font-bold mr-1 block uppercase tracking-wider">رقم الهاتف</label>
                                                <input type="tel" id="phone_number" name="phone_number" required value="{{ old('phone_number') }}"
                                                       class="w-full form-input text-gray-800 placeholder-gray-500 rounded-lg sm:rounded-xl py-2.5 sm:py-4 px-3 sm:px-4 text-sm sm:text-base text-right focus:outline-none transition-all duration-300"
                                                       placeholder="أدخل رقم الهاتف">
                                                @error('phone_number') <span class="text-red-400 text-xs sm:text-sm">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <!-- Back -->
                                        <div class="face-back absolute inset-0 backface-hidden">
                                            <div class="space-y-1 sm:space-y-2">
                                                <label class="text-gold-100/80 text-[10px] sm:text-xs font-bold mr-1 block uppercase tracking-wider text-bahrain-red">رقم هاتف التواصل لرب العائلة</label>
                                                <input type="tel" id="contact_phone" name="contact_phone" value="{{ old('contact_phone') }}"
                                                       class="w-full form-input text-gray-800 placeholder-gray-500 rounded-lg sm:rounded-xl py-2.5 sm:py-4 px-3 sm:px-4 text-sm sm:text-base text-right focus:outline-none transition-all duration-300"
                                                       placeholder="أدخل رقم للتواصل">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Family Fields (Hidden by default) --}}
                                <div id="family-fields" class="hidden">
                                    {{-- Number of Family Members --}}
                                    <div class="gsap-item mb-3 sm:mb-4">
                                        <label for="family_members" class="block text-brand-cream mb-1 sm:mb-2 text-xs sm:text-sm font-semibold">عدد أفراد العائلة</label>
                                        <input type="number" id="family_members" name="family_members" min="2" max="10" value="{{ old('family_members', 2) }}"
                                               class="w-full py-2.5 sm:py-3 px-3 sm:px-4 bg-dark-navy/80 border border-brand-cream/30 rounded-lg sm:rounded-xl text-white text-sm sm:text-base focus:border-brand-cream focus:ring-2 focus:ring-brand-cream/30 transition-all opacity-100">
                                        @error('family_members') <span class="text-red-400 text-xs sm:text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- Submit Button --}}
                                <div class="gsap-item flip-scene perspective-1000 h-[48px] sm:h-[60px] mt-3 sm:mt-4">
                                    <div class="flip-card w-full h-full relative transform-style-3d" id="btn-flip">
                                        <!-- Front Button -->
                                        <div class="face-front absolute inset-0 backface-hidden">
                                            <button type="submit" class="w-full h-full btn-primary group relative overflow-hidden rounded-xl transition-all hover:scale-[1.01] active:scale-[0.99]">
                                                <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent"></div>
                                                <div class="absolute -inset-full top-0 block h-full w-1/2 -skew-x-12 bg-gradient-to-r from-transparent to-white opacity-30 group-hover:animate-shine left-0"></div>
                                                <div class="relative w-full h-full flex items-center justify-center gap-1 sm:gap-2">
                                                    <span class="text-base sm:text-lg font-bold text-white tracking-wide drop-shadow-md">سجّل الآن</span>
                                                    <i data-lucide="target" class="w-4 h-4 sm:w-5 sm:h-5 text-white"></i>
                                                </div>
                                            </button>
                                        </div>
                                        <!-- Back Button -->
                                        <div class="face-back absolute inset-0 backface-hidden">
                                            <button type="submit" class="w-full h-full btn-primary group relative overflow-hidden rounded-xl transition-all hover:scale-[1.01] active:scale-[0.99]">
                                                <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent"></div>
                                                <div class="absolute -inset-full top-0 block h-full w-1/2 -skew-x-12 bg-gradient-to-r from-transparent to-white opacity-30 group-hover:animate-shine left-0"></div>
                                                <div class="relative w-full h-full flex items-center justify-center gap-1 sm:gap-2">
                                                    <span class="text-base sm:text-lg font-bold text-white tracking-wide drop-shadow-md">سجّل الآن</span>
                                                    <i data-lucide="users" class="w-4 h-4 sm:w-5 sm:h-5 text-white"></i>
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        {{-- Sponsors Section --}}
                        @include('sponsors')

                        {{-- Current Ramadan Info --}}
                        <div class="gsap-item mt-4 sm:mt-6 mx-2 sm:mx-4 p-[1px] rounded-2xl bg-gradient-to-r from-transparent via-green-800/50 to-transparent">
                            <div class="bg-black/40 backdrop-blur-md rounded-2xl p-3 sm:p-4 text-center border border-green-500/10 relative overflow-hidden">
                                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-1/2 h-[1px] bg-gradient-to-r from-transparent via-green-500/50 to-transparent"></div>

                                <h4 class="text-green-400 font-bold mb-1 sm:mb-2 flex items-center justify-center gap-1 sm:gap-2 text-xs sm:text-sm md:text-base">
                                    <i data-lucide="moon" class="w-3 h-3 sm:w-4 sm:h-4 fill-green-400/20"></i>
                                    <span>أهلاً بكم في شهر رمضان المبارك</span>
                                </h4>
                                <div class="flex justify-center items-center gap-2 sm:gap-4 text-[10px] sm:text-xs md:text-sm text-gray-400 font-mono">
                                    <span>{{ $ramadanHijri ?? '1 رمضان 1447 هـ' }}</span>
                                    <span class="w-0.5 h-0.5 sm:w-1 sm:h-1 rounded-full bg-gray-600"></span>
                                    <span>{{ $ramadanDate ?? '18 فبراير 2026' }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Registration is closed - Simple "Coming Soon" message --}}
                        <div class="text-center py-8 sm:py-12">
                            <div class="mb-4 sm:mb-6">
                                <span class="text-4xl sm:text-6xl md:text-7xl">🌙</span>
                            </div>
                            <h2 class="text-3xl sm:text-4xl md:text-5xl font-black mb-2 sm:mb-4 gold-text">
                                قريباً
                            </h2>
                            <p class="text-gray-300 text-sm sm:text-lg md:text-xl font-light">
                                سيتم فتح التسجيل مع بداية شهر رمضان المبارك
                            </p>
                        </div>
                    @endif
            </div>
        </main>

        <footer class="gsap-item mt-6 sm:mt-12 text-center relative z-10 pb-4 sm:pb-6">
            <p class="text-gray-500 text-[9px] sm:text-[10px] mb-2 tracking-widest uppercase">© {{ date('Y') }} Bahrain Television | All Rights Reserved</p>

            <div class="flex justify-center flex-wrap gap-x-3 sm:gap-x-4 gap-y-1 sm:gap-y-2 mb-2 sm:mb-3 text-[9px] sm:text-[10px] font-bold text-gray-400">
                <a href="{{ route('privacy') }}" class="hover:text-gold-500 transition-colors uppercase tracking-widest">Privacy</a>
                <span class="text-gray-700 hidden sm:inline">/</span>
                <a href="{{ route('terms') }}" class="hover:text-gold-500 transition-colors uppercase tracking-widest">Terms</a>
                <span class="text-gray-700 hidden sm:inline">/</span>
                <a href="{{ route('policy') }}" class="hover:text-gold-500 transition-colors uppercase tracking-widest">Conditions</a>
            </div>

            <div class="inline-flex items-center gap-2 sm:gap-3 px-2 sm:px-3 py-1 sm:py-1.5 rounded-full bg-white/5 border border-white/5 text-[9px] sm:text-[10px] font-mono text-gray-400">
                <div class="flex items-center gap-1 sm:gap-1.5">
                    <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2 bg-green-500"></span>
                    </span>
                    <span>{{ number_format($totalHits ?? 0) }} Live</span>
                </div>
                <div class="w-[1px] h-2 sm:h-3 bg-gray-600"></div>
                <span class="text-gold-500">v{{ $appVersion ?? '1.0.0' }}</span>
            </div>
        </footer>
    </div>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- GSAP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <!-- Three.js — cinematic background -->
    <script src="https://cdn.jsdelivr.net/npm/three@0.170.0/build/three.min.js"></script>

    <script>
        lucide.createIcons();

        // ═══════════════════════════════════════════════════
        // THREE.JS — Cinematic Particle Warp Background
        // ═══════════════════════════════════════════════════
        (function initThreeBackground() {
            const canvas = document.getElementById('threejs-bg');
            if (!canvas || typeof THREE === 'undefined') return;
            // Skip on mobile — CSS media query hides canvas but also bail early to save resources
            if (window.innerWidth < 768) return;

            const renderer = new THREE.WebGLRenderer({ canvas, antialias: false, alpha: true });
            renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.5));
            renderer.setSize(window.innerWidth, window.innerHeight);
            renderer.setClearColor(0x000000, 0);

            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(70, window.innerWidth / window.innerHeight, 0.1, 200);
            camera.position.z = 6;

            // ── Particle geometry ──────────────────────────
            const PARTICLE_COUNT = 320;
            const positions  = new Float32Array(PARTICLE_COUNT * 3);
            const colors     = new Float32Array(PARTICLE_COUNT * 3);
            const velocities = new Float32Array(PARTICLE_COUNT); // z-velocity per particle

            const palettes = [
                new THREE.Color('#C59D5F'),  // gold
                new THREE.Color('#F5DEB3'),  // cream
                new THREE.Color('#A81C2E'),  // maroon
                new THREE.Color('#ffffff'),  // white star
            ];

            function resetParticle(i) {
                // Spread in a wide cone ahead of camera
                positions[i*3]   = (Math.random() - 0.5) * 22;
                positions[i*3+1] = (Math.random() - 0.5) * 22;
                positions[i*3+2] = -60 - Math.random() * 60; // far behind

                velocities[i] = 2.5 + Math.random() * 4; // z-speed

                const c = palettes[Math.floor(Math.random() * palettes.length)];
                colors[i*3]   = c.r;
                colors[i*3+1] = c.g;
                colors[i*3+2] = c.b;
            }

            for (let i = 0; i < PARTICLE_COUNT; i++) {
                resetParticle(i);
                // Scatter some already close for immediate effect
                if (i < 80) positions[i*3+2] = (Math.random() - 0.5) * 60;
            }

            const geo = new THREE.BufferGeometry();
            geo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
            geo.setAttribute('color',    new THREE.BufferAttribute(colors, 3));

            const mat = new THREE.PointsMaterial({
                size: 0.12,
                vertexColors: true,
                transparent: true,
                opacity: 0.65,
                sizeAttenuation: true,
                blending: THREE.AdditiveBlending,
                depthWrite: false,
            });

            const particles = new THREE.Points(geo, mat);
            scene.add(particles);

            // ── Animation loop ─────────────────────────────
            const clock  = new THREE.Clock();
            let settled  = false;
            const WARP_DURATION = 2.2; // seconds of warp phase

            function tick() {
                requestAnimationFrame(tick);

                const delta   = Math.min(clock.getDelta(), 0.05);
                const elapsed = clock.getElapsedTime();

                const pos = geo.attributes.position.array;

                if (!settled) {
                    // WARP PHASE: particles rush toward camera
                    const progress = Math.min(elapsed / WARP_DURATION, 1);
                    const speed    = (1 - progress) * 18 + 2; // fast → gentle

                    for (let i = 0; i < PARTICLE_COUNT; i++) {
                        pos[i*3+2] += velocities[i] * speed * delta;
                        if (pos[i*3+2] > 8) resetParticle(i); // recycle behind camera
                    }

                    mat.opacity = Math.min(0.65, elapsed * 0.45);

                    if (elapsed >= WARP_DURATION) settled = true;
                } else {
                    // SETTLED PHASE: slow gentle float
                    for (let i = 0; i < PARTICLE_COUNT; i++) {
                        pos[i*3+2] += delta * (velocities[i] * 0.18);
                        pos[i*3+1] += Math.sin(elapsed * 0.4 + i * 0.07) * delta * 0.04;
                        pos[i*3]   += Math.cos(elapsed * 0.3 + i * 0.11) * delta * 0.02;
                        if (pos[i*3+2] > 8) resetParticle(i);
                    }
                }

                geo.attributes.position.needsUpdate = true;

                // Subtle camera drift
                camera.position.x = Math.sin(elapsed * 0.09) * 0.35;
                camera.position.y = Math.cos(elapsed * 0.07) * 0.2;
                camera.lookAt(0, 0, 0);

                renderer.render(scene, camera);
            }
            tick();

            // Resize
            window.addEventListener('resize', () => {
                camera.aspect = window.innerWidth / window.innerHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(window.innerWidth, window.innerHeight);
            });
        })();

        // ═══════════════════════════════════════════════════
        // GSAP — Cinematic DOM Entry Sequence
        // ═══════════════════════════════════════════════════
        window.addEventListener('load', () => {
            // Skip GSAP animations on mobile - elements are already visible via CSS
            if (window.innerWidth < 768) {
                // Set initial states to visible immediately on mobile
                gsap.set(".gsap-entry", { opacity: 1, y: 0, scale: 1 });
                gsap.set(".gsap-card", { opacity: 1, y: 0, scale: 1, rotationX: 0 });
                gsap.set(".gsap-item", { opacity: 1, y: 0, x: 0, scale: 1, rotationX: 0 });
                return;
            }

            // 1. Hard-set initial states (3D positions off-screen)
            gsap.set(".gsap-entry", {
                opacity: 0,
                y: -70,
                scale: 0.88,
                transformPerspective: 900,
            });
            gsap.set(".gsap-card", {
                opacity: 0,
                y: 120,
                scale: 0.82,
                rotationX: 28,
                transformPerspective: 1100,
                transformOrigin: "50% 110%",
            });
            gsap.set(".gsap-item", {
                opacity: 0,
                y: 55,
                x: function(i) { return i % 2 === 0 ? -40 : 40; }, // alternate sides
                scale: 0.88,
                rotationX: 12,
                transformPerspective: 900,
            });
            gsap.set(".flip-card", { rotationY: 0 });

            // 2. Main timeline — starts after Three.js warp completes (≈2.2s)
            const tl = gsap.timeline({ delay: 1.6 });

            // Header sweeps down from above
            tl.to(".gsap-entry", {
                opacity: 1,
                y: 0,
                scale: 1,
                duration: 1.1,
                ease: "power4.out",
            });

            // Card RISES from below with a satisfying 3D flip
            tl.to(".gsap-card", {
                opacity: 1,
                y: 0,
                scale: 1,
                rotationX: 0,
                duration: 1.25,
                ease: "power4.out",
            }, "-=0.55");

            // Inner items cascade in with elastic spring — alternating left/right
            tl.to(".gsap-item", {
                opacity: 1,
                y: 0,
                x: 0,
                scale: 1,
                rotationX: 0,
                duration: 0.9,
                stagger: {
                    amount: 0.75,
                    ease: "power2.inOut",
                },
                ease: "back.out(1.4)",
            }, "-=0.65");
        });

        // Tab Logic (Preserved)
        let currentTab = 'individual';

        function switchTab(tab) {
            if (currentTab === tab) return;
            currentTab = tab;

            const bg = document.getElementById('tab-bg');
            const tabInd = document.getElementById('tab-individual');
            const tabFam = document.getElementById('tab-family');

            // Check if mobile - use simpler, slower animations
            const isMobile = window.innerWidth < 768;
            const duration = isMobile ? 0.3 : 0.6;
            const flipDuration = isMobile ? 0 : 0.9;

            if (tab === 'family') {
                gsap.to(bg, {
                    left: "98%",
                    xPercent: -100,
                    duration: duration,
                    ease: isMobile ? "power2.out" : "power3.inOut"
                });

                tabFam.className = "flex-1 relative z-10 py-3 text-sm font-bold text-white flex justify-center items-center gap-2 transition-colors duration-300";
                tabInd.className = "flex-1 relative z-10 py-3 text-sm font-bold text-gray-400 flex justify-center items-center gap-2 transition-colors duration-300";

                // Skip flip animation on mobile
                if (!isMobile) {
                    gsap.to(".flip-card", {
                        rotationY: 180,
                        duration: 0.9,
                        stagger: 0.08,
                        ease: "back.out(1.2)"
                    });
                }

            } else {
                gsap.to(bg, {
                    left: "4px",
                    xPercent: 0,
                    duration: duration,
                    ease: isMobile ? "power2.out" : "power3.inOut"
                });

                tabFam.className = "flex-1 relative z-10 py-3 text-sm font-bold text-gray-400 flex justify-center items-center gap-2 transition-colors duration-300";
                tabInd.className = "flex-1 relative z-10 py-3 text-sm font-bold text-white flex justify-center items-center gap-2 transition-colors duration-300";

                // Skip flip animation on mobile
                if (!isMobile) {
                    gsap.to(".flip-card", {
                        rotationY: 0,
                        duration: 0.9,
                        stagger: 0.08,
                        ease: "back.out(1.2)"
                    });
                }
            }
        }

        // ==================== REGISTRATION TYPE TOGGLE WITH SPINNING ANIMATION ====================
        function setupRegistrationToggle() {
            const individualToggle = document.getElementById('individual-toggle');
            const familyToggle = document.getElementById('family-toggle');
            const registrationType = document.getElementById('registration_type');
            const familyFields = document.getElementById('family-fields');
            const nameLabel = document.querySelector('label[for="name"]');
            const cprLabel = document.querySelector('label[for="cpr"]');
            const form = document.querySelector('form[method="POST"]');
            const registrationForm = document.querySelector('.registration-form');
            let isAnimating = false;

            // Ensure all required elements exist
            if (!individualToggle || !familyToggle || !registrationType) {
                console.warn('Registration form elements not found');
                return;
            }

            // Check if gsap is available
            const hasGSAP = typeof window.gsap !== 'undefined';

            function updateButtonStyles(isFamily) {
                if (isFamily) {
                    individualToggle.style.background = 'transparent';
                    individualToggle.style.color = '#E8D7C3';
                    familyToggle.style.background = 'linear-gradient(135deg, #A81C2E, #E8D7C3)';
                    familyToggle.style.color = '#FFFFFF';
                } else {
                    individualToggle.style.background = 'linear-gradient(135deg, #A81C2E, #E8D7C3)';
                    individualToggle.style.color = '#FFFFFF';
                    familyToggle.style.background = 'transparent';
                    familyToggle.style.color = '#E8D7C3';
                }
            }

            function animateFormEntrance() {
                // Animation disabled - forms appear immediately with inline styles
                if (!hasGSAP || !registrationForm) return;

                const elements = registrationForm.querySelectorAll('label, input, select, button');
                if (!elements.length) return;

                // Ensure all elements are visible
                gsap.killTweensOf(elements);
                gsap.set(elements, { opacity: 1, y: 0 });
            }

            function setIndividualMode() {
                registrationType.value = 'individual';
                nameLabel.textContent = 'الاسم الكامل';
                cprLabel.textContent = 'رقم الهوية (CPR)';
                familyFields.style.display = 'none';
                familyFields.style.opacity = '0';
                updateButtonStyles(false);
                animateFormEntrance();
            }

            function setFamilyMode() {
                registrationType.value = 'family';
                nameLabel.textContent = 'اسم المسؤول عن العائلة';
                cprLabel.textContent = 'رقم الهوية (CPR) للمسؤول';
                familyFields.style.display = 'flex';
                familyFields.style.flexDirection = 'column';
                familyFields.style.gap = '1rem';
                familyFields.style.opacity = '1';
                updateButtonStyles(true);
                animateFormEntrance();
            }

            function switchFormWithSpin(isFamily) {
                if (isAnimating) return;

                // Check if already on the selected mode
                const currentMode = registrationType.value;
                if ((isFamily && currentMode === 'family') || (!isFamily && currentMode === 'individual')) {
                    return;
                }

                isAnimating = true;

                // Disable buttons during animation
                individualToggle.disabled = true;
                familyToggle.disabled = true;

                if (!hasGSAP) {
                    // Fallback without GSAP
                    if (isFamily) {
                        setFamilyMode();
                    } else {
                        setIndividualMode();
                    }
                    isAnimating = false;
                    individualToggle.disabled = false;
                    familyToggle.disabled = false;
                    return;
                }

                // Create GSAP timeline for the spinning animation with slower transition and overlap
                const tl = gsap.timeline({
                    onComplete: function() {
                        isAnimating = false;
                        individualToggle.disabled = false;
                        familyToggle.disabled = false;
                    }
                });

                // Animate form container flip based on mode - slowed down with 0.3s overlap
                if (isFamily) {
                    // Family mode animation - 0.6s each half with 0.3s overlap
                    tl.to(registrationForm, {
                        duration: 0.6,
                        rotationY: 90,
                        x: 100,
                        opacity: 0.5,
                        ease: "power2.inOut"
                    }, 0)
                    .call(() => setFamilyMode(), null, 0.3)
                    .to(registrationForm, {
                        duration: 0.6,
                        rotationY: 0,
                        x: 0,
                        opacity: 1,
                        ease: "power2.inOut"
                    }, 0.3);
                } else {
                    // Individual mode animation - 0.6s each half with 0.3s overlap
                    tl.to(registrationForm, {
                        duration: 0.6,
                        rotationY: -90,
                        x: -100,
                        opacity: 0.5,
                        ease: "power2.inOut"
                    }, 0)
                    .call(() => setIndividualMode(), null, 0.3)
                    .to(registrationForm, {
                        duration: 0.6,
                        rotationY: 0,
                        x: 0,
                        opacity: 1,
                        ease: "power2.inOut"
                    }, 0.3);
                }
            }

            individualToggle.addEventListener('click', function(e) {
                e.preventDefault();
                switchFormWithSpin(false);
            });

            familyToggle.addEventListener('click', function(e) {
                e.preventDefault();
                switchFormWithSpin(true);
            });

            // Set initial state
            setIndividualMode();
        }

        // Initialize on DOMContentLoaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupRegistrationToggle);
        } else {
            // DOM is already loaded
            setupRegistrationToggle();
        }

        // Also setup if GSAP loads late
        window.addEventListener('load', function() {
            if (typeof window.gsap !== 'undefined' && document.getElementById('individual-toggle')) {
                setupRegistrationToggle();
            }
        });

        // ==================== PRELOADER / SPLASH SCREEN ====================
        (function() {
            const SPLASH_DURATION = 3000; // Show splash for exactly 3 seconds
            const FADE_DURATION = 800;    // Fade out duration

            // Generate random stars for splash background
            function initStars() {
                const starsContainer = document.getElementById('preloaderStars');
                if (!starsContainer) return;

                for (let i = 0; i < 50; i++) {
                    const star = document.createElement('div');
                    star.className = 'star';
                    star.style.left = Math.random() * 100 + '%';
                    star.style.top = Math.random() * 100 + '%';
                    star.style.width = (Math.random() * 3 + 1) + 'px';
                    star.style.height = star.style.width;
                    star.style.animationDelay = Math.random() * 2 + 's';
                    star.style.animationDuration = (Math.random() * 2 + 1) + 's';
                    starsContainer.appendChild(star);
                }
            }

            // Function to reveal main content
            function revealContent() {
                const preloader = document.getElementById('preloader');
                const lottieBackground = document.querySelector('.lottie-background');
                const mainContainer = document.querySelector('.main-container');

                if (preloader) preloader.classList.add('fade-out');
                if (lottieBackground) lottieBackground.classList.add('revealed');
                if (mainContainer) mainContainer.classList.add('revealed');
            }

            // Initialize immediately
            initStars();

            // Start the reveal sequence after splash duration
            // Use both DOMContentLoaded and load to ensure it fires
            function startReveal() {
                setTimeout(revealContent, SPLASH_DURATION);
            }

            // Safety fallback: Force reveal after 4 seconds max
            setTimeout(() => {
                const mainContainer = document.querySelector('.main-container');
                const preloader = document.getElementById('preloader');
                const lottieBackground = document.querySelector('.lottie-background');

                if (mainContainer && !mainContainer.classList.contains('revealed')) {
                    mainContainer.classList.add('revealed');
                }
                if (preloader && !preloader.classList.contains('fade-out')) {
                    preloader.classList.add('fade-out');
                }
                if (lottieBackground && !lottieBackground.classList.contains('revealed')) {
                    lottieBackground.classList.add('revealed');
                }
            }, 4000);

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', startReveal);
            } else {
                startReveal();
            }

            window.addEventListener('load', startReveal);
        })();

        // ==================== COUNTDOWN ENDED HANDLER ====================
        document.addEventListener('DOMContentLoaded', function() {
            // Only initialize FlipDown for guests (when the element exists)
            const flipdownEl = document.getElementById('flipdown');
            if (!flipdownEl || window.flipdownInitialized) return;

            window.flipdownInitialized = true;

            // Ramadan 1447 starts on configured date at midnight (Bahrain time)
            // Using Bahrain timezone (UTC+3)
            const ramadanStartISO = '{{ $ramadanStartISO ?? "2026-02-18" }}';
            const ramadanDate = new Date(ramadanStartISO + 'T00:00:00+03:00');
            const ramadanTimestamp = Math.floor(ramadanDate.getTime() / 1000);

            // Clear container before initializing
            flipdownEl.innerHTML = '';

            // Initialize FlipDown
            try {
                const flipdown = new FlipDown(ramadanTimestamp, 'flipdown', {
                    theme: 'dark'
                });

                flipdown.start().ifEnded(() => {
                    // When countdown ends, update the message
                    const countdownTitle = document.querySelector('.gsap-entry h3');
                    const dateInfo = document.querySelector('.gsap-entry .text-center.p-6');

                    if (countdownTitle) {
                        countdownTitle.innerHTML = '<span class="bg-clip-text text-transparent bg-gradient-to-b from-gold-300 to-gold-500">رمضان كريم!</span>';
                    }

                    if (dateInfo) {
                        dateInfo.innerHTML = '<h4 class="text-green-400 font-bold text-xl">أهلاً بكم في شهر رمضان المبارك</h4><p class="text-white mt-2">سيتم فتح التسجيل قريباً</p>';
                    }

                    // Hide the flipdown
                    flipdownEl.style.opacity = '0';
                    flipdownEl.style.transition = 'opacity 1s';
                });
            } catch (error) {
                console.error('FlipDown initialization error:', error);
            }
        });
    </script>
    <!-- Onboarding Tutorial Script -->
    @if(request()->has('tutorial'))
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css"/>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const driver = window.driver.js.driver;
            
            const driverObj = driver({
                showProgress: true,
                steps: [
                    { element: '#name', popover: { title: 'الاسم الكامل', description: 'أدخ�� اسمك كما يظهر في البطاقة الذكية' } },
                    { element: '#cpr', popover: { title: 'الرقم الشخصي', description: 'أدخل رقمك الشخصي المكون من 9 أرقام' } },
                    { element: '#phone_number', popover: { title: 'رقم الهاتف', description: 'أدخل رقم هاتف فعال للتواصل في حال الفوز' } },
                    { element: 'button[type="submit"]', popover: { title: 'تأكيد التسجيل', description: 'اضغط هنا لإرسال طلبك' } }
                ]
            });
            
            // Start tutorial after a delay to allow animations
            setTimeout(() => {
                driverObj.drive();
            }, 3000);
        });
    </script>
    @endif
</body>
</html>