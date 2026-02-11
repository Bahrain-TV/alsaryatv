# 📧 AlSarya Email Templates Documentation

This document covers the professional HTML email templates created for the AlSarya TV Show Registration System.

## Templates Overview

### 1. **Version Deployment Email** (`version-deployed.blade.php`)

Announces new version releases to users with feature highlights and update information.

**File Location:** `resources/views/emails/version-deployed.blade.php`
**Mailable Class:** `App\Mail\VersionDeployed`

#### Features

- 🚀 Golden gradient header with version badge
- ✓ Dynamic feature list
- 📋 Version details and requirements
- 🔄 Call-to-action button with update link
- Responsive mobile design
- Dark theme matching homepage

#### Usage Example

```php
use App\Mail\VersionDeployed;
use Illuminate\Support\Facades\Mail;

// Basic usage
Mail::to('user@example.com')->send(
    new VersionDeployed(
        version: '2026.0205.1',
        features: [
            'تحسينات الأداء والاستقرار',
            'واجهة مستخدم محسّنة',
            'ميزات جديدة ومبتكرة',
            'إصلاحات الأخطاء'
        ],
        updateLink: 'https://alsarya.tv/update'
    )
);

// Queued (background)
Mail::to('user@example.com')->queue(
    new VersionDeployed(
        version: config('app.version'),
        features: $newFeatures
    )
);
```

#### Template Variables

```blade
{{ $version }}        // Version number (e.g., "2026.0205.1")
{{ $features }}       // Array of feature descriptions
{{ $update_link }}    // URL to update page
```

#### Color Scheme

- Primary: Gold/Amber (`#fbbf24`, `#f59e0b`)
- Accent: Emerald Green (`#34d399`, `#10b981`)
- Background: Dark (`#0f172a`, `#1e1b4b`)

---

### 2. **Winner Announcement Email** (`winner-announcement.blade.php`)

Announces winners of the AlSarya show with prize details and instructions.

**File Location:** `resources/views/emails/winner-announcement.blade.php`
**Mailable Class:** `App\Mail\WinnerAnnouncement`

#### Features

- 🏆 Victory green header with animated celebration
- 🎉 Winner information card with details
- 💰 Prize information section
- 📋 Step-by-step instructions
- ⚠️ Fraud warning notice
- Responsive mobile design
- Animated elements (bouncing emojis, glowing borders)

#### Usage Example

```php
use App\Mail\WinnerAnnouncement;
use Illuminate\Support\Facades\Mail;
use App\Models\Caller;

// Get winner from database
$winner = Caller::where('is_winner', true)->first();

// Send winner announcement
Mail::to($winner->email)->send(
    new WinnerAnnouncement(
        winnerName: $winner->name,
        winnerCpr: $winner->cpr,
        prizeAmount: '250',  // in Bahraini Dinar
        prizeDescription: 'جائزة حصرية بقيمة 250 دينار بحريني'
    )
);

// Or queued for background processing
Mail::to($winner->email)->queue(
    new WinnerAnnouncement(
        winnerName: $winner->name,
        winnerCpr: $winner->cpr,
    )
);
```

#### Template Variables

```blade
{{ $winner_name }}            // Winner's full name
{{ $winner_cpr }}             // Winner's CPR (national ID) - displayed partially
{{ $prize_amount }}           // Prize amount (numeric)
{{ $prize_description }}      // Detailed prize description
```

#### Color Scheme

- Primary: Emerald Green (`#10b981`, `#059669`)
- Accent: Gold (`#fbbf24`)
- Background: Dark (`#0f172a`, `#1e1b4b`)
- Animated glow: Emerald with pulsing effect

---

## Implementation Guide

### 🔒 Email Safety & Environment Control

### ⚠️ CRITICAL: Development vs Production

This system has **automatic email environment guards** to prevent accidental emails being sent from development:

#### Development Environment (`APP_ENV=local`)

- ✅ Emails are **logged** to `storage/logs/mail.log`
- ❌ Emails are **NOT actually sent** via SMTP
- 🔍 All email details are recorded for testing/debugging
- 📧 Safe to test email functionality without sending real emails

#### Production Environment (`APP_ENV=production`)

