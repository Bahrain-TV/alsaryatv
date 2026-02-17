# Deployment Guide — AlSarya TV

This document explains how to deploy the AlSarya TV application to production from your local machine.

## Quick Start

```bash
# Deploy to production
./publish.sh

# Fresh database deployment (drops all data and re-creates with seeding)
./publish.sh --fresh

# Reset database (drops all data, re-creates, but NO seeding)
./publish.sh --reset-db

# Add seeding after migration
./publish.sh --seed

# Dry-run (validate without executing)
./publish.sh --dry-run

# Show help
./publish.sh --help
```

---

## Architecture Overview

The deployment system consists of **two scripts**:

### 1. `publish.sh` (Local Machine)
- Runs on **your local machine**
- Validates git state (no uncommitted changes, on correct branch)
- Pushes code to GitHub (or your remote)
- Connects to production via SSH
- Triggers the remote deployment script

### 2. `deploy.sh` (Production Server)
- Runs on **the production server**
- Executes all deployment logic
- Handles migrations, asset builds, permissions, caching
- Provides rollback on failure
- Sends notifications (Discord/ntfy)

---

## Prerequisites

### Local Machine
- ✓ Git installed and configured
- ✓ SSH key for production server (`~/.ssh/id_rsa` or configured path)
- ✓ SSH access to production server already set up

### Production Server
- ✓ `deploy.sh` exists in app directory
- ✓ `.env` file configured properly
- ✓ `composer` and `php` installed
- ✓ `npm` or `pnpm` available (for frontend builds)

---

## Configuration

### Set Production Server Details

Edit `.env` and ensure these are set:

```bash
# SSH Connection
PROD_SSH_USER=alsar4210
PROD_SSH_HOST=alsarya.tv
PROD_SSH_PORT=22
PROD_APP_DIR=/home/alsarya.tv/public_html

# Git Branch to deploy
PROD_GIT_BRANCH=main

# Notifications
NOTIFY_DISCORD=false
DISCORD_WEBHOOK="your-webhook-url"
```

### SSH Key Configuration

By default, `publish.sh` looks for SSH key at `~/.ssh/id_rsa`.

To use a different key:
```bash
export SSH_KEY_PATH=/path/to/your/key
./publish.sh
```

Or add to `.env`:
```bash
SSH_KEY_PATH=~/.ssh/custom_key
```

---

## Usage Examples

### Standard Deployment
```bash
./publish.sh
```
- Validates git state
- Pushes code to GitHub
- Runs remote deployment
- Performs health checks

**Output:**
```
[INFO]   AlSarya TV - Production Deployment
[OK]     Required commands available
[OK]     Git repository detected
[OK]     SSH connection successful
[OK]     On correct branch: main
[OK]     No uncommitted changes

Deployment Configuration:
  Server: alsar4210@alsarya.tv:22
  App Dir: /home/alsarya.tv/public_html
  Branch: main
  Commit: a1b2c3d4e5

Continue with deployment? (y/n) y

[INFO]   Pushing code to remote repository...
[OK]     Code pushed to remote (main)
[INFO]   Triggering deployment on production server...
[INFO]   Remote deployment completed successfully
[INFO]   Checking deployment health...
[OK]     ✓ Application health check passed (HTTP 200)

========================================
  ✓ Publication successful!
========================================

[OK]     Deployed: a1b2c3d4e5 (fix: Update feature X)
[OK]     To: alsar4210@alsarya.tv:/home/alsarya.tv/public_html
[OK]     Branch: main
```

### Fresh Database Deployment
```bash
./publish.sh --fresh
```
- Drops all database tables
- Re-creates schema from migrations
- Seeds database with initial data
- Useful for major updates or testing

⚠️ **WARNING**: This **permanently deletes** all caller data!

### Reset Database (Without Seeding)
```bash
./publish.sh --reset-db
```
- Drops all database tables
- Re-creates schema from migrations
- **Does NOT** seed the database
- Useful when you want a clean slate without auto-seeding
- Caller data is backed up first

⚠️ **WARNING**: This **permanently deletes** all caller data!

### With Seeding
```bash
./publish.sh --seed
```
- Runs normal migration
- Executes seeders afterward
- Does not drop tables

### Skip Frontend Build
```bash
./publish.sh --no-build
```
- Skips npm build step
- Useful if only backend code changed
- Faster deployment

