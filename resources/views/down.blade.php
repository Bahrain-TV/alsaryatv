<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#070608">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="description" content="صيانة مجدولة وتحديث للنظام - برنامج السارية تلفزيون البحرين">
    <title>صيانة مجدولة | برنامج السارية - تلفزيون البحرين</title>

    <!-- Preconnect & Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=changa:600,700,800|tajawal:400,500,700,800&display=swap" rel="stylesheet">

    <style>
        /* ================================================================
           DESIGN TOKENS & RESET
           ================================================================ */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        :root {
            --ink: #070608;
            --ink-card: rgba(14, 11, 18, 0.76);
            --ink-card-sub: rgba(22, 17, 28, 0.62);
            --border-gold: rgba(255, 183, 3, 0.22);
            --border-subtle: rgba(255, 255, 255, 0.08);
            --text-primary: #ffffff;
            --text-secondary: rgba(248, 250, 252, 0.75);
            --text-muted: rgba(248, 250, 252, 0.5);
            --gold: #ffb703;
            --gold-light: #ffd166;
            --gold-glow: rgba(255, 183, 3, 0.35);
            --maroon: #a81c2e;
            --maroon-glow: rgba(168, 28, 46, 0.4);
            --emerald: #10b981;
            --emerald-glow: rgba(16, 185, 129, 0.35);
            --cyan: #8ecae6;
            --radius-lg: 24px;
            --radius-md: 16px;
            --radius-sm: 12px;
            --radius-pill: 9999px;
            --transition-smooth: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-bounce: 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        html {
            font-size: 16px;
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            min-height: 100svh;
            min-height: 100dvh;
            background-color: var(--ink);
            color: var(--text-primary);
            font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.55;
            overflow-x: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* ================================================================
           AMBIENT BACKGROUND & NEBULA LIGHTS
           ================================================================ */
        .bg-layer-image {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(180deg, rgba(7, 6, 8, 0.82) 0%, rgba(7, 6, 8, 0.94) 100%),
                url("{{ asset('images/alsarya-bg-2026-by-gemini-compressed.jpeg') }}"),
                url("{{ asset('images/bahrain-bay.jpg') }}");
            background-size: cover;
            background-position: center 25%;
            background-repeat: no-repeat;
            pointer-events: none;
            z-index: 0;
            opacity: 0.45;
            transform: scale(1.02);
            filter: blur(1px);
        }

        .bg-glow-top {
            position: fixed;
            top: -15vh;
            right: 10vw;
            width: clamp(280px, 45vw, 600px);
            height: clamp(280px, 45vw, 600px);
            background: radial-gradient(circle, var(--maroon-glow) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
            opacity: 0.65;
            animation: pulseNebula 12s ease-in-out infinite alternate;
        }

        .bg-glow-bottom {
            position: fixed;
            bottom: -15vh;
            left: 10vw;
            width: clamp(260px, 40vw, 550px);
            height: clamp(260px, 40vw, 550px);
            background: radial-gradient(circle, var(--gold-glow) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
            opacity: 0.45;
            animation: pulseNebula 10s ease-in-out infinite alternate-reverse;
        }

        @keyframes pulseNebula {
            0% { transform: scale(1) translate(0, 0); opacity: 0.4; }
            50% { transform: scale(1.12) translate(15px, -15px); opacity: 0.65; }
            100% { transform: scale(0.95) translate(-10px, 10px); opacity: 0.4; }
        }

        /* ================================================================
           MAIN SHELL & CONTAINER
           ================================================================ */
        .down-shell {
            position: relative;
            z-index: 1;
            width: 100%;
            min-height: 100vh;
            min-height: 100svh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: max(16px, env(safe-area-inset-top)) max(14px, env(safe-area-inset-right)) max(20px, env(safe-area-inset-bottom)) max(14px, env(safe-area-inset-left));
        }

        .down-wrapper {
            width: 100%;
            max-width: 860px;
            display: flex;
            flex-direction: column;
            gap: clamp(14px, 2.5vw, 24px);
            margin: 0 auto;
        }

        /* ================================================================
           TOP BASMALA & RAMADAN MOTIF
           ================================================================ */
        .basmala-banner {
            text-align: center;
            color: var(--gold-light);
            font-size: clamp(0.9rem, 2.8vw, 1.15rem);
            font-weight: 700;
            letter-spacing: 0.04em;
            text-shadow: 0 2px 14px rgba(255, 183, 3, 0.4);
            opacity: 0.92;
            padding: 2px 0;
            animation: fadeInDown 0.6s ease-out;
        }

        /* ================================================================
           MAIN GLASS CARD
           ================================================================ */
        .down-card {
            background: var(--ink-card);
            border: 1px solid var(--border-gold);
            border-radius: clamp(20px, 4vw, var(--radius-lg));
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow:
                0 24px 64px -12px rgba(0, 0, 0, 0.65),
                0 0 0 1px rgba(255, 255, 255, 0.05),
                inset 0 1px 0 rgba(255, 255, 255, 0.12);
            padding: clamp(18px, 3.5vw, 36px);
            display: flex;
            flex-direction: column;
            gap: clamp(16px, 2.8vw, 28px);
            animation: cardEntrance 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* ================================================================
           HERO & LOGO SECTION
           ================================================================ */
        .hero-section {
            display: flex;
            align-items: center;
            gap: clamp(16px, 3vw, 32px);
        }

        .logo-container {
            flex-shrink: 0;
            width: clamp(80px, 18vw, 120px);
            height: clamp(80px, 18vw, 120px);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-halo {
            position: absolute;
            inset: -15%;
            background: radial-gradient(circle, rgba(255, 183, 3, 0.28) 0%, rgba(168, 28, 46, 0.18) 50%, transparent 70%);
            border-radius: 50%;
            animation: haloGlow 4s ease-in-out infinite alternate;
            z-index: 0;
        }

        @keyframes haloGlow {
            0% { transform: scale(0.92); opacity: 0.5; }
            100% { transform: scale(1.15); opacity: 0.9; }
        }

        .logo-img-box {
            position: relative;
            z-index: 1;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: levitateLogo 5s ease-in-out infinite;
        }

        .logo-img-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 8px 24px rgba(255, 183, 3, 0.35));
            user-select: none;
            -webkit-user-drag: none;
        }

        @keyframes levitateLogo {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-7px) rotate(1.5deg); }
        }

        .hero-text {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .badge-row {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 5px 12px;
            border-radius: var(--radius-pill);
            background: rgba(255, 183, 3, 0.12);
            border: 1px solid rgba(255, 183, 3, 0.4);
            color: var(--gold-light);
            font-size: clamp(0.78rem, 2vw, 0.88rem);
            font-weight: 700;
            white-space: nowrap;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: var(--gold);
            box-shadow: 0 0 10px var(--gold);
            animation: pulseGlow 1.8s infinite;
        }

        .network-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: var(--radius-pill);
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-subtle);
            color: var(--text-secondary);
            font-size: 0.78rem;
            font-weight: 500;
        }

        .network-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: var(--emerald);
            box-shadow: 0 0 8px var(--emerald-glow);
        }

        .network-dot.offline {
            background-color: #ef4444;
            box-shadow: 0 0 8px rgba(239, 68, 68, 0.5);
        }

        @keyframes pulseGlow {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.4); opacity: 0.6; }
        }

        .hero-title {
            font-family: 'Changa', 'Tajawal', sans-serif;
            font-size: clamp(1.4rem, 4.2vw, 2.1rem);
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1.25;
            letter-spacing: -0.02em;
            text-wrap: balance;
        }

        .hero-title span {
            background: linear-gradient(135deg, #ffffff 30%, var(--gold-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-desc {
            color: var(--text-secondary);
            font-size: clamp(0.88rem, 2.2vw, 1.02rem);
            line-height: 1.6;
            margin-top: 2px;
            text-wrap: balance;
        }

        /* ================================================================
           PROGRESS & STATUS STRIP
           ================================================================ */
        .status-strip {
            background: var(--ink-card-sub);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            padding: clamp(12px, 2.2vw, 18px);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .strip-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            font-size: clamp(0.82rem, 2vw, 0.92rem);
        }

        .strip-title {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--gold-light);
            font-weight: 700;
        }

        .strip-state {
            color: var(--text-muted);
            font-size: 0.82rem;
        }

        .progress-track {
            position: relative;
            width: 100%;
            height: 7px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 99px;
            overflow: hidden;
        }

        .progress-bar-flow {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 35%;
            background: linear-gradient(90deg, transparent 0%, var(--gold) 50%, var(--gold-light) 80%, transparent 100%);
            border-radius: 99px;
            animation: flowShine 2.2s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }

        @keyframes flowShine {
            0% { right: -35%; }
            100% { right: 100%; }
        }

        /* ================================================================
           CARDS GRID
           ================================================================ */
        .down-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: clamp(10px, 2vw, 16px);
        }

        .sub-card {
            background: var(--ink-card-sub);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            padding: clamp(14px, 2.5vw, 20px);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 12px;
            position: relative;
            overflow: hidden;
            transition: transform var(--transition-smooth), border-color var(--transition-smooth), background-color var(--transition-smooth);
        }

        .sub-card:hover {
            border-color: rgba(255, 183, 3, 0.3);
            background-color: rgba(28, 22, 36, 0.72);
        }

        .card-top-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: var(--text-muted);
            font-size: clamp(0.75rem, 1.8vw, 0.85rem);
            font-weight: 500;
        }

        .card-top-label .icon {
            font-size: 1.1rem;
        }

        .card-main-content {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .countdown-timer-text {
            font-family: 'Changa', 'Tajawal', sans-serif;
            font-size: clamp(1.2rem, 3.2vw, 1.7rem);
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.2;
            display: flex;
            align-items: baseline;
            gap: 6px;
        }

        .countdown-timer-text .timer-num {
            color: var(--gold);
            font-feature-settings: "tnum";
            font-variant-numeric: tabular-nums;
        }

        .counter-big-val {
            font-family: 'Changa', 'Tajawal', sans-serif;
            font-size: clamp(1.4rem, 3.8vw, 2.1rem);
            font-weight: 800;
            color: var(--gold-light);
            line-height: 1.1;
            font-feature-settings: "tnum";
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.01em;
        }

        .card-caption {
            font-size: clamp(0.75rem, 1.8vw, 0.85rem);
            color: var(--text-secondary);
            line-height: 1.4;
        }

        /* Action Button */
        .btn-retry {
            width: 100%;
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: var(--radius-sm);
            background: linear-gradient(135deg, rgba(255, 183, 3, 0.2) 0%, rgba(168, 28, 46, 0.25) 100%);
            border: 1px solid rgba(255, 183, 3, 0.45);
            color: var(--gold-light);
            font-family: 'Tajawal', sans-serif;
            font-size: clamp(0.85rem, 2vw, 0.95rem);
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all var(--transition-smooth);
            touch-action: manipulation;
        }

        .btn-retry:hover {
            background: linear-gradient(135deg, rgba(255, 183, 3, 0.35) 0%, rgba(168, 28, 46, 0.4) 100%);
            border-color: var(--gold);
            color: #ffffff;
            box-shadow: 0 4px 16px rgba(255, 183, 3, 0.25);
            transform: translateY(-1px);
        }

        .btn-retry:active {
            transform: scale(0.98);
        }

        .btn-retry.loading {
            opacity: 0.75;
            pointer-events: none;
        }

        .btn-retry .spinner-icon {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: var(--gold);
            animation: spin 0.7s linear infinite;
        }

        .btn-retry.loading .spinner-icon {
            display: inline-block;
        }

        .btn-retry.loading .retry-icon {
            display: none;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Dynamic Message Box */
        .message-slider {
            background: linear-gradient(135deg, rgba(255, 183, 3, 0.08) 0%, rgba(168, 28, 46, 0.08) 100%);
            border: 1px dashed rgba(255, 183, 3, 0.25);
            border-radius: var(--radius-md);
            padding: clamp(12px, 2.2vw, 16px);
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 54px;
        }

        .message-icon {
            flex-shrink: 0;
            font-size: 1.4rem;
            animation: bounceSubtle 2.5s ease-in-out infinite;
        }

        @keyframes bounceSubtle {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }

        .message-body {
            flex: 1;
            font-size: clamp(0.82rem, 2vw, 0.92rem);
            color: var(--text-secondary);
            line-height: 1.5;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        /* ================================================================
           OFFICIAL LINKS & FOOTER
           ================================================================ */
        .footer-channels {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: clamp(8px, 2vw, 14px);
            flex-wrap: wrap;
            padding-top: 4px;
        }

        .channel-btn {
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 14px;
            border-radius: var(--radius-pill);
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-subtle);
            color: var(--text-secondary);
            font-size: clamp(0.78rem, 1.8vw, 0.86rem);
            font-weight: 500;
            text-decoration: none;
            transition: all var(--transition-smooth);
            touch-action: manipulation;
        }

        .channel-btn:hover {
            background: rgba(255, 183, 3, 0.12);
            border-color: rgba(255, 183, 3, 0.35);
            color: var(--gold-light);
            transform: translateY(-1px);
        }

        .channel-btn:active {
            transform: scale(0.97);
        }

        .channel-btn img {
            width: 18px;
            height: 18px;
            object-fit: contain;
            filter: brightness(0.9);
        }

        .footer-copyright {
            text-align: center;
            font-size: clamp(0.72rem, 1.6vw, 0.8rem);
            color: var(--text-muted);
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .footer-copyright span a {
            color: var(--gold-light);
            text-decoration: none;
        }

        /* Toast Feedback */
        .toast-notify {
            position: fixed;
            bottom: max(20px, env(safe-area-inset-bottom));
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: rgba(18, 14, 22, 0.95);
            border: 1px solid var(--gold);
            color: #ffffff;
            padding: 10px 20px;
            border-radius: var(--radius-pill);
            font-size: 0.88rem;
            font-weight: 600;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(12px);
            z-index: 100;
            opacity: 0;
            pointer-events: none;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .toast-notify.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
            pointer-events: auto;
        }

        /* ================================================================
           ENTRANCE ANIMATIONS
           ================================================================ */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 0.92; transform: translateY(0); }
        }

        @keyframes cardEntrance {
            from { opacity: 0; transform: translateY(18px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ================================================================
           MOBILE & RESPONSIVE BREAKPOINTS
           ================================================================ */
        @media (max-width: 680px) {
            .hero-section {
                flex-direction: column;
                text-align: center;
                gap: 14px;
            }

            .hero-text {
                align-items: center;
                text-align: center;
            }

            .logo-container {
                width: 90px;
                height: 90px;
            }

            .badge-row {
                justify-content: center;
            }

            .down-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .strip-header {
                justify-content: space-between;
                align-items: center;
            }

            .card-main-content {
                align-items: center;
                text-align: center;
            }

            .countdown-timer-text {
                justify-content: center;
            }

            .card-caption {
                text-align: center;
            }

            .footer-channels {
                gap: 6px;
            }

            .channel-btn {
                padding: 7px 12px;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 380px) {
            .down-shell {
                padding: 10px 8px;
            }

            .down-card {
                padding: 14px 12px;
                gap: 14px;
            }

            .hero-title {
                font-size: 1.3rem;
            }

            .logo-container {
                width: 75px;
                height: 75px;
            }

            .counter-big-val {
                font-size: 1.35rem;
            }
        }

        /* ================================================================
           REDUCED MOTION & HIGH CONTRAST
           ================================================================ */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        @media (prefers-contrast: more) {
            .down-card {
                background: #000000;
                border-color: #ffffff;
            }
            .status-pill {
                border-color: #ffffff;
                color: #ffffff;
            }
        }
    </style>
</head>
<body class="antialiased">
    <!-- Ambient Background Lighting -->
    <div class="bg-layer-image" aria-hidden="true"></div>
    <div class="bg-glow-top" aria-hidden="true"></div>
    <div class="bg-glow-bottom" aria-hidden="true"></div>

    <!-- Main Content Shell -->
    <main class="down-shell" id="app">
        <div class="down-wrapper">
            <!-- Ramadan Basmala Banner -->
            <header class="basmala-banner" aria-label="بسم الله الرحمن الرحيم">
                بِسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ
            </header>

            <!-- Main Interactive Card -->
            <section class="down-card" aria-labelledby="down-main-title">
                <!-- Hero & Brand Section -->
                <div class="hero-section">
                    <div class="logo-container">
                        <div class="logo-halo" aria-hidden="true"></div>
                        <div class="logo-img-box">
                            @if(file_exists(public_path('images/alsarya-logo-2026-1.png')))
                                <img
                                    src="{{ asset('images/alsarya-logo-2026-1.png') }}"
                                    alt="شعار برنامج السارية"
                                    width="120"
                                    height="120"
                                    loading="eager"
                                    decoding="async">
                            @elseif(file_exists(public_path('images/alsarya-logo-2026-tiny.png')))
                                <img
                                    src="{{ asset('images/alsarya-logo-2026-tiny.png') }}"
                                    alt="شعار برنامج السارية"
                                    width="120"
                                    height="120"
                                    loading="eager"
                                    decoding="async">
                            @else
                                <img
                                    src="{{ asset('images/alsarya-logo.png') }}"
                                    alt="شعار برنامج السارية"
                                    width="120"
                                    height="120"
                                    loading="eager"
                                    decoding="async">
                            @endif
                        </div>
                    </div>

                    <div class="hero-text">
                        <div class="badge-row">
                            <span class="status-pill">
                                <span class="pulse-dot" aria-hidden="true"></span>
                                <span>صيانة وتحديث مجدول</span>
                            </span>
                            <span class="network-pill" id="networkStatus">
                                <span class="network-dot" id="networkDot" aria-hidden="true"></span>
                                <span id="networkText">حالة الاتصال: متصل</span>
                            </span>
                        </div>

                        <h1 class="hero-title" id="down-main-title">
                            نجهز لكم <span>تجربة أفضل</span>
                        </h1>

                        <p class="hero-desc">
                            نعمل حالياً على تطوير وتحديث المنظومة لخدمتكم بشكل أسرع وأكثر تميزاً خلال شهر رمضان المبارك.
                        </p>
                    </div>
                </div>

                <!-- Status & Continuous Flow Progress -->
                <div class="status-strip" aria-label="حالة التحديث">
                    <div class="strip-header">
                        <div class="strip-title">
                            <span>⚙️</span>
                            <span>جاري تطبيق التحديثات البرمجية والأمنية</span>
                        </div>
                        <div class="strip-state">
                            تحديثات مستمرة
                        </div>
                    </div>
                    <div class="progress-track" aria-hidden="true">
                        <div class="progress-bar-flow"></div>
                    </div>
                </div>

                <!-- Responsive 2-Column Grid -->
                <div class="down-grid">
                    <!-- Column 1: Connectivity Check & Retry -->
                    <div class="sub-card">
                        <div class="card-top-label">
                            <span>الفحص التلقائي للاتصال</span>
                            <span class="icon">🔄</span>
                        </div>

                        <div class="card-main-content">
                            <div class="countdown-timer-text">
                                <span>إعادة الفحص خلال:</span>
                                <span class="timer-num" id="countdownSec">15</span>
                                <span>ثانية</span>
                            </div>
                            <p class="card-caption">
                                سيتم تحديث الصفحة فور اكتمال التحديثات وعودة الخدمة تلقائياً.
                            </p>
                        </div>

                        <button type="button" class="btn-retry" id="btnCheckNow" aria-label="فحص الاتصال والتحقق من عودة الموقع">
                            <span class="retry-icon">⚡</span>
                            <span class="spinner-icon" aria-hidden="true"></span>
                            <span id="btnCheckText">فحص الاتصال الآن</span>
                        </button>
                    </div>

                    <!-- Column 2: Total Visits / Stats -->
                    <div class="sub-card">
                        <div class="card-top-label">
                            <span>إجمالي الزيارات والمسجلين</span>
                            <span class="icon">👥</span>
                        </div>

                        <div class="card-main-content">
                            <div class="counter-big-val" id="hitsCounterDisplay" aria-live="polite">
                                0
                            </div>
                            <p class="card-caption">
                                شاكرين لكم طيب المتابعة وثقتكم الغالية في برنامج السارية.
                            </p>
                        </div>

                        <div class="badge-row" style="margin-top: auto;">
                            <span class="status-pill" style="border-color: rgba(255,255,255,0.1); color: var(--text-secondary); font-size: 0.76rem;">
                                🌙 رمضان 1447 هـ
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Rotating Info Banner -->
                <div class="message-slider" id="messageSlider" aria-live="polite">
                    <span class="message-icon" id="msgEmoji">🌟</span>
                    <p class="message-body" id="msgContent">
                        برنامج السارية يأتيكم برعاية وزارة الإعلام وتلفزيون البحرين خلال الشهر الفضيل.
                    </p>
                </div>

                <!-- Footer Official Channels & Links -->
                <footer class="footer-channels" aria-label="روابط القنوات الرسمية">
                    <a href="https://www.mia.gov.bh" target="_blank" rel="noopener noreferrer" class="channel-btn">
                        <span>وزارة الإعلام</span>
                    </a>
                    <a href="https://www.instagram.com/alsaryatv" target="_blank" rel="noopener noreferrer" class="channel-btn">
                        <span>انستغرام السارية</span>
                    </a>
                    <a href="https://www.bahraintv.bh" target="_blank" rel="noopener noreferrer" class="channel-btn">
                        <span>تلفزيون البحرين</span>
                    </a>
                    <a href="/" class="channel-btn" style="border-color: var(--border-gold); color: var(--gold-light);">
                        <span>تحديث الصفحة الرئيسية</span>
                    </a>
                </footer>
            </section>

            <!-- Bottom Copyright & Info -->
            <footer class="footer-copyright">
                <span>برنامج السارية &copy; {{ date('Y') }} - تلفزيون البحرين | جميع الحقوق محفوظة</span>
            </footer>
        </div>
    </main>

    <!-- Toast Notification -->
    <div class="toast-notify" id="toastBox" role="alert" aria-live="assertive">
        <span id="toastIcon">ℹ️</span>
        <span id="toastMessage">جاري فحص الاتصال...</span>
    </div>

    <!-- Client Script -->
    <script>
        (function () {
            'use strict';

            const CONFIG = {
                pollIntervalSeconds: 15,
                hitCountTarget: {{ (int) ($totalHits ?? (class_exists(\App\Models\Caller::class) ? rescue(fn() => \App\Models\Caller::count(), 24987, false) : 24987)) }},
                countDurationMs: 1400,
                messages: [
                    { emoji: '🌟', text: 'برنامج السارية يأتيكم برعاية وزارة الإعلام وتلفزيون البحرين طوال أيام شهر رمضان المبارك.' },
                    { emoji: '🔒', text: 'نعمل على تطبيق أحدث معايير الأمان وتسريع استجابة الخوادم لضمان تجربة سلسة لكافة المشاركين.' },
                    { emoji: '🎁', text: 'جوائز كبرى ومسابقات يومية مشوقة بانتظاركم.. شكراً لصبركم وحسن متابعتكم.' },
                    { emoji: '⚙️', text: 'فرق العمل التقنية تواصل العمل على مدار الساعة لضمان أفضل أداء وجاهزية للنظام.' }
                ]
            };

            let countdownTimer = CONFIG.pollIntervalSeconds;
            let countdownIntervalId = null;
            let isChecking = false;
            let currentMsgIndex = 0;

            // DOM Elements
            const countdownEl = document.getElementById('countdownSec');
            const btnCheckNow = document.getElementById('btnCheckNow');
            const btnCheckText = document.getElementById('btnCheckText');
            const hitsDisplay = document.getElementById('hitsCounterDisplay');
            const msgEmoji = document.getElementById('msgEmoji');
            const msgContent = document.getElementById('msgContent');
            const networkDot = document.getElementById('networkDot');
            const networkText = document.getElementById('networkText');
            const toastBox = document.getElementById('toastBox');
            const toastIcon = document.getElementById('toastIcon');
            const toastMessage = document.getElementById('toastMessage');

            /**
             * Convert Western digits to Arabic-Indic numerals for authentic Arabic UI
             */
            function formatArabicNumber(num) {
                if (typeof Intl !== 'undefined') {
                    return new Intl.NumberFormat('ar-BH').format(num);
                }
                const arabicDigits = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
                return num.toString().replace(/\d/g, d => arabicDigits[d]);
            }

            /**
             * Toast notification feedback
             */
            let toastTimeout = null;
            function showToast(icon, message, durationMs = 3000) {
                if (!toastBox) return;
                if (toastTimeout) clearTimeout(toastTimeout);

                toastIcon.textContent = icon;
                toastMessage.textContent = message;
                toastBox.classList.add('show');

                toastTimeout = setTimeout(() => {
                    toastBox.classList.remove('show');
                }, durationMs);
            }

            /**
             * Smooth animated hit counter
             */
            function animateHitsCounter(target) {
                if (!hitsDisplay) return;
                if (!target || target <= 0) {
                    hitsDisplay.textContent = formatArabicNumber(0);
                    return;
                }

                const startTime = performance.now();
                const startVal = 0;

                function updateCounter(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / CONFIG.countDurationMs, 1);
                    // Ease out expo
                    const easeProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                    const currentVal = Math.floor(startVal + (target - startVal) * easeProgress);

                    hitsDisplay.textContent = formatArabicNumber(currentVal);

                    if (progress < 1) {
                        requestAnimationFrame(updateCounter);
                    } else {
                        hitsDisplay.textContent = formatArabicNumber(target);
                    }
                }

                requestAnimationFrame(updateCounter);
            }

            /**
             * Rotate info messages seamlessly
             */
            function rotateMessage() {
                if (!msgEmoji || !msgContent) return;

                currentMsgIndex = (currentMsgIndex + 1) % CONFIG.messages.length;
                const nextMsg = CONFIG.messages[currentMsgIndex];

                msgContent.style.opacity = '0';
                msgContent.style.transform = 'translateY(6px)';

                setTimeout(() => {
                    msgEmoji.textContent = nextMsg.emoji;
                    msgContent.textContent = nextMsg.text;
                    msgContent.style.opacity = '1';
                    msgContent.style.transform = 'translateY(0)';
                }, 300);
            }

            /**
             * Online / Offline network state handling
             */
            function updateOnlineStatus() {
                if (!networkDot || !networkText) return;
                if (navigator.onLine) {
                    networkDot.classList.remove('offline');
                    networkText.textContent = 'حالة الاتصال: متصل';
                } else {
                    networkDot.classList.add('offline');
                    networkText.textContent = 'حالة الاتصال: غير متصل بالإنترنت';
                    showToast('⚠️', 'يبدو أن جهازك غير متصل بالإنترنت حالياً');
                }
            }

            /**
             * Check server status via HEAD request
             */
            async function checkSiteStatus(manual = false) {
                if (isChecking) return;
                isChecking = true;

                if (btnCheckNow) {
                    btnCheckNow.classList.add('loading');
                    if (btnCheckText) btnCheckText.textContent = 'جاري التحقق...';
                }

                try {
                    // Try fetching with cache busting
                    const response = await fetch('/?t=' + Date.now(), {
                        method: 'HEAD',
                        cache: 'no-store'
                    });

                    // If HTTP 200 or successful redirect to live app
                    if (response.ok && response.status === 200) {
                        showToast('✅', 'عادت الخدمة للعمل! جاري نقلك...');
                        setTimeout(() => {
                            window.location.href = '/';
                        }, 800);
                        return;
                    } else {
                        if (manual) {
                            showToast('⏳', 'الخادم ما زال قيد التحديث والصيانة.. سنعود قريباً');
                        }
                    }
                } catch (err) {
                    if (manual) {
                        showToast('⏳', 'الصيانة جارية حالياً، شكراً لصبركم');
                    }
                } finally {
                    isChecking = false;
                    countdownTimer = CONFIG.pollIntervalSeconds;
                    if (countdownEl) countdownEl.textContent = formatArabicNumber(countdownTimer);

                    if (btnCheckNow) {
                        btnCheckNow.classList.remove('loading');
                        if (btnCheckText) btnCheckText.textContent = 'فحص الاتصال الآن';
                    }
                }
            }

            /**
             * Start countdown timer
             */
            function startCountdown() {
                if (countdownIntervalId) clearInterval(countdownIntervalId);

                countdownIntervalId = setInterval(() => {
                    if (countdownTimer > 1) {
                        countdownTimer--;
                        if (countdownEl) countdownEl.textContent = formatArabicNumber(countdownTimer);
                    } else {
                        checkSiteStatus(false);
                    }
                }, 1000);
            }

            /**
             * Initialization
             */
            function init() {
                // Initial counter animation
                animateHitsCounter(CONFIG.hitCountTarget);

                // Start countdown
                startCountdown();

                // Rotate messages every 6 seconds
                setInterval(rotateMessage, 6000);

                // Online/Offline detection
                window.addEventListener('online', updateOnlineStatus);
                window.addEventListener('offline', updateOnlineStatus);
                updateOnlineStatus();

                // Manual check button handler
                if (btnCheckNow) {
                    btnCheckNow.addEventListener('click', (e) => {
                        e.preventDefault();
                        // Haptic feedback if supported on mobile
                        if (navigator.vibrate) {
                            try { navigator.vibrate(25); } catch(e) {}
                        }
                        checkSiteStatus(true);
                    });
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
    </script>
</body>
</html>
