
<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');

        body {
            font-family: 'Tajawal', Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background: #0f172a;
            text-align: center;
            direction: rtl;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: linear-gradient(135deg, #1e293b, #0f172a);
            padding: 0;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }
        .header {
            text-align: center;
            padding: 30px 20px 20px;
            background: linear-gradient(135deg, #A81C2E, #7f1d1d);
            border-radius: 16px 16px 0 0;
        }
        .header img {
            max-width: 180px;
            height: auto;
            margin-bottom: 10px;
        }
        .header h1 {
            font-family: 'Tajawal', Arial, sans-serif;
            color: #E8D7C3;
            font-size: 28px;
            margin: 10px 0 0;
            font-weight: 700;
        }
        .content {
            padding: 30px 25px;
            color: #e2e8f0;
            font-size: 16px;
        }
        .content p {
            margin: 12px 0;
        }
        .content strong {
            color: #f59e0b;
            font-size: 18px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .footer a {
            color: #f59e0b;
            text-decoration: none;
        }
        .button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #A81C2E, #E8D7C3);
            color: #fff !important;
            text-decoration: none;
            border-radius: 12px;
            margin: 20px 0;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/alsarya-logo-2026-tiny.png') }}" alt="السارية - برنامج تلفزيون البحرين" />
            <h1>🌙 مرحباً بك في برنامج السارية</h1>
        </div>

        <div class="content">
            <p>باسووردك الجديد: <strong>{{ $password }}</strong></p>
            <p>يرجى تغييره بمجرد تسجيل الدخول.</p>
            <p>شكرًا لاستخدامك برنامج السارية!</p>
            <a href="https://alsarya.tv" class="button">🔗 زيارة الموقع</a>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} برنامج السارية - تلفزيون البحرين. جميع الحقوق محفوظة.</p>
            <p>الرد على هذا البريد الإلكتروني للحصول على المساعدة.</p>
            <p>لقد تلقيت هذا البريد الإلكتروني لأنك اشتركت في نشرتنا الإخبارية.</p>
            <p><a href="{{ $unsubscribe_link }}">إلغاء الاشتراك</a> | <a href="{{ $view_in_browser_link }}">عرض في المتصفح</a></p>
        </div>
    </div>
</body>
</html>