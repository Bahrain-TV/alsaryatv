@if(file_exists(public_path('images/branding/logo.png')))
    <img src="{{ asset('images/branding/logo.png') }}" alt="{{ config('app.name') }}" {{ $attributes->merge(['class' => 'h-12 w-auto']) }} />
@else
    <img src="{{ asset('images/branding/logo.png') }}" alt="{{ config('app.name') }}" {{ $attributes->merge(['class' => 'h-12 w-auto']) }} />
@endif
