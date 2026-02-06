# 📧 AlSarya Email Templates Documentation

This document covers the professional HTML email templates created for the AlSarya TV Show Registration System.

## Templates Overview

### 1. **Version Deployment Email** (`version-deployed.blade.php`)
Announces new version releases to users with feature highlights and update information.

**File Location:** `resources/views/emails/version-deployed.blade.php`
**Mailable Class:** `App\Mail\VersionDeployed`

#### Features:
- 🚀 Golden gradient header with version badge
- ✓ Dynamic feature list
- 📋 Version details and requirements
- 🔄 Call-to-action button with update link
- Responsive mobile design
- Dark theme matching homepage

#### Usage Example:

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

#### Template Variables:
```blade
{{ $version }}        // Version number (e.g., "2026.0205.1")
{{ $features }}       // Array of feature descriptions
{{ $update_link }}    // URL to update page
```

#### Color Scheme:
- Primary: Gold/Amber (`#fbbf24`, `#f59e0b`)
- Accent: Emerald Green (`#34d399`, `#10b981`)
- Background: Dark (`#0f172a`, `#1e1b4b`)

---

### 2. **Winner Announcement Email** (`winner-announcement.blade.php`)
Announces winners of the AlSarya show with prize details and instructions.

**File Location:** `resources/views/emails/winner-announcement.blade.php`
**Mailable Class:** `App\Mail\WinnerAnnouncement`

#### Features:
- 🏆 Victory green header with animated celebration
- 🎉 Winner information card with details
- 💰 Prize information section
- 📋 Step-by-step instructions
- ⚠️ Fraud warning notice
- Responsive mobile design
- Animated elements (bouncing emojis, glowing borders)

#### Usage Example:

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

#### Template Variables:
```blade
{{ $winner_name }}            // Winner's full name
{{ $winner_cpr }}             // Winner's CPR (national ID) - displayed partially
{{ $prize_amount }}           // Prize amount (numeric)
{{ $prize_description }}      // Detailed prize description
```

#### Color Scheme:
- Primary: Emerald Green (`#10b981`, `#059669`)
- Accent: Gold (`#fbbf24`)
- Background: Dark (`#0f172a`, `#1e1b4b`)
- Animated glow: Emerald with pulsing effect

---

## Implementation Guide

### Setup Requirements

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

2. **Queue Configuration** (optional but recommended)
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

2. Pass the variable to the view:
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

3. Use in template:
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
```bash
php artisan tinker

# Version Email
use App\Mail\VersionDeployed;
Mail::to('test@example.com')->send(new VersionDeployed('2026.0205.1', ['Feature 1']))

# Winner Email
use App\Mail\WinnerAnnouncement;
Mail::to('winner@example.com')->send(new WinnerAnnouncement('احمد محمد', '12345678'))
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
