<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Service Unavailable') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=tajawal:400,500,700&display=swap" rel="stylesheet" />
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Tajawal', 'sans-serif'],
                    },
                    colors: {
                        primary: '#1d4ed8', // Adjust to match brand
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f7fafc; /* Light gray bg */
            color: #2d3748; /* Dark gray text */
        }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen px-4">

    <div class="max-w-xl w-full text-center space-y-8 p-10 bg-white shadow-xl rounded-2xl border border-gray-100">
        <!-- Logo -->
        <div class="flex justify-center">
             <img src="{{ asset('images/alsarya-logo-2026-tiny.png') }}" class="h-24 w-auto drop-shadow-md" alt="AlSarya TV">
        </div>

        <!-- Title -->
        <h1 class="text-3xl font-bold text-gray-800">
            {{ __('Service Unavailable') }}
        </h1>

        <!-- Dynamic Message -->
        <div class="text-lg text-gray-600 space-y-2">
            @if(isset($exception) && $exception->getMessage())
                @foreach(explode("\n", $exception->getMessage()) as $line)
                    <p>{{ $line }}</p>
                @endforeach
            @else
                <p>{{{MAINTENANCE_MESSAGE}}}</p>
            @endif
        </div>

        <!-- Progress Indicator (Fake) -->
        <div class="relative pt-1">
            <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-indigo-200 animate-pulse">
                <div style="width: 100%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500"></div>
            </div>
            <p class="text-xs text-gray-400">Updating System...</p>
        </div>

        <!-- Footer -->
        <div class="text-sm text-gray-400 mt-8 font-light">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>

</body>
</html>
