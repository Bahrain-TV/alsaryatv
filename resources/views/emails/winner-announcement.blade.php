<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>📋 تقرير الفائزين - برنامج السارية</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;600;700;800&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            height: 100%;
        }

        body {
            font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1a1a3e 50%, #0f172a 100%);
            background-attachment: fixed;
            padding: 16px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            min-width: 256px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: linear-gradient(135deg, rgba(20, 30, 60, 0.98) 0%, rgba(15, 23, 42, 0.98) 100%);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.6),
                        0 0 120px rgba(16, 185, 129, 0.25);
            border: 1px solid rgba(16, 185, 129, 0.35);
        }

        /* === ADMIN HEADER (STATS) === */
        .admin-header {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            padding: 40px 32px;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-bottom: 3px solid rgba(16, 185, 129, 0.5);
        }

        .admin-header::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -8%;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            z-index: 0;
        }

        .admin-header::after {
            content: '';
            position: absolute;
            bottom: -40%;
            left: -8%;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
            z-index: 0;
        }

        .admin-header-content {
            position: relative;
            z-index: 1;
        }

        .admin-title {
            color: #ffffff;
            font-size: 2.4rem;
            font-weight: 800;
            margin: 0 0 8px 0;
            text-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.5px;
        }

        .admin-subtitle {
            color: rgba(255, 255, 255, 0.92);
            font-size: 1.05rem;
            font-weight: 500;
            margin: 0;
            letter-spacing: 0.5px;
        }

        /* === STATS GRID === */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            padding: 24px 32px;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(52, 211, 153, 0.05) 100%);
            border-bottom: 2px solid rgba(16, 185, 129, 0.2);
        }

        .stat-box {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-box:hover {
            background: rgba(15, 23, 42, 0.8);
            border-color: rgba(16, 185, 129, 0.6);
            transform: translateY(-2px);
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.65);
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .stat-value {
            color: #34d399;
            font-size: 2rem;
            font-weight: 800;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .stat-emoji {
            font-size: 1.8rem;
        }

        /* === WINNERS TABLE (Desktop) === */
        .winners-section {
            padding: 32px;
        }

        .winners-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid rgba(16, 185, 129, 0.3);
        }

        .winners-title {
            color: #ffffff;
            font-size: 1.6rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.3px;
        }

        .winners-title-emoji {
            font-size: 2rem;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            background: rgba(15, 23, 42, 0.4);
        }

        thead {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.25) 0%, rgba(52, 211, 153, 0.15) 100%);
            border-bottom: 2px solid rgba(16, 185, 129, 0.4);
        }

        th {
            color: #34d399;
            font-size: 0.9rem;
            font-weight: 700;
            padding: 16px 12px;
            text-align: right;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        tbody tr {
            border-bottom: 1px solid rgba(52, 211, 153, 0.15);
            transition: all 0.2s ease;
        }

        tbody tr:hover {
            background: rgba(16, 185, 129, 0.1);
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        td {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.95rem;
            padding: 14px 12px;
            text-align: right;
            vertical-align: middle;
        }

        .winner-rank {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.2) 0%, rgba(16, 185, 129, 0.1) 100%);
            color: #fbbf24;
            font-weight: 800;
            font-size: 1.05rem;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
        }

        .winner-name-cell {
            color: #ffffff;
            font-weight: 700;
            font-size: 0.98rem;
        }

        .cpr-masked {
            font-family: 'Courier New', monospace;
            color: #94a3b8;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 2px;
        }

        .hits-badge {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.25) 0%, rgba(168, 85, 247, 0.15) 100%);
            color: #c4b5fd;
            padding: 6px 12px;
            border-radius: 8px;
            display: inline-block;
            font-weight: 700;
            font-size: 0.9rem;
            white-space: nowrap;
        }

        .date-cell {
            color: rgba(255, 255, 255, 0.65);
            font-size: 0.85rem;
        }

        /* === MOBILE CARDS (Hide on desktop) === */
        .winner-cards {
            display: none;
        }

        /* === ADMIN NOTE SECTION === */
        .admin-note {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.12) 0%, rgba(245, 158, 11, 0.08) 100%);
            border: 2px solid rgba(251, 191, 36, 0.35);
            border-radius: 14px;
            padding: 24px;
            margin: 24px 0 0 0;
            border-left: 4px solid #fbbf24;
        }

        .admin-note-title {
            color: #fbbf24;
            font-size: 1.1rem;
            font-weight: 800;
            margin: 0 0 12px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-note-content {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.95rem;
            line-height: 1.7;
            margin: 0;
        }

        /* === ACTION BUTTONS === */
        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            white-space: nowrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(16, 185, 129, 0.4);
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }

        .btn-secondary {
            background: rgba(51, 65, 85, 0.8);
            color: #e2e8f0;
            border: 1px solid rgba(148, 163, 184, 0.3);
        }

        .btn-secondary:hover {
            background: rgba(51, 65, 85, 1);
            border-color: rgba(148, 163, 184, 0.6);
        }

        /* === FOOTER === */
        .footer {
            background: rgba(15, 23, 42, 0.8);
            padding: 28px 32px;
            text-align: center;
            border-top: 1px solid rgba(16, 185, 129, 0.2);
        }

        .footer-brand {
            font-size: 1.2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0 0 8px 0;
        }

        .footer-text {
            color: rgba(255, 255, 255, 0.55);
            font-size: 0.85rem;
            line-height: 1.7;
            margin: 0;
        }

        .footer-divider {
            color: rgba(255, 255, 255, 0.25);
            margin: 0 6px;
        }

        /* === RESPONSIVE: TABLET === */
        @media (max-width: 768px) {
            .admin-header {
                padding: 32px 24px;
            }

            .admin-title {
                font-size: 2rem;
            }

            .stats-grid {
                padding: 20px 24px;
                gap: 10px;
            }

            .stat-box {
                padding: 14px;
            }

            .stat-value {
                font-size: 1.7rem;
            }

            .winners-section {
                padding: 24px;
            }

            th {
                padding: 12px 8px;
                font-size: 0.8rem;
            }

            td {
                padding: 12px 8px;
                font-size: 0.9rem;
            }

            .admin-note {
                padding: 20px;
            }

            .btn {
                padding: 11px 20px;
                font-size: 0.9rem;
                min-height: 44px;
            }

            .footer {
                padding: 24px;
            }
        }

        /* === RESPONSIVE: MOBILE === */
        @media (max-width: 600px) {
            .container {
                border-radius: 12px;
                margin: 0;
            }

            body {
                padding: 12px;
            }

            .admin-header {
                padding: 28px 20px;
            }

            .admin-header::before,
            .admin-header::after {
                display: none;
            }

            .admin-title {
                font-size: 1.7rem;
                margin-bottom: 6px;
            }

            .admin-subtitle {
                font-size: 0.95rem;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr 1fr 1fr;
                padding: 16px 20px;
                gap: 8px;
            }

            .stat-box {
                padding: 12px;
                border-radius: 10px;
            }

            .stat-box:hover {
                transform: none;
            }

            .stat-label {
                font-size: 0.75rem;
                margin-bottom: 4px;
            }

            .stat-value {
                font-size: 1.4rem;
                gap: 4px;
            }

            .stat-emoji {
                font-size: 1.3rem;
            }

            /* HIDE TABLE, SHOW CARDS */
            .table-wrapper {
                display: none;
            }

            .winner-cards {
                display: grid;
                grid-template-columns: 1fr;
                gap: 12px;
                margin: 0;
            }

            .winner-card {
                background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(52, 211, 153, 0.08) 100%);
                border: 1px solid rgba(16, 185, 129, 0.3);
                border-radius: 12px;
                padding: 16px;
                transition: all 0.2s ease;
            }

            .winner-card:active {
                background: linear-gradient(135deg, rgba(16, 185, 129, 0.25) 0%, rgba(52, 211, 153, 0.15) 100%);
                transform: scale(0.98);
            }

            .card-header {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 12px;
            }

            .card-rank {
                background: linear-gradient(135deg, rgba(251, 191, 36, 0.25) 0%, rgba(16, 185, 129, 0.15) 100%);
                color: #fbbf24;
                font-weight: 800;
                font-size: 1.1rem;
                width: 40px;
                height: 40px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .card-name {
                flex: 1;
            }

            .card-name-text {
                color: #ffffff;
                font-size: 1.05rem;
                font-weight: 800;
                margin: 0;
                line-height: 1.2;
            }

            .card-rows {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .card-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px;
                background: rgba(15, 23, 42, 0.4);
                border-radius: 8px;
                border-left: 3px solid rgba(16, 185, 129, 0.4);
            }

            .card-row-label {
                color: rgba(255, 255, 255, 0.65);
                font-size: 0.85rem;
                font-weight: 600;
            }

            .card-row-value {
                color: rgba(255, 255, 255, 0.9);
                font-size: 0.95rem;
                font-weight: 700;
                text-align: left;
            }

            .hits-badge {
                background: linear-gradient(135deg, rgba(139, 92, 246, 0.3) 0%, rgba(168, 85, 247, 0.2) 100%);
                color: #d8b4fe;
                padding: 4px 10px;
                border-radius: 6px;
                font-size: 0.85rem;
            }

            .winners-section {
                padding: 20px;
            }

            .winners-header {
                margin-bottom: 16px;
                padding-bottom: 12px;
            }

            .winners-title {
                font-size: 1.3rem;
            }

            .winners-title-emoji {
                font-size: 1.6rem;
            }

            .admin-note {
                padding: 16px;
                margin-top: 20px;
                border-left-width: 3px;
            }

            .admin-note-title {
                font-size: 1rem;
                margin-bottom: 10px;
            }

            .admin-note-content {
                font-size: 0.9rem;
                line-height: 1.6;
            }

            .action-buttons {
                gap: 8px;
                margin-top: 16px;
                flex-direction: column;
            }

            .btn {
                width: 100%;
                padding: 14px 16px;
                font-size: 0.95rem;
                min-height: 48px;
            }

            .footer {
                padding: 20px;
            }

            .footer-text {
                font-size: 0.8rem;
            }
        }

        /* === RESPONSIVE: ULTRA-SMALL PHONES === */
        @media (max-width: 380px) {
            .admin-title {
                font-size: 1.5rem;
            }

            .admin-subtitle {
                font-size: 0.85rem;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
                padding: 12px 16px;
            }

            .stat-label {
                font-size: 0.7rem;
            }

            .stat-value {
                font-size: 1.2rem;
            }

            .card-rank {
                width: 36px;
                height: 36px;
                font-size: 1rem;
            }

            .card-row {
                padding: 8px;
                gap: 8px;
            }

            .card-row-label {
                font-size: 0.8rem;
            }

            .card-row-value {
                font-size: 0.9rem;
            }

            .btn {
                padding: 12px 14px;
                font-size: 0.9rem;
                min-height: 44px;
            }
        }

        /* === PRINT STYLES === */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .container {
                box-shadow: none;
                border: none;
                background: white;
                max-width: 100%;
            }

            .btn, .action-buttons {
                display: none;
            }

            tbody tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- ADMIN HEADER WITH STATS -->
        <div class="admin-header">
            <div class="admin-header-content">
                <h1 class="admin-title">📊 تقرير الفائزين</h1>
                <p class="admin-subtitle">برنامج السارية المباشر على تلفزيون البحرين</p>
            </div>
        </div>

        <!-- STATS GRID -->
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-label">🏆 إجمالي الفائزين</div>
                <p class="stat-value">
                    <span class="stat-emoji">🎯</span>
                    <span>{{ $winners_count }}</span>
                </p>
            </div>
            <div class="stat-box">
                <div class="stat-label">📞 إجمالي الاتصالات</div>
                <p class="stat-value">
                    <span class="stat-emoji">📈</span>
                    <span>{{ $total_hits }}</span>
                </p>
            </div>
            <div class="stat-box">
                <div class="stat-label">⏰ وقت التقرير</div>
                <p class="stat-value" style="font-size: 1.2rem;">
                    <span>{{ substr($generated_at, 0, 10) }}</span>
                </p>
            </div>
            <div class="stat-box">
                <div class="stat-label">📅 متوسط الاتصالات</div>
                <p class="stat-value">
                    <span class="stat-emoji">📊</span>
                    <span>{{ $winners_count > 0 ? round($total_hits / $winners_count) : 0 }}</span>
                </p>
            </div>
        </div>

        <!-- WINNERS SECTION -->
        <div class="winners-section">
            <div class="winners-header">
                <span class="winners-title-emoji">👥</span>
                <h2 class="winners-title">قائمة الفائزين</h2>
            </div>

            <!-- DESKTOP TABLE -->
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>رقم الهوية</th>
                            <th>الهاتف</th>
                            <th>الاتصالات</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($winners as $index => $winner)
                            <tr>
                                <td>
                                    <span class="winner-rank">{{ $index + 1 }}</span>
                                </td>
                                <td class="winner-name-cell">
                                    {{ $winner->name }}
                                </td>
                                <td>
                                    <span class="cpr-masked">
                                        {{ substr($winner->cpr ?? '***', 0, 3) }}****{{ substr($winner->cpr ?? '***', -2) }}
                                    </span>
                                </td>
                                <td>{{ $winner->phone }}</td>
                                <td>
                                    <span class="hits-badge">{{ $winner->hits }} 📞</span>
                                </td>
                                <td class="date-cell">
                                    {{ $winner->created_at?->locale('ar')->translatedFormat('j M Y') ?? 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 32px; color: rgba(255, 255, 255, 0.5);">
                                    🔍 لا توجد فائزين مسجلين حالياً
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- MOBILE CARDS -->
            <div class="winner-cards">
                @forelse($winners as $index => $winner)
                    <div class="winner-card">
                        <div class="card-header">
                            <div class="card-rank">{{ $index + 1 }}</div>
                            <div class="card-name">
                                <p class="card-name-text">{{ $winner->name }}</p>
                            </div>
                        </div>
                        <div class="card-rows">
                            <div class="card-row">
                                <span class="card-row-label">🪪 الهوية</span>
                                <span class="card-row-value cpr-masked">
                                    {{ substr($winner->cpr ?? '***', 0, 3) }}****{{ substr($winner->cpr ?? '***', -2) }}
                                </span>
                            </div>
                            <div class="card-row">
                                <span class="card-row-label">📱 الهاتف</span>
                                <span class="card-row-value">{{ $winner->phone }}</span>
                            </div>
                            <div class="card-row">
                                <span class="card-row-label">☎️ الاتصالات</span>
                                <span class="card-row-value">
                                    <span class="hits-badge">{{ $winner->hits }}</span>
                                </span>
                            </div>
                            <div class="card-row">
                                <span class="card-row-label">📅 التاريخ</span>
                                <span class="card-row-value">
                                    {{ $winner->created_at?->locale('ar')->translatedFormat('j M Y') ?? 'N/A' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 32px; color: rgba(255, 255, 255, 0.5);">
                        🔍 لا توجد فائزين مسجلين حالياً
                    </div>
                @endforelse
            </div>

            <!-- ADMIN NOTE -->
            <div class="admin-note">
                <h4 class="admin-note-title">
                    <span>⚠️ ملاحظة إدارية</span>
                </h4>
                <p class="admin-note-content">
                    هذا التقرير يحتوي على بيانات الفائزين المُختلفة الحالية. لم يتم إرسال أي بريد إلكتروني لهؤلاء الفائزين. 
                    تواصل مع الفائزين من خلال أرقام هواتفهم المسجلة. الحفاظ على خصوصية بيانات الفائزين مسؤوليتك.
                </p>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="action-buttons">
                <a href="#" class="btn btn-primary">📥 تصدير البيانات</a>
                <a href="#" class="btn btn-primary">🖨️ طباعة التقرير</a>
                <a href="#" class="btn btn-secondary">← العودة للوحة التحكم</a>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <p class="footer-brand">🌙 السارية</p>
            <p class="footer-text">
                برنامج السارية
                <span class="footer-divider">•</span>
                تلفزيون البحرين
                <span class="footer-divider">•</span>
                © {{ date('Y') }}
            </p>
            <p class="footer-text" style="margin-top: 8px; font-size: 0.75rem;">
                تقرير آلي مُنشأ في: {{ $generated_at }}
            </p>
        </div>
    </div>
</body>
</html>
