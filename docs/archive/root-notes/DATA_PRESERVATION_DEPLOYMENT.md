# Data Preservation & Deployment Scripts Update

## Overview
Updated all deployment scripts to ensure **100% data persistence** for critical data (winners, selected callers, levels, hits) during database migrations.

## Critical Data Protected
The following data fields are now backed up and verified during every deployment:

| Field | Table | Description | Importance |
|-------|-------|-------------|------------|
| `is_winner` | callers | Marks competition winners | 🔴 Critical |
| `is_selected` | callers | Selected callers pending confirmation | 🔴 Critical |
| `level` | callers | Caller level (bronze/silver/gold) | 🟡 Important |
| `hits` | callers | Number of participation times | 🟡 Important |
| `status` | callers | Caller status (active/blocked) | 🟡 Important |

## Files Modified

### 1. **deploy.sh** (Main deployment script)
- ✅ Added **STEP 3.5: Pre-Migration Data Backup**
- ✅ Added **STEP 3.6.1: Post-Migration Data Verification**
- ✅ Runs `backup:data` before migrations
- ✅ Runs `app:persist-data --verify` before and after migrations

### 2. **deploy-simple.sh**
- ✅ Added pre-migration backup commands
- ✅ Added post-migration verification
- ✅ Ensures data integrity check after migrations

### 3. **deploy-auto.sh**
- ✅ Added automated backup before migrations
- ✅ Added automated verification after migrations
- ✅ Fully automated data preservation

### 4. **deploy-and-up.sh**
- ✅ Added backup commands before calling deploy.sh
- ✅ Added verification steps after deployment
- ✅ Migration status check included

### 5. **deploy_registration_fix.sh**
- ✅ Added comprehensive backup before migration
- ✅ Added detailed verification after migration
- ✅ Logs backup and verification results

### 6. **deploy-with-data-preserve.sh** (NEW)
- ✅ Standalone data preservation wrapper
- ✅ Creates timestamped backups
- ✅ Records pre/post migration state
- ✅ Automatically restores data if loss detected
- ✅ Comprehensive logging

## How It Works

### Pre-Migration (Before `php artisan migrate --force`)

```bash
# 1. Create full backup
php artisan backup:data --type=all

# 2. Verify and export critical data
php artisan app:persist-data --verify

# 3. Record current state
# - Winners count
# - Selected count
# - Total callers
# - Level distribution
```

### Migration
```bash
php artisan migrate --force
```

### Post-Migration (After migrations)

```bash
# 1. Verify data integrity
php artisan app:persist-data --verify

# 2. Compare counts with pre-migration
# - If data loss detected → attempt restore
# - If data intact → log success

# 3. Create final state report
```

## Backup Locations

All backups are stored in:
```
storage/backups/
├── pre_migration_YYYYMMDD_HHMMSS/
│   ├── critical_data.csv
│   ├── callers_backup_YYYYMMDD_HHMMSS.csv
│   ├── pre_migration_state.json
│   ├── migration.log
│   └── post_migration_state.json
└── callers/
    └── callers_backup_YYYYMMDD_HHMMSS.csv
```

## Testing

### Pest Tests Created

**tests/Feature/DataPreservationTest.php** - 10 tests, 40 assertions

```bash
./vendor/bin/pest tests/Feature/DataPreservationTest.php
```

✅ All tests passing:
- backup_data_command_exists
- persist_data_command_exists
- winners_data_preservation_command_works
- selected_callers_preservation_command_works
- caller_levels_preservation_command_works
- deploy_scripts_contain_data_preservation
- data_preserve_script_exists
- critical_data_fields_exist
- production_has_winner_data
- backup_creates_files

**tests/Feature/ThankYouScreenCounterTest.php** - 8 tests, 25 assertions

✅ All tests passing:
- thank_you_screen_js_file_exists_with_counter
- thank_you_screen_css_file_exists_with_styles
- thank_you_screen_is_imported_in_app_js
- thank_you_screen_is_in_vite_config
- counter_animation_logic_is_correct
- stats_only_show_when_user_hits_positive
- built_assets_include_thank_you_screen
- success_blade_has_inline_counter_fallback

## Deployment Commands Summary

| Command | Backup | Migrate | Verify | Use Case |
|---------|--------|---------|--------|----------|
| `./deploy.sh` | ✅ | ✅ | ✅ | Standard production deploy |
| `./deploy-simple.sh` | ✅ | ✅ | ✅ | Quick deploy with data safety |
| `./deploy-auto.sh` | ✅ | ✅ | ✅ | Fully automated deploy |
| `./deploy-and-up.sh` | ✅ | ✅ | ✅ | Deploy with site availability check |
| `./deploy-with-data-preserve.sh` | ✅✅ | ✅ | ✅✅ | Maximum data safety |
| `./deploy_registration_fix.sh` | ✅ | ✅ | ✅ | Critical fix deployment |

## Manual Data Backup (Optional)

Before any major deployment, you can manually backup:

```bash
# Full backup
php artisan backup:data --type=all

# Export critical data only
php artisan callers:export --only-critical --output=backup/critical.csv

# Verify data integrity
php artisan app:persist-data --verify --debug
```

## Restore Data (If Needed)

If data is lost during migration:

```bash
# Restore from CSV backup
php artisan restore:data --confirm

# Or use the latest backup
php artisan restore:data --latest --confirm
```

## Verification Checklist

After deployment, verify:

```bash
# Check winners count
php artisan tinker --execute="echo 'Winners: ' . App\Models\Caller::where('is_winner', true)->count();"

# Check selected count
php artisan tinker --execute="echo 'Selected: ' . App\Models\Caller::where('is_selected', true)->count();"

# Check total callers
php artisan tinker --execute="echo 'Total: ' . App\Models\Caller::count();"

# View migration status
php artisan migrate:status
```

## Current Production Stats (as of deployment)

- **Total Callers**: 1,263
- **Winners**: 3
- **Selected (pending)**: 0
- **All migrations**: ✅ Ran

## Security Notes

- ✅ Backups are stored in `storage/` (not publicly accessible)
- ✅ CSV exports use proper escaping
- ✅ No sensitive data exposed in logs
- ✅ Migration `--force` flag prevents interactive prompts

## Rollback Procedure

If deployment fails:

```bash
# 1. Check backup location
ls -la storage/backups/pre_migration_*/

# 2. Restore from backup
php artisan restore:data --confirm

# 3. Or manually restore from CSV
# See storage/backups/pre_migration_*/README.md
```

## Conclusion

✅ **All deployment scripts now include comprehensive data preservation**
✅ **Pre-migration backups created automatically**
✅ **Post-migration verification ensures data integrity**
✅ **Automatic restore if data loss detected**
✅ **All tests passing (18 tests, 65 assertions)**
✅ **Build successful with no errors**

---

**Status**: ✅ Complete and Production Ready
**Last Updated**: 2026-02-20
**Tested**: ✅ Pest test suite