- ✅ Emails are **actually sent** via configured SMTP server
- 📨 Proper mail server required and configured
- 🔒 Environment guards ensure only production sends real emails

### How It Works

```
Email Send Attempt
        ↓
MailEnvironmentServiceProvider listens
        ↓
Is APP_ENV=local/testing? → YES → Log to storage/logs/mail.log ✅
        ↓
Is APP_ENV=production? → YES → Send via SMTP ✅
```

---

## Setup Requirements

1. **Mail Configuration** (`.env`)

```
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@alsarya.tv
MAIL_FROM_NAME="برنامج السارية"
```

Or for development/testing:

```
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@alsarya.tv
MAIL_FROM_NAME="برنامج السارية"
```

1. **Queue Configuration** (optional but recommended)

```
QUEUE_CONNECTION=database  # or redis, for background processing
```

### Sending Emails in Controllers

```php
<?php

namespace App\Http\Controllers;

use App\Mail\VersionDeployed;
use App\Mail\WinnerAnnouncement;
use App\Models\Caller;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    // Send version update to all users
    public function notifyVersionDeployment()
    {
        $users = User::all();

        foreach ($users as $user) {
            Mail::to($user->email)->queue(
                new VersionDeployed(
                    version: config('app.version'),
                    features: [
                        'تحسينات الأداء',
                        'واجهة محسّنة',
                        'ميزات جديدة'
                    ]
                )
            );
        }

        return response()->json(['message' => 'Version notification sent']);
    }

    // Send winner announcement
    public function notifyWinner(Caller $caller)
    {
        Mail::to($caller->email ?? $caller->phone)->send(
            new WinnerAnnouncement(
                winnerName: $caller->name,
                winnerCpr: $caller->cpr,
                prizeAmount: '500',
                prizeDescription: 'جائزة حصرية بقيمة 500 دينار بحريني'
            )
        );

        return response()->json(['message' => 'Winner notification sent']);
    }
}
```

### Integration with Winner Selection Feature

Add to your winner selection API or controller:

```php
// After marking someone as winner
if ($caller->update(['is_winner' => true])) {
    // Send winner announcement email
    Mail::to($caller->email)->queue(
        new WinnerAnnouncement(
            winnerName: $caller->name,
            winnerCpr: $caller->cpr,
            prizeAmount: config('alsarya.prize.amount', '250'),
            prizeDescription: config('alsarya.prize.description', 'جائزة حصرية')
        )
    );

    // Block from future appearances
    $caller->update(['status' => 'blocked']);

    return response()->json([
        'success' => true,
        'message' => 'تم تحديد الفائز وإرسال الإشعار'
    ]);
}
```

---

## Customization Guide

### Modifying Email Templates

1. **Colors**: Edit the hex color values in the `<style>` section
2. **Logos/Images**: Replace image URLs with your own
3. **Text Content**: Modify Arabic text directly in the templates
4. **Links**: Update href attributes for buttons and links
5. **Animations**: Adjust `@keyframes` for different effects

### Adding Custom Variables

To add new variables to emails:

1. Update the Mailable class constructor:

```php
public function __construct(
    public string $customField = 'default value',
) {
    //
}
```

1. Pass the variable to the view:

```php
public function content(): Content
{
    return new Content(
        view: 'emails.your-template',
        with: [
            'custom_field' => $this->customField,
        ],
    );
}
```

1. Use in template:

```blade
{{ $custom_field }}
```

---

## Testing Emails

### Preview in Browser

```bash
# Add route to resources/views/emails
Route::get('/preview/version-deployed', fn () =>
    view('emails.version-deployed', [
        'version' => '2026.0205.1',
        'features' => ['Feature 1', 'Feature 2'],
        'update_link' => 'https://example.com'
    ])
);

Route::get('/preview/winner-announcement', fn () =>
    view('emails.winner-announcement', [
        'winner_name' => 'محمد أحمد',
        'winner_cpr' => '12345678901234',
        'prize_amount' => '500',
        'prize_description' => 'جائزة حصرية'
    ])
);
```

### Console Testing (Tinker)

