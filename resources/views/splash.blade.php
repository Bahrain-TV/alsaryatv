<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AlSaryaTV') }}</title>
    <meta name="description" content="برنامج السارية - مسابقة رمضانية">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=tajawal:400,500,600,700,800&display=swap" rel="stylesheet" />

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
            background: #0F172A;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .scene-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            perspective: 1500px;
        }

        .bg-gradient {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #0F172A 0%, #1a0a2e 25%, #2d0a3a 50%, #1a0a2e 75%, #0F172A 100%);
            background-size: 400% 400%;
            animation: gradientPulse 8s ease-in-out infinite;
            z-index: 0;
        }

        @keyframes gradientPulse {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            width: 2px;
            height: 2px;
            background: #fff;
            border-radius: 50%;
            opacity: 0;
            animation: particleFloat 4s ease-in-out infinite;
        }

        @keyframes particleFloat {
            0%, 100% { opacity: 0; transform: translateY(0) scale(1); }
            50% { opacity: 0.6; transform: translateY(-20px) scale(1.5); }
        }

        /* ===== PHASE 1: SPONSORS WITH INDIVIDUAL & COMBINED DISPLAY ===== */
        .sponsors-phase {
            position: absolute;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3rem;
            opacity: 0;
            z-index: 10;
        }

        .sponsored-by {
            font-size: clamp(1.3rem, 3.5vw, 2.2rem);
            background: linear-gradient(135deg, #F5DEB3 0%, #E8D7C3 40%, #A81C2E 75%, #8B6914 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
            letter-spacing: 6px;
            text-transform: uppercase;
            opacity: 0;
            transform: translateY(20px);
            filter: drop-shadow(0 2px 10px rgba(168, 28, 46, 0.5));
        }

        /* Individual sponsor card display */
        .sponsor-card {
            position: absolute;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
        }

        .sponsor-card .sponsor-card-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3rem;
            padding: 3rem;
            background: rgba(168, 28, 46, 0.2);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            border: 1px solid rgba(232, 215, 195, 0.4);
            box-shadow: 0 8px 32px rgba(168, 28, 46, 0.2);
        }

        .sponsor-card .sponsor-card-title {
            font-size: clamp(1.2rem, 3.5vw, 2rem);
            color: #F5DEB3;
            font-weight: 700;
            text-align: center;
            letter-spacing: 3px;
            text-transform: uppercase;
            text-shadow: 0 2px 8px rgba(168, 28, 46, 0.4);
        }

        .sponsor-card .sponsor-card-logo {
            width: 240px;
            height: auto;
            max-width: 90vw;
            filter: drop-shadow(0 0 30px rgba(168, 28, 46, 0.6))
                    drop-shadow(0 0 40px rgba(232, 215, 195, 0.4));
            transform: scale(0.85);
            transition: all 0.5s ease;
        }

        .sponsor-card:hover .sponsor-card-logo {
            filter: drop-shadow(0 0 50px rgba(168, 28, 46, 0.7))
                    drop-shadow(0 0 60px rgba(232, 215, 195, 0.5));
            transform: scale(0.9);
        }

        /* Combined sponsors display */
        .sponsors-logos {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5rem;
            flex-wrap: wrap;
            opacity: 0;
            padding: 3rem 2rem;
            background: rgba(168, 28, 46, 0.15);
            backdrop-filter: blur(18px);
            border-radius: 20px;
            border: 1px solid rgba(232, 215, 195, 0.35);
            box-shadow: 0 8px 32px rgba(168, 28, 46, 0.15);
        }

        .sponsor-logo {
            width: 160px;
            height: auto;
            max-width: 40vw;
            opacity: 0;
            transform: scale(0.7) translateY(40px);
            filter: drop-shadow(0 0 20px rgba(168, 28, 46, 0.5))
                    drop-shadow(0 0 30px rgba(232, 215, 195, 0.4));
            transition: all 0.5s ease;
        }

        .sponsor-logo.active {
            opacity: 1;
            transform: scale(1) translateY(0);
        }

        .sponsor-logo:hover {
            filter: drop-shadow(0 0 40px rgba(168, 28, 46, 0.7))
                    drop-shadow(0 0 50px rgba(232, 215, 195, 0.6));
            transform: scale(1.05) translateY(-5px);
        }

        /* Individual card animations */
        @keyframes cardSlideInFromLeft {
            0% { opacity: 0; transform: translateX(-100px) scale(0.8); }
            50% { opacity: 1; }
            100% { opacity: 1; transform: translateX(0) scale(1); }
        }

        @keyframes cardSlideInFromRight {
            0% { opacity: 0; transform: translateX(100px) scale(0.8); }
            50% { opacity: 1; }
            100% { opacity: 1; transform: translateX(0) scale(1); }
        }

        @keyframes cardSlideOut {
            0% { opacity: 1; transform: scale(1); }
            100% { opacity: 0; transform: scale(0.8); }
        }

        /* ===== PHASE 2: MAGICAL TRANSITION ===== */
        .magic-transition {
            position: absolute;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 20;
            opacity: 0;
            pointer-events: none;
        }

        .magic-circle {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            border: 2px solid transparent;
            opacity: 0;
        }

        .magic-circle.inner {
            border-color: rgba(168, 28, 46, 0.9);
            animation: none;
        }

        .magic-circle.outer {
            border-color: rgba(139, 105, 20, 0.7);
            animation: none;
        }

        .magic-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 1px solid rgba(168, 28, 46, 0.7);
            opacity: 0;
        }

        .sparkle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: #E8D7C3;
            border-radius: 50%;
            opacity: 0;
            box-shadow: 0 0 10px #E8D7C3, 0 0 20px #A81C2E, 0 0 30px rgba(139, 105, 20, 0.6);
        }

        /* ===== PHASE 3: SHOW LOGO ===== */
        .show-logo-phase {
            position: absolute;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            opacity: 0;
            z-index: 30;
            transform-style: preserve-3d;
        }

        .logo-container {
            position: relative;
            width: 320px;
            height: 320px;
            display: flex;
            align-items: center;
            justify-content: center;
            transform-style: preserve-3d;
        }

        .logo-glow {
            position: absolute;
            width: 350px;
            height: 350px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(168, 28, 46, 0.5) 0%, rgba(139, 105, 20, 0.3) 40%, transparent 70%);
            opacity: 0;
            animation: glowPulse 2.5s ease-in-out infinite;
            filter: blur(2px);
        }

        @keyframes glowPulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.15); opacity: 0.8; }
        }

        .show-logo {
            width: 280px;
            height: auto;
            position: relative;
            z-index: 2;
            filter: drop-shadow(0 20px 40px rgba(168, 28, 46, 0.7))
                    drop-shadow(0 0 30px rgba(139, 105, 20, 0.5))
                    drop-shadow(0 10px 20px rgba(232, 215, 195, 0.3));
            opacity: 0;
            transform: rotateY(180deg) scale(0.5);
        }

        .show-title {
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 800;
            background: linear-gradient(135deg, #F5DEB3 0%, #E8D7C3 35%, #A81C2E 65%, #8B6914 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            opacity: 0;
            transform: translateY(20px);
            text-align: center;
            filter: drop-shadow(0 2px 15px rgba(168, 28, 46, 0.3));
        }

        .show-subtitle {
            font-size: clamp(1rem, 2.5vw, 1.3rem);
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
            opacity: 0;
            transform: translateY(15px);
            text-align: center;
        }

        /* ===== BASMALA (ALWAYS AT TOP - ALWAYS VISIBLE) ===== */
        .basmala {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 200;
            font-size: clamp(1.2rem, 3vw, 1.8rem);
            color: #F5DEB3;
            font-weight: 600;
            letter-spacing: 2px;
            text-align: center;
            opacity: 1;
            text-shadow: 0 2px 10px rgba(168, 28, 46, 0.5),
                         0 0 15px rgba(232, 215, 195, 0.3);
            filter: drop-shadow(0 2px 8px rgba(168, 28, 46, 0.4));
            pointer-events: none;
        }

        @keyframes basmalaFloat {
            0%, 100% { transform: translateX(-50%) translateY(0); opacity: 1; }
            50% { transform: translateX(-50%) translateY(-6px); opacity: 1; }
        }

        /* ===== PHASE 4: FADE TO OBLIVION ===== */
        .oblivion-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at center, transparent 0%, #0F172A 100%);
            opacity: 0;
            z-index: 100;
            pointer-events: none;
        }

        .vignette {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            box-shadow: inset 0 0 150px rgba(15, 23, 42, 0.95);
            opacity: 0;
            z-index: 99;
            pointer-events: none;
        }

        /* ===== ANIMATIONS ===== */
        @keyframes sponsorFadeIn {
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes sponsorLogoIn {
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        @keyframes magicCircleExpand {
            0% { transform: scale(0); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: scale(2); opacity: 0; }
        }

        @keyframes ringExpand {
            0% { transform: scale(0.5); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: scale(3); opacity: 0; }
        }

        @keyframes sparkleBurst {
            0% { transform: scale(0); opacity: 1; }
            100% { transform: scale(1); opacity: 0; }
        }

        @keyframes logoReveal {
            0% { opacity: 0; transform: rotateY(180deg) scale(0.5); }
            50% { opacity: 1; }
            100% { opacity: 1; transform: rotateY(0deg) scale(1); }
        }

        @keyframes textFadeIn {
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeToBlack {
            to { opacity: 1; }
        }

        @keyframes finalFade {
            to { opacity: 0; transform: scale(0.9); }
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .basmala {
                top: 15px;
                font-size: clamp(1rem, 2.5vw, 1.4rem);
            }

            .sponsor-card .sponsor-card-content {
                gap: 2rem;
                padding: 2rem;
                border-radius: 15px;
            }

            .sponsor-card .sponsor-card-logo {
                width: 180px;
            }

            .sponsor-card .sponsor-card-title {
                font-size: clamp(1rem, 2.5vw, 1.4rem);
            }

            .sponsors-logos {
                gap: 2.5rem;
                flex-direction: column;
                padding: 1.5rem;
            }

            .sponsor-logo {
                width: 120px;
            }

            .sponsored-by {
                font-size: clamp(1rem, 2.5vw, 1.6rem);
            }

            .logo-container {
                width: 240px;
                height: 240px;
            }

            .show-logo {
                width: 200px;
            }

            .logo-glow {
                width: 280px;
                height: 280px;
            }

            .magic-circle {
                width: 350px;
                height: 350px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <div class="bg-gradient"></div>

    <div class="particles" id="particles"></div>

    <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
         BASMALA - ALWAYS AT TOP (Islamic tradition)
         بسم الله الرحمن الرحيم
         "In the name of Allah, the Most Gracious, the Most Merciful"
         ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
    <div class="basmala" id="basmala">بسم الله الرحمن الرحيم</div>

    <div class="scene-container">
        <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
             PHASE 1: SPONSOR INTRODUCTION (0-12 seconds)
             Individual cards slide in left/right, then combine
             SPONSORS VISIBLE FOR 5.5+ SECONDS (crucial for brand awareness)
             ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
        <div class="sponsors-phase" id="sponsorsPhase">
            <!-- Individual Sponsor Cards -->
            <!-- Card 1: تلفزيون البحرين (BTV) - enters from left at 0s, exits at 2s -->
            <div class="sponsor-card" id="sponsorCard1">
                <div class="sponsor-card-content">
                    <div class="sponsor-card-title">تلفزيون البحرين</div>
                    <img src="{{ asset('images/btv-logo-ar.png') }}" alt="تلفزيون البحرين" class="sponsor-card-logo">
                </div>
            </div>

            <!-- Card 2: Beyon Money - enters from right at 2s, exits at 4s -->
            <div class="sponsor-card" id="sponsorCard2">
                <div class="sponsor-card-content">
                    <div class="sponsor-card-title">Beyon Money</div>
                    <img src="{{ asset('images/beyon-money-logo-wide.png') }}" alt="Beyon Money" class="sponsor-card-logo">
                </div>
            </div>

            <!-- Combined Display: Both sponsors together (4s-11s) - LONGEST PHASE -->
            <div class="sponsored-by" id="sponsoredByText">برعاية</div>
            <div class="sponsors-logos" id="sponsorsLogos">
                <img src="{{ asset('images/btv-logo-ar.png') }}" alt="تلفزيون البحرين" class="sponsor-logo" id="sponsor1">
                <img src="{{ asset('images/beyon-money-logo-wide.png') }}" alt="Beyon Money" class="sponsor-logo" id="sponsor2">
            </div>
        </div>

        <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
             PHASE 2: MAGICAL TRANSITION (12-14.5 seconds)
             Sponsors fade out, magical circles & sparkles appear
             ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
        <div class="magic-transition" id="magicTransition">
            <div class="magic-circle inner" id="magicCircleInner"></div>
            <div class="magic-circle outer" id="magicCircleOuter"></div>
            <div class="magic-ring" id="magicRing1" style="width: 120%; height: 120%;"></div>
            <div class="magic-ring" id="magicRing2" style="width: 140%; height: 140%;"></div>
            <div class="magic-ring" id="magicRing3" style="width: 160%; height: 160%;"></div>
        </div>

        <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
             PHASE 3: SHOW LOGO REVEAL (14.5-17.5 seconds)
             AlSarya TV logo spins and reveals with title/subtitle
             ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
        <div class="show-logo-phase" id="showLogoPhase">
            <div class="logo-container">
                <div class="logo-glow" id="logoGlow"></div>
                @if(file_exists(public_path('images/alsarya-logo-2026-1.png')))
                    <img src="{{ asset('images/alsarya-logo-2026-1.png') }}" alt="برنامج السارية" class="show-logo" id="showLogo">
                @else
                    <img src="{{ asset('images/alsarya-logo-2026-tiny.png') }}" alt="برنامج السارية" class="show-logo" id="showLogo">
                @endif
            </div>
            <h1 class="show-title" id="showTitle">برنامج السارية</h1>
            <p class="show-subtitle" id="showSubtitle">مسابقة رمضانية حصرية</p>
        </div>
    </div>

    <!-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
         PHASE 4: FADE TO OBLIVION (17.5-20 seconds)
         Screen fades to black with vignette, redirects to registration
         ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
    <div class="vignette" id="vignette"></div>
    <div class="oblivion-overlay" id="oblivionOverlay"></div>

    <script>
        (function() {
            const particles = document.getElementById('particles');
            const sponsorCount = window.innerWidth > 768 ? 40 : 20;

            for (let i = 0; i < sponsorCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 4 + 's';
                particle.style.animationDuration = (3 + Math.random() * 2) + 's';
                particles.appendChild(particle);
            }
        })();

        function createSparkles(container, count) {
            const sparkles = [];
            for (let i = 0; i < count; i++) {
                const sparkle = document.createElement('div');
                sparkle.className = 'sparkle';
                const angle = (i / count) * Math.PI * 2;
                const radius = 150 + Math.random() * 100;
                sparkle.style.left = `calc(50% + ${Math.cos(angle) * radius}px)`;
                sparkle.style.top = `calc(50% + ${Math.sin(angle) * radius}px)`;
                container.appendChild(sparkle);
                sparkles.push(sparkle);
            }
            return sparkles;
        }

        function animateSplash() {
            const timeline = [];

            // Display basmala immediately and keep it visible throughout entire animation
            const basmala = document.getElementById('basmala');
            basmala.style.opacity = '1';
            basmala.style.animation = 'basmalaFloat 4s ease-in-out infinite';
            basmala.style.pointerEvents = 'none';

            // PHASE 1: Individual Sponsor Cards + Combined Display (0-12s)
            // EXTENDED FOR SPONSOR LOGO VISIBILITY
            timeline.push(() => {
                const sponsorsPhase = document.getElementById('sponsorsPhase');
                const sponsorCard1 = document.getElementById('sponsorCard1');
                const sponsorCard2 = document.getElementById('sponsorCard2');
                const sponsoredBy = document.getElementById('sponsoredByText');
                const sponsorsLogos = document.getElementById('sponsorsLogos');
                const sponsor1 = document.getElementById('sponsor1');
                const sponsor2 = document.getElementById('sponsor2');

                sponsorsPhase.style.opacity = '1';

                // 0-2s: Display Sponsor 1 card (BTV) individually
                setTimeout(() => {
                    sponsorCard1.style.animation = 'cardSlideInFromLeft 1.2s cubic-bezier(0.34, 1.56, 0.64, 1) forwards';
                }, 100);

                // 2-4s: Replace with Sponsor 2 card (Beyon Money)
                setTimeout(() => {
                    sponsorCard1.style.animation = 'cardSlideOut 0.8s ease-in forwards';
                }, 2000);

                setTimeout(() => {
                    sponsorCard2.style.animation = 'cardSlideInFromRight 1.2s cubic-bezier(0.34, 1.56, 0.64, 1) forwards';
                }, 2000);

                // 4-5s: Transition to combined display
                setTimeout(() => {
                    sponsorCard2.style.animation = 'cardSlideOut 0.8s ease-in forwards';
                }, 4000);

                setTimeout(() => {
                    sponsorsLogos.style.opacity = '1';
                    sponsoredBy.style.animation = 'sponsorFadeIn 0.8s ease-out forwards';
                }, 4200);

                // 4.5-5.5s: Animate both sponsor logos together
                setTimeout(() => {
                    sponsor1.style.animation = 'sponsorLogoIn 1s cubic-bezier(0.34, 1.56, 0.64, 1) forwards';
                }, 4500);

                setTimeout(() => {
                    sponsor2.style.animation = 'sponsorLogoIn 1s cubic-bezier(0.34, 1.56, 0.64, 1) forwards';
                }, 4800);

                // 5.5-11s: HOLD SPONSOR LOGOS VISIBLE FOR 5.5 SECONDS!
                // Give plenty of time for images to load and display
                setTimeout(() => {
                    // Keep sponsors visible - they're the stars!
                    console.log('Sponsors prominently displayed - 5.5+ seconds visible');
                }, 5500);

                // 11-12s: Fade out sponsors to transition to magic
                setTimeout(() => {
                    sponsoredBy.style.transition = 'opacity 1s ease-out';
                    sponsoredBy.style.opacity = '0';
                    sponsor1.style.transition = 'opacity 1s ease-out';
                    sponsor1.style.opacity = '0';
                    sponsor2.style.transition = 'opacity 1s ease-out';
                    sponsor2.style.opacity = '0';
                }, 11000);
            });

            // PHASE 2: Fade sponsors & start magic (12-14.5s)
            timeline.push(() => {
                const sponsorsPhase = document.getElementById('sponsorsPhase');
                const magicTransition = document.getElementById('magicTransition');

                sponsorsPhase.style.transition = 'opacity 1s ease-out';
                sponsorsPhase.style.opacity = '0';

                setTimeout(() => {
                    magicTransition.style.opacity = '1';

                    const innerCircle = document.getElementById('magicCircleInner');
                    const outerCircle = document.getElementById('magicCircleOuter');
                    const rings = [
                        document.getElementById('magicRing1'),
                        document.getElementById('magicRing2'),
                        document.getElementById('magicRing3')
                    ];

                    innerCircle.style.animation = 'magicCircleExpand 1.2s ease-out forwards';
                    outerCircle.style.animation = 'magicCircleExpand 1.5s ease-out 0.2s forwards';

                    rings.forEach((ring, i) => {
                        ring.style.animation = `ringExpand ${1 + i * 0.3}s ease-out ${i * 0.15}s forwards`;
                    });

                    const sparkles = createSparkles(magicTransition, 20);
                    sparkles.forEach((sparkle, i) => {
                        setTimeout(() => {
                            sparkle.style.animation = 'sparkleBurst 0.6s ease-out forwards';
                        }, i * 50);
                    });
                }, 400);
            });

            // PHASE 3: Reveal show logo (14.5-17.5s)
            timeline.push(() => {
                const magicTransition = document.getElementById('magicTransition');
                const showLogoPhase = document.getElementById('showLogoPhase');
                const logoGlow = document.getElementById('logoGlow');
                const showLogo = document.getElementById('showLogo');
                const showTitle = document.getElementById('showTitle');
                const showSubtitle = document.getElementById('showSubtitle');

                magicTransition.style.transition = 'opacity 0.5s ease-out';
                magicTransition.style.opacity = '0';

                showLogoPhase.style.opacity = '1';

                setTimeout(() => {
                    logoGlow.style.opacity = '1';
                }, 200);

                setTimeout(() => {
                    showLogo.style.animation = 'logoReveal 1.2s cubic-bezier(0.34, 1.56, 0.64, 1) forwards';
                }, 300);

                setTimeout(() => {
                    showTitle.style.animation = 'textFadeIn 0.8s ease-out forwards';
                }, 1000);

                setTimeout(() => {
                    showSubtitle.style.animation = 'textFadeIn 0.8s ease-out forwards';
                }, 1200);
            });

            // PHASE 4: Fade to oblivion (17.5-20s)
            timeline.push(() => {
                const vignette = document.getElementById('vignette');
                const oblivionOverlay = document.getElementById('oblivionOverlay');
                const showLogoPhase = document.getElementById('showLogoPhase');
                const basmala = document.getElementById('basmala');

                // KEEP BASMALA VISIBLE - it always stays at top
                basmala.style.opacity = '1';

                vignette.style.animation = 'fadeToBlack 1.5s ease-in forwards';

                setTimeout(() => {
                    oblivionOverlay.style.animation = 'fadeToBlack 2s ease-in forwards';
                }, 500);

                setTimeout(() => {
                    showLogoPhase.style.animation = 'finalFade 1.5s ease-in forwards';
                }, 1000);
            });

            // Execute timeline - EXTENDED SPONSOR VISIBILITY
            timeline[0](); // 0s - Individual sponsor cards + combined display (0-12s)
            setTimeout(timeline[1], 12000); // 12s - Magic transition starts
            setTimeout(timeline[2], 14500); // 14.5s - Show logo reveal
            setTimeout(timeline[3], 17500); // 17.5s - Oblivion fade

            // Redirect after complete (total animation: 20 seconds)
            // Mark that splash has been shown
            sessionStorage.setItem('splashShown', 'true');

            setTimeout(() => {
                // Redirect to registration form after splash
                window.location.href = '/';
            }, 20000);
        }

        // Check if splash has already been shown in this session
        function shouldShowSplash() {
            // Allow forcing splash with ?force-splash=true
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('force-splash') === 'true') {
                sessionStorage.removeItem('splashShown');
                return true;
            }

            // Check if splash was already shown in this session
            const splashShown = sessionStorage.getItem('splashShown');
            if (splashShown === 'true') {
                console.log('Splash already shown in this session, skipping...');
                return false;
            }

            return true;
        }

        // Skip handlers
        function skipToRegistration() {
            sessionStorage.setItem('splashShown', 'true');
            window.location.href = '/?skip-splash=true';
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') skipToRegistration();
        });

        document.addEventListener('click', () => {
            skipToRegistration();
        });

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && sessionStorage.getItem('splashShown') === 'true') {
                skipToRegistration();
            }
        });

        // Start animation only if splash should be shown
        window.addEventListener('load', () => {
            if (shouldShowSplash()) {
                animateSplash();
            } else {
                // Skip splash and go directly to registration
                skipToRegistration();
            }
        });
    </script>
</body>
</html>
