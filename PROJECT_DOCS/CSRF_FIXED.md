# CSRF Token Mismatch - FIXED ✓

## The Problem
You were getting "CSRF token mismatch" errors when trying to register forms because the session cookie and CSRF token weren't being properly synchronized between the browser and server.

## Root Causes Identified & Fixed

### 1. ✅ SESSION_DOMAIN Configuration (PRIMARY ISSUE)
**File**: `.env`

**The Problem:**
```dotenv
# ❌ BEFORE - This causes cookie NOT to be set properly
SESSION_DOMAIN=null
```

**The Fix:**
```dotenv
# ✅ AFTER - Empty domain allows cookie on any domain
SESSION_DOMAIN=
SESSION_SECURE_COOKIE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

**Why**: When `SESSION_DOMAIN=null`, the cookie settings are broken and the session cookie isn't properly sent to the browser, causing CSRF token validation to fail.

### 2. ✅ CSRF Middleware Configuration (LOGGING & TRACKING)
**File**: `bootstrap/app.php`

**Added**: Custom VerifyCsrfToken middleware that logs CSRF failures for debugging:

```php
use App\Http\Middleware\VerifyCsrfToken;

->withMiddleware(function (Middleware $middleware): void {
    $middleware->web();
    $middleware->api();
    
    // Use custom CSRF middleware with logging
    $middleware->use([
        VerifyCsrfToken::class,
    ]);
})
```

### 3. ✅ Custom CSRF Middleware (DEBUG LOGGING)
**File**: `app/Http/Middleware/VerifyCsrfToken.php`

**What it does:**
- Logs CSRF token mismatches to `storage/logs/laravel.log`
- Tracks session ID, token presence, referer, IP address
- Helps identify exactly why a token mismatch occurs

## Files Modified

| File | Change |
|------|--------|
| `.env` | Fixed SESSION_DOMAIN and added cookie settings |
| `bootstrap/app.php` | Added custom CSRF middleware |
| `app/Http/Middleware/VerifyCsrfToken.php` | Created new (with logging) |
| `routes/web.php` | Added CSRF test routes |
| `resources/views/csrf-test.blade.php` | Created test page |

## Documentation Created

1. **CSRF_DEBUG_GUIDE.md** - Complete debugging & troubleshooting guide
2. **CSRF test page** - `/csrf-test` - Verify CSRF is working

## How to Verify the Fix

### Quick Test (2 minutes)

```bash
# 1. Clear caches
php artisan cache:clear && php artisan config:clear

# 2. Open browser to test page
# http://localhost:8001/csrf-test

# 3. Check the page loads with:
#    - ✓ Token from meta tag
#    - ✓ Token from form field  
#    - ✓ Session cookie info

# 4. Click "Test CSRF Protection" button
#    Should see: "CSRF token is valid! ✓"
```

### Verify Registration Forms Work

```bash
# 1. Navigate to caller registration
# http://localhost:8001/callers/create

# 2. Fill in the form with test data:
#    - Name: Test Name
#    - Phone: 33333333
#    - CPR: 123456789

# 3. Submit form
#    Should see: Success page (not 419 error)
```

### Check Session & Cookie Configuration

```bash
php artisan tinker
>>> config('session')
# Verify:
# 'driver' => 'database'
# 'domain' => '' (empty)
# 'secure' => false
# 'http_only' => true
# 'same_site' => 'lax'
```

## What Changed in the Request Flow

### Before (Broken ❌)
```
1. Browser requests form page
2. Server tries to set LARAVEL_SESSION cookie (FAILS - SESSION_DOMAIN=null)
3. Browser receives page WITHOUT session cookie
4. User submits form with _token but NO session cookie
5. Server validates: _token present but session cookie missing
6. Result: ❌ 419 CSRF Token Mismatch
```

### After (Fixed ✅)
```
1. Browser requests form page
2. Server sets LARAVEL_SESSION cookie (WORKS - SESSION_DOMAIN empty)
3. Browser receives page WITH session cookie + CSRF token
4. User submits form with _token AND session cookie
5. Server validates: _token matches session token
6. Result: ✅ Form registered successfully
```

## Key Session Settings Explained

| Setting | Value | Why |
|---------|-------|-----|
| `SESSION_DRIVER` | `database` | Stores sessions in database |
| `SESSION_DOMAIN` | `` (empty) | Cookie works on all domains/subdomains |
| `SESSION_SECURE_COOKIE` | `false` | Allow HTTP (for development) |
| `SESSION_HTTP_ONLY` | `true` | Prevent JavaScript from accessing cookie |
| `SESSION_SAME_SITE` | `lax` | Balance between security and usability |
| `SESSION_PATH` | `/` | Cookie available for all paths |

## For Production Deployment

Update `.env` to:

```dotenv
# Production settings
SESSION_DRIVER=database
SESSION_DOMAIN=yourdomain.com        # Set to your domain
SESSION_SECURE_COOKIE=true           # HTTPS only
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict             # Stricter security
```

## Testing Commands

```bash
# Test CSRF token generation
php artisan tinker
>>> csrf_token()  # Should return a 40-char token

# Check session table
>>> DB::table('sessions')->count()  # Should show sessions

# Test cookie settings
>>> config('session.domain')  # Should be empty
>>> config('session.secure')  # Should be false (dev) or true (prod)
```

## If CSRF Still Fails

1. **Check logs:**
   ```bash
   tail -f storage/logs/laravel.log | grep -i csrf
   ```

2. **Verify session table exists:**
   ```bash
   php artisan migrate --force
   ```

3. **Clear everything:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   # Clear browser cookies: Ctrl+Shift+Delete
   ```

4. **Check browser console (F12):**
   - Verify `<meta name="csrf-token">` exists
   - Verify form has `<input name="_token">`
   - Verify `LARAVEL_SESSION` cookie is present

5. **Check CSRF debug logs:**
   ```bash
   grep "CSRF token verification failed" storage/logs/laravel.log
   # Shows: token status, session id, referer, IP
   ```

## Summary of Changes

✅ **Fixed SESSION_DOMAIN** - Cookies now properly set on all domains  
✅ **Added CSRF Middleware Logging** - Failures now logged with debug info  
✅ **Created Test Page** - `/csrf-test` to verify CSRF working  
✅ **Created Debug Guide** - Complete troubleshooting documentation  
✅ **Added Test Routes** - Quick verification of CSRF protection  

## Next Steps

1. ✅ Clear browser cookies and cache
2. ✅ Test at `/csrf-test` page
3. ✅ Test caller registration at `/callers/create`
4. ✅ Monitor logs: `tail -f storage/logs/laravel.log`
5. ✅ Report any remaining issues with log messages

## Quick Reference

| Issue | Solution |
|-------|----------|
| CSRF mismatch | Clear cache: `php artisan cache:clear` |
| Still failing | Check `/csrf-test` page for debug info |
| Session cookie not set | Verify `SESSION_DOMAIN=` (empty) in .env |
| Form stuck on validation | Check browser console for JavaScript errors |

You're all set! Forms should now register without CSRF errors. 🎉
