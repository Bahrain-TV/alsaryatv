# 📧 Winner Announcement Email Feature

## Overview

The winner announcement system has been completely redesigned to:

✅ **Send to Admin Only** - Winners are NOT emailed directly
✅ **List All Winners** - Email shows all selected winners in one comprehensive view
✅ **Responsive Design** - Desktop table view on large screens, mobile-optimized card view on phones
✅ **Statistics Dashboard** - Shows totals, hit averages, and date generated
✅ **Admin Configuration** - Configurable via environment variable

---

## Configuration

### Environment Variables

Add to your `.env` file:

```env
# Admin notification emails (comma-separated)
# These admins will receive ALL winner announcement emails
# Winners are contacted via PHONE ONLY
ADMIN_EMAILS="admin@alsarya.tv,manager@alsarya.tv"
```

**Default**: `admin@alsarya.tv`

### How It Works

1. Admin selects winners in the Filament dashboard
2. Admin triggers email notification
3. Email is sent to **admin emails only** (not winners)
4. Email contains list of all selected winners with statistics

---

## Usage

### Basic Usage (Get All Current Winners)

```php
<?php
use App\Mail\WinnerAnnouncement;
use Illuminate\Support\Facades\Mail;

// Send email to all configured admin emails with current winners
Mail::send(new WinnerAnnouncement());
```

### Advanced Usage (Send Specific Winners)

```php
<?php
use App\Mail\WinnerAnnouncement;
use App\Models\Caller;
use Illuminate\Support\Facades\Mail;

// Get specific winners (e.g., today's winners)
$todaysWinners = Caller::where('is_winner', true)
    ->whereDate('created_at', today())
    ->get();

// Send email with specific winners
Mail::send(new WinnerAnnouncement($todaysWinners));
```

### Queue Usage (Recommended for Production)

```php
<?php
use App\Mail\WinnerAnnouncement;
use Illuminate\Support\Facades\Mail;

// Queue the email for immediate sending
Mail::queue(new WinnerAnnouncement());

// Or with specific winners
$winners = Caller::where('is_winner', true)->get();
Mail::queue(new WinnerAnnouncement($winners));
```

---

## Email Template Features

### Desktop View
- Responsive table with all winners
- 6 columns: #, Name, CPR (masked), Phone, Hits, Date
- Hover effects on rows for better UX
- Professional green-themed design

### Mobile View (< 600px)
- Card-based layout replaces table
- One winner per card
- All details clearly visible with better spacing
- Touch-friendly design with proper min-height buttons
- Extra-optimized for ultra-small phones (< 380px)

### Statistics Grid (All Screen Sizes)
- Total winners count
- Total hits/calls sum
- Report generation date/time
- Average hits per winner

### Admin Note Section
- Reminds admins that winners were NOT emailed
- Clear instructions to contact via phone only
- Data privacy reminder

---

## Responsive Breakpoints

| Screen Size | View Type | Column Grid |
|------------|-----------|------------|
| ≥ 768px | Table | Desktop table layout |
| 600-767px | Hybrid | Smaller table with compressed spacing |
| 380-599px | Cards | Full mobile card layout |
| < 380px | Cards | Ultra-small optimized cards |

---

## Key Features

### 1. **Admin-Only Recipients**

The `envelope()` method reads from configuration:

```php
public function envelope(): Envelope
{
    $adminEmails = config('alsarya.admin_emails') ?? [
        config('mail.from.address') ?? 'noreply@alsarya.tv',
    ];

    return new Envelope(
        to: $adminEmails,  // ← Winners NOT included
        subject: "📋 تقرير الفائزين - برنامج السارية (عدد الفائزين: {$winnerCount})",
    );
}
```

### 2. **Dynamic Data Passing**

```php
public function content(): Content
{
    return new Content(
        view: 'emails.winner-announcement',
        with: [
            'winners' => $this->winners,
            'winners_count' => $this->winners->count(),
            'total_hits' => $this->winners->sum('hits'),
            'generated_at' => now()->locale('ar')->translatedFormat('j F Y H:i'),
        ],
    );
}
```

### 3. **Security Features**

- CPR numbers are **masked** (shows only first 3 and last 2 digits)
- Only admins receive the email (no winner email addresses collected)
- Clear note that winners should NOT expect email contact
- Font: Monospace for technical data (CPR), Arabic-optimized Tajawal for text

---

## Blade Template Structure

