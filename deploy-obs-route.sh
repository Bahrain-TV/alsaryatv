#!/bin/bash
###############################################################################
# DEPLOY OBS OVERLAY ROUTE — AlSarya TV Production
#
# Deploys the /obs-overlay route to production server (alsarya.tv) and verifies it works.
# This is required for the automated OBS recording to work
#
###############################################################################

set -e
set -o pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

log() { echo -e "${CYAN}→${NC} $*"; }
ok()  { echo -e "${GREEN}✓${NC} $*"; }
err() { echo -e "${RED}✗${NC} $*"; exit 1; }

cd "$(dirname "$0")"

# SSH Configuration
PROD_USER=${PROD_SSH_USER:-root}
PROD_HOST=${PROD_SSH_HOST:-alsarya.tv}
PROD_PORT=${PROD_SSH_PORT:-22}
PROD_DIR=${PROD_APP_DIR:-/home/alsarya.tv/public_html}

echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}   📺 Deploy OBS Overlay Route to Production${NC}"
echo -e "${GREEN}   Target: ${PROD_USER}@${PROD_HOST}:${PROD_PORT}${NC}"
echo -e "${GREEN}   Dir:    ${PROD_DIR}${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

# Step 1: Push to GitHub
log "Pushing latest changes to GitHub..."
git add -A
git commit -m "chore: deploy obs-overlay route" || true
git push origin main || {
    err "Failed to push to GitHub"
}
ok "Pushed to GitHub"

# Step 2: Deploy to production
log "Deploying to production server..."

DEPLOY_CMD="
cd '$PROD_DIR' && \
echo '=== Pulling latest code ===' && \
git pull origin main && \
echo '' && \
echo '=== Clearing caches ===' && \
php artisan optimize:clear && \
php artisan config:clear && \
php artisan route:clear && \
php artisan view:clear && \
echo '' && \
echo '=== Rebuilding caches ===' && \
php artisan config:cache && \
php artisan route:cache && \
php artisan view:cache && \
echo '' && \
echo '=== Verifying /obs-overlay route ===' && \
php artisan route:list --path=obs-overlay && \
echo '' && \
echo '=== Testing URL ===' && \
curl -s -o /dev/null -w 'HTTP Status: %{http_code}\n' https://alsarya.tv/obs-overlay && \
echo '' && \
echo '=== DEPLOYMENT COMPLETE ==='
"

if ssh -p "$PROD_PORT" \
    -o ConnectTimeout=15 \
    -o StrictHostKeyChecking=no \
    "$PROD_USER@$PROD_HOST" \
    "$DEPLOY_CMD"; then
    ok "Deployment completed successfully"
else
    err "Deployment failed - check SSH connection and server status"
fi

# Step 3: Verify
echo ""
log "Verifying deployment..."
sleep 2

HTTP_CODE=$(curl -s -o /dev/null -w '%{http_code}' https://alsarya.tv/obs-overlay 2>/dev/null || echo "000")

if [ "$HTTP_CODE" = "200" ]; then
    ok "✅ Production URL is working (HTTP $HTTP_CODE)"
elif [ "$HTTP_CODE" = "404" ]; then
    err "❌ Route still returns 404 - check if deployment completed"
elif [ "$HTTP_CODE" = "000" ]; then
    err "❌ Could not reach alsarya.tv - check DNS/server"
else
    warn "⚠️  URL returned HTTP $HTTP_CODE (may still work)"
fi

echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
ok "Deployment Complete!"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
log "Test the URL: https://alsarya.tv/obs-overlay"
log "Then run: php artisan obs:record --environment=production"
echo ""

exit 0
