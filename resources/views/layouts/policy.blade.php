<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} - سياسة الخصوصية</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Global Tajawal Font -->
    <link rel="stylesheet" href="{{ asset('css/tajawal-font.css') }}">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-tajawal text-gray-900 antialiased">
    <div class="min-h-screen bg-gradient-to-b from-gray-900 to-gray-800">
        <header class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="relative flex items-start justify-stretch">
                    <div class="flex items-center" style="margin-inline-start: auto;">
                        <a href="{{ route('home') }}" class="flex items-start">
                            <img class="h-12 w-auto" src="{{ asset('images/btv-logo-ar.png') }}" alt="{{ config('app.name') }}">
                        </a>
                        <a href="{{ route('home') }}" class="flex items-start">
                            <img class="h-16 w-auto px-8" src="{{ asset('images/moi-logo-2024-sm.png') }}" alt="{{ config('app.name') }}">
                        </a>
                        <a href="{{ route('home') }}" class="flex items-start">
                            <img class="h-14 w-auto sm:h-4" src="{{ asset('images/alsarya-logo.png') }}" alt="{{ config('app.name') }}">
                        </a>
                    </div>
                </nav>
            </div>
        </header>

        <main class="py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-gray-900/90 backdrop-blur-sm shadow-xl rounded-2xl overflow-hidden">
                    <div class="px-8 py-12 md:p-12">
                        <div class="prose prose-lg max-w-none rtl:text-right">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center text-sm text-gray-300">
                    <p>{{ config('app.name_ar_footer') }}</p>
                </div>
            </div>
        </footer>
        @include('sponsors')
    </div>

    @stack('scripts')
</body>
</html>