### Force All Steps
```bash
./publish.sh --force
```
- Ignores change detection
- Reinstalls dependencies
- Rebuilds all assets
- Useful if something is broken

### Database Operation Comparison

| Flag | Action | Seeding | Use Case | Data Loss |
|------|--------|---------|----------|-----------|
| (none) | `php artisan migrate` | No | Normal deployment | No |
| `--fresh` | `php artisan migrate:fresh --seed` | Yes | Complete reset with data | Yes |
| `--reset-db` | `php artisan migrate:fresh` | No | Clean slate, manual setup | Yes |
| `--seed` | `php artisan db:seed` | Yes | Add seeders to existing DB | No |

### Dry-Run (Validate Only)
```bash
./publish.sh --dry-run
```
- Validates all prerequisites
- Shows what would happen
- **Does not make any changes**
- Perfect for testing configuration

---

## What Happens During Deployment

### 1. Local Validation (`publish.sh`)
```
✓ Check git repository
✓ Validate current branch matches PROD_GIT_BRANCH
✓ Ensure no uncommitted changes
✓ Confirm SSH connectivity
✓ Test SSH authentication
```

### 2. Code Push
```
✓ Push current branch to remote (GitHub)
```

### 3. Remote Deployment (`deploy.sh`)
```
✓ Enable maintenance mode (site returns 503)
✓ Pull latest code from remote
✓ Detect what changed (git diff)
✓ Install composer dependencies (if needed)
✓ Install npm dependencies (if needed)
✓ Build frontend assets (if needed)
✓ Run database migrations (if needed)
✓ Sync version numbers
✓ Cache configuration, routes, views
✓ Clear old caches
✓ Create storage symlink
✓ Restart queue workers
✓ Disable maintenance mode (site is live)
✓ Run health checks
```

---

## Troubleshooting

### SSH Connection Errors

```
[ERROR] Failed to connect via SSH to alsar4210@alsarya.tv:22
```

**Solutions:**
1. Verify server is reachable:
   ```bash
   ping alsarya.tv
   ssh alsar4210@alsarya.tv "echo 'connected'"
   ```

2. Check SSH key permissions:
   ```bash
   chmod 600 ~/.ssh/id_rsa
   chmod 700 ~/.ssh
   ```

3. Verify `PROD_SSH_HOST` in `.env` is correct

4. Check firewall allows SSH on port 22

### Git Push Fails

```
[ERROR] Failed to push code to remote
```

**Solutions:**
1. Ensure you have push access to the repository
2. Verify GitHub credentials are configured
3. Check for uncommitted changes:
   ```bash
   git status
   ```
4. Ensure on correct branch:
   ```bash
   git checkout main
   ```

### Remote Deployment Fails

**Check logs on production server:**
```bash
ssh alsar4210@alsarya.tv
tail -f /home/alsarya.tv/public_html/storage/logs/laravel.log
```

**Common issues:**
- Database migrations failed
- Insufficient disk space
- Permission errors
- Missing PHP extensions

### Health Check Fails

```
[WARN] ✗ Application returned HTTP 500 (expected 200/302)
```

**Solutions:**
1. SSH to server and check logs
2. Verify database migrations completed
3. Check Laravel error logs
4. Ensure `.env` configuration is correct
5. Run manual health check:
   ```bash
   php artisan tinker
   DB::connection()->getPdo();
   ```

---

## Monitoring & Logs

### View Deployment History
```bash
ssh alsar4210@alsarya.tv
tail -f /home/alsarya.tv/public_html/storage/logs/laravel.log
```

### View Migration Status
```bash
ssh alsar4210@alsarya.tv "cd /home/alsarya.tv/public_html && php artisan migrate:status"
```

### Check Deployment Lock Files
```bash
ssh alsar4210@alsarya.tv "ls -la /home/alsarya.tv/public_html/storage/framework/deployment.lock"
```

If locked, and deployment is not running:
```bash
ssh alsar4210@alsarya.tv "rm /home/alsarya.tv/public_html/storage/framework/deployment.lock"
```

---

## Rollback

If deployment fails and breaks the site:

### Automatic Rollback
`deploy.sh` automatically restores the site if something goes wrong:
```
[ERROR] Deploy failed! Restoring site to LIVE status...
[OK]    Site restored to live.
```

### Manual Rollback
```bash
ssh alsar4210@alsarya.tv "cd /home/alsarya.tv/public_html && php artisan up"
```

