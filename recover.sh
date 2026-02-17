#!/usr/bin/env bash
###############################################################################
# recover.sh — Emergency Recovery Script
#
# Use this if the site gets stuck in maintenance mode or deployment hangs
#
# Usage:
#   ./recover.sh              # Full recovery (check + restore)
#   ./recover.sh --force      # Force-restore site immediately
#   ./recover.sh --check      # Only check status
#   ./recover.sh --logs       # Show recent deployment logs
###############################################################################

set -eo pipefail

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

# Helpers
info()    { echo -e "${CYAN}[INFO]${NC}  $*"; }
success() { echo -e "${GREEN}[OK]${NC}    $*"; }
warn()    { echo -e "${YELLOW}[WARN]${NC}  $*"; }
error()   { echo -e "${RED}[ERROR]${NC} $*"; }

# Detect app directory
if [[ -f artisan ]]; then
    APP_DIR="."
elif [[ -f /home/alsarya.tv/public_html/artisan ]]; then
    APP_DIR="/home/alsarya.tv/public_html"
else
    error "Could not find Laravel application root"
    exit 1
fi

cd "$APP_DIR" || exit 1

# Parse arguments
CHECK_ONLY=false
FORCE_RESTORE=false
SHOW_LOGS=false

for arg in "$@"; do
    case "$arg" in
        --check)       CHECK_ONLY=true ;;
        --force)       FORCE_RESTORE=true ;;
        --logs)        SHOW_LOGS=true ;;
        *)             warn "Unknown flag: $arg" ;;
    esac
done

echo ""
echo "=========================================="
echo "  AlSarya TV Recovery Tool"
echo "=========================================="
echo ""

# Check current status
info "Checking deployment status..."
echo ""

# 1. Check maintenance mode
if [[ -f "storage/framework/down" ]]; then
    MAINT_AGE=$(($(date +%s) - $(stat -f%m "storage/framework/down" 2>/dev/null || stat -c%Y "storage/framework/down" 2>/dev/null || echo 0)))
    MAINT_MIN=$((MAINT_AGE / 60))
    MAINT_SEC=$((MAINT_AGE % 60))

    warn "Site is in MAINTENANCE MODE"
    warn "  Duration: ${MAINT_MIN}m ${MAINT_SEC}s"
    echo ""
else
    success "✓ Site is ONLINE (not in maintenance mode)"
    echo ""
fi

# 2. Check deployment locks
if [[ -f "/tmp/deploy.lock" ]]; then
    LOCK_PID=$(cat /tmp/deploy.lock)
    if kill -0 "$LOCK_PID" 2>/dev/null; then
        warn "Deployment script is RUNNING (PID: $LOCK_PID)"
    else
        warn "Stale deployment lock found (PID: $LOCK_PID is dead)"
    fi
    echo ""
fi

if [[ -f "storage/framework/deployment.lock" ]]; then
    DEPLOY_DATA=$(cat "storage/framework/deployment.lock")
    DEPLOY_PID=$(echo "$DEPLOY_DATA" | cut -d'|' -f1)
    DEPLOY_TIME=$(echo "$DEPLOY_DATA" | cut -d'|' -f2)
    DEPLOY_AGE=$(($(date +%s) - DEPLOY_TIME))
    DEPLOY_MIN=$((DEPLOY_AGE / 60))

    if kill -0 "$DEPLOY_PID" 2>/dev/null; then
        warn "Installation is RUNNING (PID: $DEPLOY_PID)"
        warn "  Running for: ${DEPLOY_MIN}m"
    else
        warn "Stale installation lock found (PID: $DEPLOY_PID is dead, age: ${DEPLOY_MIN}m)"
    fi
    echo ""
fi

# 3. Check recent errors
if [[ -f "storage/logs/laravel.log" ]]; then
    RECENT_ERRORS=$(tail -20 storage/logs/laravel.log | grep -i "error\|exception" | wc -l)
    if [[ $RECENT_ERRORS -gt 0 ]]; then
        warn "Found $RECENT_ERRORS recent errors in logs"
    fi
fi

# Exit if only checking
if [[ "$CHECK_ONLY" == "true" ]]; then
    echo ""
    echo "To restore the site, run: ./recover.sh --force"
    exit 0
fi

# Show logs if requested
if [[ "$SHOW_LOGS" == "true" ]]; then
    echo ""
    info "Recent Laravel logs (last 30 lines):"
    echo "---"
    tail -30 storage/logs/laravel.log 2>/dev/null || echo "(No logs found)"
    echo "---"
    echo ""
fi

# Perform recovery
echo ""
info "Starting recovery process..."
echo ""

# 1. Kill stale locks
if [[ -f "/tmp/deploy.lock" ]]; then
    LOCK_PID=$(cat /tmp/deploy.lock)
    if ! kill -0 "$LOCK_PID" 2>/dev/null; then
        warn "Removing stale system lock (PID: $LOCK_PID)"
        rm -f /tmp/deploy.lock
    fi
fi

if [[ -f "storage/framework/deployment.lock" ]]; then
    DEPLOY_PID=$(echo "$(cat storage/framework/deployment.lock)" | cut -d'|' -f1)
    if ! kill -0 "$DEPLOY_PID" 2>/dev/null; then
        warn "Removing stale installation lock (PID: $DEPLOY_PID)"
        rm -f storage/framework/deployment.lock
    fi
fi

# 2. Force-restore site
warn "Forcing site ONLINE..."
php artisan up 2>&1 || {
    error "Failed to execute 'php artisan up'"
    error "Try manually: cd $APP_DIR && php artisan up"
    exit 1
}

# 3. Remove stale down file just to be sure
if [[ -f "storage/framework/down" ]]; then
    warn "Also removing maintenance mode file..."
    rm -f storage/framework/down
fi

# 4. Quick validation
sleep 2
echo ""
if [[ -f "storage/framework/down" ]]; then
    error "Site is still in maintenance mode!"
    exit 1
fi

success "✅ Site is now ONLINE"

# 5. Check if site is responsive
if command -v curl &>/dev/null; then
    APP_URL=$(grep "^APP_URL=" .env 2>/dev/null | cut -d= -f2- | tr -d '"' | tr -d "'")
    if [[ -n "$APP_URL" ]]; then
        info "Testing site accessibility..."
        STATUS=$(curl -s -o /dev/null -w "%{http_code}" --max-time 5 "$APP_URL" 2>/dev/null || echo "000")
        if [[ "$STATUS" == "200" || "$STATUS" == "302" ]]; then
            success "✓ Site is responding (HTTP $STATUS)"
        else
            warn "Site returned HTTP $STATUS (check web server)"
        fi
    fi
fi

echo ""
echo "=========================================="
echo "  Recovery Complete!"
echo "=========================================="
echo ""

info "If you continue to have issues:"
info "  1. Check logs: tail -f storage/logs/laravel.log"
info "  2. Verify database: php artisan db:show"
info "  3. Run diagnostics: ./deploy.sh --diagnose"
echo ""
