<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>📋 تقرير الفائزين - برنامج السارية</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;600;700;800&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Tajawal', 'Segoe UI', 'Arial Unicode MS', 'Lucida Grande', sans-serif; 
            background: #0f172a; 
            padding: 16px;
            direction: rtl;
            text-align: right;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .container { max-width: 900px; margin: 0 auto; background: #1a2d4d; border-radius: 16px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        /* Ensure Arabic text rendering */
        p, span, div, td, th, h1, h2, h3, h4, h5, h6 { 
            direction: rtl; 
            text-align: right;
            word-wrap: break-word;
            word-break: break-word;
        }
        @media (max-width: 600px) {
            body { padding: 12px; }
            .container { border-radius: 12px; }
            .desktop-only { display: none !important; }
            .mobile-only { display: block !important; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- ADMIN HEADER WITH STATS -->
        <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%); padding: 40px 32px; text-align: center; border-bottom: 3px solid rgba(16, 185, 129, 0.5);">
            <h1 style="color: #ffffff; font-size: 2.4rem; font-weight: 800; margin: 0 0 8px 0; text-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);">📊 تقرير الفائزين</h1>
            <p style="color: rgba(255, 255, 255, 0.92); font-size: 1.05rem; font-weight: 500; margin: 0;">برنامج السارية المباشر على تلفزيون البحرين</p>
        </div>

        <!-- STATS GRID -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; padding: 24px 32px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(52, 211, 153, 0.05) 100%); border-bottom: 2px solid rgba(16, 185, 129, 0.2);">
            <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 16px; text-align: center;">
                <div style="color: rgba(255, 255, 255, 0.65); font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">🏆 إجمالي الفائزين</div>
                <p style="color: #34d399; font-size: 2rem; font-weight: 800; margin: 0;">🎯 {{ $winners_count }}</p>
            </div>
            <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 16px; text-align: center;">
                <div style="color: rgba(255, 255, 255, 0.65); font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">📞 إجمالي الاتصالات</div>
                <p style="color: #34d399; font-size: 2rem; font-weight: 800; margin: 0;">📈 {{ $total_hits }}</p>
            </div>
            <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 16px; text-align: center;">
                <div style="color: rgba(255, 255, 255, 0.65); font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">⏰ وقت التقرير</div>
                <p style="color: #34d399; font-size: 1.2rem; font-weight: 800; margin: 0;">{{ substr($generated_at, 0, 10) }}</p>
            </div>
            <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 16px; text-align: center;">
                <div style="color: rgba(255, 255, 255, 0.65); font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">📅 متوسط الاتصالات</div>
                <p style="color: #34d399; font-size: 2rem; font-weight: 800; margin: 0;">📊 {{ $winners_count > 0 ? round($total_hits / $winners_count) : 0 }}</p>
            </div>
        </div>

        <!-- WINNERS SECTION -->
        <div style="padding: 32px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid rgba(16, 185, 129, 0.3);">
                <span style="font-size: 2rem;">👥</span>
                <h2 style="color: #ffffff; font-size: 1.6rem; font-weight: 800; margin: 0;">قائمة الفائزين</h2>
            </div>

            <!-- WINNERS TABLE -->
            <div style="overflow-x: auto;" class="desktop-only">
                <table>
                    <thead>
                        <tr style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.25) 0%, rgba(52, 211, 153, 0.15) 100%); border-bottom: 2px solid rgba(16, 185, 129, 0.4);">
                            <th style="color: #34d399; font-size: 0.9rem; font-weight: 700; padding: 16px 12px; text-align: right; letter-spacing: 0px; text-transform: uppercase; border-right: 1px solid rgba(16, 185, 129, 0.2); direction: rtl; font-family: 'Tajawal', 'Segoe UI', 'Arial Unicode MS', sans-serif;">#</th>
                            <th style="color: #34d399; font-size: 0.9rem; font-weight: 700; padding: 16px 12px; text-align: right; letter-spacing: 0px; border-right: 1px solid rgba(16, 185, 129, 0.2); direction: rtl; font-family: 'Tajawal', 'Segoe UI', 'Arial Unicode MS', sans-serif;">الاسم</th>
                            <th style="color: #34d399; font-size: 0.9rem; font-weight: 700; padding: 16px 12px; text-align: right; letter-spacing: 0px; border-right: 1px solid rgba(16, 185, 129, 0.2); direction: rtl; font-family: 'Tajawal', 'Segoe UI', 'Arial Unicode MS', sans-serif;">رقم الهوية</th>
                            <th style="color: #34d399; font-size: 0.9rem; font-weight: 700; padding: 16px 12px; text-align: right; letter-spacing: 0px; border-right: 1px solid rgba(16, 185, 129, 0.2); direction: rtl; font-family: 'Tajawal', 'Segoe UI', 'Arial Unicode MS', sans-serif;">الهاتف</th>
                            <th style="color: #34d399; font-size: 0.9rem; font-weight: 700; padding: 16px 12px; text-align: right; letter-spacing: 0px; border-right: 1px solid rgba(16, 185, 129, 0.2); direction: rtl; font-family: 'Tajawal', 'Segoe UI', 'Arial Unicode MS', sans-serif;">الاتصالات</th>
                            <th style="color: #34d399; font-size: 0.9rem; font-weight: 700; padding: 16px 12px; text-align: right; direction: rtl; font-family: 'Tajawal', 'Segoe UI', 'Arial Unicode MS', sans-serif;">التاريخ</th>
                        </tr>
                    </thead>
                    <tbody style="background: rgba(15, 23, 42, 0.4);">
                        @forelse($winners as $index => $winner)
                            <tr style="border-bottom: 1px solid rgba(52, 211, 153, 0.15);">
                                <td style="color: rgba(255, 255, 255, 0.85); font-size: 0.95rem; padding: 14px 12px; text-align: right; vertical-align: middle; border-right: 1px solid rgba(16, 185, 129, 0.1);">
                                    <span style="background: linear-gradient(135deg, rgba(251, 191, 36, 0.2) 0%, rgba(16, 185, 129, 0.1) 100%); color: #fbbf24; font-weight: 800; font-size: 1.05rem; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; min-width: 32px; height: 32px;">{{ $index + 1 }}</span>
                                </td>
                                <td style="color: #ffffff; font-weight: 700; font-size: 0.98rem; padding: 14px 12px; text-align: right; vertical-align: middle; border-right: 1px solid rgba(16, 185, 129, 0.1); direction: rtl; font-family: 'Tajawal', 'Segoe UI', 'Arial Unicode MS', sans-serif; word-break: break-word;">{{ $winner->name }}</td>
                                <td style="color: #94a3b8; font-size: 0.85rem; font-weight: 600; letter-spacing: 2px; padding: 14px 12px; text-align: right; vertical-align: middle; border-right: 1px solid rgba(16, 185, 129, 0.1); font-family: 'Courier New', monospace;">{{ $winner->cpr }}</td>
                                <td style="color: rgba(255, 255, 255, 0.85); font-size: 0.95rem; padding: 14px 12px; text-align: right; vertical-align: middle; border-right: 1px solid rgba(16, 185, 129, 0.1); direction: rtl; font-family: 'Tajawal', 'Segoe UI', 'Arial Unicode MS', sans-serif; word-break: break-word;">{{ $winner->phone }}</td>
                                <td style="color: rgba(255, 255, 255, 0.85); font-size: 0.95rem; padding: 14px 12px; text-align: right; vertical-align: middle; border-right: 1px solid rgba(16, 185, 129, 0.1);">
                                    <span style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.25) 0%, rgba(168, 85, 247, 0.15) 100%); color: #c4b5fd; padding: 6px 12px; border-radius: 8px; display: inline-block; font-weight: 700; font-size: 0.9rem;">{{ $winner->hits }} 📞</span>
                                </td>
                                <td style="color: rgba(255, 255, 255, 0.65); font-size: 0.9rem; padding: 14px 12px; text-align: right; vertical-align: middle; direction: rtl; word-break: break-word;">{{ $winner->created_at?->locale('ar')->translatedFormat('j F Y') ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 32px; color: rgba(255, 255, 255, 0.5);">🔍 لا توجد فائزين مسجلين حالياً</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- MOBILE CARDS -->
            <div class="mobile-only" style="display: none;">
                @forelse($winners as $index => $winner)
                    <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(52, 211, 153, 0.08) 100%); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 14px; padding: 14px; margin-bottom: 14px; overflow: hidden;">
                        <!-- CARD HEADER: Rank & Name -->
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px; padding-bottom: 14px; border-bottom: 1px solid rgba(16, 185, 129, 0.2);">
                            <div style="background: linear-gradient(135deg, rgba(251, 191, 36, 0.3) 0%, rgba(16, 185, 129, 0.2) 100%); color: #fbbf24; font-weight: 800; font-size: 1rem; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; line-height: 1;">{{ $index + 1 }}</div>
                            <div style="flex: 1; overflow: hidden;">
                                <p style="color: #34d399; font-size: 0.75rem; font-weight: 700; margin: 0 0 2px 0; text-transform: uppercase; letter-spacing: 0px; direction: rtl; font-family: 'Tajawal', 'Segoe UI', 'Arial Unicode MS', sans-serif;">🏆 الفائز</p>
                                <p style="color: #ffffff; font-size: 1rem; font-weight: 800; margin: 0; word-break: break-word; line-height: 1.4; direction: rtl; font-family: 'Tajawal', 'Segoe UI', 'Arial Unicode MS', sans-serif;">{{ $winner->name }}</p>
                            </div>
                        </div>

                        <!-- CARD BODY: Key Info in 2-column layout -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                            <!-- CPR Card -->
                            <div style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 10px; padding: 12px; text-align: center;">
                                <div style="color: rgba(255, 255, 255, 0.6); font-size: 0.75rem; font-weight: 600; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.3px;">🪪</div>
                                <div style="color: #94a3b8; font-size: 0.85rem; font-weight: 700; font-family: 'Courier New', monospace; word-break: break-all; line-height: 1.3;">{{ $winner->cpr }}</div>
                            </div>

                            <!-- Hits Card -->
                            <div style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.2) 0%, rgba(168, 85, 247, 0.1) 100%); border: 1px solid rgba(168, 85, 247, 0.3); border-radius: 10px; padding: 12px; text-align: center;">
                                <div style="color: rgba(255, 255, 255, 0.6); font-size: 0.75rem; font-weight: 600; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.3px;">☎️ الاتصالات</div>
                                <div style="color: #d8b4fe; font-size: 1.3rem; font-weight: 800; line-height: 1;">{{ $winner->hits }}</div>
                            </div>
                        </div>

                        <!-- CARD FOOTER: Phone & Date in single row -->
                        <div style="background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 10px; padding: 12px;">
                            <div style="color: rgba(255, 255, 255, 0.55); font-size: 0.75rem; font-weight: 600; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0px; direction: rtl;">📞 الهاتف</div>
                            <div style="color: #e2e8f0; font-size: 0.95rem; font-weight: 700; margin-bottom: 10px; word-break: break-word; line-height: 1.4; direction: rtl;">{{ $winner->phone }}</div>
                            <div style="border-top: 1px solid rgba(16, 185, 129, 0.15); padding-top: 10px; color: rgba(255, 255, 255, 0.65); font-size: 0.9rem; font-weight: 600; direction: rtl; word-break: break-word; line-height: 1.4;">📅 {{ $winner->created_at?->locale('ar')->translatedFormat('j F Y H:i') ?? 'N/A' }}</div>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 32px 16px; color: rgba(255, 255, 255, 0.5);">
                        <div style="font-size: 2rem; margin-bottom: 8px;">🔍</div>
                        <div style="font-size: 0.95rem;">لا توجد فائزين في آخر 24 ساعة</div>
                    </div>
                @endforelse
            </div>

            <!-- ADMIN NOTE -->
            <div style="background: linear-gradient(135deg, rgba(251, 191, 36, 0.12) 0%, rgba(245, 158, 11, 0.08) 100%); border: 2px solid rgba(251, 191, 36, 0.35); border-radius: 14px; padding: 24px; margin: 24px 0 0 0; border-left: 4px solid #fbbf24;">
                <h4 style="color: #fbbf24; font-size: 1.1rem; font-weight: 800; margin: 0 0 12px 0;">⚠️ ملاحظة إدارية</h4>
                <p style="color: rgba(255, 255, 255, 0.85); font-size: 0.95rem; line-height: 1.7; margin: 0;">
                    هذا التقرير يحتوي على بيانات الفائزين المُختلفة الحالية. لم يتم إرسال أي بريد إلكتروني لهؤلاء الفائزين. 
                    تواصل مع الفائزين من خلال أرقام هواتفهم المسجلة. الحفاظ على خصوصية بيانات الفائزين مسؤوليتك.
                </p>
            </div>
        </div>

        <!-- FOOTER -->
        <div style="background: rgba(15, 23, 42, 0.8); padding: 28px 32px; text-align: center; border-top: 1px solid rgba(16, 185, 129, 0.2);">
            <p style="font-size: 1.2rem; font-weight: 800; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin: 0 0 8px 0;">🌙 السارية</p>
            <p style="color: rgba(255, 255, 255, 0.55); font-size: 0.85rem; line-height: 1.7; margin: 0;">
                برنامج السارية <span style="color: rgba(255, 255, 255, 0.25);">•</span> تلفزيون البحرين <span style="color: rgba(255, 255, 255, 0.25);">•</span> © {{ date('Y') }}
            </p>
            <p style="color: rgba(255, 255, 255, 0.55); font-size: 0.75rem; margin-top: 8px;">
                تقرير آلي مُنشأ في: {{ $generated_at }}
            </p>
        </div>
    </div>
</body>
</html>
