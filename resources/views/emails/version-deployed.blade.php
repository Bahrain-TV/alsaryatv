<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إصدار جديد - برنامج السارية</title>
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
                        0 0 50px rgba(124, 58, 237, 0.2);
            border: 1px solid rgba(124, 58, 237, 0.2);
        }

        /* Header Section */
        .header {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.95) 0%, rgba(245, 158, 11, 0.95) 100%);
            padding: 40px 30px;
            text-align: center;
            border-bottom: 3px solid rgba(34, 197, 94, 0.4);
        }

        .header h1 {
            color: #0f172a;
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            letter-spacing: -0.5px;
        }

        .header-subtitle {
            color: rgba(15, 23, 42, 0.8);
            font-size: 0.95rem;
            font-weight: 600;
            margin-top: 8px;
            letter-spacing: 1px;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(5, 150, 105, 0.1) 100%);
            padding: 40px 30px;
            text-align: center;
            border-bottom: 1px solid rgba(52, 211, 153, 0.2);
        }

        .emoji-icon {
            font-size: 3.5rem;
            margin-bottom: 15px;
            display: inline-block;
        }

        .hero h2 {
            color: #34d399;
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0 0 12px 0;
            letter-spacing: -0.3px;
        }

        .hero-text {
            color: rgba(255, 255, 255, 0.85);
            font-size: 1rem;
            line-height: 1.8;
            margin: 0;
        }

        /* Content Section */
        .content {
            padding: 40px 30px;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.8;
        }

        .content h3 {
            color: #fbbf24;
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0 0 20px 0;
            border-right: 4px solid #fbbf24;
            padding-right: 12px;
        }

        .content p {
            margin: 0 0 16px 0;
            font-size: 0.95rem;
            line-height: 1.8;
        }

        .feature-list {
            background: rgba(15, 23, 42, 0.6);
            border-radius: 12px;
            padding: 20px 24px;
            margin: 20px 0;
            border-left: 4px solid #34d399;
        }

        .feature-list li {
            margin: 12px 0;
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.95rem;
        }

        .feature-list li::before {
            content: "✓ ";
            color: #34d399;
            font-weight: 700;
            margin-right: 8px;
        }

        .feature-list ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        /* Version Badge */
        .version-badge {
            display: inline-block;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #0f172a;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
            margin: 15px 0;
            letter-spacing: 0.5px;
        }

        /* CTA Button */
        .button-container {
            text-align: center;
            margin: 30px 0;
        }

        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #0f172a;
            padding: 14px 36px;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 16px rgba(251, 191, 36, 0.3);
            letter-spacing: 0.5px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(251, 191, 36, 0.4);
        }

        /* Highlight Box */
        .highlight-box {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.1) 0%, rgba(52, 211, 153, 0.1) 100%);
            border: 1px solid rgba(251, 191, 36, 0.2);
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #fbbf24;
        }

        .highlight-box p {
            margin: 0;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
        }

        /* Footer Section */
        .footer {
            background: rgba(15, 23, 42, 0.8);
            padding: 30px;
            text-align: center;
            border-top: 1px solid rgba(124, 58, 237, 0.2);
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
            color: #fbbf24;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            margin: 0 10px;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #34d399;
        }

        .separator {
            color: rgba(255, 255, 255, 0.3);
        }

        .logo-text {
            font-size: 1.25rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fbbf24 0%, #34d399 100%);
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
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .hero {
                padding: 30px 20px;
            }

            .content {
                padding: 25px 20px;
            }

            .emoji-icon {
                font-size: 2.5rem;
            }

            .hero h2 {
                font-size: 1.5rem;
            }

            .btn {
                padding: 12px 30px;
                font-size: 0.95rem;
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
            <h1>🚀 إصدار جديد</h1>
            <p class="header-subtitle">تحديث الميزات والتحسينات</p>
        </div>

        <!-- Hero Section -->
        <div class="hero">
            <div class="emoji-icon">✨</div>
            <h2>برنامج السارية - إصدار محدث</h2>
            <p class="hero-text">تم نشر إصدار جديد من تطبيقك مع ميزات رائعة وتحسينات الأداء</p>
        </div>

        <!-- Content -->
        <div class="content">
            <h3>📋 تفاصيل الإصدار</h3>
            <p>مرحباً بك!</p>
            <p>يسعدنا إخبارك بأن <strong>إصدار جديد</strong> من برنامج السارية قد تم نشره للتو.</p>

            <div class="version-badge">
                v{{ $version ?? '2026.0204.4' }}
            </div>

            <h3 style="margin-top: 30px;">🎯 ما الجديد؟</h3>
            <div class="feature-list">
                <ul>
                    @if(isset($features) && is_array($features))
                        @foreach($features as $feature)
                            <li>{{ $feature }}</li>
                        @endforeach
                    @else
                        <li>تحسينات الأداء والاستقرار</li>
                        <li>واجهة مستخدم محسّنة</li>
                        <li>ميزات جديدة ومبتكرة</li>
                        <li>إصلاحات الأخطاء والمشاكل</li>
                    @endif
                </ul>
            </div>

            <div class="highlight-box">
                <p><strong>💡 نصيحة:</strong> تحديث متاح الآن. قم بتحديث تطبيقك للحصول على أفضل تجربة استخدام.</p>
            </div>

            <h3 style="margin-top: 30px;">🔧 متطلبات التحديث</h3>
            <p>لا توجد متطلبات خاصة. التحديث متوافق مع جميع الإصدارات السابقة.</p>

            <div class="button-container">
                <a href="{{ $update_link ?? '#' }}" class="btn">🔄 تحديث الآن</a>
            </div>

            <p style="margin-top: 25px; color: rgba(255, 255, 255, 0.7); font-size: 0.9rem;">
                إذا واجهت أي مشاكل، لا تتردد في التواصل معنا للحصول على الدعم الفني.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="logo-text">السارية</div>
            <p class="footer-text">برنامج السارية - مسابقة رمضانية على شاشة تلفزيون البحرين</p>
            <p class="footer-text">© {{ date('Y') }} جميع الحقوق محفوظة</p>
            <div class="footer-links">
                <a href="{{ url('/') }}">الموقع الرئيسي</a>
                <span class="separator">|</span>
                <a href="mailto:info@alsarya.tv">تواصل معنا</a>
                <span class="separator">|</span>
                <a href="{{ route('privacy') }}">سياسة الخصوصية</a>
            </div>
        </div>
    </div>
</body>
</html>
