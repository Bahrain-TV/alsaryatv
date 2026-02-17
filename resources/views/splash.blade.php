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
            background: #000;
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
            background: linear-gradient(135deg, #0a0a0a 0%, #1a0a1a 25%, #0d0d1a 50%, #1a0a1a 75%, #0a0a0a 100%);
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

        /* ===== PHASE 1: SPONSORS ===== */
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
            font-size: clamp(1.2rem, 3vw, 1.8rem);
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
            letter-spacing: 4px;
            text-transform: uppercase;
            opacity: 0;
            transform: translateY(20px);
        }

        .sponsors-logos {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4rem;
            flex-wrap: wrap;
        }

        .sponsor-logo {
            width: 140px;
            height: auto;
            opacity: 0;
            transform: scale(0.8) translateY(30px);
            filter: drop-shadow(0 0 20px rgba(255, 255, 255, 0.3));
            transition: filter 0.5s ease;
        }

        .sponsor-logo:hover {
            filter: drop-shadow(0 0 40px rgba(255, 255, 255, 0.6));
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
            border-color: rgba(168, 28, 46, 0.8);
            animation: none;
        }

        .magic-circle.outer {
            border-color: rgba(232, 215, 195, 0.5);
            animation: none;
        }

        .magic-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 1px solid rgba(168, 28, 46, 0.6);
            opacity: 0;
        }

        .sparkle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: #E8D7C3;
            border-radius: 50%;
            opacity: 0;
            box-shadow: 0 0 10px #E8D7C3, 0 0 20px #A81C2E;
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
            background: radial-gradient(circle, rgba(168, 28, 46, 0.4) 0%, rgba(232, 215, 195, 0.2) 40%, transparent 70%);
            opacity: 0;
            animation: glowPulse 2s ease-in-out infinite;
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
            filter: drop-shadow(0 20px 40px rgba(168, 28, 46, 0.6))
                    drop-shadow(0 0 30px rgba(232, 215, 195, 0.4));
            opacity: 0;
            transform: rotateY(180deg) scale(0.5);
        }

        .show-title {
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 800;
            background: linear-gradient(135deg, #E8D7C3 0%, #F5DEB3 40%, #A81C2E 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            opacity: 0;
            transform: translateY(20px);
            text-align: center;
        }

        .show-subtitle {
            font-size: clamp(1rem, 2.5vw, 1.3rem);
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
            opacity: 0;
            transform: translateY(15px);
            text-align: center;
        }

        /* ===== PHASE 4: FADE TO OBLIVION ===== */
        .oblivion-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at center, transparent 0%, #000 100%);
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
            box-shadow: inset 0 0 150px rgba(0, 0, 0, 0.9);
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
            .sponsors-logos {
                gap: 2rem;
                flex-direction: column;
            }

            .sponsor-logo {
                width: 100px;
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

    <div class="scene-container">
        <!-- PHASE 1: SPONSORS -->
        <div class="sponsors-phase" id="sponsorsPhase">
            <div class="sponsored-by" id="sponsoredByText">برعاية</div>
            <div class="sponsors-logos">
                <img src="{{ asset('images/btv-logo-ar.png') }}" alt="تلفزيون البحرين" class="sponsor-logo" id="sponsor1">
                <img src="{{ asset('images/beyon-money-logo-wide.png') }}" alt="Beyon Money" class="sponsor-logo" id="sponsor2">
            </div>
        </div>

        <!-- PHASE 2: MAGICAL TRANSITION -->
        <div class="magic-transition" id="magicTransition">
            <div class="magic-circle inner" id="magicCircleInner"></div>
            <div class="magic-circle outer" id="magicCircleOuter"></div>
            <div class="magic-ring" id="magicRing1" style="width: 120%; height: 120%;"></div>
            <div class="magic-ring" id="magicRing2" style="width: 140%; height: 140%;"></div>
            <div class="magic-ring" id="magicRing3" style="width: 160%; height: 160%;"></div>
        </div>

        <!-- PHASE 3: SHOW LOGO -->
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

    <!-- PHASE 4: OBLIVION -->
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

            // PHASE 1: Sponsors (0-3s)
            timeline.push(() => {
                const sponsorsPhase = document.getElementById('sponsorsPhase');
                const sponsoredBy = document.getElementById('sponsoredByText');
                const sponsor1 = document.getElementById('sponsor1');
                const sponsor2 = document.getElementById('sponsor2');

                sponsorsPhase.style.opacity = '1';

                setTimeout(() => {
                    sponsoredBy.style.animation = 'sponsorFadeIn 0.8s ease-out forwards';
                }, 300);

                setTimeout(() => {
                    sponsor1.style.animation = 'sponsorLogoIn 1s cubic-bezier(0.34, 1.56, 0.64, 1) forwards';
                }, 800);

                setTimeout(() => {
                    sponsor2.style.animation = 'sponsorLogoIn 1s cubic-bezier(0.34, 1.56, 0.64, 1) forwards';
                }, 1100);
            });

            // PHASE 2: Fade sponsors & start magic (3-4s)
            timeline.push(() => {
                const sponsorsPhase = document.getElementById('sponsorsPhase');
                const magicTransition = document.getElementById('magicTransition');

                sponsorsPhase.style.transition = 'opacity 0.8s ease-out';
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

            // PHASE 3: Reveal show logo (4.5-6.5s)
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

            // PHASE 4: Fade to oblivion (8-10s)
            timeline.push(() => {
                const vignette = document.getElementById('vignette');
                const oblivionOverlay = document.getElementById('oblivionOverlay');
                const showLogoPhase = document.getElementById('showLogoPhase');

                vignette.style.animation = 'fadeToBlack 1.5s ease-in forwards';

                setTimeout(() => {
                    oblivionOverlay.style.animation = 'fadeToBlack 2s ease-in forwards';
                }, 500);

                setTimeout(() => {
                    showLogoPhase.style.animation = 'finalFade 1.5s ease-in forwards';
                }, 1000);
            });

            // Execute timeline
            timeline[0](); // 0s - Sponsors start
            setTimeout(timeline[1], 3000); // 3s - Magic transition
            setTimeout(timeline[2], 4500); // 4.5s - Show logo
            setTimeout(timeline[3], 8000); // 8s - Oblivion

            // Redirect after complete
            setTimeout(() => {
                window.location.href = '/';
            }, 10500);
        }

        // Skip handlers
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') window.location.href = '/';
        });

        document.addEventListener('click', () => {
            window.location.href = '/';
        });

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) window.location.href = '/';
        });

        // Start animation
        window.addEventListener('load', animateSplash);
    </script>
</body>
</html>
