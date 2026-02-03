<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>برنامج السارية - وبس خلصنا</title>

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
            background-image: url("{{ asset('images/seef-district-from-sea.jpg') }}");
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
            animation-delay: 3s;
            animation-fill-mode: forwards;
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
                <lottie-player src="lottie/crecent-moon-ramadan.json" background="transparent" speed="1" style="width: 200px; height: 200px;" loop autoplay></lottie-player>
            </div>
            <h1 class="text-3xl font-bold text-center mb-4">وبس خلصنا ...</h1>
            <p class="text-xl text-center mb-6">نشوفكم جريب.</p>
            {{-- Removed progress bar --}}
            <div class="mt-6 text-center">
                <a href="/" class="text-indigo-400 hover:text-indigo-300 text-sm">
                    موقع برنامج السارية</a>
            </div>
            {{-- Removed counters and countdown --}}

        </div>
    </div>

    <div>
        <section class="py-3 sponsors-container" id="sponsors">
            <div class="container mx-auto px-4">
                <div class="flex flex-col items-center justify-center">
                    <h2 class="text-2xl sm:text-4xl font-bold text-white mb-6 intro-dance">
                        هذا البرنامج يأتيكم برعاية
                    </h2>
    
                    <!-- Display logos side by side with animation -->
                    <div class="w-auto flex flex-row space-x-7 gap-10 animate__animated animate__fadeInUp"
                        id="sponsors-logos">
                        <div class="intro-dance" id="left-sponsor-logo">
                            <x-bapco-logo class="w-36 h-auto lg:w-60 pt-0" />
                        </div>
                        <div class="intro-dance" id="right-sponsor-logo">
                            <img src="{{ asset('images/beyon-money-logo-wide.png') }}" alt="Beyon Money بيون موني"
                                class="w-40 md:w-44 lg:w-60 h-auto pt-2 pr-2" />
                        </div>
                    </div>
                    <div class="w-auto flex flex-row space-x-7 gap-10 animate__animated animate__fadeInUp intro-dance"
                        id="sponsors-logos">
                        <img src="{{ asset('images/suhail-media-logo-color-white.png') }}" alt="Suhail Media"
                            class="mx-auto mb-4 h-24" />
    
                    </div>
    
                </div>
            </div>
        </section>
    </div>

    <footer id="footer-closed" class="absolute bottom-0 w-full bg-black bg-opacity-50 text-white py-4 z-10 rtl">
        <div class="container mx-auto px-4">
            <!-- Top section with branding and visitor stats -->
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-3">
                <!-- Branding section -->
                <div class="text-center md:text-start">
                    <p>
                        <a href="#"
                            class="opacity-80 hover:opacity-100 transition-all duration-300 transform hover:-translate-y-1 block md:inline-block text-sm font-tajawal">
                            {{ config('app.ar_translations.footer_title') ?? config('app.name_ar_footer', 'أوتوكيولايف©️ ' .
                            date('Y')) }}
                        </a>
                    </p>
                    <p>
                        <a href="#"
                            class="hidden md:inline-block opacity-80 hover:opacity-100 transition-all duration-300 transform hover:-translate-y-1 ml-3 text-sm font-tajawal">
                            {{ config('app.en_translations.footer_title') ?? config('app.name_en_footer', 'AutoQLive©️ ' .
                            date('Y')) }}
                        </a>
                    </p>
                </div>
    
                <!-- Stats section -->
                @if(isset($hits))
                <div class="flex flex-wrap gap-2 justify-center md:justify-end">
                    <p class="opacity-90 bg-black bg-opacity-50 px-2 py-1 rounded-full text-xs">
                        إجمالي المشاركات حتى الآن: <span class="text-orange-300">{{ $totalHits ?? 0 }}</span>
                    </p>
                    <span class="opacity-50 text-gray-300 hidden md:inline">⚪️</span>
                    <p class="opacity-90 bg-black bg-opacity-50 px-2 py-1 rounded-full text-xs">
                        الزيارات: <span class="text-orange-300">{{ $hits ?? 0 }}</span>
                    </p>
                </div>
                @endif
            </div>
    
            <!-- Bottom section with copyright and links -->
            <div class="border-t border-gray-700 border-opacity-25 pt-2 flex flex-col md:flex-row items-center">
                <p class="opacity-90 text-xs transition-opacity hover:opacity-100 mb-2 md:mb-0 text-center md:text-end">
                    تصميم وبرمجة فريق عمل برنامج الســاريــة ©️ 2019 - 2025
    
                <p
                    class="opacity-90 text-xs transition-opacity hover:opacity-100 mb-2 md:mb-0 mx-4 text-center md:text-start">
                    تلفزيون البحرين - وزارة الإعلام
                </p>
                </p>
    
                <div class="flex items-center gap-2">
                    <p class="opacity-90 text-xs transition-opacity hover:opacity-100 mb-2 md:mb-0 text-center md:text-end">
                        <a href="{{ route('privacy') }}"
                            class="text-xs text-indigo-300 hover:text-indigo-900 hover:bg-indigo-400 px-2 py-1 rounded font-bold">
                            سياسة الخصوصية
                        </a>
                    </p>
    
                    <p class="opacity-90 text-xs transition-opacity hover:opacity-100 mb-2 md:mb-0 text-center md:text-end">
                        <span class="opacity-90 text-xs bg-black bg-opacity-50 px-2 py-1 rounded-full">
                            الإصدار <span class="text-indigo-300">{{ config('app.version', 'v1.0') }}</span>
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // GSAP Animation for dramatic outro
            const tl = gsap.timeline({ 
                delay: 2,
            });
        });
    </script>
</body>
</html>