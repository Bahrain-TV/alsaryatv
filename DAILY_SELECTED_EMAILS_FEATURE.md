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
- **Config:** `config/daily-selected-emails.php` - Persistent email settings

### 4. Scheduler Configuration
- **Path:** `app/Console/Kernel.php`
- **Schedule:** Daily at 9:00 AM (Asia/Bahrain timezone)

## Usage

### Quick Start

```bash
# First time - set your email and send
php artisan app:send:daily-selected-emails --email=your-email@example.com

# Future runs - uses persisted email automatically
php artisan app:send:daily-selected-emails
```

### Email Management Commands

```bash
# Set persistent email (replaces all existing TO recipients)
php artisan app:send:daily-selected-emails --set-email=your-email@example.com

# Add email to existing recipients
php artisan app:send:daily-selected-emails --add-email=another@example.com

# Remove email from recipients
php artisan app:send:daily-selected-emails --remove-email=unwanted@example.com

# Clear all custom emails and restore defaults
php artisan app:send:daily-selected-emails --clear-emails

# Send with temporary email (also persists for future runs)
php artisan app:send:daily-selected-emails --email=temp@example.com
```

### Full Command Options

```bash
# Send with default recipients
php artisan app:send:daily-selected-emails

# Send to specific email (persists for future runs)
php artisan app:send:daily-selected-emails --email=example@gmail.com

# Include CC recipients (persists)
php artisan app:send:daily-selected-emails --cc=user1@example.com,user2@example.com

# Include BCC recipients (persists)
php artisan app:send:daily-selected-emails --bcc=hidden@example.com

# Change the number of names (default: 10, persists)
php artisan app:send:daily-selected-emails --limit=5

# Force send even if no selections today
php artisan app:send:daily-selected-emails --force
```

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

## Persistent Configuration

### How It Works

Email recipients are stored in `config/daily-selected-emails.php`. When you specify an email address using `--email`, it is automatically saved for future runs.

**Example:**
```bash
# First run - sets and persists the email
php artisan app:send:daily-selected-emails --email=my@email.com

# Second run - uses the persisted email automatically
php artisan app:send:daily-selected-emails
# → Sends to my@email.com without needing to specify it again
```

### Config File Structure

```php
<?php

return [
    'recipients' => [
        'to' => ['your-email@example.com'],
        'cc' => ['cc@example.com'],
        'bcc' => ['bcc@example.com'],
    ],
    'limit' => 10,  // Number of names to include
    'last_updated' => '2026-02-19T12:30:00Z',
];
```

### Default Recipients

If no custom email is configured, the report is sent to:
- `aldoyh.info@gmail.com`
- `alsaryatv@gmail.com`

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
