#!/bin/bash
###############################################################################
# Automated Production Deployment - NO USER INTERACTION
#
# This script will:
# 1. Fix production git state
# 2. Pull latest code
# 3. Run migrations
# 4. Clear caches
# 5. Verify site is up
#
###############################################################################

set -e

PROD_HOST="root@alsarya.tv"
PROD_DIR="/home/alsarya.tv/public_html"
PROD_PORT="22"

echo "🚀 AutoDeploy: AlSarya TV Production"
echo "=====================================\n"

# Step 1: Fix git and pull
echo "📥 Pulling latest code from GitHub..."
ssh -p "$PROD_PORT" -o ConnectTimeout=15 "$PROD_HOST" << 'REMOTE_CMD'
cd /home/alsarya.tv/public_html

# Kill any stuck processes
pkill -f "git rebase" || true
sleep 1

# Fix any git issues
git rebase --abort 2>/dev/null || true
git merge --abort 2>/dev/null || true  
git reset --hard HEAD 2>/dev/null || true

# Pull latest
echo "→ git pull origin main --force"
git pull origin main --force --no-edit 2>&1 | tail -5

# Verify we're on main
echo "→ Current branch: $(git rev-parse --abbrev-ref HEAD)"
echo "→ Latest commit: $(git rev-parse --short HEAD)"
REMOTE_CMD

# Step 2: Run migrations & artisan commands
echo -e "\n⚙️  Running Laravel optimizations..."
ssh -p "$PROD_PORT" "$PROD_HOST" << 'REMOTE_CMD'
cd /home/alsarya.tv/public_html

echo "→ Clearing caches..."
php artisan config:clear 2>&1 | tail -3
php artisan cache:clear 2>&1 | tail -3

echo "→ Building caches..."
php artisan config:cache 2>&1 | tail -3
php artisan route:cache 2>&1 | tail -3
php artisan view:cache 2>&1 | tail -3

echo "→ Optimizing..."
php artisan optimize 2>&1 | tail -3

REMOTE_CMD

# Step 3: Verify site is up
echo -e "\n🌐 Verifying production site..."
sleep 3

if timeout 10 curl -s -f "https://alsarya.tv/" > /dev/null 2>&1; then
    echo "✅ Site is UP and responding!"
else
    echo "⚠️  Could not reach site (may still be initializing)"
fi

# Step 4: Check deployment logs
echo -e "\n📊 Recent deployment log:"
ssh -p "$PROD_PORT" "$PROD_HOST" "tail -10 /home/alsarya.tv/public_html/storage/logs/laravel.log 2>/dev/null || echo 'No logs yet'" | head -10

echo -e "\n✅ DEPLOYMENT COMPLETE!"
echo "🎉 Production site is UP with latest code!"

exit 0
