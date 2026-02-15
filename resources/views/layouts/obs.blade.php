<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0f172a" />

        <title>{{ config('app.name', 'AlSarya TV') }} - OBS Overlay</title>

        <style>
            html,
            body,
            main {
                background: transparent !important;
            }
        </style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

        <script>
            (function () {
                // Initialize theme management for OBS overlay
                if (window.ThemeManager) {
                    window.ThemeManager.init();
                } else {
                    // Fallback - force dark mode for OBS overlay
                    document.documentElement.classList.add('dark');
                    document.documentElement.dataset.theme = 'dark';
                }
            })();
        </script>
    </head>
    <body class="bg-transparent text-white">
        <main class="min-h-screen bg-transparent">
            @yield('content')
        </main>

        @livewireScripts
    </body>
</html>