```php
php artisan tinker

# Version Email
use App\Mail\VersionDeployed;
Mail::to('test@example.com')->send(new VersionDeployed('2026.0205.1', ['Feature 1']))

# Winner Email
use App\Mail\WinnerAnnouncement;
Mail::to('winner@example.com')->send(new WinnerAnnouncement('احمد محمد', '12345678'))
```

### Viewing Logged Emails in Development

In development, emails are logged to `storage/logs/mail.log`. View them:

```bash
# View last 50 lines of mail log
tail -50 storage/logs/mail.log

# Follow mail log in real-time
tail -f storage/logs/mail.log

# View all emails from today
grep "$(date +%Y-%m-%d)" storage/logs/mail.log

# Get email report programmatically
php artisan tinker
>>> use App\Services\MailGuard;
>>> MailGuard::getEmailReport()
>>> MailGuard::getPendingEmails()
>>> MailGuard::clearLogs() // Clear email logs
```

---

## 🛡️ Environment Guard Details

### MailEnvironmentServiceProvider

Located at `app/Providers/MailEnvironmentServiceProvider.php`

**What it does:**

- Listens to all mail events (`MessageSending`, `MessageSent`)
- In development: Logs email details and recipient info
- In production: Allows normal SMTP sending
- Prevents accidental real email sends from dev environments

**Log Format (Development):**

```
📧 Email Intercepted in Development Mode
- Recipient: user@example.com
- Subject: New Version Released
- Timestamp: 2026-02-06 14:30:45
- Environment: local
- Mailer: log

Email Body Preview:
<html>...</html>...

✋ Email NOT sent (development environment) - Use `php artisan mail:show` to preview
```

### MailGuard Service

Located at `app/Services/MailGuard.php`

**Available Methods:**

```php
use App\Services\MailGuard;

// Check environment
MailGuard::isProduction();      // Returns true if APP_ENV=production
MailGuard::isDevelopment();     // Returns true if APP_ENV=local/testing

// Get email information
MailGuard::getEmailReport();    // Get formatted report of mail config
MailGuard::getPendingEmails();  // Get all logged emails (dev only)
MailGuard::clearLogs();         // Clear mail.log (dev only)

// Example usage in controller
if (MailGuard::isDevelopment()) {
    $report = MailGuard::getEmailReport();
    Log::info('Email report', $report);
}
```

---

## ⚙️ Production Deployment Checklist

Before deploying to production, ensure:

- [ ] `.env` has `APP_ENV=production`
- [ ] `.env` has valid SMTP credentials:

  ```
  MAIL_MAILER=smtp
  MAIL_HOST=your-smtp-host.com
  MAIL_PORT=587
  MAIL_USERNAME=your-email@example.com
  MAIL_PASSWORD=your-secure-password
  MAIL_ENCRYPTION=tls
  ```

- [ ] `MAIL_FROM_ADDRESS` and `MAIL_FROM_NAME` are correct
- [ ] SMTP server is accessible and tested
- [ ] Queue is configured for background job processing:

  ```
  QUEUE_CONNECTION=redis  # or database/sqs/etc
  ```

- [ ] Email logs are monitored: `tail -f storage/logs/mail.log`
- [ ] Tested sending at least one real email before going live

### Production SMTP Configuration Example

```bash
# .env (Production)
APP_ENV=production
APP_DEBUG=false

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com        # or your SMTP provider
MAIL_PORT=587
MAIL_USERNAME=noreply@alsarya.tv
MAIL_PASSWORD=your-app-password  # Use app-specific password, not personal password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@alsarya.tv
MAIL_FROM_NAME="برنامج السارية"

# Queue configuration (highly recommended)
QUEUE_CONNECTION=redis
REDIS_HOST=your-redis-host
REDIS_PORT=6379
```

---

## ⚠️ Common Mistakes to Avoid

### ❌ DON'T: Send emails with development credentials in production

```php
// BAD - Credentials are hardcoded
Mail::to($email)->send(new VersionDeployed(...));
// This will try to use dev SMTP settings in production!
```

### ❌ DON'T: Commit `.env` with real passwords to git

```bash
# Never do this:
git add .env
git commit -m "Add email credentials"  # DANGEROUS!

# Instead, use .env.example as a template
git add .env.example
```

### ❌ DON'T: Use personal email passwords