### Git Rollback
```bash
# On production server:
git reset --hard HEAD~1
```

---

## Performance Optimization

### Change Detection
The deployment script is smart:
- Only rebuilds frontend if JavaScript/CSS changed
- Only installs dependencies if `composer.lock` or `package.json` changed
- Only runs migrations if migration files changed

**Force full rebuild:**
```bash
./publish.sh --force
```

### Typical Deployment Times
- **No changes**: ~10 seconds (exit early)
- **Backend only**: ~30 seconds
- **Frontend only**: ~2-3 minutes
- **Full rebuild**: ~5-10 minutes
- **Fresh database**: ~1-2 minutes

---

## Notifications

### Discord Notifications
Configure in `.env`:
```bash
NOTIFY_DISCORD=true
DISCORD_WEBHOOK=https://discord.com/api/webhooks/YOUR_WEBHOOK_URL
```

Deployment bot will post:
- Deployment started
- Deployment succeeded/failed
- Links to commit

### ntfy Notifications
Configure in `.env`:
```bash
NTFY_URL=https://ntfy.sh/your-topic
```

Get notifications on your phone via:
- ntfy.sh app
- Browser notifications
- Android push notifications

---

## Best Practices

✅ **Do:**
- Commit all changes before deploying
- Review changes before pushing
- Use `--dry-run` to validate first
- Test locally before deploying
- Keep `.env` in sync
- Monitor logs after deployment

❌ **Don't:**
- Deploy with uncommitted changes
- Deploy from different branch without verification
- Use `--fresh` on accident (data loss!)
- Deploy during peak usage hours
- Change database manually after deployment

---

## Security Considerations

### SSH Key Security
- Keep SSH key secure: `chmod 600 ~/.ssh/id_rsa`
- Use SSH agent to avoid exposing key
- Rotate keys periodically

### Environment Variables
- Never commit `.env` file to Git
- Use `.env.example` for documentation
- Sensitive values (passwords, tokens) only in `.env`

### Production Safety
- Maintenance mode prevents access during deployment
- Rollback happens automatically on failure
- Health checks verify functionality after deploy

---

## Advanced Configuration

### Environment Variables
```bash
# Override SSH key location
export SSH_KEY_PATH=~/.ssh/production_key

# Debug mode (verbose output)
export DEBUG=true

# Override deployment branch
export PROD_GIT_BRANCH=production

./publish.sh
```

### Custom Deployment Logic
Edit `deploy.sh` to add custom steps:
```bash
# ── Step X: Custom Operation ─────────────────────────────────
info "Running custom operation..."
run php artisan custom:command
success "Custom operation completed."
```

---

## Getting Help

### Check Prerequisites
```bash
./publish.sh --dry-run
```

### View Script Help
```bash
./publish.sh --help
```

### SSH Test
```bash
ssh -v alsar4210@alsarya.tv "echo 'connection successful'"
```

### View Remote Deploy Status
```bash
ssh alsar4210@alsarya.tv "tail -50 /home/alsarya.tv/public_html/storage/logs/laravel.log"
```

---

## Summary

### Common Commands

| Task | Command | Effect |
|------|---------|--------|
| Deploy | `./publish.sh` | Normal deployment, migrate only |
| Fresh DB | `./publish.sh --fresh` | Drop all, recreate, seed |
| Reset DB | `./publish.sh --reset-db` | Drop all, recreate, NO seed |
| With seeding | `./publish.sh --seed` | Run seeders after migration |
| Validate only | `./publish.sh --dry-run` | Check but don't execute |
| Force rebuild | `./publish.sh --force` | Rebuild everything |
| Help | `./publish.sh --help` | Show this help message |

### Database Reset Options

When you need to reset the production database:

- **`--fresh`**: Use when you want a complete reset WITH initial data (seeders)
  ```bash
  ./publish.sh --fresh
  # Drops all tables, runs migrations, seeds initial data
  ```

- **`--reset-db`**: Use when you want just the schema (structure only)
  ```bash
  ./publish.sh --reset-db
  # Drops all tables, runs migrations, no seeding
  # Useful for clean slate before manual data import
  ```

Both options:
- ✓ Automatically back up data first
- ✓ Export callers to CSV before reset
- ✓ Log the operation
- ⚠️ **Permanently delete** all caller data

The deployment system is designed to be **safe, fast, and reliable**. When in doubt, use `--dry-run` to validate before executing.
