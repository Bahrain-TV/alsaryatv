# Data Persistence Command Setup Guide

## Overview
The `app:persist-data` command ensures data persistency across production environments by:
- Verifying data integrity in the database
- Creating CSV backups of critical data
- Monitoring disk space and backup structure
- Logging persistence metrics

## Command Usage

### Basic Usage
```bash
php artisan app:persist-data
```

### With Options
```bash
# Verify data integrity
php artisan app:persist-data --verify

# Export data to CSV backup
php artisan app:persist-data --export-csv

# Show debug information
php artisan app:persist-data --debug

# Run with all checks
php artisan app:persist-data --verify --export-csv
```

## Scheduling in Production

### Option 1: Using Laravel Scheduler (Recommended)

Create or update `app/Console/Kernel.php`:

```php
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run persist-data every hour
        $schedule->command('app:persist-data')->hourly()
            ->name('persist-data-hourly')
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('Data persistence command completed successfully');
            })
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('Data persistence command failed');
            });

        // Run daily at 2:00 AM with full checks
        $schedule->command('app:persist-data --verify --export-csv')->dailyAt('02:00')
            ->name('persist-data-daily')
            ->timezone('Asia/Bahrain'); // Adjust timezone as needed
    }
}
```

### Option 2: Using Cron (Direct)

Add to your server's crontab:

```bash
# Run every hour
0 * * * * cd /Users/aldoyh/Sites/RAMADAN/alsaryatv-latest && php artisan app:persist-data >> storage/logs/persist-data.log 2>&1

# Run daily at 2:00 AM
0 2 * * * cd /Users/aldoyh/Sites/RAMADAN/alsaryatv-latest && php artisan app:persist-data --verify --export-csv >> storage/logs/persist-data.log 2>&1
```

### Option 3: Using Supervisor (For Always-Running Daemon)

Create `/etc/supervisor/conf.d/alsaryatv-schedule.conf`:

```ini
[program:alsaryatv-schedule]
process_name=%(program_name)s_%(process_num)02d
command=php /Users/aldoyh/Sites/RAMADAN/alsaryatv-latest/artisan schedule:work
autostart=true
autorestart=true
numprocs=1
stdout_logfile=/Users/aldoyh/Sites/RAMADAN/alsaryatv-latest/storage/logs/schedule-worker.log
stderr_logfile=/Users/aldoyh/Sites/RAMADAN/alsaryatv-latest/storage/logs/schedule-worker.log
```

Then run:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start alsaryatv-schedule:*
```

## Features

### Data Integrity Checks
- ✓ Database connection verification
- ✓ Count total callers
- ✓ Detect NULL values in critical fields
- ✓ Identify duplicate CPRs
- ✓ Report winner and family statistics

### Backup Management
- ✓ Automatic CSV export
- ✓ Timestamped backups (kept for 30 days)
- ✓ Automatic cleanup of old backups
- ✓ Directory creation and verification

### Monitoring
- ✓ Available disk space reporting
- ✓ Backup structure verification
- ✓ Detailed logging of all operations
- ✓ Success/failure notifications

## Output Example

```
╔════════════════════════════════════════════════════════╗
║         Data Persistence Command Started              ║
╚════════════════════════════════════════════════════════╝

→ Verifying data integrity...
  ✓ Database connection verified
  ✓ Total callers in database: 5,234
  ✓ Total winners marked: 123
  ✓ Total family members marked: 45
✓ Data integrity verification completed

→ Exporting data to CSV backup...
  ✓ CSV backup created: callers_backup_2025-02-02_19-00-00.csv
  ✓ Timestamped backup created: callers_backup_2025-02-02_19-00-00.csv
  ✓ Cleaned up 2 old backups (kept 30)
✓ Data export completed

→ Verifying backup structure...
  ✓ Backup directory verified: backups/callers (45 files)
  ✓ Available disk space: 125.50 GB
✓ Backup structure verified

→ Logging persistence metrics...
  ✓ Metrics logged:
    - timestamp: 2025-02-02 19:00:00
    - total_callers: 5234.00
    - total_winners: 123.00
    - total_family: 45.00
    - database: mysql
    - environment: production
    - disk_space_available_gb: 125.50
✓ Metrics logged successfully

✓ Data persistence check completed successfully!
```

## Environment Variables

Ensure your `.env` file has proper database configuration:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alsaryatv
DB_USERNAME=root
DB_PASSWORD=your_password

LOG_CHANNEL=stack
```

## Log Files

Check logs in `storage/logs/`:
- `laravel.log` - General application logs
- `persist-data.log` - Dedicated persist-data logs (if configured)

## Monitoring Commands

```bash
# View recent persist-data executions
tail -f storage/logs/laravel.log | grep "persist-data"

# Check last backup files
ls -lah storage/app/backups/callers/ | tail -10

# View data persistence metrics
grep "Data persistence metrics" storage/logs/laravel.log
```

## Troubleshooting

### Command hangs or times out
- Check database connectivity: `php artisan tinker` → `DB::connection()->getPdo()`
- Increase PHP timeout in `php.ini`: `max_execution_time = 300`

### Permission denied errors
- Ensure storage directory is writable: `chmod -R 775 storage`
- Check file ownership: `chown -R www-data:www-data storage`

### Disk space warnings
- Monitor `disk_space_available_gb` in logs
- Archive old backups or clean up unnecessary files
- Configure backup retention in `cleanOldBackups()` method

## Production Deployment Checklist

- [ ] Test command locally: `php artisan app:persist-data`
- [ ] Create `app/Console/Kernel.php` with schedule
- [ ] Set correct timezone in schedule
- [ ] Configure log files with proper rotation
- [ ] Set up monitoring/alerting for failed runs
- [ ] Test cron/scheduler with: `php artisan schedule:work`
- [ ] Verify backups are created in `storage/app/backups/callers/`
- [ ] Set up automated alerts for critical data issues
- [ ] Document backup restoration procedures

## Related Commands

- `app:callers:import` - Import callers from CSV
- `app:show:stats` - Display application statistics
- `queue:work` - Process background jobs

## Support

For issues or questions, check the application logs and verify:
1. Database connectivity
2. Storage permissions
3. Disk space availability
4. Laravel scheduler is running (if using scheduler option)
