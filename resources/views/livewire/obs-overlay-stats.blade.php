<style>
    .obs-overlay-wrap {
        position: fixed;
        left: 50%;
        bottom: 10vh;
        transform: translateX(-50%);
        width: min(64rem, 100%);
        padding: 0 1.5rem;
        z-index: 50;
    }

    .obs-overlay-float {
        animation: obsOverlayFloat 7s ease-in-out infinite 0.8s;
        will-change: transform;
    }

    .obs-overlay-panel {
        animation: obsOverlayEnter 0.7s ease-out both;
    }

    .obs-overlay-pulse {
        animation: obsOverlayPulse 1.8s ease-in-out infinite;
    }

    @keyframes obsOverlayEnter {
        0% {
            opacity: 0;
            transform: translateY(18px) scale(0.98);
        }
        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes obsOverlayFloat {
        0%,
        100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-8px);
        }
    }

    @keyframes obsOverlayPulse {
        0%,
        100% {
            opacity: 0.7;
            transform: scale(1);
        }
        50% {
            opacity: 1;
            transform: scale(1.25);
        }
    }
</style>

<div wire:poll.2s="refreshStats" class="obs-overlay-wrap">
    <div class="obs-overlay-float">
        <div class="obs-overlay-panel rounded-2xl border border-white/10 bg-black/70 p-6 shadow-lg backdrop-blur">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="obs-overlay-pulse inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                <p
                    class="text-sm font-semibold text-white"
                    data-obs-text
                    data-obs-en="{{ __('Live dashboard feed', [], 'en') }}"
                    data-obs-ar="{{ __('Live dashboard feed', [], 'ar') }}"
                    dir="auto"
                >
                    {{ __('Live dashboard feed', [], 'en') }}
                </p>
            </div>
            <span class="text-xs text-white/70">{{ __('Updated') }} {{ $lastUpdatedAt }}</span>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-white/5 p-4">
                <p
                    class="text-xs uppercase tracking-wide text-white/60"
                    data-obs-text
                    data-obs-en="{{ __('Total callers', [], 'en') }}"
                    data-obs-ar="{{ __('Total callers', [], 'ar') }}"
                    dir="auto"
                >
                    {{ __('Total callers', [], 'en') }}
                </p>
                <p
                    class="mt-2 text-2xl font-semibold text-white"
                    data-obs-text
                    data-obs-value="true"
                    data-obs-en="{{ number_format($totalCallers) }}"
                    dir="auto"
                >
                    {{ number_format($totalCallers) }}
                </p>
            </div>
            <div class="rounded-xl bg-white/5 p-4">
                <p
                    class="text-xs uppercase tracking-wide text-white/60"
                    data-obs-text
                    data-obs-en="{{ __('Today callers', [], 'en') }}"
                    data-obs-ar="{{ __('Today callers', [], 'ar') }}"
                    dir="auto"
                >
                    {{ __('Today callers', [], 'en') }}
                </p>
                <p
                    class="mt-2 text-2xl font-semibold text-white"
                    data-obs-text
                    data-obs-value="true"
                    data-obs-en="{{ number_format($todayCallers) }}"
                    dir="auto"
                >
                    {{ number_format($todayCallers) }}
                </p>
            </div>
            <div class="rounded-xl bg-white/5 p-4">
                <p
                    class="text-xs uppercase tracking-wide text-white/60"
                    data-obs-text
                    data-obs-en="{{ __('Total hits', [], 'en') }}"
                    data-obs-ar="{{ __('Total hits', [], 'ar') }}"
                    dir="auto"
                >
                    {{ __('Total hits', [], 'en') }}
                </p>
                <p
                    class="mt-2 text-2xl font-semibold text-white"
                    data-obs-text
                    data-obs-value="true"
                    data-obs-en="{{ number_format($totalHits) }}"
                    dir="auto"
                >
                    {{ number_format($totalHits) }}
                </p>
            </div>
        </div>

        <div class="mt-4 text-xs text-white/70">
            <span
                data-obs-text
                data-obs-en="{{ __('Win ratio', [], 'en') }}"
                data-obs-ar="{{ __('Win ratio', [], 'ar') }}"
                dir="auto"
            >
                {{ __('Win ratio', [], 'en') }}
            </span>
            :
            <span
                data-obs-text
                data-obs-value="true"
                data-obs-en="{{ $winRatio }}%"
                dir="auto"
            >
                {{ $winRatio }}%
            </span>
        </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const overlayPanel = document.querySelector('.obs-overlay-panel');
        if (!overlayPanel || !window.gsap || !gsap.plugins || !gsap.plugins.text) {
            return;
        }

        const cycleSeconds = 30;
        const transitionSeconds = 0.6;
        const arabicHoldSeconds = 6;
        const englishHoldSeconds = Math.max(
            1,
            cycleSeconds - (transitionSeconds * 2 + arabicHoldSeconds)
        );
        const introDelaySeconds = 1;
        const introCountSeconds = 1.6;
        const introLabelSeconds = 0.45;
        const introLabelStagger = 0.08;
        const introValueStagger = 0.12;

        const arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        const toArabicDigits = (value) =>
            value.replace(/\d/g, (digit) => arabicDigits[Number(digit)]);

        const formatNumber = (value, decimals) => {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }).format(value);
        };

        const collectTargets = () => {
            const targets = Array.from(overlayPanel.querySelectorAll('[data-obs-text]'));
            targets.forEach((element) => {
                const english = element.getAttribute('data-obs-en')?.trim() ||
                    element.textContent.trim();
                element.setAttribute('data-obs-en', english);

                if (element.dataset.obsValue === 'true') {
                    element.setAttribute('data-obs-ar', toArabicDigits(english));
                }
            });
            return targets;
        };

        const getLabelTargets = () => Array.from(
            overlayPanel.querySelectorAll('[data-obs-text]:not([data-obs-value="true"])')
        );

        const getValueTargets = () => Array.from(
            overlayPanel.querySelectorAll('[data-obs-text][data-obs-value="true"]')
        );

        const animateLocale = (locale) => {
            const targets = collectTargets();
            targets.forEach((element) => {
                const targetText = locale === 'ar'
                    ? element.getAttribute('data-obs-ar')
                    : element.getAttribute('data-obs-en');
                if (!targetText) {
                    return;
                }

                gsap.to(element, {
                    text: { value: targetText, delimiter: '' },
                    duration: transitionSeconds,
                    ease: 'power2.inOut',
                    overwrite: 'auto'
                });
            });
        };

        const setIntroBlankState = () => {
            getLabelTargets().forEach((element) => {
                gsap.set(element, { text: '' });
            });

            getValueTargets().forEach((element) => {
                const targetText = element.getAttribute('data-obs-en')?.trim() ||
                    element.textContent.trim();
                const zeroText = targetText.includes('%') ? '0%' : '0';
                gsap.set(element, { text: zeroText });
            });
        };

        const buildLabelIntro = () => {
            const targets = getLabelTargets();
            const tl = gsap.timeline();

            targets.forEach((element, index) => {
                const targetText = element.getAttribute('data-obs-en') || '';
                tl.to(element, {
                    text: { value: targetText, delimiter: '' },
                    duration: introLabelSeconds,
                    ease: 'power2.out',
                    overwrite: 'auto'
                }, index * introLabelStagger);
            });

            return tl;
        };

        const buildValueIntro = () => {
            const targets = getValueTargets();
            const tl = gsap.timeline();

            targets.forEach((element, index) => {
                const targetText = element.getAttribute('data-obs-en')?.trim() ||
                    element.textContent.trim();
                const cleanTarget = targetText.replace(/,/g, '');
                const decimals = cleanTarget.includes('.')
                    ? cleanTarget.split('.')[1].replace(/\D/g, '').length
                    : 0;
                const hasPercent = cleanTarget.includes('%');
                const numericTarget = Number(cleanTarget.replace(/[^0-9.]/g, '')) || 0;

                const state = { value: 0 };
                tl.to(state, {
                    value: numericTarget,
                    duration: introCountSeconds,
                    ease: 'power2.out',
                    onUpdate: () => {
                        const formatted = formatNumber(state.value, decimals);
                        const nextText = hasPercent ? `${formatted}%` : formatted;
                        gsap.set(element, { text: nextText });
                    }
                }, index * introValueStagger);
            });

            return tl;
        };

        if (window.obsOverlayTimeline) {
            window.obsOverlayTimeline.kill();
        }

        const timeline = gsap.timeline({ repeat: -1 });
        timeline.add(() => setIntroBlankState());
        timeline.to({}, { duration: introDelaySeconds });
        timeline.add(buildLabelIntro());
        timeline.add(buildValueIntro(), '>-0.05');
        timeline.to({}, { duration: englishHoldSeconds });
        timeline.add(() => animateLocale('ar'));
        timeline.to({}, { duration: arabicHoldSeconds });
        timeline.add(() => animateLocale('en'));
        timeline.to({}, { duration: transitionSeconds });

        window.obsOverlayTimeline = timeline;
    });
</script>
