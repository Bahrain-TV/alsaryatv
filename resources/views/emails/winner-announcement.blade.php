<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎉 مبروك! أنت الفائز!</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;600;700;800&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 25%, #2d1b69 50%, #1e1b4b 75%, #0f172a 100%);
            background-attachment: fixed;
            padding: 20px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: rgba(30, 41, 59, 0.95);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5),
                        0 0 100px rgba(16, 185, 129, 0.3);
            border: 2px solid rgba(16, 185, 129, 0.4);
            animation: glow 3s ease-in-out infinite;
        }

        @keyframes glow {
            0%, 100% {
                box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5),
                            0 0 50px rgba(16, 185, 129, 0.3);
            }
            50% {
                box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5),
                            0 0 100px rgba(16, 185, 129, 0.5);
            }
        }

        /* Header Section - Victory Green */
        .header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            padding: 50px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: -50%;
            left: -10%;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .header h1 {
            color: #ffffff;
            font-size: 2.8rem;
            font-weight: 800;
            margin: 0;
            text-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.5px;
            position: relative;
            z-index: 1;
        }

        .header-subtitle {
            color: rgba(255, 255, 255, 0.95);
            font-size: 1rem;
            font-weight: 600;
            margin-top: 10px;
            letter-spacing: 1px;
            position: relative;
            z-index: 1;
        }

        /* Celebration Section */
        .celebration {
            text-align: center;
            padding: 35px 30px;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(5, 150, 105, 0.1) 100%);
            border-bottom: 2px solid rgba(52, 211, 153, 0.3);
        }

        .celebration-emoji {
            font-size: 4rem;
            margin-bottom: 15px;
            display: inline-block;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .celebration h2 {
            color: #34d399;
            font-size: 2rem;
            font-weight: 800;
            margin: 0 0 8px 0;
            letter-spacing: -0.3px;
        }

        .celebration-text {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.05rem;
            margin: 0;
            line-height: 1.6;
        }

        /* Content Section */
        .content {
            padding: 40px 30px;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.8;
        }

        .content h3 {
            color: #fbbf24;
            font-size: 1.3rem;
            font-weight: 700;
            margin: 0 0 15px 0;
            border-right: 4px solid #fbbf24;
            padding-right: 12px;
        }

        .content p {
            margin: 0 0 16px 0;
            font-size: 0.95rem;
            line-height: 1.8;
        }

        /* Winner Card */
        .winner-card {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.15) 0%, rgba(52, 211, 153, 0.15) 100%);
            border: 2px solid rgba(251, 191, 36, 0.3);
            border-radius: 14px;
            padding: 28px;
            margin: 25px 0;
            text-align: center;
        }

        .winner-label {
            color: #fbbf24;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .winner-name {
            color: #34d399;
            font-size: 1.8rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.3px;
        }

        .winner-detail {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-top: 8px;
        }

        /* Prize Section */
        .prize-section {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(251, 191, 36, 0.2);
            border-radius: 12px;
            padding: 24px;
            margin: 25px 0;
            border-left: 4px solid rgba(16, 185, 129, 0.8);
        }

        .prize-section h4 {
            color: #34d399;
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0 0 12px 0;
        }

        .prize-section p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.95rem;
            margin: 0;
            line-height: 1.7;
        }

        .prize-highlight {
            background: rgba(16, 185, 129, 0.2);
            padding: 16px;
            border-radius: 8px;
            margin-top: 12px;
            color: #34d399;
            font-weight: 700;
            font-size: 0.95rem;
            text-align: center;
        }

        /* Instructions Section */
        .instructions {
            background: linear-gradient(135deg, rgba(252, 211, 77, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%);
            border: 1px solid rgba(251, 191, 36, 0.2);
            border-radius: 12px;
            padding: 24px;
            margin: 25px 0;
            border-left: 4px solid #fbbf24;
        }

        .instructions h4 {
            color: #fbbf24;
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0 0 12px 0;
        }

        .instructions ol {
            margin: 0;
            padding-right: 20px;
            color: rgba(255, 255, 255, 0.85);
        }

        .instructions li {
            margin: 10px 0;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* CTA Button */
        .button-container {
            text-align: center;
            margin: 30px 0;
        }

        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            padding: 16px 42px;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
            letter-spacing: 0.5px;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(16, 185, 129, 0.4);
        }

        /* Important Notice */
        .notice-box {
            background: rgba(251, 191, 36, 0.1);
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }

        .notice-box p {
            color: #fbbf24;
            font-weight: 600;
            font-size: 0.95rem;
            margin: 0;
        }

        /* Footer Section */
        .footer {
            background: rgba(15, 23, 42, 0.8);
            padding: 30px;
            text-align: center;
            border-top: 1px solid rgba(16, 185, 129, 0.2);
        }

        .footer-text {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
            line-height: 1.8;
            margin: 0 0 12px 0;
        }

        .footer-links {
            margin-top: 15px;
        }

        .footer-links a {
            color: #34d399;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            margin: 0 10px;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #fbbf24;
        }

        .separator {
            color: rgba(255, 255, 255, 0.3);
        }

        .logo-text {
            font-size: 1.25rem;
            font-weight: 800;
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0 0 10px 0;
            letter-spacing: -0.5px;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 8px;
            }

            .header {
                padding: 40px 20px;
            }

            .header h1 {
                font-size: 2.2rem;
            }

            .celebration {
                padding: 25px 20px;
            }

            .celebration-emoji {
                font-size: 3rem;
            }

            .celebration h2 {
                font-size: 1.6rem;
            }

            .content {
                padding: 25px 20px;
            }

            .winner-card {
                padding: 20px;
            }

            .winner-name {
                font-size: 1.5rem;
            }

            .btn {
                padding: 14px 36px;
                font-size: 0.95rem;
                width: 100%;
            }

            .footer {
                padding: 25px 20px;
            }

            .footer-links a {
                display: block;
                margin: 8px 0;
            }

            .separator {
                display: none;
            }

            .instructions ol {
                padding-right: 16px;
            }
        }

        /* Dark Mode Adjustments */
        @media (prefers-color-scheme: dark) {
            body {
                background: #0f172a;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🎉 مبروك!</h1>
            <p class="header-subtitle">أنت الفائز في برنامج السارية</p>
        </div>

        <!-- Celebration -->
        <div class="celebration">
            <div class="celebration-emoji">🏆</div>
            <h2>فائز محظوظ!</h2>
            <p class="celebration-text">تم اختيارك عشوائياً ليكون الفائز في الحلقة الأخيرة</p>
        </div>

        <!-- Content -->
        <div class="content">
            <p style="font-size: 1rem; margin-bottom: 20px;">
                مرحباً <strong>{{ $winner_name ?? 'الفائز الكريم' }}</strong>! ✨
            </p>

            <p>
                يسعدنا إخبارك أنك تم اختيارك ليكون <strong>فائزاً</strong> في برنامج السارية المباشر على شاشة تلفزيون البحرين!
            </p>

            <!-- Winner Information Card -->
            <div class="winner-card">
                <div class="winner-label">🌟 بيانات الفائز</div>
                <p class="winner-name">{{ $winner_name ?? 'الفائز' }}</p>
                <div class="winner-detail">
                    رقم الهوية: <strong>{{ substr($winner_cpr ?? '***', 0, 3) }}****{{ substr($winner_cpr ?? '***', -2) }}</strong>
                </div>
                <div class="winner-detail">
                    التاريخ: <strong>{{ now()->locale('ar')->translatedFormat('j F Y') }}</strong>
                </div>
            </div>

            <!-- Prize Information -->
            <div class="prize-section">
                <h4>💰 الجائزة</h4>
                @if(isset($prize_amount))
                    <p>{{ $prize_description ?? 'تهانينا! لقد ربحت جائزة قيمة.' }}</p>
                    <div class="prize-highlight">
                        المبلغ: {{ $prize_amount }} د.ب
                    </div>
                @else
                    <p>تهانينا! لقد ربحت جائزة حصرية من برنامج السارية.</p>
                    <div class="prize-highlight">
                        سيتم التواصل معك قريباً بتفاصيل الجائزة
                    </div>
                @endif
            </div>

            <!-- Instructions -->
            <div class="instructions">
                <h4>📋 الخطوات التالية</h4>
                <ol>
                    <li><strong>تحميل تطبيق Bapco Energies:</strong> يجب عليك تحميل تطبيق Bapco Energies للاستفادة من جائزتك</li>
                    <li><strong>التحقق من البيانات:</strong> تأكد من صحة بيانات الهوية والهاتف لديك</li>
                    <li><strong>الانتظار للتواصل:</strong> سيتواصل معك فريقنا خلال 24 ساعة</li>
                    <li><strong>تأكيد الجائزة:</strong> سيتم تحويل الجائزة إلى حسابك مباشرة</li>
                </ol>
            </div>

            <!-- Notice -->
            <div class="notice-box">
                <p>⚠️ احذر من الاحتيال! لا تشاركي بيانات حسابك مع أحد. فريقنا الرسمي فقط هو من سيتواصل معك.</p>
            </div>

            <div class="button-container">
                <a href="{{ url('/callers/success') }}" class="btn">عرض التفاصيل الكاملة</a>
            </div>

            <p style="margin-top: 25px; text-align: center; color: rgba(255, 255, 255, 0.7); font-size: 0.9rem;">
                شكراً لك على المشاركة في برنامج السارية!
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="logo-text">🌙 السارية</div>
            <p class="footer-text">برنامج السارية - مسابقة رمضانية حصرية على شاشة تلفزيون البحرين</p>
            <p class="footer-text">© {{ date('Y') }} جميع الحقوق محفوظة | تلفزيون البحرين</p>
            <div class="footer-links">
                <a href="{{ url('/') }}">الموقع الرئيسي</a>
                <span class="separator">|</span>
                <a href="#" target="_blank">تحميل Bapco Energies</a>
                <span class="separator">|</span>
                <a href="mailto:winners@alsarya.tv">تواصل معنا</a>
            </div>
        </div>
    </div>
</body>
</html>
