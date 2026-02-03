<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlSaryaTV Maintenance</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #4338ca;
            padding: 20px;
            text-align: center;
        }
        .header img {
            max-width: 200px;
            height: auto;
        }
        .content {
            padding: 20px 30px;
            text-align: right;
        }
        .footer {
            background-color: #f8f8f8;
            padding: 15px 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        h1 {
            color: #4338ca;
            margin-top: 0;
        }
        .maintenance-icon {
            display: block;
            margin: 20px auto;
            width: 80px;
            height: auto;
        }
        .countdown {
            text-align: center;
            margin: 20px 0;
            font-size: 20px;
            font-weight: bold;
            color: #e53e3e;
        }
        .button {
            display: inline-block;
            background-color: #4338ca;
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/alsarya-logo.png') }}" alt="AlSaryaTV Logo">
        </div>
        
        <div class="content">
            <h1>الموقع تحت الصيانة</h1>
            
            <p>عزيزنا المستخدم،</p>
            
            <p>نود إعلامك أن موقع السارية TV غير متاح حاليًا بسبب {{ $reason }}.</p>
            
            <div class="maintenance-icon">
                ⚙️🔧
            </div>
            
            <p>الوقت المتوقع لانتهاء الصيانة هو <strong>{{ $downtimeSeconds }}</strong> ثانية من الآن (تقريبًا {{ $estimatedEndTime }}).</p>
            
            <div class="countdown">
                {{ $downtimeSeconds }} ثانية
            </div>
            
            <p>نعتذر عن أي إزعاج قد يسببه ذلك ونقدر صبرك وتفهمك.</p>
            
            <p>يمكنك محاولة تحديث الصفحة بعد الوقت المذكور للوصول إلى الموقع مرة أخرى.</p>
            
            <p>مع خالص الشكر،<br>فريق السارية TV</p>
            
            <a href="https://alsarya.tv" class="button">زيارة الموقع</a>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} AlSaryaTV. جميع الحقوق محفوظة.</p>
            <p>هذا بريد إلكتروني تلقائي، يرجى عدم الرد عليه.</p>
        </div>
    </div>
</body>
</html>
