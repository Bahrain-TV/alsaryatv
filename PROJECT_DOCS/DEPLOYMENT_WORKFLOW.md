# Deployment Workflow with Maintenance Mode

## Overview

The deployment process now uses a custom maintenance mode page (`down.blade.php`) to provide a user-friendly experience during deployment. This ensures users see a professional message instead of a generic error.

## Deployment Flow

### Before Deployment
```bash
./deploy.sh
```

### Step 1: Maintenance Mode Activated
```bash
php artisan down --render=down
```
- Application enters maintenance mode
- Custom `down.blade.php` page is rendered
- Users see: "لحظات وسنعود..." (A few moments and we'll be back...)
- Shows live hit counter and countdown timer
- Crescent moon animation displayed

### Step 2: Deployment Proceeds
```
→ Bumping version...
→ Fixing permissions...
→ Installing PHP dependencies (composer install)
→ Dumping autoloader...
→ Installing Node dependencies (npm install)
→ Building frontend assets (npm run build)
→ Running database migrations
→ Creating storage symlink
→ Clearing caches and optimizing
→ Restarting queue workers
→ Finalizing permissions...
```

### Step 3: Maintenance Mode Disabled
```bash
php artisan up
```
- Application comes back online
- Users can access the site again
- All deployments are applied

## Down Page Features

The `down.blade.php` page displays:

✅ **Professional Design**
- Dark theme with Seef District background image
- Glassmorphic card design
- Crescent moon Lottie animation
- Arabic messaging (RTL layout)

✅ **User Information**
- Main message: "لحظات وسنعود..." (A few moments and we'll be back...)
- Subtitle: "قاعدين نسحب على الأسماء ... دعواتكم 🤩" (We're drawing on the names... prayers for you 😊)
- Live hit counter (number of participants)
- Countdown timer to redirect
- Link back to main site

✅ **Session Data**
- Shows actual participant count from session
- Countdown timer (default 60 seconds before redirect)
- Includes sponsor information

## Manual Maintenance Mode

### Put App in Maintenance Mode
```bash
php artisan down --render=down
```

### Bring App Back Online
```bash
php artisan up
```

### View App in Maintenance Mode (as Developer)
```bash
php artisan down --render=down --secret=MySecretToken123
```
Then access: `http://localhost:8001/MySecretToken123`

## Customization

### Modify Down Page Message
Edit `resources/views/down.blade.php`:
```blade
<h1 class="text-2xl font-bold text-center mb-4">لحظات وسنعود...</h1>
```

### Change Countdown Timer
Edit in `down.blade.php` JavaScript:
```javascript
let secondsLeft = {{ session('seconds', 60) }}; // Change 60 to desired seconds
```

### Update Background Image
Edit in down.blade.php style:
```html
background-image: url("{{ asset('images/seef-district-from-sea.jpg') }}");
```

## Deployment Script Updates

**File**: `deploy.sh` (line 157)

**Before**:
```bash
$ART_CMD down || { ... }
```

**After**:
```bash
$ART_CMD down --render=down || { ... }
```

This ensures the custom maintenance page is displayed instead of the default Laravel maintenance mode page.

## Error Handling

If deployment fails at any point, the application is automatically brought back online:

```bash
if [ ... ]; then
    send_discord_message "❌ Deployment failed: [reason]"
    $ART_CMD up  # ← Brings app back online
    exit 1
fi
```

This ensures users are never stuck on the maintenance page if something goes wrong.

## Discord Notifications

Deployment status is sent to Discord via webhook:

- 🚀 Deployment started
- 📦 Version bumped
- ❌ Any errors during deployment
- ✅ Deployment completed successfully

## Best Practices

1. **Schedule Deployments During Low Traffic**
   - Deploy during off-peak hours when fewer users are accessing the app

2. **Test Deployment Script Locally**
   - Run the script in a staging environment first
   - Verify all migrations and builds complete successfully

3. **Monitor Discord for Notifications**
   - Check deployment status and error messages
   - Act quickly if deployment fails

4. **Verify After Deployment**
   - Check that the app is back online
   - Verify all features work correctly
   - Test in multiple browsers/devices

5. **Keep Down Page Updated**
   - Ensure messaging is current
   - Test the maintenance page before deploying
   - Verify links and resources load correctly

## Maintenance Mode Detection

The app is in maintenance mode when the following file exists:
```
storage/framework/down
```

This file is automatically created/removed by Laravel commands:
- `php artisan down` → Creates the file
- `php artisan up` → Removes the file

## Related Configuration

**File**: `.env`
```
APP_ENV=production      # Used to determine environment
APP_MAINTENANCE_DRIVER=file  # How maintenance mode is detected
```

**File**: `deploy.sh`
```
APP_ENV="staging"       # Set to production when deploying
DISCORD_WEBHOOK="..."   # Webhook for notifications
```

## Troubleshooting

### Issue: Maintenance page doesn't show
**Solution**: Verify `php artisan down --render=down` executed successfully

### Issue: App stuck in maintenance mode
**Solution**: Manually run `php artisan up` to bring it back online

### Issue: Custom down page not rendering
**Solution**: 
1. Check `resources/views/down.blade.php` exists
2. Clear view cache: `php artisan view:clear`
3. Re-run down command: `php artisan down --render=down`

### Issue: Users see generic error instead of down page
**Solution**: 
1. Verify `.env` has `APP_MAINTENANCE_DRIVER=file`
2. Check `storage/framework/down` file exists after running `down`
3. Check `resources/views/down.blade.php` exists

## Deployment Workflow Summary

```
START DEPLOYMENT
    ↓
Run: php artisan down --render=down
    ↓ (Users see custom down page)
Deploy updates
    ↓
Run migrations
    ↓
Build assets
    ↓
Clear caches
    ↓
Run: php artisan up
    ↓ (Users can access app again)
Send Discord notification
    ↓
END DEPLOYMENT
```

## Next Steps

1. Test deployment script in staging environment
2. Verify maintenance page displays correctly
3. Monitor first production deployment
4. Update down page message if needed
5. Document any customizations made

---

**Status**: ✅ Configured and Ready  
**Last Updated**: 2026-02-02  
**Environment**: Production-Ready  
