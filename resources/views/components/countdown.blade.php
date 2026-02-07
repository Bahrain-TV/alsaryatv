<div class="countdown-section">
    <div id="flipdown-{{ $id ?? 'main' }}" class="flipdown flipdown__theme-dark"></div>
</div>

@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flipdown@0.3.2/dist/flipdown.min.css" />
        <style>
            :root {
                --countdown-label-color: oklch(0.35 0 0); /* Light mode: dark gray/charcoal text */
                --countdown-rotor-color: oklch(0.2 0 0); /* Light mode: dark numbers */
            }

            @media (prefers-color-scheme: dark) {
                :root {
                    --countdown-label-color: oklch(0.96 0 0); /* Dark mode: nearly white text */
                    --countdown-rotor-color: oklch(0.98 0 0); /* Dark mode: bright white numbers */
                }
            }

            .countdown-section {
                display: flex;
                flex-direction: column;
                align-items: center;
                margin: 2rem 0;
                width: 100%;
            }
            /* Custom styling to ensure it looks good and doesn't stack incorrectly */
            .flipdown {
                font-family: 'Tajawal', sans-serif !important;
                margin: 0 auto !important;
                direction: ltr !important;
            }
            .flipdown .rotor-group-heading::before {
                color: var(--countdown-label-color) !important;
                font-family: 'Tajawal', sans-serif !important;
                font-weight: 600 !important;
            }
            /* Improve rotor (number) visibility in both light and dark modes */
            .flipdown .rotor-digit {
                color: var(--countdown-rotor-color) !important;
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
        const targetDate = new Date("{{ $targetDate ?? '2026-02-26T00:00:00+03:00' }}").getTime() / 1000;
        new FlipDown(targetDate, "flipdown-{{ $id ?? 'main' }}", {
            theme: 'dark'
        }).start();
    });
</script>
@endpush