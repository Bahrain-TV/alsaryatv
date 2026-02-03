@push('styles')
    <style>
        body {
    background-image: url('{{ asset("images/seef-district-from-sea.jpg") }}');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    font-family: 'Tajawal', sans-serif;
    background-repeat: no-repeat;
    background-color: #1a1a1a; /* Fallback color */
    position: relative;
    isolation: isolate;
    min-height: 100vh;

    transition: background-image 0.3s ease-in-out;
}
</style>
@endpush

<div>
    <!-- The only way to do great work is to love what you do. - Steve Jobs -->
    {{-- @if(now()->isAfter('2025-02-27 21:00:00'))
        <div class="absolute w-full text-white items-center p-4 font-tajawal" id="beta">
            <p>بث تجريبي</p>
        </div>
    @endif --}}
</div>