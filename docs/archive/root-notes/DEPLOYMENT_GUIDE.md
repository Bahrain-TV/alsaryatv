# Deployment Guide — AlSarya TV

## Overview

The deployment system consists of two complementary scripts:

1. **`deploy.sh`** - Smart deployment script that auto-detects environment
2. **`publish.sh`** - Full deployment with asset sync and backup management

## Quick Start

### From Local Machine (Recommended)

```bash
# Simple deploy (auto-detects local, SSH to production)
./deploy.sh

# Deploy with asset sync (images, lottie, sounds, fonts)
./deploy.sh --sync-assets

# Dry run (preview changes)
./deploy.sh --dry-run
```

### From Production Server

```bash
# Direct deploy (when already SSH'd into production)
./deploy.sh
```

## Script Comparison

| Feature | `deploy.sh` | `publish.sh` |
|---------|-------------|--------------|
| Auto-detect environment | ✅ | ❌ (always remote) |
| SSH from local to prod | ✅ | ✅ |
| Git pull on remote | ✅ | ✅ |
| Composer install | ✅ | ✅ |
| NPM build | ✅ | ✅ |
| Laravel caches | ✅ | ✅ |
| Database migrations | ✅ | ✅ |
| **Sync assets (local→prod)** | Via `publish.sh` | ✅ |
| **Sync backups (prod→local)** | ❌ | ✅ |
| **Sync media (prod→local)** | ❌ | ✅ |
| Force uncommitted files | ❌ | ✅ `--force` |
| Fresh database | ❌ | ✅ `--fresh` |
| Quick bring-up | ❌ | ✅ `--quick-up` |

## Deployment Flow

### Local Execution (`./deploy.sh` from Mac/Linux)

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. Detect running on LOCAL machine                              │
│ 2. SSH to production server (alsarya.tv)                        │
│ 3. Run remote deploy.sh on server                               │
│    - Git pull                                                   │
│    - Composer install                                           │
│    - NPM build                                                  │
│    - Laravel cache                                              │
│    - Migrations                                                 │
│ 4. (Optional) Run publish.sh --sync-assets-only                 │
│    - Sync public/images/ → production                           │
│    - Sync public/lottie/ → production                           │
│    - Sync public/sounds/ → production                           │
│    - Sync public/fonts/ → production                            │
└─────────────────────────────────────────────────────────────────┘
```

### Production Execution (`./deploy.sh` from SSH session)

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. Detect running on PRODUCTION server                          │
│ 2. Run deployment directly:                                     │
│    - Git pull origin main                                       │
│    - Composer install --no-dev                                  │
│    - NPM run build                                              │
│    - php artisan config:cache                                   │
│    - php artisan route:cache                                    │
│    - php artisan view:cache                                     │
│    - php artisan migrate --force                                │
│    - php artisan queue:restart                                  │
└─────────────────────────────────────────────────────────────────┘
```

## Configuration

### Environment Variables (.env or shell)

```bash
# SSH Connection
PROD_SSH_USER=root
PROD_SSH_HOST=alsarya.tv
PROD_SSH_PORT=22
PROD_APP_DIR=/home/alsarya.tv/public_html

# SSH Key (optional, uses default ~/.ssh/id_rsa if not set)
SSH_KEY=~/.ssh/id_rsa

# Deploy options
SYNC_ASSETS=true   # For deploy.sh
```

### publish.sh Configuration

```bash
# Additional options for publish.sh
PROD_GIT_BRANCH=main
LOCAL_BACKUP_DIR=storage/backups
```

## Usage Examples

### Basic Deploy

```bash
# From local machine
./deploy.sh

# With verbose output
VERBOSE=1 ./deploy.sh

# Dry run (no changes)
./deploy.sh --dry-run
```

### Deploy with Asset Sync

```bash
# Deploy code + sync images/assets
./deploy.sh --sync-assets

# Or use publish.sh for full asset management
./publish.sh --sync-assets-only
```

### Advanced publish.sh Usage

```bash
# Full deployment with all features
./publish.sh

# Bring site up quickly (skip build)
./publish.sh --quick-up

# Fresh database (DROPS ALL DATA)
./publish.sh --fresh

# Sync only assets (no deploy)
./publish.sh --sync-assets-only

# Skip backup sync
./publish.sh --no-backup-sync

# Dry run
./publish.sh --dry-run
```

## Asset Synchronization

### Assets Synced Local → Production

- `public/images/` → `/home/alsarya.tv/public_html/public/images/`
- `public/lottie/` → `/home/alsarya.tv/public_html/public/lottie/`
- `public/sounds/` → `/home/alsarya.tv/public_html/public/sounds/`
- `public/fonts/` → `/home/alsarya.tv/public_html/public/fonts/`
- `storage/app/public/` → `/home/alsarya.tv/public_html/storage/app/public/`

### Backups Synced Production → Local (publish.sh)

- Database dumps (`.sqlite`, `.sql`)
- CSV exports
- User uploads

## Troubleshooting

### SSH Connection Issues

```bash
# Test SSH connection
ssh -i ~/.ssh/id_rsa -p 22 root@alsarya.tv

# If connection fails, check:
# 1. SSH key permissions: chmod 600 ~/.ssh/id_rsa
# 2. SSH agent: ssh-add ~/.ssh/id_rsa
# 3. Firewall/network connectivity
```

### Deployment Failed

```bash
# Check remote logs
ssh -p 22 root@alsarya.tv 'tail -100 /home/alsarya.tv/public_html/storage/logs/deploy*.log'

# Check Laravel logs
ssh -p 22 root@alsarya.tv 'tail -100 /home/alsarya.tv/public_html/storage/logs/laravel.log'

# Manual recovery
ssh -p 22 root@alsarya.tv
cd /home/alsarya.tv/public_html
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```

### Asset Sync Failed

```bash
# Check rsync is installed
which rsync

# Install if missing (macOS)
brew install rsync

# Test rsync connection
rsync -avz -e "ssh -p 22 -i ~/.ssh/id_rsa" \
  public/images/ \
  root@alsarya.tv:/home/alsarya.tv/public_html/public/images/
```

## Maintenance Mode

### Enable Maintenance Mode

```bash
# Via SSH
ssh -p 22 root@alsarya.tv 'cd /home/alsarya.tv/public_html && php artisan down'

# Or via publish.sh
./publish.sh --up  # Brings site up after deploy
```

### Disable Maintenance Mode

```bash
# Via SSH
ssh -p 22 root@alsarya.tv 'cd /home/alsarya.tv/public_html && php artisan up'
```

## Post-Deployment Checklist

- [ ] Verify site is accessible: https://alsarya.tv
- [ ] Check deployment logs
- [ ] Verify database migrations ran
- [ ] Test key functionality (registration, dashboard)
- [ ] Clear browser cache if needed
- [ ] Monitor error logs for 15 minutes

## Emergency Rollback

```bash
# SSH to production
ssh -p 22 root@alsarya.tv

# Navigate to app directory
cd /home/alsarya.tv/public_html

# Rollback to previous commit
git reset --hard HEAD~1

# Re-deploy
./deploy.sh
```

## Support

For deployment issues:
1. Check logs: `storage/logs/deploy_*.log`
2. Review this guide
3. Contact: aldoyh.info@gmail.com
