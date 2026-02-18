# Deploy.sh Quick Reference Card

## 🚀 Common Commands

```bash
# Standard deployment (SAFE - recommended)
./deploy.sh

# Test run (shows what would happen)
./deploy.sh --dry-run

# Deploy without building frontend (faster)
./deploy.sh --no-build

# Deploy with image optimization (slower, creates backups)
./deploy.sh --optimize-images

# Force deploy if maintenance mode stuck
./deploy.sh --up

# Check production health
./deploy.sh --diagnose
```

## ⚠️ Dangerous Commands (Use Carefully)

```bash
# DROPS all database tables and reseeds!
./deploy.sh --fresh

# Resets database structure (no seeding)
./deploy.sh --reset-db
```

## 🆘 Emergency Commands

```bash
# Site stuck in maintenance mode?
php artisan up

# Config broken?
php artisan config:clear

# Clear all caches
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Restore database from backup
mysql -u username -p database < storage/backups/backup_*.sql

# Rollback code
git reset --hard HEAD~1
composer install --no-dev
php artisan config:clear
php artisan up
```

## ✅ Safety Checks (Automatic)

**Before Deployment:**
- ✓ Critical files exist
- ✓ Storage directory writable
- ✓ Git changes warned
- ✓ PHP version checked
- ✓ Disk space validated (>1GB)
- ✓ Log errors reviewed

**During Deployment:**
- ✓ Database backup created & verified
- ✓ Config validated before caching
- ✓ Images backed up before optimization
- ✓ Temp files used for safe operations

**After Deployment:**
- ✓ Homepage accessible
- ✓ Registration form works
- ✓ No new errors in logs
- ✓ Cache system operational
- ✓ Storage symlink exists

## 📊 File Locations

| File | Purpose |
|------|---------|
| `storage/backups/backup_*.sql` | Database backups |
| `storage/backups/images_*/` | Image optimization backups |
| `storage/framework/image_manifest.json` | Image checksums |
| `storage/logs/laravel.log` | Error logs |
| `storage/framework/last_successful_deploy` | Deploy record |

## 🎯 Deployment Flow

```
1. Pre-deployment checks
   ├─ Files exist
   ├─ Permissions OK
   ├─ Disk space OK
   └─ Log review

2. Database backup
   ├─ Connection test
   ├─ Create backup
   └─ Verify backup size

3. Code sync
   ├─ Git fetch
   ├─ Git reset --hard
   └─ Change detection

4. Dependencies
   ├─ Composer install
   └─ NPM build (optional)

5. Database migrations
   ├─ Run migrations
   └─ Verify no pending

6. Asset optimization
   ├─ Image manifest
   └─ Image optimization (optional)

7. Caching
   ├─ Config validation
   ├─ Config cache
   ├─ Route cache
   └─ View cache

8. Go live
   ├─ Disable maintenance
   └─ Health checks

9. Post-verification
   ├─ Homepage test
   ├─ Form test
   ├─ Log review
   └─ Cache check
```

## 🔧 Troubleshooting

### Deployment Failed

1. **Read error message** (red text)
2. **Check logs:** `tail -100 storage/logs/laravel.log`
3. **Run:** `php artisan up` (if stuck in maintenance)
4. **Fix issue** based on error
5. **Retry:** `./deploy.sh`

### Site Broken After Deploy

1. **Clear caches:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   php artisan up
   ```

2. **Check logs:**
   ```bash
   tail -50 storage/logs/laravel.log
   ```

3. **Restore database** (if DB issue):
   ```bash
   ls -lt storage/backups/*.sql
   mysql -u user -p db < backup_YYYYMMDD_HHMMSS.sql
   ```

4. **Rollback code** (if code issue):
   ```bash
   git reset --hard HEAD~1
   composer install --no-dev
   php artisan config:clear
   php artisan up
   ```

### Images Broken

```bash
# Restore from optimization backup
ls -lt storage/backups/images_*
cp -r storage/backups/images_YYYYMMDD_HHMMSS/* public/images/
chown -R alsar4210:alsar4210 public/images
```

## 📞 Need Help?

**Check these first:**
1. Error message in terminal
2. `storage/logs/laravel.log`
3. `./deploy.sh --diagnose`

**Common fixes:**
- Maintenance mode stuck: `php artisan up`
- Config broken: `php artisan config:clear`
- Permissions wrong: `chown -R alsar4210:alsar4210 storage bootstrap/cache`
- Symlink broken: `rm public/storage && php artisan storage:link`

## 🎓 Best Practices

**DO:**
- ✓ Test on staging first
- ✓ Review git changes before deploying
- ✓ Deploy during low-traffic hours
- ✓ Monitor for 15 minutes after deploy
- ✓ Keep this reference handy

**DON'T:**
- ✗ Deploy without reviewing changes
- ✗ Deploy during peak hours if avoidable
- ✗ Use --fresh unless you mean it
- ✗ Ignore error messages
- ✗ Deploy and immediately leave

---

**Quick Win:** If something goes wrong, in this order:
1. `php artisan up`
2. `php artisan config:clear`
3. Check logs
4. Restore backup if needed

**Remember:** The script has safety checks, but always stay alert during deployment!