```
/resources/views/emails/winner-announcement.blade.php
├── Admin Header (Green gradient)
│   ├── Title & Subtitle
│   └── Decorative circles
├── Statistics Grid
│   ├── Total Winners
│   ├── Total Hits
│   ├── Report Date
│   └── Average Hits
├── Winners Section
│   ├── DESKTOP: Responsive table
│   │   ├── # (Rank badge)
│   │   ├── Name
│   │   ├── CPR (Masked)
│   │   ├── Phone
│   │   ├── Hits (Badge)
│   │   └── Date
│   └── MOBILE: Winner cards
│       ├── Rank circle
│       ├── Winner name
│       └── Details rows
├── Admin Note (Yellow border)
│   └── Critical reminders
├── Action Buttons
│   ├── Export Data
│   ├── Print Report
│   └── Back to Dashboard
└── Footer
    ├── Brand
    ├── Copyright
    └── Generated timestamp
```

---

## Styling Highlights

### Colors Used

| Element | Color | Purpose |
|---------|-------|---------|
| Primary | `#10b981` (Green) | Header, success actions |
| Secondary | `#fbbf24` (Gold) | Badges, highlights, warnings |
| Accent | `#34d399` (Teal) | Stats, emphasis |
| Background | `#0f172a` (Dark blue) | Main content area |
| Text Primary | `#ffffff` | Main text |
| Text Secondary | `rgba(..., 0.65)` | Labels, hints |

### Responsive Fonts

| Size | Usage | Screen |
|------|-------|--------|
| 2.4rem | Main title | Desktop |
| 2rem | Title | Tablet |
| 1.7rem | Title | Mobile |
| 1.5rem | Title | Ultra-small |
| 1.6rem | Section headers | All |
| 0.95rem | Body text | Desktop |
| 0.9rem | Body text | Mobile |

---

## Testing

### Test Email with Sample Winners

```php
<?php
use App\Mail\WinnerAnnouncement;
use App\Models\Caller;

// Create test data
$testWinners = Caller::factory(5)->create(['is_winner' => true]);

// Test the mail
$mail = new WinnerAnnouncement($testWinners);

// Use mail assertions in test
$this->assertMailableHasCsvAttachment($mail); // Would fail - no CSV (yet)
```

### Test with Real Data

```bash
# Queue the email
php artisan tinker
> Mail::send(new WinnerAnnouncement());
> exit

# Check admin email (if using log or testing driver)
tail storage/logs/laravel.log
```

---

## Future Enhancements

Potential features to add:

- [ ] CSV/Excel export of winners
- [ ] PDF report generation (with logos)
- [ ] Winner filtering by date range
- [ ] Prize details per winner
- [ ] SMS notification support (parallel to email)
- [ ] Winner confirmation back-link (admin dashboard)
- [ ] Duplicate detection warnings

---

## Troubleshooting

### Email Not Sending

**Issue**: Email goes to the void

**Solutions**:
```bash
# Check admin emails configuration
php artisan config:show alsarya.admin_emails

# Verify .env has ADMIN_EMAILS
grep ADMIN_EMAILS .env

# Test with log driver
MAIL_MAILER=log php artisan tinker
> Mail::send(new WinnerAnnouncement());
> exit

# Check logs
tail storage/logs/laravel.log
```

### Styles Not Rendering

**Issue**: Email shows with broken styles (Gmail, Outlook)

**Solutions**:
- Email clients strip some CSS - styles are **inlined** in template
- Gmail blocks pseudo-elements like `::before` - shimmer effects won't show in Gmail
- Use web-safe colors only (already done)

### Responsive Not Working

**Issue**: Mobile view shows desktop table

**Solutions**:
```html
<!-- Ensure viewport meta tag -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Media queries should work in all clients that support responsive -->
<!-- Some clients like Outlook don't support media queries -->
```

---

## File Changes Summary

1. **`app/Mail/WinnerAnnouncement.php`** - Refactored to accept winner collection
2. **`resources/views/emails/winner-announcement.blade.php`** - Complete redesign with responsive tables + cards
3. **`config/alsarya.php`** - Added `admin_emails` configuration
4. **`.env.example`** - Added `ADMIN_EMAILS` variable with comment

---

## Related Documentation

- [Laravel Mail Documentation](https://laravel.com/docs/11.x/mail)
- [Email Responsive Design](https://www.htmlemailcheck.com/)
- [MJML - Responsive Email Framework](https://mjml.io/)
- [Tajawal Font](https://www.decotype.com/tajawal/tajawal-typeface-2/)

