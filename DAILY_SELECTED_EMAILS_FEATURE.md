# 🌟 Daily Selected Emails Feature

## Overview

Automated daily email feature that sends the last 10 selected names from the Winner Selection dashboard. This feature showcases **Qwen Code** capabilities with a stunning, animated email design.

## Files Created

### 1. Mailable Class
- **Path:** `app/Mail/DailySelectedEmails.php`
- **Purpose:** Handles email composition and delivery

### 2. Email Template
- **Path:** `resources/views/emails/daily-selected-emails.blade.php`
- **Features:**
  - Beautiful purple gradient theme
  - Animated floating particles
  - Responsive grid layout for name cards
  - Statistics section
  - RTL support for Arabic
  - Qwen Code branding

### 3. Console Command
- **Path:** `app/Console/Commands/SendDailySelectedEmailsCommand.php`
- **Purpose:** Retrieves selected callers and triggers email sending

### 4. Scheduler Configuration
- **Path:** `app/Console/Kernel.php`
- **Schedule:** Daily at 9:00 AM (Asia/Bahrain timezone)

## Usage

### Manual Execution

```bash
# Send with default recipients
php artisan app:send:daily-selected-emails

# Send to specific email
php artisan app:send:daily-selected-emails --email=example@gmail.com

# Include CC recipients
php artisan app:send:daily-selected-emails --cc=user1@example.com,user2@example.com

# Include BCC recipients
php artisan app:send:daily-selected-emails --bcc=hidden@example.com

# Change the number of names (default: 10)
php artisan app:send:daily-selected-emails --limit=5

# Force send even if no selections today
php artisan app:send:daily-selected-emails --force
```

### Automatic Execution

The email is automatically sent every day at **9:00 AM Bahrain time** via the Laravel scheduler.

Ensure your cron is set up:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Default Recipients

If no email is specified, the report is sent to:
- `aldoyh.info@gmail.com`
- `alsaryatv@gmail.com`

## Email Design Features

### Visual Elements
- ✨ **Animated Header** - Bouncing star icon with gradient background
- 🎨 **Purple Theme** - Matches the Qwen brand aesthetic
- 🌟 **Floating Particles** - CSS animated background effects
- 📊 **Statistics Grid** - Shows total selected, eligible callers, date, and time
- 🎯 **Name Cards** - Individual cards for each selected person with:
  - Name
  - CPR (Identity Number)
  - Phone Number
  - Participation Count (Hits)

### Responsive Design
- Mobile-friendly layout
- Adaptive grid system
- Touch-optimized interactions

## Configuration

### Email Settings

Make sure your `.env` file has proper mail configuration:

```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@alsaryatv.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Customizing Recipients

Edit the `$defaultRecipients` array in `SendDailySelectedEmailsCommand.php`:

```php
protected array $defaultRecipients = [
    'your-email@example.com',
    'another-email@example.com',
];
```

### Customizing Schedule Time

Edit `app/Console/Kernel.php`:

```php
$schedule->command('app:send:daily-selected-emails')
    ->dailyAt('09:00') // Change time here
    ->timezone('Asia/Bahrain');
```

## Testing

### Test the Command

```bash
# Run with verbose output
php artisan app:send:daily-selected-emails --email=test@example.com -vvv

# Check the logs
tail -f storage/logs/daily-selected-emails.log
```

### Preview the Email

You can preview the email in your browser by creating a test route:

```php
Route::get('/test-email', function () {
    $selectedCallers = [
        ['name' => 'Test User', 'cpr' => '123456789', 'phone' => '12345678', 'hits' => 5],
    ];
    
    return view('emails.daily-selected-emails', [
        'selectedCallers' => $selectedCallers,
        'totalCount' => 100,
        'reportDate' => now(),
        'formattedDate' => now()->locale('ar')->translatedFormat('j F Y'),
        'dayName' => now()->locale('ar')->translatedFormat('l'),
    ]);
});
```

## Logs

Email sending activity is logged to:
- `storage/logs/daily-selected-emails.log` - Command execution log
- `storage/logs/laravel.log` - General application log

## Troubleshooting

### Email Not Sending

1. Check mail configuration in `.env`
2. Verify SMTP credentials
3. Check firewall/port access
4. Review logs: `tail -f storage/logs/laravel.log`

### No Selected Callers Found

- Ensure callers have been selected via the Winner Selection dashboard
- Check that `is_selected` field is set to `true` in the database
- Use `--force` flag to send even with empty selections

### Scheduler Not Running

1. Verify cron job is configured
2. Check Laravel scheduler: `php artisan schedule:list`
3. Ensure timezone is correct

## Version History

- **5.1.0** (2026-02-19) - Initial release with Qwen-powered animated design

---

**Powered by Qwen Code** 🚀  
*Advanced AI Capabilities for AlSarya TV Show Registration System*
