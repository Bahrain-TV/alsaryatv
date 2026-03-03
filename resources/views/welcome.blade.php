<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="description"
        content="{{ config('app.ar_translations.description') ?? 'البرنامج الرئيسي المباشر على شاشة تلفزيون البحرين خلال شهر رمضان المبارك.' }}" />
    <meta name="theme-color" content="#0f172a" />
    <meta property="og:title" content="{{ config('app.ar_translations.title') ?? 'السارية' }}" />
    <meta property="og:description"
        content="{{ config('app.ar_translations.description') ?? 'البرنامج الرئيسي المباشر على شاشة تلفزيون البحرين خلال شهر رمضان المبارك.' }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url('/') }}" />
    <meta property="og:image" content="{{ asset('images/og-banner.jpg') }}" />
    <meta name="twitter:card" content="summary_large_image" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon/favicon.ico') }}" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/favicon-32x32.png') }}" />
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon/favicon-16x16.png') }}" />

    <title>{{ config('app.ar_translations.title') ?? 'السارية' }} - قريباً في رمضان</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=tajawal:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- FlipDown Countdown Timer -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flipdown@0.3.2/dist/flipdown.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/flipdown@0.3.2/dist/flipdown.min.js"></script>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

</head>

<body dir="rtl">
    <!-- ==================== PRELOADER ==================== -->
    <div class="preloader" id="preloader">
        <div class="preloader-stars" id="preloaderStars"></div>
        <div class="preloader-content">
            <div class="preloader-logo-container">
                <div class="preloader-particles">
                    <div class="particle"></div>
                    <div class="particle"></div>
                    <div class="particle"></div>
                    <div class="particle"></div>
                    <div class="particle"></div>
                    <div class="particle"></div>
                    <div class="particle"></div>
                    <div class="particle"></div>
                </div>
                <div class="preloader-ring"></div>
                <div class="preloader-ring-2"></div>
                <img src="{{ asset('images/bahrain-tv-sm.png') }}" alt="Bahrain TV" class="preloader-logo" />
            </div>
            <div class="preloader-text">جاري التحميل</div>
        </div>
    </div>
    <!-- Lottie Animation Background -->
    <div class="lottie-background">
        <lottie-player
            id="lottie-bg"
            src="{{ asset('lottie/crecent-moon-ramadan.json') }}"
            background="transparent"
            speed="0.3"
            mode="bounce"
            loop
            autoplay>
        </lottie-player>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Logo -->
        <div class="logo-section">
            {{-- <img src="{{ asset('images/alsarya-tv-show-logo.png') }}" alt="السارية" /> --}}
        </div>

        <!-- Downtime Card -->
        <div class="downtime-card">
            <div class="bismillah">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ</div>
            
            <h1 class="main-title">برنامج السارية</h1>
            <p class="subtitle">على شاشة تلفزيون البحرين</p>

            @if(config('alsarya.registration.enabled', false) || auth()->check())
                {{-- Registration is enabled - show registration form --}}
                <div class="open-message bg-gradient-to-r from-brand-maroon/20 to-brand-cream/20 border-2 border-brand-maroon/40 rounded-2xl p-6 mb-8 backdrop-blur-sm">
                    <h3 class="text-brand-cream text-2xl font-bold mb-2">🌙 رمضان كريم!</h3>
                    <p class="text-white/80 text-lg">التسجيل مفتوح الآن - سجّل للمشاركة في المسابقة</p>
                </div>

                {{-- Registration Form for Logged-in Users --}}
                <div class="registration-form bg-dark-card border border-brand-cream/20 rounded-2xl p-8 mb-8 opacity-100 backdrop-blur-md">
                    {{-- Registration Type Toggle --}}
                    <div class="flex gap-4 mb-6 justify-center">
                        <button type="button" id="individual-toggle"
                                class="flex-1 py-3 px-4 bg-brand-gradient text-white font-bold border-2 border-brand-cream rounded-xl cursor-pointer transition-all duration-300 text-base hover:shadow-lg hover:shadow-brand-maroon/50 opacity-100">
                            👤 تسجيل فردي
                        </button>
                        <button type="button" id="family-toggle"
                                class="flex-1 py-3 px-4 bg-transparent text-brand-cream font-bold border-2 border-brand-cream rounded-xl cursor-pointer transition-all duration-300 text-base hover:bg-brand-cream/10 opacity-100">
                            👨‍👩‍👧‍👦 تسجيل عائلي
                        </button>
                    </div>

                    <form method="POST" action="{{ route('callers.store') }}" dir="rtl" style="display: flex; flex-direction: column; gap: 1rem;">
                        @csrf

                        {{-- Hidden field to track registration type --}}
                        <input type="hidden" id="registration_type" name="registration_type" value="individual">

                        {{-- Name --}}
                        <div>
                            <label for="name" class="block text-brand-cream mb-2 font-semibold">الاسم الكامل</label>
                            <input type="text" id="name" name="name" required value="{{ old('name') }}"
                                   class="w-full py-3 px-4 bg-dark-navy/80 border border-brand-cream/30 rounded-xl text-white text-base placeholder-white/50 focus:border-brand-cream focus:ring-2 focus:ring-brand-cream/30 transition-all opacity-100"
                                   placeholder="أدخل اسمك الكامل">
                            @error('name') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- CPR --}}
                        <div>
                            <label for="cpr" class="block text-brand-cream mb-2 font-semibold">رقم الهوية (CPR)</label>
                            <input type="text" id="cpr" name="cpr" required value="{{ old('cpr') }}" pattern="\d*"
                                   class="w-full py-3 px-4 bg-dark-navy/80 border border-brand-cream/30 rounded-xl text-white text-base placeholder-white/50 focus:border-brand-cream focus:ring-2 focus:ring-brand-cream/30 transition-all opacity-100"
                                   placeholder="أدخل رقم الهوية">
                            @error('cpr') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label for="phone_number" class="block text-brand-cream mb-2 font-semibold">رقم الهاتف</label>
                            <input type="tel" id="phone_number" name="phone_number" required value="{{ old('phone_number') }}"
                                   class="w-full py-3 px-4 bg-dark-navy/80 border border-brand-cream/30 rounded-xl text-white text-base placeholder-white/50 focus:border-brand-cream focus:ring-2 focus:ring-brand-cream/30 transition-all opacity-100"
                                   placeholder="أدخل رقم الهاتف">
                            @error('phone_number') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Family Fields (Hidden by default) --}}
                        <div id="family-fields" class="hidden">
                            {{-- Family Name --}}
                            <div>
                                <label for="family_name" class="block text-brand-cream mb-2 font-semibold">اسم العائلة</label>
                                <input type="text" id="family_name" name="family_name" value="{{ old('family_name') }}"
                                       class="w-full py-3 px-4 bg-dark-navy/80 border border-brand-cream/30 rounded-xl text-white text-base placeholder-white/50 focus:border-brand-cream focus:ring-2 focus:ring-brand-cream/30 transition-all opacity-100"
                                       placeholder="أدخل اسم العائلة (اختياري)">
                                @error('family_name') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>

                            {{-- Number of Family Members --}}
                            <div>
                                <label for="family_members" class="block text-brand-cream mb-2 font-semibold">عدد أفراد العائلة</label>
                                <input type="number" id="family_members" name="family_members" min="2" max="10" value="{{ old('family_members', 2) }}"
                                       class="w-full py-3 px-4 bg-dark-navy/80 border border-brand-cream/30 rounded-xl text-white text-base focus:border-brand-cream focus:ring-2 focus:ring-brand-cream/30 transition-all opacity-100">
                                @error('family_members') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit"
                                class="w-full py-4 bg-brand-gradient text-white font-bold text-lg rounded-xl cursor-pointer mt-2 transition-all duration-300 hover:shadow-lg hover:shadow-brand-maroon/50 hover:scale-105 active:scale-95 opacity-100">
                            🎯 سجّل الآن
                        </button>
                    </form>
                </div>

                {{-- Current Ramadan Info --}}
                <div class="ramadan-info bg-gradient-to-r from-brand-maroon/20 to-brand-cream/20 border-2 border-brand-cream/40 rounded-2xl p-6 backdrop-blur-sm text-center">
                    <h4 class="text-brand-cream text-2xl font-bold mb-3">🌙 أهلاً بكم في شهر رمضان المبارك</h4>
                    <div class="text-brand-cream text-lg font-semibold mb-2">{{ $ramadanHijri ?? '1 رمضان 1447 هـ' }}</div>
                    <div class="text-brand-light-cream text-xl font-bold">{{ $ramadanDate ?? '28 فبراير 2026' }}</div>
                </div>
            @else
                {{-- Guests see the countdown timer --}}
                <!-- Registration Closed -->
                <div class="closed-message">
                    <h3>⏸️ التسجيل مغلق حالياً</h3>
                    <p>سيتم فتح التسجيل مع بداية شهر رمضان المبارك</p>
                </div>

                <!-- Countdown -->
                <div class="countdown-section">
                    <div class="countdown-label">
                        العد التنازلي لشهر رمضان المبارك
                    </div>
                    <div id="flipdown" class="flipdown flipdown__theme-dark"></div>
                </div>

                <!-- Ramadan Date Info -->
                <div class="ramadan-info">
                    <h4>🌙 أول أيام شهر رمضان المبارك</h4>
                    <div class="date">{{ $ramadanDate ?? '28 فبراير 2026' }}</div>
                    <div class="hijri">{{ $ramadanHijri ?? '1 رمضان 1447 هـ' }}</div>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer-section">
            <div style="display:flex;align-items:center;justify-content:center;gap:0.75rem;margin-bottom:0.5rem;">
                <img src="{{ asset('images/btv-logo-ar.png') }}" alt="تلفزيون البحرين" style="height:2rem;width:auto;opacity:0.9;" />
                <div style="text-align:center;line-height:1.4;">
                    <span style="display:block;font-weight:700;color:#E8D7C3;font-size:0.95rem;">تلفزيون البحرين</span>
                    <span style="display:block;font-size:0.8rem;color:#cbd5e1;opacity:0.85;">يقدم لكم</span>
                </div>
            </div>
            <p>© {{ date('Y') }} <a href="https://live.bh" target="_blank">تلفزيون البحرين</a> | جميع الحقوق محفوظة</p>

            <div class="footer-meta">
                <span class="visitors-count">
                    👁️ عدد الزوار: {{ number_format($totalHits ?? 0) }}
                </span>
                <span class="separator">|</span>
                <span class="version-tag" title="إصدار التطبيق">v{{ $appVersion ?? '1.0.0' }}</span>
                <span class="separator">|</span>
                <a href="{{ route('login') }}" class="login-link">تسجيل الدخول</a>
            </div>
        </div>
    </div>

    <!-- Lottie Player Library -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    
    <script>
        // ==================== REGISTRATION TYPE TOGGLE WITH SPINNING ANIMATION ====================
        function setupRegistrationToggle() {
            const individualToggle = document.getElementById('individual-toggle');
            const familyToggle = document.getElementById('family-toggle');
            const registrationType = document.getElementById('registration_type');
            const familyFields = document.getElementById('family-fields');
            const nameLabel = document.querySelector('label[for="name"]');
            const cprLabel = document.querySelector('label[for="cpr"]');
            const form = document.querySelector('form[method="POST"]');
            const registrationForm = document.querySelector('.registration-form');
            let isAnimating = false;

            // Ensure all required elements exist
            if (!individualToggle || !familyToggle || !registrationType) {
                console.warn('Registration form elements not found');
                return;
            }

            // Check if gsap is available
            const hasGSAP = typeof window.gsap !== 'undefined';

            function updateButtonStyles(isFamily) {
                if (isFamily) {
                    individualToggle.style.background = 'transparent';
                    individualToggle.style.color = '#E8D7C3';
                    familyToggle.style.background = 'linear-gradient(135deg, #A81C2E, #E8D7C3)';
                    familyToggle.style.color = '#FFFFFF';
                } else {
                    individualToggle.style.background = 'linear-gradient(135deg, #A81C2E, #E8D7C3)';
                    individualToggle.style.color = '#FFFFFF';
                    familyToggle.style.background = 'transparent';
                    familyToggle.style.color = '#E8D7C3';
                }
            }

            function animateFormEntrance() {
                // Animation disabled - forms appear immediately with inline styles
                if (!hasGSAP || !registrationForm) return;

                const elements = registrationForm.querySelectorAll('label, input, select, button');
                if (!elements.length) return;

                // Ensure all elements are visible
                gsap.killTweensOf(elements);
                gsap.set(elements, { opacity: 1, y: 0 });
            }

            function setIndividualMode() {
                registrationType.value = 'individual';
                nameLabel.textContent = 'الاسم الكامل';
                cprLabel.textContent = 'رقم الهوية (CPR)';
                familyFields.style.display = 'none';
                familyFields.style.opacity = '0';
                updateButtonStyles(false);
                animateFormEntrance();
            }

            function setFamilyMode() {
                registrationType.value = 'family';
                nameLabel.textContent = 'اسم المسؤول عن العائلة';
                cprLabel.textContent = 'رقم الهوية (CPR) للمسؤول';
                familyFields.style.display = 'flex';
                familyFields.style.flexDirection = 'column';
                familyFields.style.gap = '1rem';
                familyFields.style.opacity = '1';
                updateButtonStyles(true);
                animateFormEntrance();
            }

            function switchFormWithSpin(isFamily) {
                if (isAnimating) return;

                // Check if already on the selected mode
                const currentMode = registrationType.value;
                if ((isFamily && currentMode === 'family') || (!isFamily && currentMode === 'individual')) {
                    return;
                }

                isAnimating = true;

                // Disable buttons during animation
                individualToggle.disabled = true;
                familyToggle.disabled = true;

                if (!hasGSAP) {
                    // Fallback without GSAP
                    if (isFamily) {
                        setFamilyMode();
                    } else {
                        setIndividualMode();
                    }
                    isAnimating = false;
                    individualToggle.disabled = false;
                    familyToggle.disabled = false;
                    return;
                }

                // Create GSAP timeline for the spinning animation with slower transition and overlap
                const tl = gsap.timeline({
                    onComplete: function() {
                        isAnimating = false;
                        individualToggle.disabled = false;
                        familyToggle.disabled = false;
                    }
                });

                // Animate form container flip based on mode - slowed down with 0.3s overlap
                if (isFamily) {
                    // Family mode animation - 0.6s each half with 0.3s overlap
                    tl.to(registrationForm, {
                        duration: 0.6,
                        rotationY: 90,
                        x: 100,
                        opacity: 0.5,
                        ease: "power2.inOut"
                    }, 0)
                    .call(() => setFamilyMode(), null, 0.3)
                    .to(registrationForm, {
                        duration: 0.6,
                        rotationY: 0,
                        x: 0,
                        opacity: 1,
                        ease: "power2.inOut"
                    }, 0.3);
                } else {
                    // Individual mode animation - 0.6s each half with 0.3s overlap
                    tl.to(registrationForm, {
                        duration: 0.6,
                        rotationY: -90,
                        x: -100,
                        opacity: 0.5,
                        ease: "power2.inOut"
                    }, 0)
                    .call(() => setIndividualMode(), null, 0.3)
                    .to(registrationForm, {
                        duration: 0.6,
                        rotationY: 0,
                        x: 0,
                        opacity: 1,
                        ease: "power2.inOut"
                    }, 0.3);
                }
            }

            individualToggle.addEventListener('click', function(e) {
                e.preventDefault();
                switchFormWithSpin(false);
            });

            familyToggle.addEventListener('click', function(e) {
                e.preventDefault();
                switchFormWithSpin(true);
            });

            // Set initial state
            setIndividualMode();
        }

        // Initialize on DOMContentLoaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupRegistrationToggle);
        } else {
            // DOM is already loaded
            setupRegistrationToggle();
        }

        // Also setup if GSAP loads late
        window.addEventListener('load', function() {
            if (typeof window.gsap !== 'undefined' && document.getElementById('individual-toggle')) {
                setupRegistrationToggle();
            }
        });

        // ==================== PRELOADER / SPLASH SCREEN ====================
        (function() {
            const SPLASH_DURATION = 3000; // Show splash for exactly 3 seconds
            const FADE_DURATION = 800;    // Fade out duration

            // Generate random stars for splash background
            function initStars() {
                const starsContainer = document.getElementById('preloaderStars');
                if (!starsContainer) return;

                for (let i = 0; i < 50; i++) {
                    const star = document.createElement('div');
                    star.className = 'star';
                    star.style.left = Math.random() * 100 + '%';
                    star.style.top = Math.random() * 100 + '%';
                    star.style.width = (Math.random() * 3 + 1) + 'px';
                    star.style.height = star.style.width;
                    star.style.animationDelay = Math.random() * 2 + 's';
                    star.style.animationDuration = (Math.random() * 2 + 1) + 's';
                    starsContainer.appendChild(star);
                }
            }

            // Function to reveal main content
            function revealContent() {
                const preloader = document.getElementById('preloader');
                const lottieBackground = document.querySelector('.lottie-background');
                const mainContainer = document.querySelector('.main-container');

                if (preloader) preloader.classList.add('fade-out');
                if (lottieBackground) lottieBackground.classList.add('revealed');
                if (mainContainer) mainContainer.classList.add('revealed');
            }

            // Initialize immediately
            initStars();

            // Start the reveal sequence after splash duration
            // Use both DOMContentLoaded and load to ensure it fires
            function startReveal() {
                setTimeout(revealContent, SPLASH_DURATION);
            }

            // Safety fallback: Force reveal after 4 seconds max
            setTimeout(() => {
                const mainContainer = document.querySelector('.main-container');
                const preloader = document.getElementById('preloader');
                const lottieBackground = document.querySelector('.lottie-background');

                if (mainContainer && !mainContainer.classList.contains('revealed')) {
                    mainContainer.classList.add('revealed');
                }
                if (preloader && !preloader.classList.contains('fade-out')) {
                    preloader.classList.add('fade-out');
                }
                if (lottieBackground && !lottieBackground.classList.contains('revealed')) {
                    lottieBackground.classList.add('revealed');
                }
            }, 4000);

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', startReveal);
            } else {
                startReveal();
            }

            window.addEventListener('load', startReveal);
        })();

        // ==================== COUNTDOWN ENDED HANDLER ====================
        document.addEventListener('DOMContentLoaded', function() {
            // Only initialize FlipDown for guests (when the element exists)
            const flipdownEl = document.getElementById('flipdown');
            if (!flipdownEl || window.flipdownInitialized) return;

            window.flipdownInitialized = true;

            // Ramadan 1447 starts on configured date at midnight (Bahrain time)
            // Using Bahrain timezone (UTC+3)
            const ramadanStartISO = '{{ $ramadanStartISO ?? "2026-02-28" }}';
            const ramadanDate = new Date(ramadanStartISO + 'T00:00:00+03:00');
            const ramadanTimestamp = Math.floor(ramadanDate.getTime() / 1000);

            // Clear container before initializing
            flipdownEl.innerHTML = '';

            // Initialize FlipDown
            try {
                const flipdown = new FlipDown(ramadanTimestamp, 'flipdown', {
                    theme: 'dark'
                });

                flipdown.start().ifEnded(() => {
                    // When countdown ends, show Ramadan message
                    const title = document.querySelector('.closed-message h3');
                    const desc = document.querySelector('.closed-message p');
                    const label = document.querySelector('.countdown-title');

                    if (title) title.innerHTML = '🌙 رمضان كريم!';
                    if (desc) desc.textContent = 'أهلاً بكم في شهر رمضان المبارك - سيتم فتح التسجيل قريباً';
                    if (label) label.innerHTML = '🎉 حل شهر رمضان المبارك!';
                });
            } catch (error) {
                console.error('FlipDown initialization error:', error);
            }
        });
    </script>
</body>

</html>