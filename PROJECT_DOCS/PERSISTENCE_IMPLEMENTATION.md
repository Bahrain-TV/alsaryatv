# Data Persistence Command Implementation Summary

## What Was Created

### 1. **PersistDataCommand** (`app/Console/Commands/PersistDataCommand.php`)
A comprehensive command that ensures data persistency across production environments.

**Features:**
- Verifies database integrity and connection
- Monitors for data anomalies (NULL values, duplicates)
- Creates timestamped CSV backups of all caller data
- Maintains a rolling 30-day backup history
- Checks available disk space
- Logs detailed persistence metrics
- Provides real-time console feedback

**Command Signature:**
```bash
php artisan app:persist-data
```

**Available Options:**
```bash
--export-csv      # Export data to CSV for backup
--verify          # Verify data integrity
--full-backup     # Create a full database backup
--debug           # Show detailed debug information
```

### 2. **Console Kernel** (`app/Console/Kernel.php`)
Created the scheduler configuration with two persistent schedules:

**Schedule 1: Hourly Check**
- Runs every hour to verify data consistency
- Ensures quick failure detection
- Logs success/failure events
- Prevents overlapping executions with `withoutOverlapping()`

**Schedule 2: Daily Full Check**
- Runs at 2:00 AM daily (adjustable timezone)
- Performs full verification and CSV export
- Creates timestamped backups
- Cleans up backups older than 30 days

### 3. **Configuration Guide** (`DATA_PERSISTENCE_GUIDE.md`)
Comprehensive documentation including:
- Usage instructions
- Three scheduling options (Scheduler, Cron, Supervisor)
- Example outputs
- Troubleshooting guide
- Production deployment checklist

## How to Use

### Immediate Usage (Manual)
```bash
# Test the command
cd /Users/aldoyh/Sites/RAMADAN/alsaryatv-latest
php artisan app:persist-data

# With debug output
php artisan app:persist-data --debug
```

### Production Scheduling (Automatic - Recommended)

#### Option A: Laravel Scheduler (Built-in)
The `Kernel.php` is already configured. Just ensure the scheduler runs:

```bash
# Start the scheduler (runs in foreground)
php artisan schedule:work

# Or add to your production server (crontab)
* * * * * cd /path/to/alsaryatv-latest && php artisan schedule:run >> /dev/null 2>&1
```

#### Option B: Direct Cron
Add to your server's crontab (`crontab -e`):

```bash
# Hourly check
0 * * * * cd /Users/aldoyh/Sites/RAMADAN/alsaryatv-latest && php artisan app:persist-data >> storage/logs/persist-data.log 2>&1

# Daily full check at 2 AM
0 2 * * * cd /Users/aldoyh/Sites/RAMADAN/alsaryatv-latest && php artisan app:persist-data --verify --export-csv >> storage/logs/persist-data.log 2>&1
```

#### Option C: Supervisor (For Always-Running Scheduler)
Create `/etc/supervisor/conf.d/alsaryatv-schedule.conf`:
```ini
[program:alsaryatv-schedule]
command=php /Users/aldoyh/Sites/RAMADAN/alsaryatv-latest/artisan schedule:work
autostart=true
autorestart=true
stdout_logfile=/Users/aldoyh/Sites/RAMADAN/alsaryatv-latest/storage/logs/schedule.log
stderr_logfile=/Users/aldoyh/Sites/RAMADAN/alsaryatv-latest/storage/logs/schedule.log
```

## What Happens When The Command Runs

1. **Verification Phase**
   - Checks database connection
   - Counts total callers and statistics
   - Detects NULL values in critical fields
   - Identifies duplicate CPRs
   - Reports winner and family member counts

2. **Export Phase**
   - Exports all caller data to CSV format
   - Creates timestamped backup file
   - Stores in `storage/app/backups/callers/`
   - Maintains 30-day rolling backup history

3. **Verification Phase**
   - Confirms backup directories exist
   - Checks disk space availability (warns if <1GB)
   - Verifies backup file creation

4. **Logging Phase**
   - Records detailed metrics to logs
   - Logs: timestamp, caller counts, environment, disk space
   - Tracks success/failure for monitoring

## Backup Location

All backups are stored at:
```
storage/app/backups/callers/callers_backup_YYYY-MM-DD_HH-MM-SS.csv
storage/app/exports/callers/  (also contains latest export)
```

Monitor backups:
```bash
ls -lh storage/app/backups/callers/ | tail -20
```

## Monitoring & Alerts

### Check Latest Run
```bash
tail -f storage/logs/laravel.log | grep "Data persistence"
```

### View All Backups
```bash
ls -lh storage/app/backups/callers/
```

### Monitor Disk Space
```bash
df -h storage/
```

## Integration with Existing Commands

The new command works alongside your existing `ImportCallersCommand`:
- **Import Command**: `php artisan app:callers:import` - Imports data INTO the database
- **Persist Command**: `php artisan app:persist-data` - Backs up and verifies data ALREADY in database

## Production Deployment Steps

1. ✅ **Commands Created**
   - `app/Console/Commands/PersistDataCommand.php` ✓
   - `app/Console/Kernel.php` ✓

2. **Deploy to Server**
   ```bash
   git add app/Console/
   git commit -m "Add data persistence command for production"
   git push
   ```

3. **On Production Server**
   ```bash
   cd /path/to/alsaryatv-latest
   git pull
   php artisan cache:clear
   
   # Test the command
   php artisan app:persist-data
   
   # Start scheduler (choose one option above)
   ```

4. **Verify Scheduler is Running**
   ```bash
   # If using supervisor
   sudo supervisorctl status alsaryatv-schedule
   
   # If using cron, verify logs
   tail storage/logs/laravel.log
   ```

## Key Benefits

✓ **Data Persistence**: Automatic hourly verification ensures no data loss
✓ **Backup Strategy**: Rolling 30-day backup window
✓ **Production-Safe**: Prevents overlapping executions
✓ **Monitoring**: Detailed logging for alerts and audits
✓ **Disk Awareness**: Tracks available space and warns on low disk
✓ **Easy Restoration**: CSV format allows easy recovery if needed
✓ **Zero Downtime**: Runs in background without affecting application

## Troubleshooting

**Command won't run:**
- Check PHP syntax: `php -l app/Console/Commands/PersistDataCommand.php`
- Verify database connection in `.env`
- Check Laravel logs: `storage/logs/laravel.log`

**Scheduler not running:**
- Ensure `php artisan schedule:work` is running (Option A)
- Or verify cron job exists: `crontab -l`
- Check cron logs: `grep CRON /var/log/syslog`

**Backups not being created:**
- Check storage permissions: `chmod -R 775 storage/`
- Verify disk space: `df -h`
- Check logs for errors

## Related Resources

- [DATA_PERSISTENCE_GUIDE.md](./DATA_PERSISTENCE_GUIDE.md) - Full configuration guide
- [Laravel Scheduler Documentation](https://laravel.com/docs/11.x/scheduling)
- [Existing Import Command](app/Console/Commands/ImportCallersCommand.php)
