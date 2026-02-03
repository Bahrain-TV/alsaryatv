# CSRF Token Mismatch - Complete Debugging Guide

## What We Fixed

✅ **Session Domain Configuration**
- Changed `SESSION_DOMAIN=null` to `SESSION_DOMAIN=` (empty)
- Added `SESSION_SECURE_COOKIE=false`
- Added `SESSION_HTTP_ONLY=true`  
- Added `SESSION_SAME_SITE=lax`

This ensures cookies are properly set on the correct domain.

## Quick Fix Steps

### Step 1: Clear Everything
```bash
cd /Users/aldoyh/Sites/RAMADAN/alsaryatv-latest

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Clear browser cookies/cache
# Ctrl+Shift+Delete in browser → Clear all cookies and cache
```

### Step 2: Verify Session Table Exists
```bash
php artisan migrate --force
php artisan tinker
>>> DB::table('sessions')->count()  # Should show 0 or count of sessions
```

### Step 3: Test CSRF Protection Manually

Open browser DevTools (F12) and check:

**1. Check CSRF Token in HTML:**
```javascript
// In browser console
document.querySelector('meta[name="csrf-token"]').content
// Should output a 40-character token
```

**2. Check Session Cookie:**
```javascript
// Check all cookies
document.cookie
// Should see something like: "XSRF-TOKEN=...; laravel_session=..."
```

**3. Before Form Submit, Verify:**
```javascript
// Check if form has _token field
document.querySelector('form input[name="_token"]')
// Should show the hidden input element
```

## Common CSRF Mismatch Causes

### ✅ FIXED: SESSION_DOMAIN Configuration
**Problem**: Cookie not being set to correct domain
```env
# ❌ BEFORE
SESSION_DOMAIN=null

# ✅ AFTER  
SESSION_DOMAIN=
SESSION_SECURE_COOKIE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

### ✅ Session Table Migration
**Problem**: Sessions table doesn't exist
```bash
php artisan migrate --force
# Verify: php artisan tinker → DB::table('sessions')->exists()
```

### 🔍 Debug CSRF Logs
**Check what's failing**: 
```bash
tail -f storage/logs/laravel.log | grep -i csrf
```

Our custom `VerifyCsrfToken` middleware now logs:
- CSRF token presence (in request/header)
- Session ID
- Referer URL
- User IP
- Request path and method

## Detailed CSRF Flow

### 1. Form Page Load
```
[Browser] → [Server]
  ↓
Server generates CSRF token from session
Server renders form with:
  - <meta name="csrf-token" content="{{ csrf_token() }}">
  - @csrf directive inside form creates:
    <input type="hidden" name="_token" value="...">
Server sets LARAVEL_SESSION cookie
  ↓
[Browser receives page + session cookie]
```

### 2. Form Submission
```
[Browser] submits form with POST
- Includes _token field from form
- Includes LARAVEL_SESSION cookie header
  ↓
[Server receives request]
- Extracts session ID from cookie
- Looks up session in database
- Compares submitted _token with session token
- ✅ Match → Request processed
- ❌ Mismatch → CSRF validation error
```

## Testing CSRF Locally

### Test 1: Simple Form Submit
```bash
# 1. In browser, go to login page
# 2. Open DevTools → Network tab
# 3. Check "Preserve log"
# 4. Submit the form
# 5. Look at the POST request headers:

# Headers should include:
# Cookie: XSRF-TOKEN=...; laravel_session=...
# X-CSRF-TOKEN: ... (if using Axios)

