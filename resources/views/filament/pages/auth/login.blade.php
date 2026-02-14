<x-filament-panels::page.simple>
    <x-slot name="card">
        <div class="w-full flex justify-center mb-4">
            <img src="{{ asset('images/alsarya-logo.png') }}" alt="{{ config('app.name', 'السارية') }}" class="h-16 w-auto" />
        </div>

        <h2 class="text-center text-2xl font-bold tracking-tight text-gray-950">
            {{ $this->getHeading() }}
        </h2>

        <p class="mt-2 text-center text-sm text-gray-500">
            {{ $this->getSubheading() }}
        </p>

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
