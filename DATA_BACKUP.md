# Data Backup & Hit Counter Logging Guide

This document explains how the deployment system backs up and logs caller data, ensuring no data loss during deployments.

---

## Overview

The AlSarya TV system includes **automatic and manual backup strategies** to protect critical caller data:

| Type | Trigger | Frequency | Retention | Format |
|------|---------|-----------|-----------|--------|
| **Database Backup** | Before each deployment | Every deploy | Manual cleanup | SQL/.sqlite backup |
| **Callers CSV Export** | Before each deployment | Every deploy | Manual cleanup | CSV file |
| **Daily Hit Logs** | After each deployment | Every deploy | 7 days (auto-delete) | CSV file |
| **Manual Backup** | On-demand via artisan | Any time | Manual cleanup | CSV file |

---

## Automatic Backups (During Deployment)

When you run `./publish.sh` or `./deploy.sh`, the system automatically:

### 1. Creates Database Backup
- **Location**: `storage/backups/backup_YYYYMMDD_HHMMSS.sql` (MySQL) or `.sqlite.backupYYYYMMDD_HHMMSS` (SQLite)
- **Includes**: All database tables and data
- **Timing**: Before any migrations run
- **Purpose**: Rollback capability if deployment fails

### 2. Exports Caller Data to CSV
- **Location**: `storage/backups/callers_backup_YYYYMMDD_HHMMSS.csv`
- **Includes**: ID, CPR, Phone, Name, Hits, Status, Winner, IP, Timestamps
- **Timing**: Before migrations
- **Purpose**: Easy recovery of caller data

### 3. Logs Daily Hit Counters
- **Location**: `storage/logs/hits/hits_YYYY-MM-DD.csv`
- **Includes**: Timestamp, Caller ID, CPR, Name, Phone, Hits, Status, IP
- **Timing**: After deployment completes
- **Purpose**: Track daily hit counter snapshots

---

## Example: Deployment with Automatic Backups

```
$ ./publish.sh

[INFO]  Creating database backup before deployment...
[OK]    SQLite database backup created: database/alsaryatv.sqlite.backup_20260217_142530
[INFO]  Exporting callers data to CSV...
[OK]    Callers CSV backup created
[INFO]  Backups stored in: storage/backups

[INFO]  Running database migrations...
[OK]    Migrations applied.

[INFO]  Logging hit counters to daily CSV...
[OK]    Hit counters logged to: storage/logs/hits/hits_2026-02-17.csv
[INFO]  Daily logs stored in: storage/logs/hits
[INFO]  Format: CSV with columns [timestamp, caller_id, cpr, name, phone, hits, status, ip_address]
```

---

## Manual Backup Command

Back up data on-demand using the Artisan command:

### Backup All Data
```bash
php artisan backup:data --type=all
```

**Output:**
```
╔══════════════════════════════════════╗
║  AlSarya TV Data Backup Manager      ║
╚══════════════════════════════════════╝

Backup directory: /path/to/storage/backups
Logs directory: /path/to/storage/logs/hits

Backing up caller data...
  ✓ Backed up 2456 callers
  → callers_backup_2026-02-17_15-30-45.csv

Logging hit counters...
  ✓ Logged 2456 hit counters
  → hits_2026-02-17.csv

✓ Backup process completed successfully!
```

### Backup Only Callers
```bash
php artisan backup:data --type=callers
```

### Log Only Hit Counters
```bash
php artisan backup:data --type=hits
```

### Clean Up Old Backups
```bash
# Delete backups older than 7 days (default)
php artisan backup:data --clean=7

# Delete backups older than 30 days
php artisan backup:data --clean=30

# Disable cleanup
php artisan backup:data --clean=0
```

---

## Backup Directory Structure

