<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Callers Data Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        .info-block {
            background-color: #f9f9f9;
            border-left: 4px solid #007bff;
            padding: 10px 15px;
            margin: 15px 0;
        }
        .custom-note {
            background-color: #fffbea;
            border-left: 4px solid #ffab00;
            padding: 10px 15px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name') }} - Callers Data</h1>
        </div>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2d3748;">Callers Data Export</h2>
        
        <p>Generated on: {{ $date }}</p>
        
        @if($recordCount)
            <p>Total Records: <strong>{{ $recordCount }}</strong></p>
        @endif
        
        @if($customNote)
            <p><strong>Note:</strong> {{ $customNote }}</p>
        @endif
    </div>
</body>
</html>
        
        <p>Hello,</p>
        
        <p>Attached is the CSV export of the callers data you requested from {{ config('app.name') }}.</p>
        
        <div class="info-block">
            <p><strong>Export Information:</strong></p>
            <ul>
                <li>Generated on: {{ $date }}</li>
                @if(isset($recordCount))
                <li>Records exported: {{ $recordCount }}</li>
                @endif
                <li>Format: CSV (Comma Separated Values)</li>
            </ul>
        </div>
        
        @if(isset($customNote) && !empty($customNote))
        <div class="custom-note">
            <p><strong>Note:</strong></p>
            <p>{{ $customNote }}</p>
        </div>
        @endif
        
        <p>The file contains caller information including names, contact details, and participation status.</p>
        
        <p>Regards,<br>{{ config('app.name_ar', 'برنامج السارية') }} Team</p>
        
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>{{ config('app.name') }} &copy; {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html>
