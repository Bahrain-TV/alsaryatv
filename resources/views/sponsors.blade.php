<div class="w-full">
    <section class="py-3 sm:py-4" id="sponsors">
        <div class="container mx-auto px-4">
            <div class="flex flex-col items-center justify-center">
                <h2 class="text-xl sm:text-2xl font-bold text-white mb-4 drop-shadow-lg">
                    هذا البرنامج يأتيكم برعاية
                </h2>

                <!-- Three sponsor logos — equal sizing, consistent presentation -->
                <div class="flex flex-wrap justify-center items-center gap-8 sm:gap-12" id="sponsors-logos">

                    {{-- Jasmis --}}
                    <div class="flex items-center justify-center" style="width:160px; height:70px;">
                        <img src="{{ asset('images/jasmis-logo.png') }}"
                             alt="Jasmis"
                             class="max-h-full max-w-full object-contain hover:scale-105 transition-transform duration-300 drop-shadow-lg" />
                    </div>

                    {{-- Al Salam — SVG has dark navy fill, invert to white for dark backgrounds --}}
                    <div class="flex items-center justify-center" style="width:160px; height:70px;">
                        <img src="{{ asset('images/alsalam-logo.svg') }}"
                             alt="Al Salam"
                             class="max-h-full max-w-full object-contain hover:scale-105 transition-transform duration-300 drop-shadow-lg"
                             style="filter: brightness(0) invert(1) sepia(0.15);" />
                    </div>

                    {{-- Bapco Energies --}}
                    <div class="flex items-center justify-center" style="width:160px; height:70px;">
                        <img src="{{ asset('images/bapco-energies.png') }}"
                             alt="Bapco Energies"
                             class="max-h-full max-w-full object-contain hover:scale-105 transition-transform duration-300 drop-shadow-lg" />
                    </div>

                </div>
            </div>
        </div>
    </section>
</div>
