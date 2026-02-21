<div class="w-full">
    <section class="py-3 sm:py-4" id="sponsors">
        <div class="container mx-auto px-4">
            <div class="flex flex-col items-center justify-center">
                <h2 class="text-xl sm:text-2xl font-bold text-white mb-6 sm:mb-8 drop-shadow-lg">
                    هذا البرنامج يأتيكم برعاية
                </h2>

                <!-- Three sponsor logos — equal sizing, consistent presentation with text labels -->
                <div class="grid grid-cols-2 justify-items-center items-start gap-6 sm:gap-10 md:gap-14" id="sponsors-logos">

                    {{-- Bapco Energies — FIRST POSITION --}}
                    <div class="sponsor-card flex flex-col items-center justify-center gap-2 sm:gap-3 group" data-sponsor="bapco">
                        <div class="sponsor-logo-container flex items-center justify-center w-40 h-40 sm:w-48 sm:h-48 md:w-56 md:h-56 p-4 sm:p-6 bg-white/5 rounded-2xl border border-white/10 backdrop-blur-sm transition-all duration-300 group-hover:bg-white/10 group-hover:border-gold-500/30 group-hover:shadow-lg group-hover:shadow-gold-500/10">
                            <img src="{{ asset('images/bapco-energies.png') }}"
                                 alt="Bapco Energies"
                                 loading="lazy"
                                 class="sponsor-logo max-h-full max-w-full object-contain transition-transform duration-300 drop-shadow-lg" />
                        </div>
                        <span class="sponsor-name text-white/90 text-sm sm:text-base md:text-lg font-bold tracking-wide drop-shadow-md" dir="auto">بابكو للطاقة - Bapco Energies</span>
                    </div>

                    {{-- Al Salam Bank — SECOND POSITION --}}
                    <div class="sponsor-card flex flex-col items-center justify-center gap-2 sm:gap-3 group" data-sponsor="alsalam">
                        <div class="sponsor-logo-container flex items-center justify-center w-40 h-40 sm:w-48 sm:h-48 md:w-56 md:h-56 p-4 sm:p-6 bg-white/5 rounded-2xl border border-white/10 backdrop-blur-sm transition-all duration-300 group-hover:bg-white/10 group-hover:border-gold-500/30 group-hover:shadow-lg group-hover:shadow-gold-500/10">
                            <img src="{{ asset('images/alsalam-logo.svg') }}"
                                 alt="Al Salam Bank"
                                 loading="lazy"
                                 class="sponsor-logo max-h-full max-w-full object-contain transition-transform duration-300 drop-shadow-lg"
                                 style="filter: brightness(0) invert(1) sepia(0.15);" />
                        </div>
                        <span class="sponsor-name text-white/90 text-sm sm:text-base md:text-lg font-bold tracking-wide drop-shadow-md" dir="auto">مصرف السلام - Al Salam Bank</span>
                    </div>

                    {{-- Jasmi's — THIRD POSITION --}}
                    <div class="sponsor-card col-span-2 flex flex-col items-center justify-center gap-2 sm:gap-3 group" data-sponsor="jasmis">
                        <div class="sponsor-logo-container flex items-center justify-center w-40 h-40 sm:w-48 sm:h-48 md:w-56 md:h-56 p-4 sm:p-6 bg-white/5 rounded-2xl border border-white/10 backdrop-blur-sm transition-all duration-300 group-hover:bg-white/10 group-hover:border-gold-500/30 group-hover:shadow-lg group-hover:shadow-gold-500/10">
                            <img src="{{ asset('images/jasmis-logo.png') }}"
                                 alt="Jasmi's"
                                 loading="lazy"
                                 class="sponsor-logo max-h-full max-w-full object-contain transition-transform duration-300 drop-shadow-lg" />
                        </div>
                        <span class="sponsor-name text-white/90 text-sm sm:text-base md:text-lg font-bold tracking-wide drop-shadow-md" dir="auto">جاسميز - Jasmi's</span>
                    </div>

                </div>
            </div>
        </div>
    </section>
</div>

<style>
/* Sponsor Logo Animations */
@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    25% { transform: translateY(-8px) rotate(1deg); }
    50% { transform: translateY(-4px) rotate(-1deg); }
    75% { transform: translateY(-12px) rotate(0.5deg); }
}

@keyframes sudden-move {
    0%, 100% { transform: translate(0, 0) scale(1); }
    10% { transform: translate(-3px, 2px) scale(1.02); }
    20% { transform: translate(4px, -3px) scale(0.98); }
    30% { transform: translate(-2px, -4px) scale(1.01); }
                
    50% { transform: translate(-4px, 2px) scale(1.03); }
    60% { transform: translate(2px, -2px) scale(1); }
    70% { transform: translate(-3px, 4px) scale(1.01); }
    80% { transform: translate(4px, -3px) scale(0.99); }
    90% { transform: translate(-2px, 2px) scale(1.02); }
}

@keyframes pulse-glow {
    0%, 100% { 
        box-shadow: 0 0 20px rgba(197, 157, 95, 0.1),
                    0 0 40px rgba(197, 157, 95, 0.05);
    }
    50% { 
        box-shadow: 0 0 30px rgba(197, 157, 95, 0.2),
                    0 0 60px rgba(197, 157, 95, 0.1);
    }
}

/* Base animation for all sponsor cards */
.sponsor-card {
    animation: float 6s ease-in-out infinite;
    will-change: transform;
}

/* Stagger animations for each sponsor */
.sponsor-card[data-sponsor="alsalam"] {
    animation-delay: 0s;
}

.sponsor-card[data-sponsor="jasmis"] {
    animation-delay: -2s;
}

.sponsor-card[data-sponsor="bapco"] {
    animation-delay: -4s;
}

/* Logo container gets the sudden random moves */
.sponsor-logo-container {
    animation: 
        float 8s ease-in-out infinite,
        sudden-move 12s linear infinite,
        pulse-glow 4s ease-in-out infinite;
    will-change: transform, box-shadow;
    transform-origin: center;
}

/* Different timing for each logo container */
.sponsor-logo-container[data-sponsor="alsalam"] {
    animation-duration: 8s, 15s, 4s;
}

.sponsor-logo-container[data-sponsor="jasmis"] {
    animation-duration: 9s, 18s, 5s;
    animation-delay: -1s, -3s, -2s;
}

.sponsor-logo-container[data-sponsor="bapco"] {
    animation-duration: 10s, 20s, 6s;
    animation-delay: -2s, -5s, -3s;
}

/* Hover effects - pause animations on hover */
.sponsor-card:hover .sponsor-logo-container {
    animation-play-state: paused;
}

.sponsor-card:hover .sponsor-logo {
    transform: scale(1.15);
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .sponsor-logo-container {
        animation-duration: 6s, 10s, 3s;
    }
    
    .sponsor-name {
        font-size: 0.875rem;
    }
}

/* Reduced motion preference */
@media (prefers-reduced-motion: reduce) {
    .sponsor-card,
    .sponsor-logo-container {
        animation: none;
    }
}
</style>

<script>

    const logoContainers = document.querySelectorAll('.sponsor-logo-container');
    
    // Add subtle random movements to each logo
    logoContainers.forEach((container, index) => {
        let currentX = 0;
        let currentY = 0;
        let targetX = 0;
        let targetY = 0;
        let speed = 0.02 + (index * 0.005); // Different speed for each
        
        function animate() {
            // Randomly change target position
            if (Math.random() < 0.02) { // 2% chance per frame
                targetX = (Math.random() - 0.5) * 8; // -4px to 4px
                targetY = (Math.random() - 0.5) * 8;
            }
            
            // Smooth interpolation to target
            currentX += (targetX - currentX) * speed;
            currentY += (targetY - currentY) * speed;
            
            // Apply subtle transform (preserving other transforms)
            const scale = 1 + Math.sin(Date.now() * 0.001 + index) * 0.02;
            container.style.transform = `translate(${currentX}px, ${currentY}px) scale(${scale})`;
            
            requestAnimationFrame(animate);
        }
        
        // Start animation loop
        animate();
    });
    
    // Add occasional sudden "jump" animation
    setInterval(() => {
        const randomContainer = logoContainers[Math.floor(Math.random() * logoContainers.length)];
        if (randomContainer && !randomContainer.matches(':hover')) {
            randomContainer.style.transition = 'transform 0.1s ease-out';
            const jumpX = (Math.random() - 0.5) * 12;
            const jumpY = (Math.random() - 0.5) * 12;
            randomContainer.style.transform = `translate(${jumpX}px, ${jumpY}px) scale(1.05)`;
            
            setTimeout(() => {
                randomContainer.style.transition = 'transform 0.3s ease-out';
                randomContainer.style.transform = 'translate(0, 0) scale(1)';
            }, 150);
        }
    }, 3000); // Every 3 seconds, one logo jumps
});
</script>