```bash
# BAD - Using personal Gmail password
MAIL_PASSWORD=my-personal-gmail-password

# GOOD - Using app-specific password from Gmail
MAIL_PASSWORD=abcd efgh ijkl mnop  # Generated by Gmail
```

### ✅ DO: Use queues for better performance

```php
// Good - Emails sent in background
Mail::to($user)->queue(new VersionDeployed(...));

// Not ideal - Blocks current request
Mail::to($user)->send(new VersionDeployed(...));
```

### ✅ DO: Validate email addresses

```php
// Good - Check before sending
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Mail::to($email)->queue(new VersionDeployed(...));
}
```

---

## 🔐 Security Best Practices

1. **Never commit `.env` to version control**

   ```bash
   echo ".env" >> .gitignore
   ```

2. **Rotate SMTP passwords regularly**
   - Update in production `.env` file
   - Don't hardcode in code

3. **Use environment-specific SMTP servers**
   - Dev: `log` mailer (no credentials needed)
   - Staging: Test SMTP account
   - Production: Production SMTP account

4. **Monitor email logs for suspicious activity**

   ```bash
   # Check for unexpected emails
   grep "recipient@suspicious.com" storage/logs/mail.log
   ```

5. **Test email sending before major deployments**

   ```bash
   php artisan tinker
   >>> use App\Mail\VersionDeployed;
   >>> Mail::to('admin@example.com')->send(new VersionDeployed())
   ```

6. **Use app-specific passwords for hosted services**
   - Gmail: Generate from Security settings
   - Office 365: Use "App Passwords"
   - Most providers have this option

---

## 📞 Support & Troubleshooting

### Emails not sending in production?

```bash
# Check mail logs
tail -50 storage/logs/mail.log

# Test SMTP connection
php artisan tinker
>>> config('mail.mailers.smtp')

# Try sending test email
>>> Mail::to('test@example.com')->send(new \App\Mail\VersionDeployed())
```

### Getting "SMTP authentication failed"

```bash
# Verify credentials in .env
# - Check MAIL_USERNAME and MAIL_PASSWORD
# - Ensure no extra spaces
# - Use correct encryption method (tls vs ssl)
# - Check SMTP port matches provider (587 for TLS, 465 for SSL)

# Test with telnet (if available)
telnet smtp.gmail.com 587
```

### Emails going to spam?

```bash
# Check SPF, DKIM, DMARC records
# Use sender: noreply@yourdomain.com (not generic)
# Include proper headers in email template
# Add unsubscribe link in footer
```

---

## Email Client Support

✅ **Supported:**

- Gmail
- Outlook
- Apple Mail
- Mozilla Thunderbird
- Mobile clients (iOS Mail, Gmail app, etc.)

✅ **Features:**

- Responsive design (mobile, tablet, desktop)
- Dark mode support
- Animated elements
- Gradient backgrounds
- RTL (Right-to-Left) for Arabic

---

## Best Practices

1. **Always queue emails** for background processing:

   ```php
   Mail::to($user)->queue(new VersionDeployed(...));
   ```

2. **Test before sending** to all users:

   ```php
   Mail::to('admin@example.com')->send(new VersionDeployed(...));
   ```

3. **Monitor delivery** with mail logs:

   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **Validate email addresses** before sending:

   ```php
   if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
       Mail::to($email)->queue(...);
   }
   ```

5. **Include unsubscribe links** for compliance:
   - Update footer links with actual unsubscribe endpoints

---

## Troubleshooting

### Emails not sending?

```bash
# Check mail configuration
php artisan config:cache
php artisan config:clear

# Check queue (if using queue)
php artisan queue:work
```

### Images not loading?

- Use absolute URLs (not relative paths)
- Use HTTPS URLs for security
- Test images with Email on Acid (emailonacid.com)

### RTL text broken?

- Ensure `dir="rtl"` is in the HTML tag
- Test with popular email clients
- Use text-align directives carefully

---

## Additional Resources

- [Laravel Mail Documentation](https://laravel.com/docs/mail)
- [Mailables Documentation](https://laravel.com/docs/mail#generating-mailables)
- [Email Client CSS Support](https://www.campaignmonitor.com/css/)

---

**Last Updated:** February 6, 2026
**Version:** 1.0.0
