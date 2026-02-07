<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>لحظات وسنعود...</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=tajawal:400,500,600,bold&display=swap" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <!-- Include Lottie Player library -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

    <style>
        body {
            background-image: url("{{ asset('images/bahrain-bay.jpg') }}");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: 'Tajawal', sans-serif;
        }

        .success-card {
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.5s ease-in-out;
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
    </style>
</head>
<body class="antialiased">
    <div class="flex items-center justify-center px-4 py-12" id="app">
        <div class="success-card max-w-md w-full p-8 text-white">
            <div class="flex justify-center mb-4">
                <lottie-player src="lottie/crecent-moon-ramadan.json" background="transparent" speed=".1" style="width: 200px; height: 200px;" loop autoplay></lottie-player>
            </div>
            <h1 class="text-2xl font-bold text-center mb-4">
                السموحة، ضيعنا واير الچارچ واللي عندي منعوي.
            </h1>
            {{-- <p class="text-center mb-4">قاعدين نسحب على الأسماء ... دعواتكم 🤩</p> --}}
            {{-- <div class="progress-bar mb-4">
                <div class="progress-bar-fill" id="progress"></div>
            </div> --}}
            <div class="mt-6 text-center">
                <a href="/" class="text-indigo-400 hover:text-indigo-300 text-sm">
                    موقع برنامج السارية</a>
            </div>
            <div class="mt-6 text-center">
                <p class="text-sm">عدد الزيارات: <span id="hits-counter" class="count-number text-orange-300"></span></p>
                <p class="text-sm">شكثر وقت باقي؟: <span id="countdown" class="text-orange-300">
                    {{ session('seconds', 60) }}
                    </span> ثواني</p>
            </div>

        </div>
    </div>

    {{-- @include('sponsors') --}}

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Get the actual hits from session
            const hits = {{ session('hits', 1) }};
            const hitsCounter = document.getElementById('hits-counter');
            const progressBar = document.getElementById('progress');
            const countdownEl = document.getElementById('countdown');

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

            // Countdown and progress bar
            progressBar.style.width = '0%';

            // Start countdown
            let secondsLeft = {{ session('seconds', 60) }};
            progressBar.style.width = '100%';

            const countdownInterval = setInterval(() => {
                secondsLeft -= 1;
                countdownEl.textContent = secondsLeft;

                if (secondsLeft <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = '/'; // Redirect back to homepage
                }
            }, 1000);
        });
    </script>
</body>
</html>