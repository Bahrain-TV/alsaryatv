<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>برنامج السارية - وبس خلصنا</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=tajawal:400,500,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

    <style>
        :root {
            --cream: #f5deb3;
            --cream-soft: rgba(245, 222, 179, 0.78);
            --maroon: #a81c2e;
            --maroon-soft: rgba(168, 28, 46, 0.35);
            --ink: #090607;
            --glass: rgba(6, 5, 8, 0.66);
            --glass-strong: rgba(10, 7, 11, 0.84);
            --line: rgba(255, 255, 255, 0.08);
            --dock-height: 11.5rem;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
        }

        body {
            position: relative;
            min-height: 100vh;
            min-height: 100svh;
            overflow-x: hidden;
            background: var(--ink);
            background-image: url("{{ asset('images/alsarya-bg-2026-by-gemini.jpeg') }}");
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            color: white;
            font-family: 'Tajawal', sans-serif;
        }

        body::before,
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
        }

        body::before {
            background:
                linear-gradient(180deg, rgba(7, 5, 8, 0.18) 0%, rgba(7, 5, 8, 0.64) 52%, rgba(7, 5, 8, 0.94) 100%);
        }

        body::after {
            background:
                radial-gradient(circle at top center, rgba(168, 28, 46, 0.3), transparent 36%),
                radial-gradient(circle at 18% 80%, rgba(245, 222, 179, 0.08), transparent 28%),
                radial-gradient(circle at 88% 22%, rgba(245, 222, 179, 0.1), transparent 24%);
            opacity: 0.9;
        }

        .basmala {
            position: fixed;
            top: 0.9rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 90;
            width: min(92vw, 30rem);
            text-align: center;
            color: var(--cream);
            font-size: clamp(0.9rem, 2.8vw, 1.4rem);
            font-weight: 700;
            letter-spacing: 0.08em;
            text-shadow: 0 4px 18px rgba(168, 28, 46, 0.45);
            pointer-events: none;
        }

        .shutdown-shell {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            min-height: 100svh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5.5rem 1rem calc(var(--dock-height) + 1.5rem);
        }

        .shutdown-stage {
            width: min(100%, 72rem);
            display: grid;
            gap: 1rem;
            align-items: center;
        }

        .brand-stack,
        .shutdown-panel {
            opacity: 0;
            transform: translateY(24px);
            animation: riseIn 0.75s ease-out forwards;
        }

        .shutdown-panel {
            animation-delay: 0.12s;
        }

        .brand-stack {
            display: grid;
            justify-items: center;
            gap: 0.9rem;
        }

        .brand-logo {
            width: min(72vw, 15rem);
            filter: drop-shadow(0 12px 24px rgba(0, 0, 0, 0.45));
        }

        .brand-logo img {
            display: block;
            width: 100%;
            height: auto;
            object-fit: contain;
        }

        .brand-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.85rem;
            border: 1px solid rgba(245, 222, 179, 0.18);
            border-radius: 999px;
            background: rgba(15, 10, 15, 0.42);
            color: var(--cream-soft);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .brand-kicker::before {
            content: '';
            width: 0.45rem;
            height: 0.45rem;
            border-radius: 999px;
            background: var(--maroon);
            box-shadow: 0 0 0 6px rgba(168, 28, 46, 0.16);
        }

        .shutdown-panel {
            width: min(100%, 42rem);
            margin: 0 auto;
            padding: 1.4rem;
            border-radius: 1.75rem;
            border: 1px solid rgba(245, 222, 179, 0.12);
            background: linear-gradient(180deg, rgba(21, 15, 22, 0.72), rgba(5, 4, 7, 0.82));
            box-shadow:
                0 22px 60px rgba(0, 0, 0, 0.42),
                inset 0 1px 0 rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        .panel-grid {
            display: grid;
            gap: 1rem;
            align-items: center;
        }

        .panel-visual {
            display: flex;
            justify-content: center;
        }

        .panel-visual-shell {
            display: grid;
            place-items: center;
            width: min(100%, 12rem);
            aspect-ratio: 1;
            border-radius: 1.5rem;
            background:
                radial-gradient(circle at center, rgba(245, 222, 179, 0.18), transparent 48%),
                linear-gradient(180deg, rgba(168, 28, 46, 0.18), rgba(245, 222, 179, 0.05));
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.03);
        }

        .panel-copy {
            text-align: center;
        }

        .panel-title {
            margin: 0;
            color: #fff7e6;
            font-size: clamp(1.9rem, 7vw, 3.2rem);
            font-weight: 800;
            line-height: 1.1;
        }

        .panel-text {
            margin: 0.95rem auto 0;
            max-width: 30rem;
            color: rgba(255, 255, 255, 0.8);
            font-size: clamp(1rem, 3.8vw, 1.15rem);
            line-height: 1.8;
        }

        .panel-meta {
            margin-top: 1.1rem;
            color: var(--cream-soft);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .panel-actions {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .panel-action,
        .panel-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 11rem;
            min-height: 3.15rem;
            padding: 0.75rem 1.2rem;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 800;
            transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
        }

        .panel-action {
            color: #fff7e6;
            background: linear-gradient(135deg, rgba(168, 28, 46, 0.94), rgba(121, 17, 29, 0.92));
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 14px 32px rgba(168, 28, 46, 0.28);
        }

        .panel-secondary {
            color: var(--cream);
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(245, 222, 179, 0.18);
        }

        .panel-action:hover,
        .panel-secondary:hover {
            transform: translateY(-2px);
        }

        .bottom-dock {
            position: fixed;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: 80;
            padding: 0 0.8rem calc(env(safe-area-inset-bottom) + 0.8rem);
            background: linear-gradient(180deg, rgba(0, 0, 0, 0), rgba(8, 6, 8, 0.18) 20%, rgba(8, 6, 8, 0.92) 100%);
        }

        .bottom-dock-inner {
            width: min(100%, 76rem);
            margin: 0 auto;
            padding: 0.8rem;
            border: 1px solid var(--line);
            border-radius: 1.5rem;
            background: linear-gradient(180deg, rgba(13, 9, 14, 0.92), rgba(5, 4, 7, 0.97));
            box-shadow: 0 -18px 34px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        .sponsor-ribbon {
            display: grid;
            gap: 0.75rem;
        }

        .sponsor-label {
            display: inline-flex;
            justify-self: start;
            align-items: center;
            padding: 0.38rem 0.8rem;
            border-radius: 999px;
            background: rgba(168, 28, 46, 0.16);
            border: 1px solid rgba(168, 28, 46, 0.35);
            color: var(--cream);
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            white-space: nowrap;
        }

        .sponsor-marquee {
            position: relative;
            overflow: hidden;
            width: 100%;
            padding: 0.75rem 0;
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.06);
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02));
            mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);
            -webkit-mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);
        }

        .sponsor-track {
            display: flex;
            align-items: center;
            gap: clamp(2rem, 7vw, 4.5rem);
            width: max-content;
            padding-inline: clamp(1rem, 4vw, 2rem);
            animation: sponsorMarquee 18s linear infinite;
            will-change: transform;
        }

        .tick-logo {
            height: clamp(1.7rem, 6vw, 2.4rem);
            width: auto;
            max-width: clamp(5.5rem, 22vw, 8rem);
            object-fit: contain;
            flex-shrink: 0;
            opacity: 0.94;
        }

        .tick-logo-alsalam {
            filter: brightness(0) invert(1) sepia(0.12);
        }

        .dock-meta {
            display: grid;
            gap: 0.5rem;
            margin-top: 0.9rem;
            padding-top: 0.85rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            text-align: center;
        }

        .dock-brand {
            color: var(--cream);
            font-size: 0.83rem;
            font-weight: 800;
            letter-spacing: 0.05em;
        }

        .dock-links {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            color: rgba(245, 222, 179, 0.62);
            font-size: 0.76rem;
        }

        .dock-links a {
            color: inherit;
            text-decoration: none;
        }

        .dock-links a:hover {
            color: var(--cream);
        }

        .dock-copy {
            color: rgba(255, 255, 255, 0.44);
            font-size: 0.72rem;
        }

        .dock-version {
            color: rgba(245, 222, 179, 0.42);
            font-family: 'Courier New', monospace;
        }

        @keyframes riseIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes sponsorMarquee {
            from {
                transform: translate3d(0, 0, 0);
            }

            to {
                transform: translate3d(-50%, 0, 0);
            }
        }

        @media (min-width: 768px) {
            :root {
                --dock-height: 9rem;
            }

            .shutdown-shell {
                padding-top: 6.5rem;
                padding-bottom: calc(var(--dock-height) + 2rem);
            }

            .shutdown-stage {
                gap: 1.35rem;
            }

            .panel-grid {
                grid-template-columns: 13rem minmax(0, 1fr);
                gap: 1.5rem;
            }

            .panel-copy {
                text-align: right;
            }

            .panel-text {
                margin-right: 0;
            }

            .panel-actions {
                justify-content: flex-start;
            }

            .sponsor-ribbon {
                grid-template-columns: auto minmax(0, 1fr);
                align-items: center;
            }

            .bottom-dock-inner {
                padding: 0.95rem 1.1rem 0.85rem;
            }

            .dock-meta {
                grid-template-columns: 1fr auto 1fr;
                align-items: center;
                text-align: initial;
            }

            .dock-brand {
                justify-self: start;
            }

            .dock-links {
                justify-self: center;
            }

            .dock-copy {
                justify-self: end;
            }
        }

        @media (max-width: 480px) {
            :root {
                --dock-height: 12.5rem;
            }

            .basmala {
                top: 0.7rem;
                font-size: 0.82rem;
                letter-spacing: 0.05em;
            }

            .shutdown-shell {
                align-items: start;
                padding-top: 4.4rem;
            }

            .shutdown-panel {
                padding: 1.15rem;
                border-radius: 1.35rem;
            }

            .panel-visual-shell {
                width: 9.5rem;
                border-radius: 1.2rem;
            }

            .panel-actions {
                flex-direction: column;
            }

            .panel-action,
            .panel-secondary {
                width: 100%;
            }

            .bottom-dock {
                padding-inline: 0.55rem;
            }

            .bottom-dock-inner {
                border-radius: 1.15rem;
                padding: 0.7rem;
            }

            .sponsor-marquee {
                padding-block: 0.65rem;
            }

            .sponsor-track {
                animation-duration: 14s;
            }
        }
    </style>
