<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="description"
        content="{{ config('app.ar_translations.description') ?? 'البرنامج الرئيسي المباشر على شاشة تلفزيون البحرين خلال شهر رمضان المبارك. ' }}" />
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

    <title>{{ config('app.ar_translations.title') ?? 'السارية' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=tajawal:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Additional Styles -->
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet" />

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/css/splash-screen.css',
        'resources/css/thank-you-screen.css'
    ])

    <!-- External libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js" defer></script>

    <style>
        body {
            background-image: url('{{ asset("images/bahrain-bay.jpg") }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: 'Tajawal', sans-serif;
            background-repeat: no-repeat;
            background-color: #1a1a1a;
            position: relative;
            isolation: isolate;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: background-image 0.3s ease-in-out;
            overflow-x: hidden;
        }

        main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .success-message {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(16, 185, 129, 0.9);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 9999;
            animation: fadeIn 0.5s ease-out forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        #confetti-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 100;
        }

        .content-container {
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        footer {
            width: 100%;
            margin-top: auto;
        }

        /* Logo animation */
        .logo-entrance {
            animation: logoSlideIn 1s ease-out forwards;
        }

        @keyframes logoSlideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Forms entrance */
        .forms-entrance {
            animation: formsFadeIn 1.2s ease-out 0.5s forwards;
            opacity: 0;
        }

        @keyframes formsFadeIn {
            to {
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            main { padding-bottom: 0; }
        }
    </style>
</head>

<body class="rtl relative" dir="rtl">
    <!-- Optional Splash Screen -->
    <div id="splash-con"></div>

    <!-- Success message container -->
    <div class="success-message" id="success-message"></div>

    @include('layouts.header')

    <main id="main-content" class="overflow-hidden">
        <!-- Logo section -->
        <div id="logo-section" class="text-center py-4 logo-entrance">
            <div class="mx-auto" style="max-width: 180px;">
                <img src="{{ asset('images/alsarya-tv-show-logo.png') }}" alt="Logo" class="mx-auto h-24 md:h-32 drop-shadow-lg" />
            </div>
        </div>

        <!-- Forms section - Always visible -->
        <div id="forms-section" class="content-container forms-entrance">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 md:p-8 shadow-2xl border border-white/20">
                    <div class="text-center mb-6">
                        <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">سجل الآن للمشاركة</h1>
                        <p class="text-slate-200 text-sm md:text-base">برنامج السارية 2026</p>
                    </div>

                    @include('calls.form-toggle')
                </div>

                @include('sponsors')
            </div>
        </div>
    </main>

    @include('layouts.footer', ['hits' => $hits ?? 200])

    <script>
        // Initialize splash screen if needed
        window.splashScreenConfig = {
            duration: 2,
            logoSrc: '{{ asset("images/bahrain-tv-sm.png") }}',
            container: 'splash-con',
            particleCount: 0  // No particles - clean and simple
        };

        // Ensure main content is visible immediately
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.getElementById('main-content');
            mainContent.style.opacity = '1';

            // Auto-hide splash screen after animation
            setTimeout(() => {
                const splash = document.getElementById('splash-con');
                if (splash) {
                    splash.style.display = 'none';
                }
            }, 2500);
        });
    </script>

    @vite(['resources/js/app.js'])
</body>

</html>