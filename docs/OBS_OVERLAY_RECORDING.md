# OBS Overlay Recording System

This system automatically records the AlSarya TV OBS overlay animation daily at 5:00 AM (Bahrain timezone) and stores it in the database.

## Components

### 1. **Database Migration**
- `database/migrations/2026_02_16_050000_create_obs_overlay_videos_table.php`
- Creates `obs_overlay_videos` table to track all recordings

### 2. **Model**
- `app/Models/ObsOverlayVideo.php`
- Provides helpers for file paths and public URLs
- Includes query scopes for filtering

### 3. **Console Command**
- `app/Console/Commands/RecordObsOverlay.php`
- Records the overlay using Playwright + FFmpeg
- Automatically stores metadata in database
- Prunes videos older than 30 days

### 4. **Scheduler**
- Registered in `app/Console/Kernel.php`
- Runs daily at **5:00 AM (Asia/Bahrain timezone)**
- Logs output to `storage/logs/obs-overlay.log`

### 5. **Filament Admin Panel**
- `app/Filament/Resources/ObsOverlayVideoResource.php`
- View, manage, and download recordings
- Filter by status (ready, archived, deleted)
- Located at `/admin/obs-overlay-videos`

## Setup

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Create Storage Symlink (if not exists)
```bash
php artisan storage:link
```

### 3. Ensure Public Folder Exists
```bash
mkdir -p storage/app/public/obs-overlays
chmod 755 storage/app/public/obs-overlays
```

### 4. Verify Scheduler is Running
The schedule will execute when your Laravel scheduler is running. Ensure one of:

**Option A: Using System Cron (Production)**
```bash
* * * * * cd /path/to/alsaryatv && php artisan schedule:run >> /dev/null 2>&1
```

**Option B: Using Supervisor (Recommended for Production)**
Create `/etc/supervisor/conf.d/laravel-scheduler.conf`:
```ini
[program:laravel-scheduler]
process_name=%(program_name)s
command=php /path/to/alsaryatv/artisan schedule:run
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/path/to/alsaryatv/storage/logs/scheduler.log
```

Then reload:
```bash
supervisorctl reread
supervisorctl update
supervisorctl start laravel-scheduler
```

**Option C: Using `schedule:work` (Development)**
```bash
php artisan schedule:work
```

## Manual Recording

To manually record the overlay:

```bash
php artisan obs:record
```

With custom parameters:
```bash
php artisan obs:record \
  --url http://localhost:8000/obs-overlay \
  --seconds 65 \
  --fps 30
```

## File Structure

Videos are stored at:
```
storage/app/public/obs-overlays/obs-overlay-YYYY-MM-DD-HH-i-ss.mov
```

Public URL:
```
https://yourdomain.com/storage/obs-overlays/obs-overlay-YYYY-MM-DD-HH-i-ss.mov
```

## Pruning

The system automatically prunes videos older than **30 days** after each recording.

To manually trigger pruning:
```bash
php artisan obs:record --prune-only
```

## Database Schema

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| filename | string | Unique filename with timestamp |
| path | string | Relative path for public access |
| file_size | bigint | Size in bytes |
| mime_type | string | Default: video/quicktime |
| recorded_at | datetime | When video was recorded |
| status | string | ready, archived, deleted |
| notes | text | Optional notes |
| created_at | datetime | Database timestamp |
| updated_at | datetime | Last update timestamp |

## Logs

Recording logs are stored at:
```
storage/logs/obs-overlay.log
```

Check for errors:
```bash
tail -f storage/logs/obs-overlay.log
```

## Troubleshooting

### Videos not recording?
1. Check if scheduler is running: `ps aux | grep schedule:work`
2. Check logs: `tail -f storage/logs/obs-overlay.log`
3. Check if app is running: ensure `/obs-overlay` loads in browser
4. Verify FFmpeg is installed: `ffmpeg -version`
5. Verify Playwright is installed: `npm ls playwright`

### Storage symlink issues?
```bash
php artisan storage:link
```

### Permission issues?
```bash
chmod -R 755 storage/app/public/obs-overlays
```

## Admin Panel Access

Navigate to `/admin/obs-overlay-videos` to manage recordings:
- View all recordings with metadata
- Download or preview videos
- Filter by status
- Delete old videos
- View file sizes and timestamps

## Performance Notes

- Each recording takes ~65 seconds to capture
- ProRes 4444 format with alpha channel = ~500-800 MB per video
- Pruning runs automatically after each recording
- Consider disk space for 30-day retention