```
storage/
├── backups/                          # Database & data exports
│   ├── backup_20260217_120000.sql   # MySQL database dumps
│   ├── backup_20260217_120000.sqlite # SQLite backup
│   ├── callers_backup_20260217_120000.csv
│   ├── callers_backup_20260217_140000.csv
│   └── ...
│
└── logs/
    └── hits/                         # Daily hit counter logs
        ├── hits_2026-02-17.csv       # Today's snapshots
        ├── hits_2026-02-18.csv
        └── ...
```

---

## CSV File Formats

### Callers Backup CSV
```csv
ID,CPR,Phone,Name,Hits,Status,Is Winner,IP Address,Created At,Updated At
1,123456789,17123456,أحمد محمد,5,active,No,192.168.1.1,2026-02-18 10:30:00,2026-02-18 11:45:00
2,987654321,17654321,فاطمة علي,3,active,Yes,192.168.1.2,2026-02-18 10:35:00,2026-02-18 11:50:00
```

### Daily Hit Logs CSV
```csv
timestamp,caller_id,cpr,name,phone,hits,status,ip_address
2026-02-17 10:00:00,1,123456789,أحمد محمد,17123456,5,active,192.168.1.1
2026-02-17 10:15:00,1,123456789,أحمد محمد,17123456,6,active,192.168.1.1
2026-02-17 10:30:00,2,987654321,فاطمة علي,17654321,3,active,192.168.1.2
2026-02-17 11:00:00,1,123456789,أحمد محمد,17123456,7,active,192.168.1.1
2026-02-17 11:00:00,2,987654321,فاطمة علي,17654321,4,active,192.168.1.2
```

---

## Data Recovery

### Recover Caller Data from CSV

```bash
# SSH to production server
ssh alsar4210@alsarya.tv

cd /home/alsarya.tv/public_html

# Find available backup
ls -lh storage/backups/callers_backup_*.csv

# Check what's in it
head -20 storage/backups/callers_backup_20260217_140000.csv

# If you need to restore from CSV, you can:
php artisan tinker
# ... then import CSV data

# Or create a restore command (we can add this if needed)
```

### Recover Database from Backup

**For SQLite:**
```bash
# List backups
ls -lh database/alsaryatv.sqlite.backup_*

# Restore (DESTRUCTIVE - will overwrite current DB)
cp database/alsaryatv.sqlite.backup_20260217_120000 database/alsaryatv.sqlite
php artisan migrate
```

**For MySQL:**
```bash
# List backups
ls -lh storage/backups/backup_*.sql

# Restore (DESTRUCTIVE)
mysql -u root -p your_database < storage/backups/backup_20260217_120000.sql
```

---

## Scheduled Daily Backups

To create **automatic daily backups** (independent of deployments), add a scheduled task:

### Option 1: Via Cron (Recommended)

Edit `/etc/cron.d/laravel-backup`:
```cron
# Daily backup at 2 AM (before peak hours)
0 2 * * * www-data cd /home/alsarya.tv/public_html && php artisan backup:data >> storage/logs/backup-cron.log 2>&1

# Cleanup old backups every Sunday at 1 AM
0 1 * * 0 www-data cd /home/alsarya.tv/public_html && php artisan backup:data --type=hits --clean=30 >> storage/logs/backup-cron.log 2>&1
```

### Option 2: Via Laravel Scheduler

Update `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    // Daily backup at 2 AM
    $schedule->command('backup:data --type=all')
        ->dailyAt('02:00')
        ->onSuccess(function () {
            \Log::info('Daily backup completed successfully');
        })
        ->onFailure(function () {
            \Log::error('Daily backup failed');
        });

    // Weekly cleanup (Sundays at 1 AM)
    $schedule->command('backup:data --type=hits --clean=30')
        ->sundays()
        ->at('01:00');
}
```

Then ensure the queue is running:
```bash
php artisan schedule:run
```

---

## Monitoring & Verification

### Check Backup Age
```bash
# On production server
ls -lh storage/backups/ | head -10
ls -lh storage/logs/hits/ | head -10
```

### Verify Backup Integrity

