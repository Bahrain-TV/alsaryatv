# Deployment Quick Reference — AlSarya TV

**Last Updated:** February 17, 2026

---

## 🚀 Quick Start

```bash
# Standard deployment from your local machine
./publish.sh

# That's it! The script will:
# ✓ Validate git state
# ✓ Push code to remote
# ✓ SSH to production
# ✓ Backup database and callers
# ✓ Run migrations
# ✓ Build frontend assets
# ✓ Clear caches
# ✓ Verify health
```

---

## 📋 Common Deployment Scenarios

### 1. Normal Production Deploy
```bash
./publish.sh
```
- Pushes latest code to production
- Runs pending migrations only
- Backs up data first
- **Impact**: No downtime, safe

### 2. Reset Production Database
```bash
./publish.sh --reset-db
```
- Drops ALL tables
- Recreates database structure (migrations)
- **NO** seeding (empty database)
- **Impact**: DATA LOSS - all callers deleted
- **When**: Testing, cleanup, fresh start
- **Backup**: Automatic backup created first

### 3. Fresh Database with Initial Data
```bash
./publish.sh --fresh
```
- Drops ALL tables
- Recreates database structure
- Runs seeders (initial data)
- **Impact**: DATA LOSS - all callers deleted
- **When**: Major redesign, full reset needed
- **Backup**: Automatic backup created first

### 4. Dry-Run (Test Without Changes)
```bash
./publish.sh --dry-run
```
- Validates all prerequisites
- Shows what WOULD happen
- Makes NO actual changes
- **When**: Testing configuration, first time setup

### 5. Skip Frontend Build
```bash
./publish.sh --no-build
```
- Faster deployment if only backend changed
- Skips npm install and build
- Saves 2-3 minutes

### 6. Force Full Rebuild
```bash
./publish.sh --force
```
- Ignores "no changes detected" optimization
- Rebuilds everything
- Useful if something seems broken

---

## 🔄 Data Backup & Recovery

### Automatic During Deployment
```bash
# Before each deployment:
# 1. Database backup: storage/backups/backup_YYYYMMDD_HHMMSS.sql
# 2. Callers CSV: storage/backups/callers_backup_YYYYMMDD_HHMMSS.csv
# 3. Hit logs: storage/logs/hits/hits_YYYY-MM-DD.csv
```

### Manual Backup On-Demand
```bash
# Backup everything
php artisan backup:data

# Backup only callers
php artisan backup:data --type=callers

# Log only today's hits
php artisan backup:data --type=hits

# Clean up backups older than 30 days
php artisan backup:data --clean=30
```

### Restore From Backup
```bash
# List available backups
php artisan restore:data

# Restore from specific backup
php artisan restore:data --file=storage/backups/callers_backup_YYYYMMDD_HHMMSS.csv

# No confirmation prompt
php artisan restore:data --file=... --confirm
```

---

## 📊 Daily Hit Counter Logging

Automatically logged every deployment to `storage/logs/hits/hits_YYYY-MM-DD.csv`:

```csv
timestamp,caller_id,cpr,name,phone,hits,status,ip_address
2026-02-17 10:00:00,1,123456789,أحمد محمد,17123456,5,active,192.168.1.1
2026-02-17 10:15:00,1,123456789,أحمد محمد,17123456,6,active,192.168.1.1
```

**Monitor from local:**
```bash
ssh alsar4210@alsarya.tv "tail -20 /home/alsarya.tv/public_html/storage/logs/hits/hits_$(date +%Y-%m-%d).csv"
```

---

## 🛡️ Safety Features

✅ **What You Get:**
- Automatic database backup before ANY changes
- CSV exports of all caller data before deployment
- Daily snapshots of hit counters
- Automatic rollback if deployment fails
- Health checks after deployment
- Maintenance mode during deployment
- Change detection (skip unnecessary rebuilds)

✅ **Data Protection:**
- 7-day automatic backup retention
- Manual backup management with cleanup
- Easy recovery procedures
- Disaster recovery plan included

❌ **What NOT to Do:**
- Don't deploy with uncommitted changes
- Don't delete backups too soon
- Don't manually edit database during deployment
- Don't use same branch for multiple deployments

---

## 🔧 Configuration

Edit `.env` to customize:

```bash
# Production Server
PROD_SSH_USER=alsar4210
PROD_SSH_HOST=alsarya.tv
PROD_SSH_PORT=22
PROD_APP_DIR=/home/alsarya.tv/public_html
PROD_GIT_BRANCH=main

# SSH Key (optional)
SSH_KEY_PATH=~/.ssh/id_rsa

# Notifications (optional)
NOTIFY_DISCORD=false
DISCORD_WEBHOOK=https://discord.com/api/webhooks/...
```

