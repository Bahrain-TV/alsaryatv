# Deployment Guide - Enhanced Image Handling

## Overview

The deployment script (`deploy.sh`) has been significantly improved with enhanced image handling, cache busting mechanisms, and deployment reliability features.

## New Features

### 1. Enhanced Image Synchronization

Images are now automatically synchronized during deployment using `rsync` with the following optimizations:

- **Differential Sync**: Only changed files are transferred
- **Checksum Verification**: Ensures file integrity
- **Automatic Permission Fixing**: Correct ownership and permissions on remote server

```bash
# Standard deployment (includes image sync)
./deploy.sh

# Force full image synchronization
./deploy.sh --sync-images
```

### 2. Image Cache Busting

A new cache busting system ensures users always see the latest images:

#### How It Works

1. During deployment, an image manifest is generated (`storage/framework/image_manifest.json`)
2. Each image gets a unique version hash based on:
   - Application version (from `VERSION` file)
   - File MD5 checksum
   - File modification timestamp

#### Usage in Blade Templates

**Option 1: Using Blade Directives** (Recommended)

```blade
<!-- Cache-busted image -->
<img src="@imagebust('alsarya-logo-2026-1.png')" alt="Logo">

<!-- Cache-busted asset -->
<link rel="stylesheet" href="@cssbust('app.css')">
<script src="@jsbust('app.js')"></script>

<!-- Generic asset busting -->
<img src="@assetbust('images/logo.png')">
```

**Option 2: Using Helper Functions**

```blade
<!-- In Blade templates -->
<img src="{{ image_bust('alsarya-logo-2026-1.png') }}" alt="Logo">
<link rel="stylesheet" href="{{ css_bust('app.css') }}">

<!-- Get version info -->
<p>Version: {{ app_version() }}</p>
```

**Option 3: Using AssetHelper Class**

```php
use App\Helpers\AssetHelper;

// In controllers or other classes
$logoUrl = AssetHelper::image('alsarya-logo-2026-1.png');
$cssUrl = AssetHelper::css('app.css');
$version = AssetHelper::getVersion();
```

### 3. Image Optimization (Optional)

Enable automatic image optimization during deployment:

```bash
# Enable image optimization
OPTIMIZE_IMAGES=true ./deploy.sh

# Or use the flag
./deploy.sh --optimize-images
```

**What gets optimized:**
- JPEG files: Quality reduced to 85%, progressive encoding enabled
- PNG files: Compression level 6 applied
- WebP files: Quality reduced to 80%

**Note:** SVG files are skipped (already optimized vector format)

### 4. Image Manifest

The deployment creates/updates `storage/framework/image_manifest.json`:

```json
{
  "version": "3.7.0-5",
  "generated_at": "2026-02-18T12:00:00+00:00",
  "total_images": 45,
  "images": {
    "alsarya-logo-2026-1.png": {
      "hash": "abc123def456...",
      "version": "3.7.0-5",
      "mtime": 1708257600,
      "size": 4428966
    }
  }
}
```

This manifest is used for:
- Cache invalidation tracking
- Deployment verification
- Debugging image issues

### 5. Diagnostics Mode

Run comprehensive diagnostics on production:

```bash
./deploy.sh --diagnose
```

**Checks performed:**
- `.env` configuration validation
- Cached config vs live config comparison
- Image file existence and checksums
- Storage symlink verification
- Web server response testing
- Blade template image references
- Directory permissions

### 6. Remote Image Comparison

When deploying from a local machine, the script now:

1. Compares local vs remote image checksums
2. Reports any differences
3. Only syncs changed images (bandwidth efficient)

## Deployment Commands Reference

| Command | Description |
|---------|-------------|
| `./deploy.sh` | Standard deployment with all features |
| `./deploy.sh --fresh` | Drop all tables, re-migrate and seed |
| `./deploy.sh --seed` | Run seeders after migration |
| `./deploy.sh --no-build` | Skip npm build step |
| `./deploy.sh --force` | Force all steps even if no changes |
| `./deploy.sh --reset-db` | Reset database (migrate:fresh without seeding) |
| `./deploy.sh --up` | Force deploy even if maintenance mode is active |
| `./deploy.sh --dry-run` | Print steps without executing |
| `./deploy.sh --diagnose` | Run production diagnostics |
| `./deploy.sh --sync-images` | Force sync all images to production |
| `./deploy.sh --optimize-images` | Optimize images during deployment |

## Migration Guide

### Updating Existing Blade Templates

To take advantage of cache busting, update your image references:

**Before:**
```blade
<img src="{{ asset('images/alsarya-logo-2026-1.png') }}" alt="Logo">
```

**After:**
```blade
<img src="@imagebust('alsarya-logo-2026-1.png')" alt="Logo">
```

### Files to Update

Key templates that should use cache-busted images:

1. `resources/views/layouts/header.blade.php`
2. `resources/views/components/application-logo.blade.php`
3. `resources/views/components/application-mark.blade.php`
4. `resources/views/welcome.blade.php`
5. `resources/views/splash.blade.php`
6. `resources/views/down.blade.php`

## Troubleshooting

### Images Not Updating After Deployment

1. **Clear browser cache**: Hard refresh (Cmd+Shift+R / Ctrl+Shift+F5)
2. **Check manifest**: Verify `storage/framework/image_manifest.json` was updated
3. **Force sync**: Run `./deploy.sh --sync-images`
4. **Check permissions**: Ensure web server can read image files

### Cache Busting Not Working

1. **Verify service provider**: Check `config/app.php` includes `AssetServiceProvider`
2. **Regenerate autoload**: Run `composer dump-autoload`
3. **Clear Laravel cache**: Run `php artisan cache:clear`
4. **Check helper functions**: Ensure `app/Helpers/functions.php` exists

### Image Optimization Failing

1. **Check GD extension**: Ensure PHP GD extension is installed
2. **Verify Intervention Image**: Run `composer show intervention/image`
3. **Check file permissions**: Ensure deploy script can write to `public/images`
4. **Skip optimization**: Deploy without `--optimize-images` flag

## Best Practices

### For Development

```bash
# Quick deployment without build
./deploy.sh --no-build

# Test with dry run first
./deploy.sh --dry-run
```

### For Production

```bash
# Full deployment with optimization
OPTIMIZE_IMAGES=true ./deploy.sh

# Or with specific flags
./deploy.sh --optimize-images --seed
```

### For Emergency Fixes

```bash
# Deploy even if maintenance mode is stuck
./deploy.sh --up

# Force everything
./deploy.sh --force --up
```

## Environment Variables

Add these to your `.env` for enhanced control:

```env
# Enable/disable image optimization (default: false)
OPTIMIZE_IMAGES=false

# Discord webhook for deployment notifications
DISCORD_WEBHOOK=
NOTIFY_DISCORD=true

# Ntfy.sh notifications
NTFY_URL=
```

## Security Notes

- Image files are validated for type before processing
- File permissions are set to 755 (readable by web server)
- Ownership is set to the application user (`alsar4210`)
- No external image services are called during optimization

## Performance Impact

### Cache Busting
- **Negligible**: Version string is cached and reused
- **Benefit**: Users get fresh images immediately after deployment

### Image Optimization
- **One-time cost**: ~1-5 seconds per image during deployment
- **Benefit**: 20-60% file size reduction on average
- **Recommendation**: Enable for production, disable for quick dev deploys

## Support

For issues or questions:
1. Run diagnostics: `./deploy.sh --diagnose`
2. Check logs: `storage/logs/laravel.log`
3. Review manifest: `storage/framework/image_manifest.json`