```bash
# Check CSV is valid
head -5 storage/backups/callers_backup_*.csv

# Count records
wc -l storage/backups/callers_backup_*.csv

# Check database backup size
du -h database/alsaryatv.sqlite.backup_*
```

### Monitor via SSH from Local
```bash
ssh alsar4210@alsarya.tv "ls -lh /home/alsarya.tv/public_html/storage/backups/ | tail -10"
```

---

## Best Practices

✅ **Do:**
- Review backup logs after each deployment
- Keep backups for at least 7-30 days
- Test recovery procedures occasionally
- Monitor backup directory size
- Document what each backup contains
- Use consistent naming conventions
- Verify backups are readable

❌ **Don't:**
- Deploy without backups
- Delete backups immediately
- Trust only automated backups (test manual ones too)
- Store backups in single location (consider offsite backup)
- Ignore backup failures
- Keep too many backups (disk space)

---

## Disaster Recovery Plan

### If Deployment Fails:

1. **Site is in maintenance mode** - automatically reverted if deploy fails
2. **Database unchanged** - backup was taken first, migrations didn't run
3. **Caller data safe** - CSV export created before any changes

### If Data Corruption Occurs:

1. **Identify issue**:
   ```bash
   php artisan tinker
   DB::table('callers')->count()
   ```

2. **Check backups**:
   ```bash
   ls -lh storage/backups/callers_backup_*.csv
   ```

3. **Restore from CSV**:
   - Download the CSV from production
   - Create a restore Artisan command
   - Test in development first
   - Apply to production

### If Database File is Lost:

1. **Restore from backup**:
   ```bash
   cp storage/backups/backup_20260217_120000.sql ./restored.sql
   mysql -u root -p < restored.sql
   ```

2. **Verify data**:
   ```bash
   php artisan migrate:status
   php artisan tinker
   ```

---

## Storage Requirements

Estimate backup sizes:
- **1,000 callers**: ~50 KB CSV, ~200 KB database
- **10,000 callers**: ~500 KB CSV, ~2 MB database
- **Daily logs (1,000 callers × 10 snapshots/day)**: ~500 KB per day

**Recommended storage**:
- 7-day retention: ~10 MB
- 30-day retention: ~50 MB
- 90-day retention: ~150 MB

Check available space:
```bash
df -h /home/alsarya.tv/
du -sh /home/alsarya.tv/public_html/storage/
```

---

## Troubleshooting

### Backups not being created

```bash
# Check permissions
ls -la storage/backups/
chmod 755 storage/backups

# Check disk space
df -h
du -sh storage/

# Run manual backup with debug
php artisan backup:data --type=all -vvv
```

### CSV file is empty or corrupted

```bash
# Verify file
file storage/backups/callers_backup_*.csv
head -5 storage/backups/callers_backup_*.csv
tail -5 storage/backups/callers_backup_*.csv

# Check for encoding issues
file -b --mime-encoding storage/backups/callers_backup_*.csv
```

### Hit logs not accumulating

```bash
# Check if command is running
grep "backup:data" /var/log/cron

# Check Laravel logs
tail -50 storage/logs/laravel.log | grep -i backup

# Manually trigger
php artisan backup:data --type=hits -v
```

---

## Summary

| Task | Command | Output |
|------|---------|--------|
| Manual backup | `php artisan backup:data` | Creates backups in `storage/backups/` |
| Backup callers only | `php artisan backup:data --type=callers` | CSV file |
| Log hits only | `php artisan backup:data --type=hits` | Daily CSV in `storage/logs/hits/` |
| Clean old files | `php artisan backup:data --clean=30` | Removes files > 30 days old |
| Schedule backups | Add to cron or scheduler | Automatic daily backups |
| Check backups | `ls -lh storage/backups/` | List all backups |
| Restore data | See recovery section | Database & caller recovery |

**Remember**: Backups are only useful if you test them regularly. Practice recovery procedures!
