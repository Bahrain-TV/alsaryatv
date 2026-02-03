<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        @font-face {
            font-family: 'Tajawal';
            src: url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');
        }
        
        body {
            font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            line-height: 1.8;
            color: #2d3748;
            background-color: #f7fafc;
            margin: 0;
            padding: 0;
            direction: rtl;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
        }
        
        .header {
            text-align: center;
            padding: 50px 0;
            background-image: url('{{ $background }}');
            background-size: cover;
            background-position: center;
            position: relative;
            border-radius: 12px 12px 0 0;
            margin: -20px -20px 20px;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.8));
            border-radius: 12px 12px 0 0;
        }
        
        .header img {
            position: relative;
            z-index: 1;
            max-width: 220px;
            height: auto;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.2));
        }
        
        .header h1 {
            position: relative;
            z-index: 1;
            color: #ffffff;
            margin-top: 25px;
            font-size: 28px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .content {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
        }
        
        .section {
            margin-bottom: 40px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            padding: 25px;
            border: 1px solid #e2e8f0;
        }
        
        .section-title {
            color: #1a202c;
            font-size: 22px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #4299e1;
            font-weight: 700;
            display: flex;
            align-items: center;
        }
        
        .section-title::before {
            content: '◈';
            margin-left: 10px;
            color: #4299e1;
        }
        
        .table-responsive {
            overflow-x: auto;
            margin: 0 -10px;
            padding: 0 10px;
        }
        
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 15px 0;
            border-radius: 8px;
            overflow: hidden;
        }
        
        th {
            background-color: #edf2f7;
            font-weight: 700;
            color: #2d3748;
            white-space: nowrap;
            font-size: 16px;
            padding: 12px 15px;
            text-align: right;
            border-bottom: 2px solid #e2e8f0;
        }
        
        td {
            background-color: #ffffff;
            color: #4a5568;
            font-size: 15px;
            padding: 12px 15px;
            text-align: right;
            border-bottom: 1px solid #e2e8f0;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:hover td {
            background-color: #f7fafc;
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #ffffff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 8px;
        }
        
        .stat-label {
            color: #718096;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ $logo }}" alt="Logo">
            <h1>{{ __('statistics.report_title') }} - {{ $date }}</h1>
        </div>

        <div class="content">
            @if(isset($data['timeStats']) && count($data['timeStats']) > 0)
            <div class="section">
                <h2 class="section-title">{{ __('statistics.time_analysis') }}</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ __('statistics.hour') }}</th>
                                <th>{{ __('statistics.calls') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['timeStats'] as $stat)
                            <tr>
                                <td>{{ str_pad($stat->hour, 2, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ number_format($stat->count) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            @if(isset($data['statusStats']) && count($data['statusStats']) > 0)
            <div class="section">
                <h2 class="section-title">{{ __('statistics.status_distribution') }}</h2>
                <div class="grid">
                    @foreach($data['statusStats'] as $stat)
                    <div class="stat-card">
                        <div class="stat-value">{{ number_format($stat->count) }}</div>
                        <div class="stat-label">{{ $stat->status ?: __('statistics.no_status') }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(isset($data['registrationPeriod']))
            <div class="section">
                <h2 class="section-title">{{ __('statistics.registration_period') }}</h2>
                <div class="grid">
                    <div class="stat-card">
                        <div class="stat-value">{{ $data['registrationPeriod']->first_registration }}</div>
                        <div class="stat-label">{{ __('statistics.first') }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ $data['registrationPeriod']->last_registration }}</div>
                        <div class="stat-label">{{ __('statistics.last') }}</div>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($data['dailyStats']) && count($data['dailyStats']) > 0)
            <div class="section">
                <h2 class="section-title">{{ __('statistics.daily_statistics') }}</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ __('statistics.date') }}</th>
                                <th>{{ __('statistics.total') }}</th>
                                <th>{{ __('statistics.families') }}</th>
                                <th>{{ __('statistics.winners') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['dailyStats'] as $stat)
                            <tr>
                                <td>{{ $stat->date }}</td>
                                <td>{{ number_format($stat->total) }}</td>
                                <td>{{ number_format($stat->families) }}</td>
                                <td>{{ number_format($stat->winners) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</body>
</html>