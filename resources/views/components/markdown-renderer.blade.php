@php
    use Illuminate\Support\Str;
@endphp

<div class="prose prose-invert max-w-none font-tajawal">
    {!! Str::markdown($content) !!}
</div>
