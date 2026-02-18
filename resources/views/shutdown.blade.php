<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>برنامج السارية - وبس خلصنا</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=tajawal:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Include Lottie Player library -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

    <style>
        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        body {
            background-image: url("{{ asset('images/seef-district-from-sea.jpg') }}");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: 'Tajawal', sans-serif;
            display: flex;
            flex-direction: column;
        }

        /* ── Main card area ─────────────────────────────── */
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem 1rem;
        }

        .success-card {
            background: rgba(0, 0, 0, 0.72);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 18px;
            box-shadow: 0 0 40px rgba(0, 0, 0, 0.55),
                        0 0 0 1px rgba(168, 28, 46, 0.15);
            opacity: 0;
            transform: translateY(24px);
            animation: cardIn 0.7s ease-out 0.8s forwards;
        }

        @keyframes cardIn {
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── Sponsors sliding ticker ─────────────────────── */
        .sponsors-bar {
            display: flex;
            align-items: center;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border-top: 1px solid rgba(168, 28, 46, 0.35);
            border-bottom: 1px solid rgba(168, 28, 46, 0.35);
            padding: 0.55rem 0;
        }

        .sponsors-bar-label {
            flex-shrink: 0;
            padding: 0 1.25rem;
            color: #F5DEB3;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 2px;
            white-space: nowrap;
            border-left: 1px solid rgba(168, 28, 46, 0.45);
        }

        .sponsors-ticker-track {
            flex: 1;
            overflow: hidden;
        }

        .sponsors-ticker-inner {
            display: flex;
            align-items: center;
            gap: 5rem;
            width: max-content;
            animation: sponsorTicker 22s linear infinite;
        }

        .sponsors-ticker-inner:hover {
            animation-play-state: paused;
        }

        @keyframes sponsorTicker {
            0%   { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        .tick-logo {
            height: 2.1rem;
            width: auto;
            max-width: 120px;
            object-fit: contain;
            flex-shrink: 0;
            opacity: 0.88;
            transition: opacity 0.25s ease;
        }

        .tick-logo:hover { opacity: 1; }

        /* Al Salam SVG has dark navy fill — invert to cream-white on dark bg */
        .tick-logo-alsalam {
            filter: brightness(0) invert(1) sepia(0.12);
        }

        /* ── Footer ─────────────────────────────────────── */
        .site-footer {
            background: rgba(0, 0, 0, 0.78);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding: 0.7rem 1.5rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .footer-inner {
            max-width: 60rem;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.4rem 1.25rem;
            font-size: 0.7rem;
            font-family: 'Tajawal', sans-serif;
        }

        .footer-brand {
            font-weight: 700;
            color: #F5DEB3;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .footer-links {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: rgba(245, 222, 179, 0.55);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .footer-links a:hover { color: #F5DEB3; }

        .footer-dot {
            color: rgba(168, 28, 46, 0.5);
            font-size: 0.55rem;
        }

        .footer-copy {
            color: rgba(255, 255, 255, 0.3);
            font-size: 0.62rem;
            white-space: nowrap;
        }

        .footer-version {
            font-size: 0.62rem;
            font-family: 'Courier New', monospace;
            color: rgba(168, 28, 46, 0.7);
            letter-spacing: 1px;
        }
    </style>
</head>
<body class="antialiased">

    <!-- ── Basmala ──────────────────────────────────────── -->
    <div class="basmala" id="basmala" style="
        position: fixed; top: 16px; left: 50%; transform: translateX(-50%);
        z-index: 200; font-size: clamp(1rem, 2.5vw, 1.5rem); color: #F5DEB3;
        font-weight: 600; letter-spacing: 2px; text-align: center; pointer-events: none;
        text-shadow: 0 2px 10px rgba(168, 28, 46, 0.5);
    ">بسم الله الرحمن الرحيم</div>

    <!-- ── Main content ─────────────────────────────────── -->
    <div class="main-content">
        <div class="success-card max-w-md w-full p-8 text-white text-center">
            <div class="flex justify-center mb-4">
                <lottie-player
                    src="lottie/crecent-moon-ramadan.json"
                    background="transparent"
                    speed="1"
                    style="width: 180px; height: 180px;"
                    loop autoplay>
                </lottie-player>
            </div>
            <h1 class="text-3xl font-bold mb-3">وبس خلصنا …</h1>
            <p class="text-lg opacity-80 mb-6">نشوفكم قريب إن شاء الله.</p>
            <div class="mt-4">
                <a href="/"
                   class="inline-block px-5 py-2.5 rounded-full text-sm font-bold transition-all duration-300"
                   style="background: rgba(168,28,46,0.25); border: 1px solid rgba(168,28,46,0.5); color: #F5DEB3;">
                    ← موقع برنامج السارية
                </a>
            </div>
        </div>
    </div>

    <!-- ── Sponsors sliding ticker ───────────────────────── -->
    <div class="sponsors-bar">
        <span class="sponsors-bar-label">برعاية</span>
        <div class="sponsors-ticker-track">
            <div class="sponsors-ticker-inner">
                {{-- First set --}}
                <img src="{{ asset('images/jasmis-logo.png') }}"     alt="Jasmis"        class="tick-logo">
                <img src="{{ asset('images/alsalam-logo.svg') }}"    alt="Al Salam"      class="tick-logo tick-logo-alsalam">
                <img src="{{ asset('images/bapco-energies.png') }}"  alt="Bapco Energies" class="tick-logo">
                {{-- Duplicate for seamless infinite loop --}}
                <img src="{{ asset('images/jasmis-logo.png') }}"     alt="Jasmis"        class="tick-logo">
                <img src="{{ asset('images/alsalam-logo.svg') }}"    alt="Al Salam"      class="tick-logo tick-logo-alsalam">
                <img src="{{ asset('images/bapco-energies.png') }}"  alt="Bapco Energies" class="tick-logo">
            </div>
        </div>
    </div>

    <!-- ── Footer ────────────────────────────────────────── -->
    <footer class="site-footer">
        <div class="footer-inner">
            <span class="footer-brand">برنامج السـاريـة &mdash; تلفزيون البحرين</span>

            <div class="footer-links">
                <a href="{{ route('privacy') }}">سياسة الخصوصية</a>
                <span class="footer-dot">●</span>
                <a href="{{ route('terms') }}">شروط الاستخدام</a>
                <span class="footer-dot">●</span>
                <a href="{{ route('policy') }}">الشروط والأحكام</a>
            </div>

            <div style="display:flex; gap:0.75rem; align-items:center;">
                <span class="footer-copy">تصميم وبرمجة فريق السارية © {{ date('Y') }}</span>
                <span class="footer-version">{{ config('app.version', 'v1.0') }}</span>
            </div>
        </div>
    </footer>

</body>
</html>
