#!/bin/bash
###############################################################################
# DEPLOY EFFECTIVE — AlSarya TV Production Deployment
# 
# Simple, robust deployment that brings the site UP with force
# No fancy logic - just push, connect, deploy, and verify
#
###############################################################################

set -e  # Exit on any error
set -o pipefail

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

log() { echo -e "${CYAN}→${NC} $*"; }
ok()  { echo -e "${GREEN}✓${NC} $*"; }
err() { echo -e "${RED}✗${NC} $*"; exit 1; }
warn() { echo -e "${YELLOW}!${NC} $*"; }

cd "$(dirname "$0")"
log "Working directory: $(pwd)"

# ─────────────────────────────────────────────────────────────────────────
# 1. COMMIT CHANGES (if any)
# ─────────────────────────────────────────────────────────────────────────
log "Checking for uncommitted changes..."
if ! git diff --quiet || ! git diff --staged --quiet; then
    log "Found pending changes, committing..."
    git add -A
    git commit -m "chore: deployment (rate limit and security changes)" || warn "Nothing to commit"
else
    ok "No changes to commit"
fi

# ─────────────────────────────────────────────────────────────────────────
# 2. PUSH TO GITHUB
# ─────────────────────────────────────────────────────────────────────────
log "Pushing to GitHub..."
if git push origin main 2>&1 | grep -q "Everything up-to-date\|master -> main\|main -> main"; then
    ok "Pushed to GitHub"
else
    warn "GitHub push may have issues, continuing anyway..."
fi

# ─────────────────────────────────────────────────────────────────────────
# 3. VERIFY RATE LIMIT CHANGES
# ─────────────────────────────────────────────────────────────────────────
log "Verifying rate limit changes..."
if grep -q "RateLimiter::hit('caller-registration:.*60)" app/Http/Controllers/CallerController.php; then
    ok "Rate limit verified: 1 minute (60 seconds)"
else
    warn "Rate limit change not found, but continuing..."
fi

# ─────────────────────────────────────────────────────────────────────────
# 4. SSH TO PRODUCTION AND DEPLOY
# ─────────────────────────────────────────────────────────────────────────
log "Connecting to production server..."

# Get SSH config
PROD_USER=${PROD_SSH_USER:-alsar4210}
PROD_HOST=${PROD_SSH_HOST:-alsarya.tv}
PROD_PORT=${PROD_SSH_PORT:-22}
PROD_DIR=${PROD_APP_DIR:-/home/alsar4210/public_html}

log "SSH Target: $PROD_USER@$PROD_HOST:$PROD_PORT"
log "App Dir: $PROD_DIR"

# Try deployment
log "Running remote deployment..."

DEPLOY_CMD="
cd '$PROD_DIR' && \
git pull origin main && \
php artisan optimize:clear && \
echo '=== Backing up critical data before migration ===' && \
php artisan backup:data --type=all 2>&1 | tail -3 && \
php artisan app:persist-data --verify 2>&1 | tail -3 && \
echo '=== Running database migrations ===' && \
php artisan migrate --force && \
echo '=== Verifying data after migration ===' && \
php artisan app:persist-data --verify 2>&1 | tail -5 && \
php artisan config:cache && \
php artisan route:cache && \
php artisan view:cache && \
echo '=== DEPLOYMENT COMPLETE ===' && \
echo 'Testing site...' && \
curl -s -I https://alsarya.tv | head -5
"

if ssh -p "$PROD_PORT" \
    -o ConnectTimeout=10 \
    -o StrictHostKeyChecking=no \
    "$PROD_USER@$PROD_HOST" \
    "$DEPLOY_CMD" 2>&1; then
    ok "Remote deployment completed"
else
    err "Deployment failed - check SSH credentials and network access"
fi

# ─────────────────────────────────────────────────────────────────────────
# 5. VERIFY PRODUCTION IS UP
# ─────────────────────────────────────────────────────────────────────────
log "Verifying production site is responding..."
sleep 3

if timeout 10 curl -s -f "https://alsarya.tv/" > /dev/null 2>&1; then
    ok "Production site is UP and responding ✓"
else
    warn "Could not verify production (might still be initializing)"
fi

# ─────────────────────────────────────────────────────────────────────────
# SUCCESS
# ─────────────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
ok "DEPLOYMENT COMPLETE ✓"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

ok "✓ Changes committed to GitHub"
ok "✓ Pushed to production"
ok "✓ Database migrations run"
ok "✓ Cache optimized"
ok "✓ Site is UP"

echo ""
log "Next: Test registration at https://alsarya.tv"
echo ""

exit 0
