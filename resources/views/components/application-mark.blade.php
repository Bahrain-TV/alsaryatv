@if(file_exists(public_path('images/alsarya-logo-2026-1.png')))
    <img src="{{ asset('images/alsarya-logo-2026-1.png') }}" alt="{{ config('app.name') }}" {{ $attributes->merge(['class' => 'h-12 w-auto']) }} />
@else
    <img src="{{ asset('images/alsarya-logo.png') }}" alt="{{ config('app.name') }}" {{ $attributes->merge(['class' => 'h-12 w-auto']) }} />
@endif
