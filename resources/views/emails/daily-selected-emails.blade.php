<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌟 الأسماء المختارة يومياً - برنامج السارية</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;600;700;800;900&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 25%, #312e81 50%, #1e1b4b 75%, #0f172a 100%);
            padding: 20px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: rgba(30, 41, 59, 0.95);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.5),
                0 0 100px rgba(139, 92, 246, 0.3),
                0 0 200px rgba(59, 130, 246, 0.2);
            border: 2px solid rgba(139, 92, 246, 0.5);
            position: relative;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, 
                #8b5cf6 0%, 
                #a78bfa 25%, 
                #c4b5fd 50%, 
                #a78bfa 75%, 
                #8b5cf6 100%);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        /* Animated background particles */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(139, 92, 246, 0.6);
            border-radius: 50%;
            animation: float 15s infinite;
        }

        .particle:nth-child(1) { left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { left: 20%; animation-delay: 2s; }
        .particle:nth-child(3) { left: 30%; animation-delay: 4s; }
        .particle:nth-child(4) { left: 50%; animation-delay: 1s; }
        .particle:nth-child(5) { left: 60%; animation-delay: 3s; }
        .particle:nth-child(6) { left: 70%; animation-delay: 5s; }
        .particle:nth-child(7) { left: 80%; animation-delay: 2.5s; }
        .particle:nth-child(8) { left: 90%; animation-delay: 4.5s; }

        @keyframes float {
            0%, 100% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
                opacity: 0;
            }
        }

        /* Header Section - Purple Gradient */
        .header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 50%, #6d28d9 100%);
            padding: 60px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: pulse 4s ease-in-out infinite;
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: -50%;
            left: -10%;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            animation: pulse 4s ease-in-out infinite 2s;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .header-icon {
            font-size: 4rem;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .header h1 {
            color: #ffffff;
            font-size: 2.8rem;
            font-weight: 900;
            margin: 0;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
            letter-spacing: -1px;
            position: relative;
            z-index: 1;
        }

        .header-subtitle {
            color: rgba(255, 255, 255, 0.95);
            font-size: 1.1rem;
            font-weight: 700;
            margin-top: 12px;
            letter-spacing: 1px;
            position: relative;
            z-index: 1;
        }

        .header-date {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 50px;
            padding: 8px 24px;
            display: inline-block;
            margin-top: 15px;
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 600;
            position: relative;
            z-index: 1;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Content Section */
        .content {
            padding: 50px 35px;
            color: rgba(255, 255, 255, 0.9);
            position: relative;
            z-index: 1;
        }

        .intro-section {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.2) 0%, rgba(124, 58, 237, 0.1) 100%);
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 35px;
            border-right: 5px solid #8b5cf6;
            text-align: center;
        }

        .intro-section h2 {
            color: #c4b5fd;
            font-size: 1.6rem;
            font-weight: 800;
            margin: 0 0 15px 0;
        }

        .intro-section p {
            font-size: 1.1rem;
            line-height: 1.9;
            margin: 0;
            color: rgba(255, 255, 255, 0.9);
        }

        .highlight-number {
            display: inline-block;
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
            padding: 15px 35px;
            border-radius: 50px;
            font-size: 1.8rem;
            font-weight: 900;
            color: #ffffff;
            margin: 20px 0;
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.4);
            animation: glow 2s ease-in-out infinite;
        }

        @keyframes glow {
            0%, 100% { box-shadow: 0 10px 30px rgba(139, 92, 246, 0.4); }
            50% { box-shadow: 0 15px 40px rgba(139, 92, 246, 0.6); }
        }

        /* Selected Names Section */
        .selected-section h3 {
            color: #a78bfa;
            font-size: 1.7rem;
            font-weight: 800;
            margin: 40px 0 25px 0;
            text-align: center;
            position: relative;
        }

        .selected-section h3::before {
            content: '✨';
            margin-left: 10px;
        }

        .selected-section h3::after {
            content: '✨';
            margin-right: 10px;
        }

        .names-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }

        .name-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.8) 0%, rgba(51, 34, 111, 0.6) 100%);
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 16px;
            padding: 25px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .name-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, #8b5cf6 0%, #a78bfa 100%);
            border-radius: 4px 0 0 4px;
        }

        .name-card:hover {
            transform: translateY(-5px);
            border-color: rgba(139, 92, 246, 0.6);
            box-shadow: 0 15px 35px rgba(139, 92, 246, 0.3);
        }

        .name-number {
            display: inline-block;
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: #ffffff;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            text-align: center;
            line-height: 35px;
            font-weight: 800;
            font-size: 1.1rem;
            margin-bottom: 12px;
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
        }

        .name-info {
            margin-top: 10px;
        }

        .name-label {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 4px;
        }

        .name-value {
            font-size: 1.15rem;
            font-weight: 700;
            color: #c4b5fd;
            word-break: break-word;
        }

        .name-value.cpr {
            font-family: 'Courier New', monospace;
            font-size: 1rem;
            letter-spacing: 1px;
        }

        .hits-badge {
            display: inline-block;
            background: rgba(139, 92, 246, 0.2);
            border: 1px solid rgba(139, 92, 246, 0.4);
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 0.85rem;
            color: #a78bfa;
            font-weight: 600;
            margin-top: 10px;
        }

        /* Stats Section */
        .stats-section {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 16px;
            padding: 30px;
            margin-top: 40px;
            border-left: 5px solid #8b5cf6;
        }

        .stats-section h3 {
            color: #a78bfa;
            font-size: 1.4rem;
            font-weight: 800;
            margin: 0 0 20px 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background: rgba(139, 92, 246, 0.1);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            border: 1px solid rgba(139, 92, 246, 0.2);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            background: rgba(139, 92, 246, 0.15);
            transform: scale(1.05);
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 2.2rem;
            font-weight: 900;
            background: linear-gradient(135deg, #a78bfa 0%, #c4b5fd 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 8px;
        }

        /* Footer Section */
        .footer {
            background: rgba(15, 23, 42, 0.8);
            padding: 35px;
            text-align: center;
            border-top: 1px solid rgba(139, 92, 246, 0.3);
            position: relative;
            z-index: 1;
        }

        .footer-logo {
            font-size: 1.4rem;
            font-weight: 900;
            background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 50%, #c4b5fd 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0 0 15px 0;
            letter-spacing: -0.5px;
        }

        .footer-text {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
            line-height: 1.9;
            margin: 8px 0;
        }

        .footer-highlight {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.85rem;
            background: rgba(139, 92, 246, 0.15);
            padding: 10px 20px;
            border-radius: 25px;
            display: inline-block;
            margin-top: 15px;
            border: 1px solid rgba(139, 92, 246, 0.3);
        }

        .powered-by {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(139, 92, 246, 0.2);
        }

        .powered-by-text {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.5);
        }

        .powered-by-text strong {
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 12px;
            }

            .header {
                padding: 40px 20px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .header-icon {
                font-size: 3rem;
            }

            .content {
                padding: 30px 20px;
            }

            .names-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .footer {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Animated Particles -->
        <div class="particles">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>

        <!-- Header -->
        <div class="header">
            <div class="header-icon">🌟</div>
            <h1>الأسماء المختارة يومياً</h1>
            <p class="header-subtitle">برنامج السارية المباشر على شاشة تلفزيون البحرين</p>
            <div class="header-date">
                {{ $dayName }} - {{ $formattedDate }}
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Intro Section -->
            <div class="intro-section">
                <h2>🎊 تهانينا للمختارين اليوم!</h2>
                <p>تم اختيار الأسماء التالية عشوائياً من بين جميع المشاركين في برنامج السارية</p>
                <div class="highlight-number">
                    {{ count($selectedCallers) }} اسم مختار
                </div>
                <p style="margin-top: 15px; font-size: 0.95rem; color: rgba(255, 255, 255, 0.8);">
                    من إجمالي <strong>{{ $totalCount }}</strong> مشارك مؤهل
                </p>
            </div>

            <!-- Selected Names Grid -->
            @if(count($selectedCallers) > 0)
                <div class="selected-section">
                    <h3>قائمة الأسماء المختارة</h3>

                    <div class="names-grid">
                        @foreach($selectedCallers as $index => $caller)
                            <div class="name-card">
                                <div class="name-number">{{ $index + 1 }}</div>
                                
                                <div class="name-info">
                                    <div class="name-label">الاسم</div>
                                    <div class="name-value">{{ $caller['name'] ?? 'غير متاح' }}</div>
                                </div>

                                @if(isset($caller['cpr']) && $caller['cpr'])
                                    <div class="name-info" style="margin-top: 12px;">
                                        <div class="name-label">رقم الهوية</div>
                                        <div class="name-value cpr">{{ $caller['cpr'] }}</div>
                                    </div>
                                @endif

                                @if(isset($caller['phone']) && $caller['phone'])
                                    <div class="name-info" style="margin-top: 12px;">
                                        <div class="name-label">رقم الهاتف</div>
                                        <div class="name-value">{{ $caller['phone'] }}</div>
                                    </div>
                                @endif

                                @if(isset($caller['hits']) && $caller['hits'])
                                    <div class="hits-badge">
                                        📞 {{ $caller['hits'] }} مشاركة
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="intro-section" style="border-right-color: #f59e0b; background: linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(245, 158, 11, 0.1) 100%);">
                    <h2 style="color: #fbbf24;">⚠️ لا توجد أسماء مختارة</h2>
                    <p>لم يتم اختيار أي أسماء في هذا اليوم. تأكد من وجود مشاركين مؤهلين في النظام.</p>
                </div>
            @endif

            <!-- Statistics Section -->
            <div class="stats-section">
                <h3>📊 إحصائيات التقرير اليومي</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">🎯</div>
                        <div class="stat-number">{{ count($selectedCallers) }}</div>
                        <div class="stat-label">اسم مختار اليوم</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-number">{{ $totalCount }}</div>
                        <div class="stat-label">إجمالي المؤهلين</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📅</div>
                        <div class="stat-number">{{ now()->locale('ar')->translatedFormat('j F') }}</div>
                        <div class="stat-label">التاريخ</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">⏰</div>
                        <div class="stat-number">{{ now()->locale('ar')->translatedFormat('H:i') }}</div>
                        <div class="stat-label">وقت الإرسال</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-logo">📺 برنامج السارية</div>
            <p class="footer-text">🎯 مسابقة رمضانية حصرية على شاشة تلفزيون البحرين</p>
            <p class="footer-text">🇧🇭 مملكة البحرين</p>
            <div class="footer-highlight">
                ✨ هذا التقرير يتم إنشاؤه وإرساله تلقائياً يومياً
            </div>
            <div class="powered-by">
                <p class="powered-by-text">
                    Powered by <strong>Qwen Code</strong> - Advanced AI Capabilities
                </p>
                <p class="footer-text" style="margin-top: 8px; font-size: 0.75rem;">
                    © {{ date('Y') }} جميع الحقوق محفوظة | تلفزيون البحرين
                </p>
            </div>
        </div>
    </div>
</body>
</html>
