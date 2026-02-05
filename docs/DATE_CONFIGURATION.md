# Date Configuration Guide

This document explains how to configure and update the Ramadan dates displayed throughout the AlSarya TV application.

## Environment Variables

All dates are controlled through environment variables in the `.env` file. This makes it easy to update dates for different Ramadan years without touching the code.

### Available Configuration Variables

```env
# Ramadan Configuration
RAMADAN_START_DATE="2026-02-28"
RAMADAN_HIJRI_DATE="1 رمضان 1447 هـ"
RAMADAN_TIMEZONE="Asia/Bahrain"

# Registration Configuration
REGISTRATION_OPEN_DATE="2026-03-01"
REGISTRATION_ENABLED=false
```

## Configuration Details

### RAMADAN_START_DATE
- **Format**: `YYYY-MM-DD`
- **Purpose**: The Gregorian calendar date when Ramadan begins
- **Used for**:
  - Countdown timer calculation
  - Display on the welcome page
  - Date formatting in Arabic
- **Example**: `2026-02-28`

### RAMADAN_HIJRI_DATE
- **Format**: Arabic text string
- **Purpose**: The Hijri (Islamic) calendar equivalent
- **Used for**: Display on the Ramadan info card
- **Example**: `1 رمضان 1447 هـ`

### RAMADAN_TIMEZONE
- **Format**: Timezone identifier
- **Purpose**: Ensures countdown is accurate for the target audience
- **Default**: `Asia/Bahrain`
- **Example**: `Asia/Riyadh`, `Asia/Dubai`

### REGISTRATION_OPEN_DATE
- **Format**: `YYYY-MM-DD`
- **Purpose**: Date when registration opens (typically same as or shortly after Ramadan start)
- **Example**: `2026-03-01`

### REGISTRATION_ENABLED
- **Format**: `true` or `false`
- **Purpose**: Manual override to enable/disable registration
- **Default**: `false`

## How to Update Dates

1. **Edit the `.env` file**:
   ```bash
   nano .env
   # or
   vim .env
   ```

2. **Update the relevant variables**:
   ```env
   RAMADAN_START_DATE="2027-02-17"
   RAMADAN_HIJRI_DATE="1 رمضان 1448 هـ"
   ```

3. **Clear the configuration cache**:
   ```bash
   php artisan config:clear
   ```

4. **Verify the changes**:
   - Visit the homepage
   - Check that the countdown shows the correct time remaining
   - Confirm the dates are displayed correctly in both Gregorian and Hijri formats

## Configuration Flow

```
.env file
    ↓
config/alsarya.php (reads from .env)
    ↓
routes/web.php (uses config values)
    ↓
welcome.blade.php (displays dates)
```

## Important Notes

1. **Timezone Matters**: The countdown timer uses the configured timezone. Ensure `RAMADAN_TIMEZONE` matches your target audience's timezone.

2. **Cache Clearing**: Always run `php artisan config:clear` after updating `.env` values to ensure changes take effect immediately.

3. **Fallback Values**: The system has fallback values if environment variables are missing, but these should not be relied upon in production.

4. **Date Format**:
   - `RAMADAN_START_DATE` must be in `YYYY-MM-DD` format
   - The system automatically formats it to Arabic: "٢٨ فبراير ٢٠٢٦"

## Automated Date Formatting

The system automatically:
- Converts `RAMADAN_START_DATE` to Arabic formatted text
- Calculates the countdown in days, hours, minutes, and seconds
- Displays dates with proper RTL (right-to-left) formatting
- Uses Arabic numerals for the countdown timer

## Example: Updating for Ramadan 1448 (2027)

```env
# In .env file
RAMADAN_START_DATE="2027-02-17"
RAMADAN_HIJRI_DATE="1 رمضان 1448 هـ"
RAMADAN_TIMEZONE="Asia/Bahrain"
REGISTRATION_OPEN_DATE="2027-02-17"
REGISTRATION_ENABLED=false
```

Then run:
```bash
php artisan config:clear php artisan config:cache
```

The homepage will automatically display the new dates and countdown timer.

## Troubleshooting

**Problem**: Dates not updating after changing `.env`
- **Solution**: Run `php artisan config:clear`

**Problem**: Countdown showing wrong time
- **Solution**: Verify `RAMADAN_TIMEZONE` is correct

**Problem**: Arabic date not showing correctly
- **Solution**: Check that `RAMADAN_HIJRI_DATE` is properly formatted with Arabic text and the hijri symbol `هـ`

## Related Files

- **Configuration**: `config/alsarya.php`
- **Routes**: `routes/web.php`
- **View**: `resources/views/welcome.blade.php`
- **Environment**: `.env`

---

**Last Updated**: February 2026
**Version**: 1.0.0
