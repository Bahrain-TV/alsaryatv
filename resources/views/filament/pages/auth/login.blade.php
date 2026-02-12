<x-filament-panels::page.simple>
    <x-slot name="card">
        <div class="w-full flex flex-col items-center mb-6">
            <img src="{{ asset('images/alsarya-logo-2026-tiny.png') }}" 
                 alt="السارية" 
                 class="h-20 w-auto object-contain mb-2"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
            />
            <div style="display:none;" class="text-center">
                <span class="text-3xl font-bold bg-gradient-to-r from-amber-400 to-amber-600 bg-clip-text text-transparent">
                    السارية
                </span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">تلفزيون البحرين</p>
        </div>

        <h2 class="text-center text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
            {{ $this->getHeading() }}
        </h2>

        @if ($this->getSubheading())
            <p class="mt-2 text-center text-sm text-gray-500 dark:text-gray-400">
                {{ $this->getSubheading() }}
            </p>
        @endif

        {{ $this->form }}

        <x-filament::button
            type="submit"
            form="mountedActionForm"
            class="w-full mt-6"
        >
            {{ __('filament::login.buttons.submit.label') }}
        </x-filament::button>

        @if (config('filament.auth.password.reset.enabled', false))
            <div class="mt-4 text-center">
                <a class="text-sm text-primary-600 hover:text-primary-500" href="{{ filament()->getResetPasswordUrl() }}">
                    {{ __('filament::login.buttons.request_password_reset.label') }}
                </a>
            </div>
        @endif

        @if (config('filament.auth.registration.enabled', false))
            <div class="mt-4 text-center">
                <a class="text-sm text-primary-600 hover:text-primary-500" href="{{ filament()->getRegistrationUrl() }}">
                    {{ __('filament::login.buttons.register.label') }}
                </a>
            </div>
        @endif
    </x-slot>
</x-filament-panels::page.simple>
