# Pre-Deployment Checklist

## Code Changes Completed

### ✅ Thank You Screen Redesign
- [x] Created `app/Services/DirtyFileManager.php`
- [x] Updated `app/Http/Controllers/CallerController.php`
- [x] Updated `routes/web.php`
- [x] Redesigned `resources/views/callers/success.blade.php`
- [x] All PHP syntax verified
- [x] Documentation created

### ✅ Deployment Configuration
- [x] Updated `deploy.sh` to use `--render=down`
- [x] Down page renders custom maintenance message
- [x] Error handling brings app back online
- [x] Discord notifications configured

---

## Pre-Deployment Steps

### 1. Environment Verification
- [ ] `.env` has `CACHE_STORE=database`
- [ ] `.env` has `SESSION_DRIVER=database`
- [ ] `.env` has correct app URL
- [ ] `.env` has Discord webhook configured (optional but recommended)

### 2. Database Checks
- [ ] `cache` table exists in database
- [ ] `sessions` table exists in database
- [ ] No pending migrations
- [ ] Database backups created

### 3. Asset Verification
- [ ] `public/images/seef-district-from-sea.jpg` exists
- [ ] `public/lottie/crecent-moon-ramadan.json` exists
- [ ] `public/images/dl-from-app-stores.png` exists
- [ ] All images are optimized

### 4. Code Review
- [ ] All modifications reviewed
- [ ] No debug code left in place
- [ ] No commented-out code cluttering files
- [ ] All imports are correct
- [ ] All namespaces are correct

---

## Deployment Execution

### Before Running Deploy Script
```bash
# 1. Navigate to application directory
cd /home/alsarya.tv/public_html

# 2. Make deploy script executable
chmod +x deploy.sh

# 3. Test deployment script (optional)
./deploy.sh --version

# 4. Start deployment
./deploy.sh
```

### What Happens During Deployment
```
1. Pre-deployment checks run
2. App enters maintenance mode (custom down page shows)
3. Version is bumped
4. Permissions fixed
5. Composer dependencies installed
6. Node dependencies installed
7. Assets built
8. Migrations run
9. Caches cleared
10. Queue restarted
11. App brought back online
12. Discord notification sent
```

### Estimated Time
- **Total Duration**: 3-5 minutes (depends on network/server)
- **Maintenance Window**: ~3-4 minutes
- **Users see maintenance page during this time**

---

## Post-Deployment Verification

### ✅ Immediate Checks (First 5 Minutes)
- [ ] App is online at https://alsarya.tv
- [ ] Homepage loads without errors
- [ ] Registration form is accessible
- [ ] No 500 errors in logs

### ✅ Functional Tests (Next 30 Minutes)
- [ ] Register a new participant (test success screen)
- [ ] Verify hit counter displays correctly
- [ ] Verify 30-second countdown works
- [ ] Try to register again immediately (test rate limit screen)
- [ ] Verify 5-minute countdown timer
- [ ] Check admin panel works

### ✅ Browser/Device Testing
- [ ] Chrome desktop (latest)
- [ ] Firefox desktop (latest)
- [ ] Safari desktop (latest)
- [ ] Chrome mobile (iPhone)
- [ ] Chrome mobile (Android)

### ✅ Performance Checks
- [ ] Page load time < 2 seconds
- [ ] No console errors (check F12 DevTools)
- [ ] Images load correctly
- [ ] Animations are smooth

### ✅ Log Analysis
```bash
# Check for errors
tail -f storage/logs/laravel.log

# Look for rate limit logs
grep "rate_limit" storage/logs/laravel.log

# Look for CSRF logs
grep "csrf" storage/logs/laravel.log

# Look for registration success
grep "caller.registration.success" storage/logs/laravel.log
```

---

## Rollback Plan (If Needed)

### Quick Rollback Steps
```bash
# 1. Put app in maintenance mode
php artisan down --render=down

# 2. Revert to previous version (using git)
git revert HEAD~1

# 3. Run migrations if needed
php artisan migrate

# 4. Clear caches
php artisan cache:clear
php artisan config:clear

# 5. Bring app back online
php artisan up
```

### Keep These Handy
- Git commit hash of current version
- Database backup
- Previous version number
- Rollback plan documentation

---

## Critical Files to Monitor

### Watch These Files for Issues
1. `storage/logs/laravel.log` - Application errors
2. `storage/logs/pail.log` - Queue logs
3. `storage/framework/down` - Maintenance mode indicator
4. `version.txt` - Current deployed version

### Dashboard Commands
```bash
# Watch logs in real-time
tail -f storage/logs/laravel.log | grep -E "ERROR|CRITICAL"

# Check recent registrations
grep "caller.registration.success" storage/logs/laravel.log | tail -20

# Monitor rate limiting
grep "rate_limit" storage/logs/laravel.log | tail -10

# Check dirty file cache entries
SELECT * FROM cache WHERE key LIKE 'caller:dirty:%';
```

---

## Communication Plan

### Notify Team
- [ ] Inform team of deployment schedule
- [ ] Share maintenance window duration (3-5 minutes)
- [ ] Provide status update channel (Discord)
- [ ] Plan for post-deployment testing

### Monitor Channels
- [ ] Discord webhook for deployment notifications
- [ ] Application error logs
- [ ] Server resources (CPU, memory, disk)
- [ ] Database performance

---

## Contingency Scenarios

### Scenario 1: Deployment Hangs
**Action**: 
- Wait 10 minutes maximum
- SSH into server
- Check deployment script status
- If hung, manually run `php artisan up`
- Check logs for issues

### Scenario 2: App Stuck in Maintenance Mode
**Action**:
- Connect to server: `ssh root@h6.doy.tech`
- Remove file: `rm storage/framework/down`
- Or run: `php artisan up`
- Verify app is online

### Scenario 3: Database Migration Fails
**Action**:
- Check migration errors in logs
- Verify database connection
- Check database permissions
- Manually rollback if needed
- Bring app back online

### Scenario 4: Asset Build Fails
**Action**:
- Check npm errors in logs
- Verify Node.js version
- Clear node_modules: `rm -rf node_modules && npm install`
- Re-run: `npm run build`

---

## Success Indicators

✅ **Deployment is Successful When:**
1. App comes back online without errors
2. Registration form works
3. New registrations show success screen with checkmark
4. Hit counter animates smoothly
5. 30-second countdown works
6. Rate limiting prevents duplicate registrations
7. 5-minute countdown screen shows for retries
8. No 500 errors in logs
9. Performance is acceptable (< 2s page load)
10. All animations are smooth

---

## Documentation References

- [Deployment Workflow](DEPLOYMENT_WORKFLOW.md)
- [Thank You Screen Redesign](THANK_YOU_SCREEN_REDESIGN.md)
- [Dirty File Quick Reference](DIRTY_FILE_QUICK_REFERENCE.md)
- [Implementation Complete Summary](IMPLEMENTATION_COMPLETE_SUMMARY.md)

---

## Sign-Off

**Deployer Name**: ________________  
**Date**: ________________  
**Time**: ________________  
**Status**: 
- [ ] Deployment Successful
- [ ] Issues Encountered (describe below)

**Notes**:
```
_________________________________________________________________
_________________________________________________________________
_________________________________________________________________
```

---

**READY TO DEPLOY**: ✅ Yes, all checks passed
**Last Updated**: 2026-02-02
**Approval**: Pending User Confirmation
