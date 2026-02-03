# 🚀 QUICK START DEPLOYMENT GUIDE

## Ready to Deploy? Start Here

### Pre-Deployment (5 minutes)
```bash
# 1. SSH into server
ssh root@h6.doy.tech

# 2. Navigate to app directory
cd /home/alsarya.tv/public_html

# 3. Make deploy script executable (if needed)
chmod +x deploy.sh

# 4. Quick check - app is running
curl -s https://alsarya.tv | grep -q "البحث" && echo "✓ App is online"

# 5. Create database backup
php artisan backup:run --only-db
```

### Deployment (Run Once)
```bash
# This single command does everything:
# 1. Puts app in maintenance mode (custom page)
# 2. Deploys code
# 3. Runs migrations
# 4. Builds assets
# 5. Clears caches
# 6. Brings app back online
# 7. Sends Discord notification

./deploy.sh
```

### Post-Deployment (5 minutes)
```bash
# 1. Verify app is online
curl -s https://alsarya.tv | grep -q "البحث" && echo "✓ Deployment successful"

# 2. Check logs for errors
tail -50 storage/logs/laravel.log

# 3. Test registration (should see success screen)
# Open browser: https://alsarya.tv/register
# Fill form → Submit
# Should see: "تم التسجيل بنجاح!" with checkmark

# 4. Test rate limiting (should see countdown)
# Try to register again immediately
# Should see: "انتظر قليلاً" with 5-minute timer
```

---

## ✅ What Was Deployed

### New
- ✅ `app/Services/DirtyFileManager.php` - Cache-based registration flag
- ✅ Redesigned `resources/views/callers/success.blade.php` - Two screens
- ✅ Updated `app/Http/Controllers/CallerController.php` - Mark dirty file
- ✅ Updated `routes/web.php` - Check dirty file flag
- ✅ Updated `deploy.sh` - Use custom maintenance page

### Features
- ✅ Success screen with checkmark & animations
- ✅ Rate limit countdown screen (5 minutes)
- ✅ Professional dark design with glassmorphism
- ✅ Mobile responsive
- ✅ Full Arabic RTL support
- ✅ Custom maintenance page during deploy

---

## 🔍 Monitoring Commands

### Check Deployment Status
```bash
# View real-time logs
tail -f storage/logs/laravel.log

# Check if app is in maintenance mode
[ -f storage/framework/down ] && echo "✓ In maintenance" || echo "✓ Online"

# Monitor registration success
grep "caller.registration.success" storage/logs/laravel.log | tail -5

# Monitor rate limiting
grep "rate_limit_exceeded" storage/logs/laravel.log | tail -5

# Check dirty file cache entries
mysql -u root -p < $(grep DB_PASSWORD .env | cut -d= -f2)
> SELECT COUNT(*) as dirty_files FROM cache WHERE key LIKE 'caller:dirty:%';
```

---

## 🆘 If Something Goes Wrong

### App Stuck in Maintenance Mode
```bash
# Manually bring app back online
php artisan up

# Verify
curl https://alsarya.tv | grep -q "البحث" && echo "✓ Fixed"
```

### Deployment Failed Midway
```bash
# 1. Check what failed
tail -100 storage/logs/laravel.log | grep ERROR

# 2. Fix the issue

# 3. Run deployment again
./deploy.sh
```

### Cache Issues
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Verify dirty file cache
SELECT * FROM cache WHERE key LIKE 'caller:dirty:%';
```

### Registration Not Working
```bash
# Verify rate limit cache
SELECT * FROM cache WHERE key LIKE 'caller_creation%';

# Verify session database
SELECT * FROM sessions WHERE user_id IS NOT NULL LIMIT 5;

# Clear and retry
php artisan cache:clear
```

---

## 🎯 Success Checklist

After deployment, verify these work:

- [ ] Homepage loads (`https://alsarya.tv`)
- [ ] Registration form loads (`https://alsarya.tv/register`)
- [ ] Submit registration → See success screen with ✅ checkmark
- [ ] Hit counter animates (1 → number)
- [ ] 30-second countdown starts
- [ ] Try to register immediately → See countdown screen
- [ ] Timer shows 5 minutes remaining
- [ ] Auto-redirect works after countdown
- [ ] Mobile layout is responsive
- [ ] No console errors (F12 → Console)
- [ ] No errors in logs (check last 50 lines)

---

## 📊 Key Endpoints

### Registration
- Form: `https://alsarya.tv/register`
- Submit: `POST /callers`
- Success: `GET /callers/success`
- Maintenance: Custom `down.blade.php` during deployment

### Admin
- Dashboard: `https://alsarya.tv/admin` (requires auth)
- Filament: `https://alsarya.tv/admin/callers`

### Testing
- CSRF Test: `https://alsarya.tv/csrf-test`
- Lightning: `https://alsarya.tv/lightning` (if available)

---

## 📝 Rate Limiting Info

**Per-CPR (Per User):**
- Limit: 1 registration per 300 seconds (5 minutes)
- Key: `caller_creation:{cpr}`
- Effect: Prevents duplicate registrations

**Per-IP (Per Location):**
- Limit: 10 registrations per 3600 seconds (1 hour)
- Key: `caller_creation_ip:{ip}`
- Effect: Prevents bulk registration abuse

Both reset automatically after TTL expires.

---

## 📱 Responsive Testing

Test on these devices/browsers:

**Desktop:**
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

**Mobile:**
- [ ] iPhone Safari
- [ ] Android Chrome
- [ ] iPad Safari

Test both portrait and landscape orientations.

---

## 🔐 Security Check

Verify these are working:

- [ ] CSRF protection (all forms have @csrf)
- [ ] Rate limiting (1 per 5 min per CPR)
- [ ] IP rate limiting (10 per hour per IP)
- [ ] Session security (HTTP-only cookies)
- [ ] Logging enabled (security events recorded)
- [ ] No PII in cache
- [ ] TTL enforced on cache entries

---

## 📞 Need Help?

Check these files for detailed info:

1. **Quick Questions**: `DIRTY_FILE_QUICK_REFERENCE.md`
2. **Deployment Issues**: `DEPLOYMENT_WORKFLOW.md`
3. **Pre-Deploy Checklist**: `PRE_DEPLOYMENT_CHECKLIST.md`
4. **Technical Details**: `THANK_YOU_SCREEN_REDESIGN.md`
5. **Visual Guide**: `THANK_YOU_SCREEN_VISUAL_GUIDE.md`
6. **Complete Summary**: `COMPLETE_IMPLEMENTATION_SUMMARY.md`

---

## ⏱️ Deployment Timeline

| Step | Time | What Happens |
|------|------|--------------|
| Start | 0:00 | Maintenance mode activated |
| Code | 0:30 | Dependencies installed, assets built |
| DB | 1:30 | Migrations run |
| Cache | 2:00 | Caches cleared, optimized |
| Online | 2:30 | App brought back online |
| **Total** | **~3-5 min** | **Users can register again** |

---

## 🎉 Deployment Complete When:

✅ App is online  
✅ Registration works  
✅ Success screen shows  
✅ No console errors  
✅ No server errors  
✅ All tests pass  

**Total Deployment Time**: 5-10 minutes (including pre/post checks)

---

**Status**: 🚀 READY TO DEPLOY  
**Last Updated**: 2026-02-02  
**Tested**: ✓ All syntax verified  
**Documentation**: ✓ Complete  

**Let's deploy!** 🎯
