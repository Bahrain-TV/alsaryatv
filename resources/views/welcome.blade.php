<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="description"
        content="{{ config('app.ar_translations.description') ?? 'البرنامج الرئيسي المباشر على شاشة تلفزيون البحرين خلال شهر رمضان المبارك.' }}" />
    <meta name="theme-color" content="#0f172a" />
    <meta property="og:title" content="{{ config('app.ar_translations.title') ?? 'السارية' }}" />
    <meta property="og:description"
        content="{{ config('app.ar_translations.description') ?? 'البرنامج الرئيسي المباشر على شاشة تلفزيون البحرين خلال شهر رمضان المبارك.' }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url('/') }}" />
    <meta property="og:image" content="{{ asset('images/og-banner.jpg') }}" />
    <meta name="twitter:card" content="summary_large_image" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}" />

    <title>{{ config('app.ar_translations.title') ?? 'السارية' }} - قريباً في رمضان</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=tajawal:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- FlipDown Countdown Timer -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flipdown@0.3.2/dist/flipdown.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/flipdown@0.3.2/dist/flipdown.min.js"></script>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    <style>
        /* Modern OKLCH Color System */
        :root {
            /* Base colors */
            --background: oklch(0.2046 0 0);
            --foreground: oklch(0.9219 0 0);
            --card: oklch(0.2686 0 0);
            --card-foreground: oklch(0.9219 0 0);

            /* Primary colors - Gold/Amber theme */
            --primary: oklch(0.7686 0.1647 70.0804);
            --primary-foreground: oklch(0 0 0);
            --primary-gold: oklch(0.7686 0.1647 70.0804);

            /* Accent colors - Emerald theme */
            --accent: oklch(0.6658 0.1574 155.0);
            --accent-foreground: oklch(0.9243 0.1151 95.7459);
            --primary-emerald: oklch(0.6658 0.1574 155.0);

            /* Semantic colors */
            --muted: oklch(0.2393 0 0);
            --muted-foreground: oklch(0.7155 0 0);
            --border: oklch(0.3715 0 0);
            --input: oklch(0.3715 0 0);
            --ring: oklch(0.7686 0.1647 70.0804);

            /* Legacy support */
            --bg-dark: oklch(0.1684 0 0);
            --bg-card: oklch(0.2686 0.5 0);
            --text-primary: oklch(0.9219 0 0);
            --text-secondary: oklch(0.7155 0 0);

            /* Shadows */
            --shadow-color: oklch(0 0 0 / 0.1);
            --shadow-sm: 0px 2px 4px var(--shadow-color);
            --shadow-md: 0px 4px 8px var(--shadow-color);
            --shadow-lg: 0px 8px 16px var(--shadow-color);
            --shadow-xl: 0px 12px 24px var(--shadow-color);

            /* Spacing scale */
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;
            --spacing-2xl: 3rem;

            /* Border radius */
            --radius-sm: 0.5rem;
            --radius-md: 0.75rem;
            --radius-lg: 1rem;
            --radius-xl: 1.5rem;

            /* Font family */
            --font-sans: 'Tajawal', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-sans);
            background: linear-gradient(
                135deg,
                oklch(0.15 0 0 / 0.95) 0%,
                oklch(0.18 0 0 / 0.90) 50%,
                oklch(0.16 0 0 / 0.95) 100%
            ),
            url('{{ asset("images/alsarya-bg-2026-by-gemini2.jpeg") }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
            color: var(--foreground);
            padding: clamp(0.5rem, 2vw, 1rem);
            padding: max(env(safe-area-inset-top), 0.5rem)
                    max(env(safe-area-inset-right), 0.5rem)
                    max(env(safe-area-inset-bottom), 0.5rem)
                    max(env(safe-area-inset-left), 0.5rem);
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Touch optimization for mobile */
        @media (hover: none) and (pointer: coarse) {
            * {
                -webkit-tap-highlight-color: transparent;
            }
        }

        /* Lottie Background Container */
        .lottie-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transform: scale(1.1);
            transition: opacity 1.5s ease, transform 1.5s ease;
        }

        .lottie-background.revealed {
            opacity: 0.5;
            transform: scale(1);
            animation: breathe 8s ease-in-out infinite 1.5s;
        }

        @keyframes fadeInLottie {
            from {
                opacity: 0;
                transform: scale(1.1);
            }
            to {
                opacity: 0.5;
                transform: scale(1);
            }
        }

        @keyframes breathe {
            0%, 100% {
                opacity: 0.5;
                transform: scale(1);
            }
            50% {
                opacity: 0.65;
                transform: scale(1.02);
            }
        }

        .lottie-background lottie-player {
            min-width: 100vw;
            min-height: 100vh;
            width: 150%;
            height: 150%;
        }

        /* Main Container - Enhanced Responsiveness */
        .main-container {
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: clamp(1rem, 4vw, 2rem);
            text-align: center;
            width: 100%;
            max-width: min(900px, calc(100vw - 2rem));
            opacity: 1;
            transform: translateY(0);
            transition: opacity 1s ease, transform 1s ease;
            box-sizing: border-box;
        }

        .main-container.hidden {
            opacity: 0;
            transform: translateY(30px);
            pointer-events: none;
        }

        .main-container.revealed {
            opacity: 1;
            transform: translateY(0);
        }

        /* Logo Section - Responsive sizing */
        .logo-section {
            margin-bottom: clamp(1.5rem, 3vw, 2rem);
            animation: fadeInDown 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .logo-section img {
            height: clamp(80px, 15vw, 120px);
            filter: drop-shadow(0 10px 30px oklch(0.7686 0.1647 70.0804 / 0.3));
            animation: logoFloat 3s ease-in-out infinite;
            will-change: transform;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Downtime Card - Modern Glassmorphism with OKLCH */
        .downtime-card {
            background: oklch(0.2686 0 0 / 0.7);
            backdrop-filter: blur(32px) saturate(180%);
            -webkit-backdrop-filter: blur(32px) saturate(180%);
            border: 1px solid oklch(0.3715 0 0 / 0.3);
            border-top: 1px solid oklch(0.9219 0 0 / 0.15);
            border-left: 1px solid oklch(0.9219 0 0 / 0.1);
            border-radius: var(--radius-xl);
            padding: clamp(2rem, 5vw, 3rem);
            width: 100%;
            box-shadow:
                var(--shadow-xl),
                0 0 60px oklch(0.7686 0.1647 70.0804 / 0.08),
                inset 0 1px 1px oklch(0.9219 0 0 / 0.1);
            animation: cardAppear 1.2s cubic-bezier(0.4, 0, 0.2, 1) 0.3s both;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        /* Hover effect for desktop */
        @media (hover: hover) {
            .downtime-card:hover {
                transform: translateY(-2px);
                box-shadow:
                    0px 12px 32px oklch(0 0 0 / 0.15),
                    0 0 80px oklch(0.7686 0.1647 70.0804 / 0.12),
                    inset 0 1px 1px oklch(0.9219 0 0 / 0.15);
            }
        }

        @keyframes cardAppear {
            from {
                opacity: 0;
                transform: translateY(40px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Bismillah - Responsive Typography */
        .bismillah {
            font-size:clamp(1.5rem, 5vw, 2.5rem);
            color: var(--primary-gold);
            margin-bottom: clamp(1rem, 3vw, 1.5rem);
            text-shadow: 0 0 30px oklch(0.7686 0.1647 70.0804 / 0.5);
            animation: shimmer 3s ease-in-out infinite;
            line-height: 1.4;
        }

        @keyframes shimmer {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.85; }
        }

        /* Main Title - Gradient Text with OKLCH */
        .main-title {
            font-size: clamp(1.75rem, 6vw, 2.5rem);
            font-weight: 800;
            background: linear-gradient(
                135deg,
                var(--primary-gold),
                oklch(0.8369 0.1644 84.4286),
                var(--primary-gold)
            );
            background-size: 200% 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: clamp(0.5rem, 2vw, 0.75rem);
            text-shadow: none;
            animation: gradientShift 6s ease infinite;
            line-height: 1.2;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .subtitle {
            font-size: clamp(1rem, 3vw, 1.25rem);
            color: var(--muted-foreground);
            margin-bottom: clamp(1.5rem, 4vw, 2rem);
            line-height: 1.6;
        }

        /* Registration Closed Message - Enhanced with OKLCH */
        .closed-message {
            background: linear-gradient(
                135deg,
                oklch(0.55 0.22 25.0 / 0.15),
                oklch(0.5 0.20 20.0 / 0.08)
            );
            border: 1px solid oklch(0.6368 0.2078 25.3313 / 0.4);
            border-radius: var(--radius-lg);
            padding: clamp(1.25rem, 3vw, 1.5rem);
            margin-bottom: clamp(1.5rem, 4vw, 2rem);
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        .closed-message h3 {
            color: oklch(0.75 0.19 25.0);
            font-size: clamp(1.25rem, 4vw, 1.5rem);
            margin-bottom: clamp(0.375rem, 1.5vw, 0.5rem);
            line-height: 1.3;
        }

        .closed-message p {
            color: var(--muted-foreground);
            font-size: clamp(0.9rem, 2.5vw, 1rem);
            line-height: 1.6;
        }

        /* Countdown Section - Enhanced Responsiveness */
        .countdown-section {
            margin: clamp(1.5rem, 4vw, 2rem) 0;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: var(--spacing-lg);
        }

        .countdown-title,
        .countdown-label {
            font-size: clamp(0.95rem, 3vw, 1.35rem);
            color: var(--primary-emerald);
            margin-bottom: var(--spacing-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: clamp(0.5rem, 2vw, 0.75rem);
            text-align: center;
            line-height: 1.6;
            font-weight: 600;
            text-shadow: 0 0 20px oklch(0.6658 0.1574 155.0 / 0.3);
        }

        .countdown-title .crescent {
            font-size: clamp(0.9rem, 2.5vw, 1.2rem);
            animation: crescentGlow 3s ease-in-out infinite;
        }

        @keyframes crescentGlow {
            0%, 100% {
                opacity: 1;
                filter: drop-shadow(0 0 5px oklch(0.6658 0.1574 155.0 / 0.5));
            }
            50% {
                opacity: 0.75;
                filter: drop-shadow(0 0 15px oklch(0.6658 0.1574 155.0 / 0.8));
            }
        }

        /* FlipDown Timer - Responsive */
        #flipdown {
            margin: clamp(1rem, 3vw, 1.5rem) auto;
            transform: scale(1);
            transition: transform 0.3s ease;
        }

        /* Scale down timer on smaller screens */
        @media (max-width: 640px) {
            #flipdown {
                transform: scale(0.85);
            }
        }

        @media (max-width: 480px) {
            #flipdown {
                transform: scale(0.75);
            }
        }

        /* Arabic Labels for FlipDown - Enhanced */
        .flipdown .rotor-group-heading {
            font-size: 0 !important;
            height: auto !important;
            line-height: 1.5 !important;
            color: transparent !important;
        }

        .flipdown .rotor-group-heading::before {
            display: block;
            font-family: var(--font-sans) !important;
            font-weight: 600;
            color: var(--muted-foreground);
            font-size: clamp(0.8rem, 2.5vw, 0.9rem) !important;
            text-transform: none !important;
            padding-top: 0.5rem;
        }

        .flipdown .rotor-group:nth-child(1) .rotor-group-heading::before {
            content: 'يوم' !important;
        }
        .flipdown .rotor-group:nth-child(2) .rotor-group-heading::before {
            content: 'ساعة' !important;
        }
        .flipdown .rotor-group:nth-child(3) .rotor-group-heading::before {
            content: 'دقيقة' !important;
        }
        .flipdown .rotor-group:nth-child(4) .rotor-group-heading::before {
            content: 'ثانية' !important;
        }

        /* Ramadan Date Info - Enhanced with OKLCH */
        .ramadan-info {
            margin-top: clamp(1.5rem, 4vw, 2rem);
            padding: clamp(1.25rem, 3vw, 1.5rem);
            background: linear-gradient(
                135deg,
                oklch(0.6658 0.1574 155.0 / 0.12),
                oklch(0.5553 0.1455 155.0 / 0.06)
            );
            border: 1px solid oklch(0.6658 0.1574 155.0 / 0.35);
            border-radius: var(--radius-lg);
            transition: border-color 0.3s ease, transform 0.2s ease;
        }

        /* Hover effect for desktop */
        @media (hover: hover) {
            .ramadan-info:hover {
                border-color: oklch(0.6658 0.1574 155.0 / 0.5);
                transform: translateY(-2px);
            }
        }

        .ramadan-info h4 {
            color: var(--primary-emerald);
            font-size: clamp(1.1rem, 3vw, 1.25rem);
            margin-bottom: clamp(0.375rem, 1.5vw, 0.5rem);
            line-height: 1.4;
            text-shadow: 0 0 15px oklch(0.6658 0.1574 155.0 / 0.3);
        }

        .ramadan-info .date {
            font-size: clamp(1.25rem, 4vw, 1.5rem);
            color: var(--foreground);
            font-weight: 700;
            line-height: 1.3;
        }

        .ramadan-info .hijri {
            color: var(--primary-gold);
            font-size: clamp(1rem, 3vw, 1.1rem);
            margin-top: clamp(0.375rem, 1.5vw, 0.5rem);
            text-shadow: 0 0 15px oklch(0.7686 0.1647 70.0804 / 0.3);
        }

        /* Footer - Enhanced Responsiveness */
        .footer-section {
            margin-top: clamp(1.5rem, 4vw, 2rem);
            color: var(--muted-foreground);
            font-size: clamp(0.8rem, 2.5vw, 0.9rem);
            animation: fadeIn 1.5s ease-out 1s both;
            line-height: 1.6;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .footer-section p {
            margin-bottom: var(--spacing-sm);
        }

        .footer-section a {
            color: var(--primary-gold);
            text-decoration: none;
            transition: color 0.3s ease, text-shadow 0.3s ease;
        }

        @media (hover: hover) {
            .footer-section a:hover {
                color: oklch(0.8369 0.1644 84.4286);
                text-shadow: 0 0 10px oklch(0.7686 0.1647 70.0804 / 0.4);
            }
        }

        .footer-meta {
            margin-top: clamp(0.5rem, 2vw, 0.75rem);
            font-size: clamp(0.75rem, 2.25vw, 0.9rem);
            color: var(--muted-foreground);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: clamp(0.5rem, 2vw, 0.75rem);
        }

        .visitors-count {
            color: var(--primary-emerald);
            font-weight: 600;
        }

        .login-link {
            text-decoration: none;
            color: var(--text-secondary);
            font-size: 0.85rem;
            transition: color 0.3s;
        }

        .login-link:hover {
            color: var(--primary-gold);
            text-decoration: underline;
        }

        .version-tag {
            font-size: 0.75rem;
            color: var(--primary-emerald);
            background: rgba(16, 185, 129, 0.1);
            padding: 0.15rem 0.5rem;
            border-radius: 4px;
            font-weight: 500;
        }

        .separator {
            opacity: 0.3;
        }

        /* Responsive - Tablet */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }

            .downtime-card {
                padding: 1.5rem;
                border-radius: 20px;
            }

            .logo-section img {
                height: 80px;
            }

            .bismillah {
                font-size: 1.75rem;
            }

            .main-title {
                font-size: 1.75rem;
            }

            .subtitle {
                font-size: 1.1rem;
            }

            .closed-message {
                padding: 1.25rem;
                margin-bottom: 1.5rem;
            }

            .closed-message h3 {
                font-size: 1.25rem;
            }

            .closed-message p {
                font-size: 0.95rem;
            }

            .countdown-section {
                margin: 1.5rem 0;
            }

            .countdown-title {
                font-size: 1.1rem;
                margin-bottom: 1rem;
            }

            .countdown-label {
                font-size: 1.1rem;
                margin-bottom: 1rem;
            }

            .ramadan-info {
                padding: 1.25rem;
                margin-top: 1.5rem;
            }

            .ramadan-info h4 {
                font-size: 1.1rem;
            }

            .ramadan-info .date {
                font-size: 1.35rem;
            }

            .ramadan-info .hijri {
                font-size: 1rem;
            }
        }

        /* Responsive - Mobile */
        @media (max-width: 480px) {
            .main-container {
                padding: 0.75rem;
            }

            .downtime-card {
                padding: 1.25rem;
                border-radius: 16px;
            }

            .bismillah {
                font-size: 1.4rem;
                margin-bottom: 1rem;
            }

            .main-title {
                font-size: 1.5rem;
            }

            .subtitle {
                font-size: 1rem;
                margin-bottom: 1.5rem;
            }

            .closed-message {
                padding: 1rem;
                margin-bottom: 1.25rem;
                border-radius: 12px;
            }

            .closed-message h3 {
                font-size: 1.1rem;
                margin-bottom: 0.4rem;
            }

            .closed-message p {
                font-size: 0.9rem;
                line-height: 1.5;
            }

            .countdown-section {
                margin: 1.25rem 0;
            }

            .countdown-title {
                font-size: 0.95rem;
                margin-bottom: 0.75rem;
                gap: 0.5rem;
            }

            .countdown-label {
                font-size: 0.95rem;
                margin-bottom: 0.75rem;
                gap: 0.5rem;
            }

            .ramadan-info {
                padding: 1rem;
                margin-top: 1.25rem;
                border-radius: 12px;
            }

            .ramadan-info h4 {
                font-size: 1rem;
            }

            .ramadan-info .date {
                font-size: 1.25rem;
            }

            .ramadan-info .hijri {
                font-size: 0.95rem;
            }

            .footer-section {
                margin-top: 1.5rem;
                font-size: 0.85rem;
            }

            .footer-meta {
                font-size: 0.85rem;
                flex-wrap: wrap;
                gap: 8px;
            }
        }

        /* Responsive - Small Mobile */
        @media (max-width: 360px) {
            .main-container {
                padding: 0.5rem;
            }

            .downtime-card {
                padding: 1rem;
                border-radius: 14px;
            }

            .bismillah {
                font-size: 1.2rem;
                margin-bottom: 0.75rem;
            }

            .main-title {
                font-size: 1.3rem;
            }

            .subtitle {
                font-size: 0.9rem;
                margin-bottom: 1.25rem;
            }

            .closed-message {
                padding: 0.875rem;
                margin-bottom: 1rem;
            }

            .closed-message h3 {
                font-size: 1rem;
            }

            .closed-message p {
                font-size: 0.85rem;
            }

            .countdown-section {
                margin: 1rem 0;
            }

            .countdown-title {
                font-size: 0.85rem;
                margin-bottom: 0.5rem;
            }

            .countdown-label {
                font-size: 0.85rem;
                margin-bottom: 0.5rem;
            }

            .ramadan-info {
                padding: 0.875rem;
                margin-top: 1rem;
            }

            .ramadan-info h4 {
                font-size: 0.9rem;
            }

            .ramadan-info .date {
                font-size: 1.1rem;
            }

            .ramadan-info .hijri {
                font-size: 0.85rem;
            }

            .footer-section {
                margin-top: 1.25rem;
                font-size: 0.8rem;
            }

            .footer-meta {
                font-size: 0.8rem;
            }
        }

        /* ==================== PRELOADER STYLES ==================== */
        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #050810;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.8s ease, visibility 0.8s ease;
            opacity: 1;
            visibility: visible;
        }

        .preloader.fade-out {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .preloader-content {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* Logo Container */
        .preloader-logo-container {
            position: relative;
            width: 200px;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* The Logo */
        .preloader-logo {
            width: 120px;
            height: auto;
            z-index: 10;
            animation: logoBreath 2s ease-in-out infinite, logoGlow 3s ease-in-out infinite;
            filter: drop-shadow(0 0 20px rgba(212, 175, 55, 0.5));
        }

        @keyframes logoBreath {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        @keyframes logoGlow {
            0%, 100% {
                filter: drop-shadow(0 0 20px rgba(212, 175, 55, 0.5)) brightness(1);
            }
            50% {
                filter: drop-shadow(0 0 40px rgba(212, 175, 55, 0.8)) brightness(1.2);
            }
        }

        /* Rotating Ring */
        .preloader-ring {
            position: absolute;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            border: 2px solid transparent;
            border-top: 2px solid rgba(212, 175, 55, 0.8);
            border-right: 2px solid rgba(212, 175, 55, 0.4);
            animation: ringRotate 2s linear infinite;
        }

        .preloader-ring::before {
            content: '';
            position: absolute;
            top: -6px;
            left: 50%;
            transform: translateX(-50%);
            width: 12px;
            height: 12px;
            background: var(--primary-gold);
            border-radius: 50%;
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.8);
        }

        @keyframes ringRotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Second Ring */
        .preloader-ring-2 {
            position: absolute;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            border: 1px solid transparent;
            border-bottom: 1px solid rgba(16, 185, 129, 0.6);
            border-left: 1px solid rgba(16, 185, 129, 0.3);
            animation: ringRotate 3s linear infinite reverse;
        }

        /* Particles */
        .preloader-particles {
            position: absolute;
            width: 200px;
            height: 200px;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: var(--primary-gold);
            border-radius: 50%;
            animation: particleFloat 3s ease-in-out infinite;
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.6);
        }

        .particle:nth-child(1) { top: 0; left: 50%; animation-delay: 0s; }
        .particle:nth-child(2) { top: 15%; left: 85%; animation-delay: 0.2s; }
        .particle:nth-child(3) { top: 50%; left: 100%; animation-delay: 0.4s; }
        .particle:nth-child(4) { top: 85%; left: 85%; animation-delay: 0.6s; }
        .particle:nth-child(5) { top: 100%; left: 50%; animation-delay: 0.8s; }
        .particle:nth-child(6) { top: 85%; left: 15%; animation-delay: 1s; }
        .particle:nth-child(7) { top: 50%; left: 0; animation-delay: 1.2s; }
        .particle:nth-child(8) { top: 15%; left: 15%; animation-delay: 1.4s; }

        @keyframes particleFloat {
            0%, 100% {
                transform: scale(1);
                opacity: 0.6;
            }
            50% {
                transform: scale(1.5);
                opacity: 1;
            }
        }

        /* Loading Text */
        .preloader-text {
            margin-top: 40px;
            font-size: 1.2rem;
            color: var(--primary-gold);
            letter-spacing: 4px;
            animation: textPulse 1.5s ease-in-out infinite;
        }

        @keyframes textPulse {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        /* Stars Background */
        .preloader-stars {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }

        .star {
            position: absolute;
            background: white;
            border-radius: 50%;
            animation: twinkle 2s ease-in-out infinite;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.2); }
        }
    </style>
</head>

<body dir="rtl">
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
                <img src="{{ asset('images/bahrain-tv-sm.png') }}" alt="Bahrain TV" class="preloader-logo" />
            </div>
            <div class="preloader-text">جاري التحميل</div>
        </div>
    </div>
    <!-- Lottie Animation Background -->
    <div class="lottie-background">
        <lottie-player
            id="lottie-bg"
            src="{{ asset('lottie/crecent-moon-ramadan.json') }}"
            background="transparent"
            speed="0.3"
            mode="bounce"
            loop
            autoplay>
        </lottie-player>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Logo -->
        <div class="logo-section">
            {{-- <img src="{{ asset('images/alsarya-tv-show-logo.png') }}" alt="السارية" /> --}}
        </div>

        <!-- Downtime Card -->
        <div class="downtime-card">
            <div class="bismillah">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ</div>
            
            <h1 class="main-title">برنامج السارية</h1>
            <p class="subtitle">على شاشة تلفزيون البحرين</p>

            @if(config('alsarya.registration.enabled', false))
                {{-- Registration is enabled - show registration form --}}
                <div class="open-message" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.1)); border: 2px solid rgba(16, 185, 129, 0.4); border-radius: 16px; padding: 1.5rem; margin-bottom: 2rem;">
                    <h3 style="color: #34d399; font-size: 1.5rem; margin-bottom: 0.5rem;">🌙 رمضان كريم!</h3>
                    <p style="color: rgba(255, 255, 255, 0.8);">التسجيل مفتوح الآن - سجّل للمشاركة في المسابقة</p>
                </div>

                {{-- Registration Form for Logged-in Users --}}
                <div class="registration-form" style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 2rem; margin-bottom: 2rem;">
                    {{-- Registration Type Toggle --}}
                    <div style="display: flex; gap: 1rem; margin-bottom: 2rem; justify-content: center;">
                        <button type="button" id="individual-toggle"
                                style="flex: 1; padding: 0.875rem 1rem; background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #0f172a; font-weight: 700; border: 2px solid #fbbf24; border-radius: 12px; cursor: pointer; transition: all 0.3s; font-size: 1rem;">
                            👤 تسجيل فردي
                        </button>
                        <button type="button" id="family-toggle"
                                style="flex: 1; padding: 0.875rem 1rem; background: transparent; color: #fbbf24; font-weight: 700; border: 2px solid #fbbf24; border-radius: 12px; cursor: pointer; transition: all 0.3s; font-size: 1rem;">
                            👨‍👩‍👧‍👦 تسجيل عائلي
                        </button>
                    </div>

                    <form method="POST" action="{{ route('callers.store') }}" dir="rtl" style="display: flex; flex-direction: column; gap: 1rem;">
                        @csrf

                        {{-- Hidden field to track registration type --}}
                        <input type="hidden" id="registration_type" name="registration_type" value="individual">

                        {{-- Name --}}
                        <div>
                            <label for="name" style="display: block; color: #fbbf24; margin-bottom: 0.5rem; font-weight: 600;">الاسم الكامل</label>
                            <input type="text" id="name" name="name" required value="{{ old('name') }}" 
                                   style="width: 100%; padding: 0.875rem 1rem; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(251, 191, 36, 0.3); border-radius: 12px; color: white; font-size: 1rem;"
                                   placeholder="أدخل اسمك الكامل">
                            @error('name') <span style="color: #f87171; font-size: 0.875rem;">{{ $message }}</span> @enderror
                        </div>

                        {{-- CPR --}}
                        <div>
                            <label for="cpr" style="display: block; color: #fbbf24; margin-bottom: 0.5rem; font-weight: 600;">رقم الهوية (CPR)</label>
                            <input type="text" id="cpr" name="cpr" required value="{{ old('cpr') }}" pattern="\d*"
                                   style="width: 100%; padding: 0.875rem 1rem; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(251, 191, 36, 0.3); border-radius: 12px; color: white; font-size: 1rem;"
                                   placeholder="أدخل رقم الهوية">
                            @error('cpr') <span style="color: #f87171; font-size: 0.875rem;">{{ $message }}</span> @enderror
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label for="phone_number" style="display: block; color: #fbbf24; margin-bottom: 0.5rem; font-weight: 600;">رقم الهاتف</label>
                            <input type="tel" id="phone_number" name="phone_number" required value="{{ old('phone_number') }}"
                                   style="width: 100%; padding: 0.875rem 1rem; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(251, 191, 36, 0.3); border-radius: 12px; color: white; font-size: 1rem;"
                                   placeholder="أدخل رقم الهاتف">
                            @error('phone_number') <span style="color: #f87171; font-size: 0.875rem;">{{ $message }}</span> @enderror
                        </div>

                        {{-- Family Fields (Hidden by default) --}}
                        <div id="family-fields" style="display: none;">
                            {{-- Family Name --}}
                            <div>
                                <label for="family_name" style="display: block; color: #fbbf24; margin-bottom: 0.5rem; font-weight: 600;">اسم العائلة</label>
                                <input type="text" id="family_name" name="family_name" value="{{ old('family_name') }}"
                                       style="width: 100%; padding: 0.875rem 1rem; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(251, 191, 36, 0.3); border-radius: 12px; color: white; font-size: 1rem;"
                                       placeholder="أدخل اسم العائلة (اختياري)">
                                @error('family_name') <span style="color: #f87171; font-size: 0.875rem;">{{ $message }}</span> @enderror
                            </div>

                            {{-- Number of Family Members --}}
                            <div>
                                <label for="family_members" style="display: block; color: #fbbf24; margin-bottom: 0.5rem; font-weight: 600;">عدد أفراد العائلة</label>
                                <input type="number" id="family_members" name="family_members" min="2" max="10" value="{{ old('family_members', 2) }}"
                                       style="width: 100%; padding: 0.875rem 1rem; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(251, 191, 36, 0.3); border-radius: 12px; color: white; font-size: 1rem;">
                                @error('family_members') <span style="color: #f87171; font-size: 0.875rem;">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" 
                                style="width: 100%; padding: 1rem; background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #0f172a; font-weight: 700; font-size: 1.125rem; border: none; border-radius: 12px; cursor: pointer; margin-top: 0.5rem; transition: all 0.3s;">
                            🎯 سجّل الآن
                        </button>
                    </form>
                </div>

                {{-- Current Ramadan Info --}}
                <div class="ramadan-info">
                    <h4>🌙 أهلاً بكم في شهر رمضان المبارك</h4>
                    <div class="date">{{ $ramadanHijri ?? '1 رمضان 1447 هـ' }}</div>
                    <div class="hijri" style="color: #34d399;">{{ $ramadanDate ?? '28 فبراير 2026' }}</div>
                </div>
            @else
                {{-- Guests see the countdown timer --}}
                <!-- Registration Closed -->
                <div class="closed-message">
                    <h3>⏸️ التسجيل مغلق حالياً</h3>
                    <p>سيتم فتح التسجيل مع بداية شهر رمضان المبارك</p>
                </div>

                <!-- Countdown -->
                <div class="countdown-section">
                    <div class="countdown-label">
                        العد التنازلي لشهر رمضان المبارك
                    </div>
                    <div id="flipdown" class="flipdown flipdown__theme-dark"></div>
                </div>

                <!-- Ramadan Date Info -->
                <div class="ramadan-info">
                    <h4>🌙 أول أيام شهر رمضان المبارك</h4>
                    <div class="date">{{ $ramadanDate ?? '28 فبراير 2026' }}</div>
                    <div class="hijri">{{ $ramadanHijri ?? '1 رمضان 1447 هـ' }}</div>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer-section">
            <p>© {{ date('Y') }} <a href="https://btv.bh" target="_blank">تلفزيون البحرين</a> | جميع الحقوق محفوظة</p>

            <div class="footer-meta">
                <span class="visitors-count">
                    👁️ عدد الزوار: {{ number_format($totalHits ?? 0) }}
                </span>
                <span class="separator">|</span>
                <span class="version-tag" title="إصدار التطبيق">v{{ $appVersion ?? '1.0.0' }}</span>
                <span class="separator">|</span>
                <a href="{{ route('login') }}" class="login-link">تسجيل الدخول</a>
            </div>
        </div>
    </div>

    <!-- Lottie Player Library -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    
    <script>
        // ==================== REGISTRATION TYPE TOGGLE ====================
        document.addEventListener('DOMContentLoaded', function() {
            const individualToggle = document.getElementById('individual-toggle');
            const familyToggle = document.getElementById('family-toggle');
            const registrationType = document.getElementById('registration_type');
            const familyFields = document.getElementById('family-fields');
            const nameLabel = document.querySelector('label[for="name"]');
            const cprLabel = document.querySelector('label[for="cpr"]');

            function setIndividualMode() {
                registrationType.value = 'individual';
                familyFields.style.display = 'none';
                nameLabel.textContent = 'الاسم الكامل';
                cprLabel.textContent = 'رقم الهوية (CPR)';

                // Update button styles
                individualToggle.style.background = 'linear-gradient(135deg, #fbbf24, #f59e0b)';
                individualToggle.style.color = '#0f172a';
                familyToggle.style.background = 'transparent';
                familyToggle.style.color = '#fbbf24';
            }

            function setFamilyMode() {
                registrationType.value = 'family';
                familyFields.style.display = 'flex';
                familyFields.style.flexDirection = 'column';
                familyFields.style.gap = '1rem';
                nameLabel.textContent = 'اسم المسؤول عن العائلة';
                cprLabel.textContent = 'رقم الهوية (CPR) للمسؤول';

                // Update button styles
                individualToggle.style.background = 'transparent';
                individualToggle.style.color = '#fbbf24';
                familyToggle.style.background = 'linear-gradient(135deg, #fbbf24, #f59e0b)';
                familyToggle.style.color = '#0f172a';
            }

            individualToggle.addEventListener('click', setIndividualMode);
            familyToggle.addEventListener('click', setFamilyMode);

            // Set initial state
            setIndividualMode();
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
            const ramadanStartISO = '{{ $ramadanStartISO ?? "2026-02-28" }}';
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
                    // When countdown ends, show Ramadan message
                    const title = document.querySelector('.closed-message h3');
                    const desc = document.querySelector('.closed-message p');
                    const label = document.querySelector('.countdown-title');

                    if (title) title.innerHTML = '🌙 رمضان كريم!';
                    if (desc) desc.textContent = 'أهلاً بكم في شهر رمضان المبارك - سيتم فتح التسجيل قريباً';
                    if (label) label.innerHTML = '🎉 حل شهر رمضان المبارك!';
                });
            } catch (error) {
                console.error('FlipDown initialization error:', error);
            }
        });
    </script>
</body>

</html>