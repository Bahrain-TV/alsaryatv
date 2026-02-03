@push('styles')
    <style>

    </style>
@endpush
<div class="mx-auto" id="ads-banner">
    <!-- It is quality rather than quantity that matters. - Lucius Annaeus Seneca -->
        <div class="banner mx-auto my-24 p-8 rounded-lg bg-gray-200 relative cursor-pointer">
            <span class="artext text-red-800 font-tajawal font-black text-3xl md:text-5xl lg:text-5xl">ودك تحط إعلانك هني؟</span>
            <span class="absolute artextb text-red-800 font-tajawal font-black md:text-4xl lg:text-3xl">الأرقام تحجي ...</span>
            <span class="absolute logo text-7xl icon opacity-30">⛵️</span>
            <span class="text text-black font-semibold">ADVERTISE HERE NOW</span>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const banner = document.querySelector('.banner');
                const icon = document.querySelector('.icon');
                const text = document.querySelector('.text');
                const artext = document.querySelector('.artext');
                const artextb = document.querySelector('.artextb');

                // Initial setup
                gsap.set([text, artext], { yPercent: -200 });
                gsap.set(icon, { scale: 0, opacity: 0 });

                // Main animation loop
                const looper = gsap.timeline({
                    repeat: -1,
                    repeatDelay: 2,
                    onStart: () => {
                        gsap.set(banner, { scale: .6 });
                    }
                });

                looper
                    .to(artext, {
                        yPercent: 0,
                        opacity: 1,
                        duration: 2,
                        ease: "elastic.out(1, 0.5)"
                    })
                    .to(icon, {
                        scale: 1,
                        opacity: 1,
                        duration: 0.5,
                        ease: "back.out(1.7)"
                    }, "-=0.5")
                    .to(text, {
                        yPercent: 0,
                        opacity: 1,
                        duration: 1,
                        ease: "elastic.out(1, 0.5)"
                    }, "-=0.3")
                    .to([artext, text, icon], {
                        opacity: 0,
                        duration: 0.5,
                        delay: 2
                    })
                    .to([artext, text, icon], {
                        yPercent: -300,
                        duration: 0
                    })
                    .from([artextb, text], {
                        opacity: 0,
                        duration: 3,
                        delay: 1,
                        ease: "elastic.out(1, 0.5)"
                    });

                // Enhanced hover interactions
                banner.addEventListener('mouseenter', () => {
                    looper.pause();
                    gsap.to(banner, {
                        scale: 1.05,
                        backgroundColor: '#FFD700',
                        boxShadow: '0 0 30px rgba(255,215,0,0.3)',
                        duration: 0.3,
                        ease: "elastic.out(1, 0.5)"
                    });
                    gsap.to([artext, text], {
                        yPercent: 0,
                        opacity: 1,
                        scale: 1.5,
                        duration: 0.4,
                        ease: "back.out(1.7)"
                    });
                    gsap.to(icon, {
                        scale: 1.8,
                        xPercent: 350,
                        opacity: 1,
                        rotation: 360,
                        duration: 0.5,
                        ease: "back.out(2)"
                    });
                });

                banner.addEventListener('mouseleave', () => {
                    gsap.to(banner, {
                        scale: 1,
                        backgroundColor: '#d5d5d5',
                        boxShadow: 'none',
                        duration: 0.3,
                        ease: "power2.inOut"
                    });
                    gsap.to([artext, text, icon, artextb], {
                        scale: 1,
                        opacity: 0,
                        rotation: 0,
                        duration: 0.3,
                        onComplete: () => {
                            looper.restart();
                        }
                    });
                });

                banner.addEventListener('click', () => {
                    // play the callers card animation

                });
            });
        </script>
</div>
