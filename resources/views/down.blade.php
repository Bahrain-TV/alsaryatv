<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>صيانة مجدولة - برنامج السارية</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=changa:400,600,700&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=tajawal:400,500,700&display=swap" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <!-- Include Lottie Player library -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

    <style>
        :root {
            --bg: #0c0f14;
            --panel: rgba(12, 15, 20, 0.82);
            --panel-border: rgba(255, 255, 255, 0.08);
            --ink: #f8fafc;
            --muted: rgba(248, 250, 252, 0.7);
            --accent: #ffb703;
            --accent-2: #fb8500;
            --cool: #8ecae6;
        }

        body {
            background-color: var(--bg);
            background-image:
                linear-gradient(120deg, rgba(12, 15, 20, 0.88), rgba(12, 15, 20, 0.65)),
                radial-gradient(circle at 20% 20%, rgba(251, 133, 0, 0.18), transparent 45%),
                radial-gradient(circle at 80% 10%, rgba(142, 202, 230, 0.16), transparent 40%),
                url("{{ asset('images/bahrain-bay.jpg') }}");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: 'Tajawal', sans-serif;
            color: var(--ink);
        }

        .down-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 48px 20px;
        }

        .down-card {
            width: min(920px, 100%);
            background: var(--panel);
            border: 1px solid var(--panel-border);
            border-radius: 28px;
            backdrop-filter: blur(14px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.45);
            padding: clamp(24px, 4vw, 48px);
            display: grid;
            gap: 24px;
            animation: fadeIn 0.6s ease-out;
        }

        .down-head {
            display: flex;
            align-items: center;
            gap: 24px;
            flex-wrap: wrap;
        }

        .down-brand {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 1 1 280px;
        }

        .down-title {
            font-family: 'Changa', sans-serif;
            font-size: clamp(1.6rem, 3vw, 2.4rem);
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.2px;
        }

        .down-subtitle {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: 1rem;
        }

        .status-pill {
            padding: 8px 16px;
            border-radius: 999px;
            background: rgba(255, 183, 3, 0.12);
            border: 1px solid rgba(255, 183, 3, 0.35);
            color: var(--accent);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .down-grid {
            display: grid;
            gap: 24px;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }

        .countdown-card {
            border-radius: 20px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            display: grid;
            gap: 12px;
        }

        .countdown-value {
            font-family: 'Changa', sans-serif;
            font-size: clamp(2.4rem, 5vw, 3.5rem);
            font-weight: 700;
            color: var(--accent);
            line-height: 1;
        }

        .countdown-label {
            color: var(--muted);
            font-size: 0.95rem;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background-color: rgba(255, 255, 255, 0.12);
            border-radius: 999px;
            overflow: hidden;
            position: relative;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent), var(--accent-2));
            border-radius: 999px;
            transition: width 0.4s linear;
            width: 0%;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
            overflow: hidden;
            position: relative;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #4F46E5, #9333EA);
            border-radius: 3px;
            transition: width 3s linear;
            width: 0%;
        }

        .count-number {
            font-feature-settings: "tnum";
            font-variant-numeric: tabular-nums;
        }

        .fun-message {
            min-height: 72px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: slideIn 0.6s ease-in-out;
            font-size: 1.25rem;
            line-height: 1.6;
            text-align: center;
        }

        @keyframes slideIn {
            0% {
                opacity: 0;
                transform: translateX(-20px);
            }
            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideOut {
            0% {
                opacity: 1;
                transform: translateX(0);
            }
            100% {
                opacity: 0;
                transform: translateX(20px);
            }
        }

        .message-exit {
            animation: slideOut 0.4s ease-in-out forwards;
        }

        .emoji-bounce {
            display: inline-block;
            animation: bounce 0.8s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body class="antialiased">
    <div class="down-shell" id="app">
        <div class="down-card">
            <div class="down-head">
                <div class="down-brand">
                    <lottie-player src="{{ asset('lottie/crecent-moon-ramadan.json') }}" background="transparent" speed=".1" style="width: 140px; height: 140px;" loop autoplay></lottie-player>
                    <div>
                        <div class="status-pill">صيانة مجدولة</div>
                        <h1 class="down-title">نجهز لكم تجربة أفضل</h1>
                        <p class="down-subtitle">نعمل حاليًا على تطوير وتحسين النظام لخدمتكم بشكل أفضل.</p>
                    </div>
                </div>
                <div class="countdown-card">
                    <div class="countdown-value"><span id="countdown">⏳</span></div>
                    <div class="countdown-label">سنعود قريبًا</div>
                    <div class="progress-bar">
                        <div class="progress-bar-fill" id="progress" style="width: 60%;"></div>
                    </div>
                </div>
            </div>

            <div class="down-grid">
                <div class="fun-message" id="funMessage">
                    <span class="emoji-bounce">⚙️</span> نعمل على تحديثات دورية لضمان أفضل أداء للنظام.
                </div>
                <div class="countdown-card">
                    <div class="countdown-label">شكرًا لزيارتكم</div>
                    <div class="countdown-value count-number" id="hits-counter"></div>
                    <a href="/" class="text-sm" style="color: var(--cool);">برنامج السارية - تلفزيون البحرين</a>
                </div>
            </div>
        </div>
    </div>

    {{-- @include('sponsors') --}}

    <script>
        // Professional maintenance messages
        const funMessages = [
            { text: 'نعمل على تحسين الأداء والاستقرار لخدمتكم بشكل أفضل.', emoji: '⚙️' },
            { text: 'جاري تطبيق التحديثات الأمنية والتقنية المجدولة.', emoji: '🔒' },
            { text: 'نقدر صبركم ونتطلع لخدمتكم قريبًا بتجربة محسّنة.', emoji: '🌟' },
        ];

        function setFunMessageOnce() {
            const messageEl = document.getElementById('funMessage');
            const message = funMessages[Math.floor(Math.random() * funMessages.length)];
            messageEl.innerHTML = `<span class="emoji-bounce">${message.emoji}</span> ${message.text}`;
        }

        document.addEventListener('DOMContentLoaded', () => {
            setFunMessageOnce();
            // Get the actual hits from session
            const hits = {{ session('hits', 1) }};
            const hitsCounter = document.getElementById('hits-counter');
            const progressBar = document.getElementById('progress');

            // Animate counter from 0 to actual hits
            let currentCount = 0;
            const duration = 1500; // 1.5 seconds
            const interval = 30; // Update every 30ms
            const steps = duration / interval;
            const increment = hits / steps;

            const counterInterval = setInterval(() => {
                currentCount += increment;
                if (currentCount >= hits) {
                    currentCount = hits;
                    clearInterval(counterInterval);
                }
                hitsCounter.textContent = Math.floor(currentCount).toLocaleString('ar-SA');
            }, interval);

            // Animate progress bar (aesthetic only)
            if (progressBar) {
                let progress = 0;
                setInterval(() => {
                    progress = (progress + 1) % 100;
                    progressBar.style.width = `${progress}%`;
                }, 200);
            }

            // Check if site is back online every 30 seconds (no auto-reload spam)
            setInterval(() => {
                fetch('/', { method: 'HEAD' })
                    .then(response => {
                        if (response.ok && response.status === 200) {
                            window.location.reload();
                        }
                    })
                    .catch(() => {
                        // Still in maintenance, do nothing
                    });
            }, 30000); // Check every 30 seconds
        });
    </script>
</body>
</html>