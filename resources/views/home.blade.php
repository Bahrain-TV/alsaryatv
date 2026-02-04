<x-guest-layout>
    <x-slot name="header">
        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <!-- FlipDown Dependencies -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flipdown@0.3.2/dist/flipdown.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flipdown@0.3.2/dist/flipdown.min.js"></script>
        <style>

            body {
                background-image: url('{{ asset("images/seef-district-from-sea.jpg") }}');
                background-size: cover;
                background-position: center;
                background-attachment: fixed;
                font-family: 'Tajawal', sans-serif;
                color: #fff;
                text-align: center;
                overflow: hidden;
            }

            #flipdown {
                margin: 2rem auto;

                /* Responsive FlipDown styling */
                #flipdown {
                    font-size: 0.75em;
                    /* Base font size */
                    padding: 0.5em;
                }

                @media (max-width: 640px) {

                    /* Small screens */
                    #flipdown {
                        font-size: 0.6em;
                        padding: 0.4em;
                    }
                }

                @media (min-width: 768px) {

                    /* Medium screens and up */
                    #flipdown {
                        font-size: 1em;
                        padding: 0.75em;
                    }
                }

                @media (min-width: 1280px) {

                    /* Large screens and up */
                    #flipdown {
                        font-size: 1.25em;
                        padding: 1em;
                    }
                }
            }

            .flipdown .rotor,
            .flipdown.flipdown__theme-dark .rotor,
            .flipdown.flipdown__theme-dark .rotor-top,
            .flipdown.flipdown__theme-dark .rotor-bottom,
            .flipdown.flipdown__theme-dark .rotor-leaf-front,
            .flipdown.flipdown__theme-dark .rotor-leaf-rear {
                background-color: #1a1a1a;
            }

            /* Custom labels styling */
            .flipdown .rotor-group:nth-child(1) .rotor-group-heading::before { content: 'يوم' !important; }
            .flipdown .rotor-group:nth-child(2) .rotor-group-heading::before { content: 'ساعة' !important; }
            .flipdown .rotor-group:nth-child(3) .rotor-group-heading::before { content: 'دقيقة' !important; }
            .flipdown .rotor-group:nth-child(4) .rotor-group-heading::before { content: 'ثانية' !important; }

            .flipdown .rotor-group-heading {
                font-family: 'Tajawal', sans-serif !important;
                color: #fff !important;
            }

            /* Glow effect for FlipDown */
            .flipdown {
                filter: drop-shadow(0 0 2px #fff);
            }

            .flipdown .rotor,
            .flipdown.flipdown__theme-dark .rotor {
                position: relative;
                transition: all 0.3s ease;
            }

            .flipdown .rotor:hover,
            .flipdown.flipdown__theme-dark .rotor:hover {
                filter: drop-shadow(0 0 15px rgba(255, 255, 255, 0.5));
                transform: scale(1.1);
            }

            .flipdown .rotor::after,
            .flipdown.flipdown__theme-dark .rotor::after {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: radial-gradient(circle at center,
                        rgba(255, 255, 255, 0.1) 0%,
                        rgba(255, 255, 255, 0) 70%);
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .flipdown .rotor:hover::after,
            .flipdown.flipdown__theme-dark .rotor:hover::after {
                opacity: 1;
            }

            /* Pulsing animation for the labels */
            .flipdown .rotor-group-heading {
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0% {
                    text-shadow: 0 0 5px #fff,
                        0 0 10px #fff,
                        0 0 15px #fff,
                        0 0 20px #228DFF,
                        0 0 35px #228DFF,
                        0 0 40px #228DFF;
                }

                50% {
                    text-shadow: 0 0 2px #fff,
                        0 0 5px #fff,
                        0 0 7px #fff,
                        0 0 10px #228DFF,
                        0 0 17px #228DFF,
                        0 0 20px #228DFF;
                }

                100% {
                    text-shadow: 0 0 5px #fff,
                        0 0 10px #fff,
                        0 0 15px #fff,
                        0 0 20px #228DFF,
                        0 0 35px #228DFF,
                        0 0 40px #228DFF;
                }
            }
        </style>
    </x-slot>

    <body class="rtl">

        @include('layouts.header')

        <div class="bg-black bg-opacity-40">
            <x-application-logo class="absolute mx-auto w-32 h-32 sm:w-48 sm:h-48" />
            <div class="bg-opacity-15 shadow">
                <div class="max-w-7xl mx-auto py-4 px-2 sm:px-6 lg:px-8">
                    <div class="mx-auto mt-20 sm:mt-28 w-full items-center">
                        <x-twin-buttons-panel />
                    </div>
                </div>
                <div class="justify-center">
                    <x-countdown :days="$days" :ramadan="$ramadan" />
                </div>
            </div>

            @include('layouts.footer', ['hits' => $hits])

            @push('scripts')
                <script>
                    // Define the toggle functions in the global scope
                    function toggleForm() {
                        const form = document.getElementById('callers-form');
                        const formElements = form.querySelectorAll('h2, label, input');
                        form.classList.toggle('hidden');
                        gsap.fromTo(formElements, {
                            y: -50,
                            opacity: 0
                        }, {
                            y: 0,
                            opacity: 1,
                            duration: 0.5,
                            stagger: 0.1,
                            ease: 'power2.out'
                        });
                    }

                    function toggleFamilyForm() {
                        const form = document.getElementById('family-form');
                        const formElements = form.querySelectorAll('h2, label, input');
                        form.classList.toggle('hidden');
                        gsap.fromTo(formElements, {
                            y: -50,
                            opacity: 0
                        }, {
                            y: 0,
                            opacity: 1,
                            duration: 0.5,
                            stagger: 0.1,
                            ease: 'power2.out'
                        });
                    }

                    document.addEventListener('DOMContentLoaded', function () {
                        // Attach event listeners after functions are defined
                        const callToggleBtn = document.getElementById('toggle-call');
                        const familyToggleBtn = document.getElementById('toggle-family');

                        if (callToggleBtn) {
                            callToggleBtn.addEventListener('click', toggleForm);
                        }

                        if (familyToggleBtn) {
                            familyToggleBtn.addEventListener('click', toggleFamilyForm);
                        }
                    });

                    document.addEventListener('DOMContentLoaded', () => {
                        // Set up FlipDown
                        const targetDate = new Date('2026-02-26T21:00:00+03:00').getTime() / 1000;
                        new FlipDown(targetDate, 'flipdown', {
                            theme: 'dark'
                        }).start();

                        // check if the form is hidden
                        if (document.getElementById('callersa-form').classList.contains('hidden')) {
                            toggleForm();
                            return false;
                        }

                        // Optional: Add RTL support for the labels
                        document.querySelectorAll('.rotor-group-heading').forEach(heading => {
                            heading.classList.add('rtl');
                            heading.classList.add('text-white');
                        });

                        // Updated Glitch effect function
                        function triggerGlitch() {
                            const elements = ['#logo img', '#days'];
                            const randomElement = elements[Math.floor(Math.random() * elements.length)];
                            const element = document.querySelector(randomElement);

                            if (element) {
                                element.classList.add('glitch');
                                element.setAttribute('data-text', element.innerText || '');

                                // Random duration between 1 and 3 seconds
                                const duration = Math.random() * 2000 + 1000;
                                setTimeout(() => {
                                    element.classList.remove('glitch');
                                }, duration);

                                // Add micro-glitches
                                for (let i = 0; i < 3; i++) {
                                    setTimeout(() => {
                                        element.style.transform = `translate(${Math.random() * 4 - 2}px, ${Math.random() * 4 - 2}px)`;
                                        setTimeout(() => {
                                            element.style.transform = '';
                                        }, 100);
                                    }, Math.random() * duration);
                                }
                            }

                            // Schedule next glitch with more random timing
                            const nextGlitch = Math.random() * 8000 + 3000; // Random time between 3-11 seconds
                            setTimeout(triggerGlitch, nextGlitch);
                        }

                        // Start glitch effect after initial delay
                        setTimeout(triggerGlitch, 5000);

                        // Toggle form after initial delay
                        // setTimeout(toggleForm, 5000);

                        // Validate call form submission
                        const callForm = document.getElementById('callers-form');
                        if(callForm) {
                            callForm.addEventListener('submit', function(e) {
                                const requiredFields = this.querySelectorAll('[required]');
                                let valid = true;
                                requiredFields.forEach(field => {
                                    if(field.type === 'checkbox' && !field.checked) {
                                        valid = false;
                                    } else if(field.type !== 'checkbox' && !field.value.trim()){
                                        valid = false;
                                    }
                                });
                                if(!valid) {
                                    e.preventDefault();
                                    alert('يرجى ملء جميع الحقول المطلوبة والموافقة على الشروط');
                                    return false;
                                }
                            });
                        }
                    });
                </script>
            @endpush

        </div>
        @include('sponsors')

    <div class="bg-black bg-opacity-30 px-4">
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-3 px-3 sm:py-6 sm:px-6 lg:px-8">
                <!-- Displaying the number of days until Ramadan -->
                <p class="text-sm sm:text-base">Days until Ramadan: {{ $days }}</p>

                <!-- Displaying the date of the next Ramadan -->
                <p class="text-sm sm:text-base">Next Ramadan: {{ $ramadan }}</p>
            </div>
        </div>
    </div>
</x-guest-layout>