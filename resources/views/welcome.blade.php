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
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}" />

    <title>{{ config('app.ar_translations.title') ?? 'السارية' }} - قريباً في رمضان</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=tajawal:400,500,600,700,800&display=swap" rel="stylesheet" />
    
    <!-- FlipDown CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flipdown/0.3.2/flipdown.min.css" />

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    <style>
        :root {
            --primary-gold: #d4af37;
            --primary-emerald: #10b981;
            --bg-dark: #0a0f1a;
            --bg-card: rgba(15, 23, 42, 0.9);
            --text-primary: #ffffff;
            --text-secondary: rgba(255, 255, 255, 0.7);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #0a0f1a 0%, #1a1f2e 50%, #0f1a2a 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
            color: var(--text-primary);
        }

        /* Animated Background Stars */
        .stars {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
            z-index: 0;
        }

        .star {
            position: absolute;
            width: 3px;
            height: 3px;
            background: white;
            border-radius: 50%;
            animation: twinkle 2s infinite ease-in-out;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.5); }
        }

        /* Crescent Moon */
        .crescent-moon {
            position: fixed;
            top: 10%;
            right: 10%;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            box-shadow: 
                inset -25px 0 0 0 #fbbf24,
                0 0 40px rgba(251, 191, 36, 0.4),
                0 0 80px rgba(251, 191, 36, 0.2);
            animation: moonGlow 4s ease-in-out infinite;
            z-index: 1;
        }

        @keyframes moonGlow {
            0%, 100% { 
                box-shadow: 
                    inset -25px 0 0 0 #fbbf24,
                    0 0 40px rgba(251, 191, 36, 0.4),
                    0 0 80px rgba(251, 191, 36, 0.2);
            }
            50% { 
                box-shadow: 
                    inset -25px 0 0 0 #fbbf24,
                    0 0 60px rgba(251, 191, 36, 0.6),
                    0 0 120px rgba(251, 191, 36, 0.3);
            }
        }

        /* Main Container */
        .main-container {
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            text-align: center;
            width: 100%;
            max-width: 900px;
        }

        /* Logo Section */
        .logo-section {
            margin-bottom: 2rem;
            animation: fadeInDown 1s ease-out;
        }

        .logo-section img {
            height: 120px;
            filter: drop-shadow(0 10px 30px rgba(212, 175, 55, 0.3));
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Downtime Card */
        .downtime-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 24px;
            padding: 3rem;
            width: 100%;
            box-shadow: 
                0 25px 80px rgba(0, 0, 0, 0.5),
                0 0 40px rgba(212, 175, 55, 0.1);
            animation: cardAppear 1.2s ease-out 0.3s both;
        }

        @keyframes cardAppear {
            from {
                opacity: 0;
                transform: translateY(40px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Bismillah */
        .bismillah {
            font-size: 2.5rem;
            color: var(--primary-gold);
            margin-bottom: 1.5rem;
            text-shadow: 0 0 30px rgba(212, 175, 55, 0.5);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        /* Main Title */
        .main-title {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-gold), #fbbf24, var(--primary-gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            text-shadow: none;
        }

        .subtitle {
            font-size: 1.25rem;
            color: var(--text-secondary);
            margin-bottom: 2rem;
        }

        /* Registration Closed Message */
        .closed-message {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.05));
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .closed-message h3 {
            color: #f87171;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .closed-message p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1rem;
        }

        /* Countdown Section */
        .countdown-section {
            margin: 2rem 0;
        }

        .countdown-label {
            font-size: 1.25rem;
            color: var(--primary-emerald);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .countdown-label::before,
        .countdown-label::after {
            content: '☪';
            font-size: 1rem;
            animation: starSpin 10s linear infinite;
        }

        @keyframes starSpin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* FlipDown Overrides */
        .flipdown {
            margin: 0 auto;
            direction: ltr;
        }

        .flipdown .rotor-group-heading::before {
            color: var(--primary-gold) !important;
            font-family: 'Tajawal', sans-serif !important;
            font-size: 0.9rem !important;
        }

        .flipdown .rotor-group:nth-child(1) .rotor-group-heading::before { content: 'يوم' !important; }
        .flipdown .rotor-group:nth-child(2) .rotor-group-heading::before { content: 'ساعة' !important; }
        .flipdown .rotor-group:nth-child(3) .rotor-group-heading::before { content: 'دقيقة' !important; }
        .flipdown .rotor-group:nth-child(4) .rotor-group-heading::before { content: 'ثانية' !important; }

        .flipdown.flipdown__theme-dark {
            --bg: transparent;
        }

        .flipdown.flipdown__theme-dark .rotor,
        .flipdown.flipdown__theme-dark .rotor-top,
        .flipdown.flipdown__theme-dark .rotor-bottom,
        .flipdown.flipdown__theme-dark .rotor-leaf-front,
        .flipdown.flipdown__theme-dark .rotor-leaf-rear {
            background: linear-gradient(180deg, #1e293b, #0f172a) !important;
            border-color: rgba(212, 175, 55, 0.2) !important;
        }

        .flipdown.flipdown__theme-dark .rotor-top,
        .flipdown.flipdown__theme-dark .rotor-leaf-front {
            color: var(--primary-gold) !important;
        }

        .flipdown.flipdown__theme-dark .rotor-bottom,
        .flipdown.flipdown__theme-dark .rotor-leaf-rear {
            color: #fbbf24 !important;
        }

        /* Ramadan Date Info */
        .ramadan-info {
            margin-top: 2rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.05));
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 16px;
        }

        .ramadan-info h4 {
            color: var(--primary-emerald);
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
        }

        .ramadan-info .date {
            font-size: 1.5rem;
            color: white;
            font-weight: 700;
        }

        .ramadan-info .hijri {
            color: var(--primary-gold);
            font-size: 1.1rem;
            margin-top: 0.5rem;
        }

        /* Footer */
        .footer-section {
            margin-top: 2rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
            animation: fadeIn 1.5s ease-out 1s both;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .footer-section a {
            color: var(--primary-gold);
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-section a:hover {
            color: #fbbf24;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }

            .downtime-card {
                padding: 1.5rem;
            }

            .logo-section img {
                height: 80px;
            }

            .bismillah {
                font-size: 1.75rem;
            }

            .main-title {
                font-size: 1.75rem;
            }

            .crescent-moon {
                width: 60px;
                height: 60px;
                top: 5%;
                right: 5%;
            }

            .flipdown .rotor {
                width: 28px !important;
                height: 40px !important;
            }
        }

        /* Lantern Decorations */
        .lantern {
            position: fixed;
            width: 40px;
            height: 80px;
            background: linear-gradient(180deg, #fbbf24, #d97706);
            border-radius: 50% 50% 50% 50% / 30% 30% 60% 60%;
            animation: lanternSwing 4s ease-in-out infinite;
            z-index: 1;
            opacity: 0.6;
        }

        .lantern::before {
            content: '';
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 20px;
            height: 10px;
            background: #92400e;
            border-radius: 5px 5px 0 0;
        }

        .lantern::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 20px;
            background: #78350f;
        }

        .lantern-left {
            left: 5%;
            top: 30%;
            animation-delay: 0s;
        }

        .lantern-right {
            right: 5%;
            top: 25%;
            animation-delay: 1s;
        }

        @keyframes lanternSwing {
            0%, 100% { transform: rotate(-5deg); }
            50% { transform: rotate(5deg); }
        }

        /* Decorative Islamic Pattern */
        .islamic-pattern {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 80 20'%3E%3Cpath d='M0 20 L20 0 L40 20 L60 0 L80 20' fill='none' stroke='%23d4af37' stroke-width='1' opacity='0.2'/%3E%3C/svg%3E") repeat-x;
            opacity: 0.3;
            z-index: 0;
        }
    </style>
</head>

<body dir="rtl">
    <!-- Animated Stars Background -->
    <div class="stars" id="stars"></div>

    <!-- Crescent Moon -->
    <div class="crescent-moon"></div>

    <!-- Lanterns -->
    <div class="lantern lantern-left"></div>
    <div class="lantern lantern-right"></div>

    <!-- Islamic Pattern -->
    <div class="islamic-pattern"></div>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Logo -->
        <div class="logo-section">
            <img src="{{ asset('images/alsarya-tv-show-logo.png') }}" alt="السارية" />
        </div>

        <!-- Downtime Card -->
        <div class="downtime-card">
            <div class="bismillah">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ</div>
            
            <h1 class="main-title">برنامج السارية</h1>
            <p class="subtitle">على شاشة تلفزيون البحرين</p>

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
                <div class="date">{{ $ramadanDate ?? '26 فبراير 2026' }}</div>
                <div class="hijri">1 رمضان 1447 هـ</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-section">
            <p>© {{ date('Y') }} <a href="https://btv.bh" target="_blank">تلفزيون البحرين</a> | جميع الحقوق محفوظة</p>
        </div>
    </div>

    <!-- FlipDown JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flipdown/0.3.2/flipdown.min.js"></script>
    
    <script>
        // Generate Stars
        function generateStars() {
            const starsContainer = document.getElementById('stars');
            const starCount = 100;

            for (let i = 0; i < starCount; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                star.style.left = Math.random() * 100 + '%';
                star.style.top = Math.random() * 100 + '%';
                star.style.animationDelay = Math.random() * 2 + 's';
                star.style.animationDuration = (Math.random() * 2 + 1) + 's';
                starsContainer.appendChild(star);
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            generateStars();

            // Ramadan 1447 starts on February 26, 2026 at midnight (Bahrain time)
            // Using Bahrain timezone (UTC+3)
            const ramadanDate = new Date('2026-02-26T00:00:00+03:00');
            const ramadanTimestamp = Math.floor(ramadanDate.getTime() / 1000);

            // Initialize FlipDown
            new FlipDown(ramadanTimestamp, {
                theme: 'dark'
            }).start().ifEnded(() => {
                // When countdown ends, show Ramadan message
                document.querySelector('.closed-message h3').textContent = '🌙 رمضان كريم!';
                document.querySelector('.closed-message p').textContent = 'أهلاً بكم في شهر رمضان المبارك - سيتم فتح التسجيل قريباً';
                document.querySelector('.countdown-label').textContent = '🎉 حل شهر رمضان المبارك!';
            });
        });
    </script>
</body>

</html>