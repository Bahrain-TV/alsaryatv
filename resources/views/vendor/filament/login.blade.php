<x-filament-panels::layout.base :livewire="$livewire">
    @props([
        'heading' => __('filament-panels::pages/auth/login.title'),
        'subheading' => null,
    ])

    <style>
        /* Ensure Tajawal font is loaded for login page */
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap');
        
        body {
            font-family: 'Tajawal', sans-serif !important;
        }
        
        .fi-body, .fi-sidebar, .fi-main {
            font-family: 'Tajawal', sans-serif !important;
        }
        
        /* Override any other font definitions */
        .fi-label, .fi-input, .fi-btn {
            font-family: 'Tajawal', sans-serif !important;
        }
    </style>

    <div class="fi-simple-layout flex min-h-screen items-center justify-center">
        <div class="fi-simple-main-ctn w-full max-w-md space-y-8 p-8">
            <div class="flex justify-center">
                <x-filament-panels::logo />
            </div>

            <h2 class="fi-simple-heading text-center text-2xl font-bold tracking-tight">
                {{ $heading }}
            </h2>

            @if ($subheading)
                <p class="fi-simple-subheading text-center text-gray-500">
                    {{ $subheading }}
                </p>
            @endif

            {{ $slot }}
        </div>
    </div>
</x-filament-panels::layout.base>
