@component('mail::message')
# AlSaryaTV Statistics Report

Please find the attached statistics report for AlSaryaTV.

@isset($content)
    @if(is_string($content))
        {!! $content !!}
    @else
        @foreach($content as $section => $text)
            ## {{ $section }}

            {!! $text !!}
        @endforeach
    @endif
@else
    <!-- Fallback content -->
    <p>No statistics available.</p>
@endisset

Thanks,<br>
{{ config('app.name') }} System
@endcomponent