# Form data should include:
# _token=... (matches meta tag value)
```

### Test 2: AJAX Request
```javascript
// If making AJAX requests, ensure token is included:
fetch('/callers', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({name: 'test'})
})
```

## If CSRF Still Fails After These Steps

### Check 1: Session Driver
```bash
# Verify config/session.php uses database
php artisan tinker
>>> config('session.driver')  # Should output: 'database'
```

### Check 2: Database Connection
```bash
php artisan tinker
>>> DB::connection()->getPdo()  # Should work without error
>>> DB::table('sessions')->count()  # Should return a number
```

### Check 3: Cookie Settings
```php
// In config/session.php, ensure:
'driver' => env('SESSION_DRIVER', 'database'),  ✅
'domain' => env('SESSION_DOMAIN'),  // Empty/null is OK
'secure' => env('SESSION_SECURE_COOKIE', false),  ✅ false for localhost
'http_only' => true,  ✅
'same_site' => 'lax',  ✅
```

### Check 4: Middleware Stack
```php
// bootstrap/app.php should have:
$middleware->web();  // Includes CSRF protection
```

### Check 5: Form Tag Attributes
```blade
<!-- ✅ CORRECT -->
<form method="POST" action="{{ route('callers.store') }}">
    @csrf
    <!-- form fields -->
</form>

<!-- ❌ WRONG -->
<form method="POST" action="{{ route('callers.store') }}">
    <!-- Missing @csrf! -->
</form>
```

## Environment-Specific Issues

### For Production Deployment
```env
# .env.production
SESSION_DRIVER=database
SESSION_DOMAIN=yourdomain.com  # Set to your domain
SESSION_SECURE_COOKIE=true      # HTTPS only
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict
```

### For Local Development
```env
# .env (local)
SESSION_DRIVER=database
SESSION_DOMAIN=
SESSION_SECURE_COOKIE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

## Verifying the Fix

```bash
# 1. Clear everything
php artisan cache:clear && php artisan config:clear

# 2. Check .env has correct settings
grep SESSION .env

# 3. Check sessions table
php artisan migrate --force

# 4. Visit the login page
# http://localhost:8001/

# 5. Open DevTools → Network tab

# 6. Look at the page load request:
#    - Response headers should include Set-Cookie with LARAVEL_SESSION
#    - HTML should include <meta name="csrf-token" content="...">

# 7. Submit form and check:
#    - POST request includes cookie: LARAVEL_SESSION=...
#    - POST data includes _token=... (same value as meta tag)
#    - Response should be success (not 419)
```

## Error Codes Reference

| Code | Meaning | Solution |
|------|---------|----------|
| 419 | CSRF token mismatch | Use @csrf in form, check session cookie |
| 419 | Session expired | Clear cookies, reload page |
| 405 | Method not allowed | Use POST, not GET |
| 422 | Validation failed | Check required fields |

## Logging & Monitoring

Check logs for CSRF issues:
```bash
# View recent CSRF failures
grep -i "csrf" storage/logs/laravel.log | tail -20

# Watch live logs
tail -f storage/logs/laravel.log | grep -i "csrf\|validation"
```

Our custom middleware logs:
- ✓ Token in request
- ✓ Token in header
- ✓ Session ID
- ✓ Referer URL
- ✓ User IP

## Still Having Issues?

1. **Check CSRF token appears in HTML source:**
   ```html
   <meta name="csrf-token" content="abc123def456...">
   ```

2. **Verify @csrf is in the form:**
   ```blade
   <form method="POST">
       @csrf
   </form>
   ```

3. **Ensure browser cookies are enabled**

4. **Check browser DevTools → Network → Cookies tab:**
   - Should see `LARAVEL_SESSION` or `laravel_session`

5. **Verify database connection works:**
   ```bash
   php artisan tinker
   >>> DB::table('sessions')->insert(['id' => 'test', 'user_id' => null, 'ip_address' => '127.0.0.1', 'user_agent' => 'test', 'payload' => '', 'last_activity' => time()])
   # If works, DB is OK
   ```

## Quick Test Command

Run this to verify CSRF is working:
```bash
# 1. Navigate to form page
curl -c /tmp/cookies.txt http://localhost:8001/callers/create

# 2. Extract CSRF token from response
TOKEN=$(grep csrf /tmp/cookies.txt | grep -o '[a-f0-9]{40}')

# 3. Submit form with token
curl -b /tmp/cookies.txt -F "_token=$TOKEN" -F "name=Test" -F "cpr=123456" http://localhost:8001/callers
```

If this succeeds, CSRF is configured correctly!
