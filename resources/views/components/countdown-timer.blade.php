<div class="countdown-timer-container">
    <div id="flipdown-{{ $id ?? 'timer' }}" class="flipdown flipdown__theme-dark"></div>
</div>

@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flipdown@0.3.2/dist/flipdown.min.css" />
        <style>
            .countdown-timer-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                margin: 2rem 0;
            }
            .flipdown {
                font-family: 'Tajawal', sans-serif !important;
                direction: ltr !important;
            }
            .flipdown .rotor-group:nth-child(1) .rotor-group-heading::before { content: 'يوم' !important; }
            .flipdown .rotor-group:nth-child(2) .rotor-group-heading::before { content: 'ساعة' !important; }
            .flipdown .rotor-group:nth-child(3) .rotor-group-heading::before { content: 'دقيقة' !important; }
            .flipdown .rotor-group:nth-child(4) .rotor-group-heading::before { content: 'ثانية' !important; }
            .flipdown .rotor-group-heading { font-family: 'Tajawal', sans-serif !important; }
        </style>
    @endpush
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/flipdown@0.3.2/dist/flipdown.min.js"></script>
    @endpush
@endonce

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const targetDate = new Date("{{ $targetDate ?? '2026-02-26T21:00:00+03:00' }}").getTime() / 1000;
        new FlipDown(targetDate, "flipdown-{{ $id ?? 'timer' }}", {
            theme: 'dark'
        }).start();
    });
</script>
@endpush
