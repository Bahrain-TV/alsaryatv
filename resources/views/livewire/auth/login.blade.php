<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />
@php
    use Illuminate\Support\Facades\Route;
@endphp

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />
<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

    <form wire:submit="login" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Email address')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="email@example.com"
        />
        <x-validation-errors class="mb-4" />

        <!-- Password -->
        <div class="relative">
            <flux:input
                wire:model="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Password')"
            />
        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

            @if (Route::has('password.request'))
                <flux:link class="absolute right-0 top-0 text-sm" :href="route('password.request')" wire:navigate>
                    {{ __('Forgot your password?') }}
                </flux:link>
            @endif
        </div>
        <form method="POST" action="{{ route('login') }}">
            @csrf

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" :label="__('Remember me')" />
            <div>
                <x-label for="email" value="{{ __('Email address') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="email@example.com" />
            </div>

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">{{ __('Log in') }}</flux:button>
        </div>
    </form>
            <div class="relative">
                <div>
                    <x-label for="password" value="{{ __('Password') }}" />
                    <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" placeholder="Password" />
                </div>

    @if (Route::has('register'))
        <div class="space-x-1 text-center text-sm text-zinc-600">
            {{ __('Don\'t have an account?') }}
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    @endif
</div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" wire:model="remember" />
                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                <button type="submit" wire:submit="login" variant="primary" class="w-full">{{ __('Log in') }}</button>

                @if (Route::has('register'))
                    <div class="space-x-1 text-center text-sm text-zinc-600">
                        {{ __('Dont have an account?') }}
                        <a href="{{ route('register') }}" wire:navigate>{{ __('Sign up') }}</a>
                    </div>
                @endif
            </div>
        </form>

        <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

        <x-auth-session-status class="text-center" :status="session('status')" />
    </x-authentication-card>
</x-guest-layout>