---

## 📖 Full Documentation

- **DEPLOYMENT.md**: Complete deployment guide
- **DATA_BACKUP.md**: Backup, logging, and recovery guide
- **app/Console/Commands/BackupData.php**: Backup command
- **app/Console/Commands/RestoreData.php**: Restore command

---

## 🆘 Troubleshooting

### "SSH connection failed"
```bash
# Test SSH connection
ssh alsar4210@alsarya.tv "echo 'connected'"

# Check SSH key
ls -la ~/.ssh/id_rsa

# Verify server settings in .env
grep PROD_SSH ~/.env
```

### "Health check failed"
```bash
# Check production logs
ssh alsar4210@alsarya.tv "tail -50 /home/alsarya.tv/public_html/storage/logs/laravel.log"

# Check database
ssh alsar4210@alsarya.tv "cd /home/alsarya.tv/public_html && php artisan tinker"
# Then: DB::connection()->getPdo();
```

### "Database locked / Migration failed"
```bash
# Check pending migrations
./publish.sh --dry-run

# Backup and reset
php artisan backup:data
./publish.sh --reset-db
```

### "Need to recover from backup"
```bash
# List backups
ls -lh storage/backups/

# Restore
php artisan restore:data --file=storage/backups/callers_backup_*.csv
```

---

## 📱 Available Commands

### From Local Machine
```bash
./publish.sh              # Deploy
./publish.sh --fresh      # Deploy with fresh DB + seed
./publish.sh --reset-db   # Deploy with fresh DB (no seed)
./publish.sh --seed       # Add seeding
./publish.sh --no-build   # Skip frontend build
./publish.sh --force      # Force all steps
./publish.sh --dry-run    # Validate only
./publish.sh --help       # Show help
```

### From Production Server
```bash
php artisan backup:data                       # Manual backup
php artisan backup:data --type=callers        # Backup callers only
php artisan backup:data --type=hits           # Log hits only
php artisan restore:data                      # Show backups
php artisan restore:data --file=path/to.csv   # Restore from CSV
```

---

## ⏱️ Typical Deployment Times

| Scenario | Time | Notes |
|----------|------|-------|
| No changes detected | ~10 sec | Exits early |
| Backend only | ~30 sec | Runs migrations |
| Frontend only | ~2-3 min | npm build takes time |
| Full rebuild | ~5-10 min | Everything rebuilt |
| Fresh database | ~1-2 min | Includes migrations |

---

## 🎯 Workflow Examples

### Scenario A: Daily Code Update
```bash
git commit -m "fix: update feature"
./publish.sh
# ✓ Pushed, deployed, verified
```

### Scenario B: Maintenance with DB Reset
```bash
php artisan backup:data
./publish.sh --reset-db
# ✓ Data backed up, DB reset, schema ready
```

### Scenario C: Emergency Recovery
```bash
# Backup current (failed) state
php artisan backup:data

# Restore from yesterday
php artisan restore:data --file=storage/backups/callers_backup_yesterday.csv

# Verify
ssh alsar4210@alsarya.tv "php artisan tinker"
# DB::table('callers')->count();
```

### Scenario D: Testing New Migration
```bash
./publish.sh --dry-run
# Validates without running
# Check output for any issues
./publish.sh
# If --dry-run passed, safe to deploy
```

---

## 📞 Support

**Still need help?**

1. Read the detailed guides:
   - `DEPLOYMENT.md` - Full deployment documentation
   - `DATA_BACKUP.md` - Backup and recovery guide

2. Run with debug:
   ```bash
   export DEBUG=true
   ./publish.sh --dry-run
   ```

3. Check production logs:
   ```bash
   ssh alsar4210@alsarya.tv "tail -100 /home/alsarya.tv/public_html/storage/logs/laravel.log"
   ```

4. Test individual commands:
   ```bash
   php artisan backup:data -v
   php artisan restore:data
   ```

---

## 🎓 Key Takeaways

1. **Always backup first** - Automatic before deployments
2. **Use `--dry-run` when unsure** - Validates without changes
3. **Data is protected** - CSV exports, daily logs, easy recovery
4. **Deployments are fast** - Smart change detection
5. **Health checks included** - Verifies success after deploy

**Remember**: When in doubt, `./publish.sh --dry-run` is your friend! 🚀

---

*AlSarya TV Show Registration System - Production Deployment Tools*
