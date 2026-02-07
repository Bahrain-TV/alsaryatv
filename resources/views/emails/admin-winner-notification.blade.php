<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📋 تقرير الفائزين</title>
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
            padding: 20px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(30, 41, 59, 0.95);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5),
                        0 0 100px rgba(59, 130, 246, 0.3);
            border: 2px solid rgba(59, 130, 246, 0.4);
        }

        /* Header Section - Admin Blue */
        .header {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
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
            font-size: 2.5rem;
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

        /* Content Section */
        .content {
            padding: 40px 30px;
            color: rgba(255, 255, 255, 0.9);
        }

        .announcement {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(30, 58, 138, 0.1) 100%);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 30px;
            border-right: 4px solid #3b82f6;
        }

        .announcement p {
            font-size: 1.05rem;
            line-height: 1.8;
            margin: 0;
            color: rgba(255, 255, 255, 0.95);
        }

        .winners-section h2 {
            color: #60a5fa;
            font-size: 1.5rem;
            font-weight: 800;
            margin: 30px 0 20px 0;
            border-right: 4px solid #60a5fa;
            padding-right: 12px;
        }

        .winners-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 12px;
            overflow: hidden;
        }

        .winners-table thead {
            background: linear-gradient(90deg, rgba(59, 130, 246, 0.2) 0%, rgba(30, 58, 138, 0.1) 100%);
        }

        .winners-table th {
            padding: 16px;
            text-align: right;
            font-weight: 700;
            color: #60a5fa;
            border-bottom: 2px solid rgba(59, 130, 246, 0.3);
            font-size: 0.95rem;
        }

        .winners-table td {
            padding: 14px 16px;
            border-bottom: 1px solid rgba(59, 130, 246, 0.15);
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.95rem;
        }

        .winners-table tr:last-child td {
            border-bottom: none;
        }

        .winners-table tbody tr:hover {
            background: rgba(59, 130, 246, 0.1);
        }

        .winner-name {
            font-weight: 600;
            color: #93c5fd;
        }

        .winner-cpr {
            font-family: 'Courier New', monospace;
            color: #93c5fd;
            font-size: 0.9rem;
        }

        .stats-section {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 12px;
            padding: 24px;
            margin-top: 30px;
            border-left: 4px solid #3b82f6;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 16px;
        }

        .stat-item {
            text-align: center;
            padding: 12px;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 8px;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: #60a5fa;
        }

        .stat-label {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 4px;
        }

        .actions-section {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(30, 58, 138, 0.05) 100%);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 12px;
            padding: 24px;
            margin-top: 30px;
            text-align: center;
        }

        .actions-section h3 {
            color: #60a5fa;
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0 0 16px 0;
        }

        .admin-link {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            color: #ffffff;
            padding: 12px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            margin: 0 8px;
        }

        .admin-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
        }

        /* Footer Section */
        .footer {
            background: rgba(15, 23, 42, 0.8);
            padding: 30px;
            text-align: center;
            border-top: 1px solid rgba(59, 130, 246, 0.2);
        }

        .footer-text {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
            line-height: 1.8;
            margin: 0 0 12px 0;
        }

        .logo-text {
            font-size: 1.25rem;
            font-weight: 800;
            background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0 0 10px 0;
            letter-spacing: -0.5px;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 8px;
            }

            .header {
                padding: 40px 20px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .content {
                padding: 25px 20px;
            }

            .winners-table th,
            .winners-table td {
                padding: 12px 8px;
                font-size: 0.85rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .footer {
                padding: 25px 20px;
            }

            .admin-link {
                display: block;
                margin: 8px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📋 تقرير الفائزين</h1>
            <p class="header-subtitle">برنامج السارية المباشر على شاشة تلفزيون البحرين</p>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Announcement -->
            <div class="announcement">
                <p>{{ $announcement }}</p>
            </div>

            <!-- Winners Table -->
            @if(count($winners) > 0)
                <div class="winners-section">
                    <h2>🏆 قائمة الفائزين ({{ $winner_count }} فائز)</h2>

                    <table class="winners-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم الفائز</th>
                                <th>رقم الهاتف</th>
                                <th>رقم الهوية</th>
                                <th>عدد المشاركات</th>
                                <th>تاريخ الاختيار</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($winners as $index => $winner)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="winner-name">{{ $winner['name'] ?? 'N/A' }}</td>
                                    <td>{{ $winner['phone'] ?? 'N/A' }}</td>
                                    <td class="winner-cpr">{{ $winner['cpr'] ?? 'N/A' }}</td>
                                    <td>{{ $winner['hits'] ?? 0 }} مشاركة</td>
                                    <td>{{ $winner['selected_at'] ?? now()->locale('ar')->translatedFormat('j F Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Statistics -->
                <div class="stats-section">
                    <h3>📊 إحصائيات الفائزين</h3>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-number">{{ $winner_count }}</div>
                            <div class="stat-label">إجمالي الفائزين</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">{{ now()->locale('ar')->translatedFormat('j F') }}</div>
                            <div class="stat-label">التاريخ</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">{{ now()->locale('ar')->translatedFormat('H:i') }}</div>
                            <div class="stat-label">الوقت</div>
                        </div>
                    </div>
                </div>
            @else
                <div class="announcement" style="border-right-color: #f59e0b; border-left-color: #f59e0b;">
                    <p>⚠️ لا توجد فائزين للإبلاغ عنهم في هذا الوقت.</p>
                </div>
            @endif

            <!-- Admin Actions -->
            <div class="actions-section">
                <h3>⚙️ الإجراءات المتاحة</h3>
                <a href="{{ url('/admin/caller-resource/winners') }}" class="admin-link">عرض جميع الفائزين</a>
                <a href="{{ url('/admin') }}" class="admin-link">لوحة التحكم</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="logo-text">📺 السارية</div>
            <p class="footer-text">برنامج السارية المباشر - مسابقة رمضانية حصرية على شاشة تلفزيون البحرين</p>
            <p class="footer-text">© {{ date('Y') }} جميع الحقوق محفوظة | تلفزيون البحرين</p>
        </div>
    </div>
</body>
</html>