</head>

<body class="antialiased">
    <div class="basmala" id="basmala">بسم الله الرحمن الرحيم</div>

    <main class="shutdown-shell">
        <section class="shutdown-stage">
            <div class="brand-stack">
                <div class="brand-logo">
                    <img src="{{ asset('images/alsarya-logo-2026-1.png') }}" alt="السارية">
                </div>
                <span class="brand-kicker">البث المباشر يعود قريباً</span>
            </div>

            <article class="shutdown-panel">
                <div class="panel-grid">
                    <div class="panel-visual">
                        <div class="panel-visual-shell">
                            <lottie-player src="lottie/crecent-moon-ramadan.json" background="transparent" speed="0.12"
                                style="width: 100%; height: 100%;" loop autoplay>
                            </lottie-player>
                        </div>
                    </div>

                    <div class="panel-copy">
                        <h1 class="panel-title">التسجيل متوقف حتى إشعار آخر.</h1>
                        <p class="panel-text">
                            نشكر لكم اهتمامكم ومتابعتكم لبرنامج السارية.. راجعين وأقوى من قبل!
                        </p>
                        <div class="panel-meta">برنامج السارية · تلفزيون البحرين</div>

                        <div class="panel-actions">
                            <a href="/" class="panel-action">فريق عمل الســــاريـة ❤️</a>
                            <a href="{{ route('policy') }}" class="panel-secondary">الاطلاع على الشروط والأحكام</a>
                        </div>
                    </div>
                </div>
            </article>
        </section>
    </main>

    <div class="bottom-dock">
        <div class="bottom-dock-inner">
            <div class="sponsor-ribbon">
                <span class="sponsor-label">برعاية</span>

                <div class="sponsor-marquee" aria-label="الرعاة">
                    <div class="sponsor-track">
                        <img src="{{ asset('images/jasmis-logo.png') }}" alt="Jasmis" class="tick-logo">
                        <img src="{{ asset('images/alsalam-logo.svg') }}" alt="Al Salam" class="tick-logo tick-logo-alsalam">
                        <img src="{{ asset('images/bapco-energies.png') }}" alt="Bapco Energies" class="tick-logo">
                        <img src="{{ asset('images/jasmis-logo.png') }}" alt="Jasmis" class="tick-logo">
                        <img src="{{ asset('images/alsalam-logo.svg') }}" alt="Al Salam" class="tick-logo tick-logo-alsalam">
                        <img src="{{ asset('images/bapco-energies.png') }}" alt="Bapco Energies" class="tick-logo">
                    </div>
                </div>
            </div>

            <div class="dock-meta">
                <span class="dock-brand">برنامج السارية - تلفزيون البحرين</span>

                <div class="dock-links">
                    <a href="{{ route('privacy') }}">سياسة الخصوصية</a>
                    <span>•</span>
                    <a href="{{ route('terms') }}">شروط الاستخدام</a>
                    <span>•</span>
                    <a href="{{ route('policy') }}">الشروط والأحكام</a>
                </div>

                <div class="dock-copy">
                    تصميم وبرمجة فريق السارية © {{ date('Y') }}
                    <span class="dock-version">{{ config('app.version', 'v1.0') }}</span>
                </div>
            </div>
        </div>
    </div>
</body>

</html>