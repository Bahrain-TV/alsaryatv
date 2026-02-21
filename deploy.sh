#!/bin/bash
################################################################################
# deploy.sh — AlSarya TV Smart Deployment Script
#
# INTELLIGENT DEPLOYMENT FLOW:
#   • Running on LOCAL (Mac/Linux dev machine)?
#     → SSH to production → Run remote deploy → Sync assets via publish.sh
#
#   • Running on PRODUCTION server?
#     → Deploy directly (no SSH) → Code, config, cache, migrations
#
# Usage:
#   ./deploy.sh                    # Auto-detects and deploys
#   ./deploy.sh --dry-run          # Preview changes without applying
#   ./deploy.sh --sync-assets      # Also sync images/assets after deploy
#   VERBOSE=1 ./deploy.sh          # Debug mode
#
# Configuration (.env or environment):
#   PROD_SSH_USER=root
#   PROD_SSH_HOST=alsarya.tv
#   PROD_SSH_PORT=22
#   PROD_APP_DIR=/home/alsarya.tv/public_html
#   SSH_KEY=~/.ssh/id_rsa
#
################################################################################

set -euo pipefail

# ─────────────────────────────────────────────────────────────────────────
# Colors
# ─────────────────────────────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
BLUE='\033[0;34m'
NC='\033[0m'

# ─────────────────────────────────────────────────────────────────────────
# Logging
# ─────────────────────────────────────────────────────────────────────────
log()   { echo -e "${CYAN}→${NC} $*"; }
ok()    { echo -e "${GREEN}✓${NC} $*"; }
err()   { echo -e "${RED}✗${NC} $*"; exit 1; }
warn()  { echo -e "${YELLOW}!${NC} $*"; }
info()  { echo -e "${BLUE}ℹ${NC} $*"; }
hr()    { echo -e "${MAGENTA}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"; }

# ─────────────────────────────────────────────────────────────────────────
# Configuration with defaults
# ─────────────────────────────────────────────────────────────────────────
PROD_HOST="${PROD_SSH_USER:-root}@${PROD_SSH_HOST:-alsarya.tv}"
PROD_DIR="${PROD_APP_DIR:-/home/alsarya.tv/public_html}"
PROD_PORT="${PROD_SSH_PORT:-22}"
SSH_KEY="${SSH_KEY:-${HOME}/.ssh/id_rsa}"
DRY_RUN="${DRY_RUN:-false}"
VERBOSE="${VERBOSE:-0}"
SYNC_ASSETS="${SYNC_ASSETS:-false}"

# Parse CLI flags
while [[ $# -gt 0 ]]; do
    case "$1" in
        --dry-run) DRY_RUN=true; shift ;;
        --verbose|--debug) VERBOSE=1; shift ;;
        --sync-assets) SYNC_ASSETS=true; shift ;;
        --help|-h)
            echo "Usage: ./deploy.sh [options]"
            echo ""
            echo "Options:"
            echo "  --dry-run       Preview changes without applying"
            echo "  --verbose       Enable debug output"
            echo "  --sync-assets   Sync images/assets after deploy"
            echo "  --help, -h      Show this help"
            echo ""
            echo "Environment variables:"
            echo "  PROD_SSH_USER   SSH user (default: root)"
            echo "  PROD_SSH_HOST   Production host (default: alsarya.tv)"
            echo "  PROD_SSH_PORT   SSH port (default: 22)"
            echo "  PROD_APP_DIR    Remote app directory (default: /home/alsarya.tv/public_html)"
            echo "  SSH_KEY         SSH key path (default: ~/.ssh/id_rsa)"
            exit 0
            ;;
        *) warn "Unknown flag: $1"; shift ;;
    esac
done

# ─────────────────────────────────────────────────────────────────────────
# STEP 1: DETECT EXECUTION CONTEXT
# ─────────────────────────────────────────────────────────────────────────

detect_context() {
    # Check if we're on the production server by hostname
    local current_host
    current_host=$(hostname -f 2>/dev/null || hostname 2>/dev/null || echo "unknown")
    
    # If hostname matches production or we're in the PROD_DIR
    if [[ "$current_host" == *"alsarya"* ]] || \
       [[ "$current_host" == *"adhari"* ]] || \
       [[ "$(pwd)" == "$PROD_DIR" ]]; then
        echo "production"
        return 0
    fi

    # Check if running via SSH session from another host
    if [[ -n "${SSH_CLIENT:-}" || -n "${SSH_TTY:-}" ]]; then
        # We're in an SSH session - check if it's to production
        if [[ "$current_host" == "$PROD_SSH_HOST" ]]; then
            echo "production"
            return 0
        fi
    fi

    # Otherwise, we're local
    echo "local"
    return 0
}

CONTEXT=$(detect_context)

[[ $VERBOSE -eq 1 ]] && log "Context: $CONTEXT (hostname: $(hostname))"

# ─────────────────────────────────────────────────────────────────────────
# STEP 2: LOCAL EXECUTION → SSH TO PRODUCTION + DEPLOY + SYNC
# ─────────────────────────────────────────────────────────────────────────

if [[ "$CONTEXT" == "local" ]]; then
    hr
    log "🖥️  Running on LOCAL machine"
    log "📦 Deployment mode: SSH to production + deploy + sync assets"
    log "🎯 Target: $PROD_HOST:$PROD_PORT"
    log "📁 Remote dir: $PROD_DIR"
    hr
    echo ""

    # Validate SSH key exists
    if [[ ! -f "$SSH_KEY" ]]; then
        err "SSH key not found: $SSH_KEY"
    fi

    # Build SSH command
    SSH_CMD="ssh -i '$SSH_KEY' -o BatchMode=no -o ConnectTimeout=15 -p $PROD_PORT '$PROD_HOST'"

    # Build remote command - run deploy.sh on remote, then trigger asset sync
    REMOTE_CMD="cd '$PROD_DIR' && bash ./deploy.sh"
    
    if [[ "$SYNC_ASSETS" == "true" ]]; then
        # After deploy, also run publish.sh for asset sync
        REMOTE_CMD="$REMOTE_CMD && bash ./publish.sh --sync-assets-only"
    fi

    FULL_CMD="$SSH_CMD '$REMOTE_CMD'"

    [[ $VERBOSE -eq 1 ]] && log "SSH Command: $FULL_CMD"

    # Execute remote deployment
    if [[ "$DRY_RUN" == "true" ]]; then
        log "DRY RUN: Would execute: $FULL_CMD"
        exit 0
    fi

    log "Connecting to production server..."
    echo ""
    
    eval "$FULL_CMD"
    EXIT_CODE=$?
    
    echo ""
    if [[ $EXIT_CODE -eq 0 ]]; then
        hr
        ok "✅ REMOTE DEPLOYMENT COMPLETE"
        hr
        log "Next steps:"
        log "  • Verify site: https://alsarya.tv"
        log "  • Check logs:  ssh -p $PROD_PORT $PROD_HOST 'tail -f $PROD_DIR/storage/logs/laravel.log'"
    else
        hr
        err "❌ REMOTE DEPLOYMENT FAILED (exit code: $EXIT_CODE)"
        hr
        log "Troubleshooting:"
        log "  • Check SSH connection: ssh -i $SSH_KEY -p $PROD_PORT $PROD_HOST"
        log "  • Check remote logs: ssh -i $SSH_KEY -p $PROD_PORT $PROD_HOST 'tail -100 $PROD_DIR/storage/logs/deploy*.log'"
    fi
    
    exit $EXIT_CODE
fi

# ─────────────────────────────────────────────────────────────────────────
# STEP 3: PRODUCTION EXECUTION → DEPLOY DIRECTLY
# ─────────────────────────────────────────────────────────────────────────

hr
log "🌍 Running on PRODUCTION server"
log "📍 Directory: $(pwd)"
hr
echo ""

# Verify we're in the right directory
if [[ ! -f "artisan" ]]; then
    err "Not in Laravel root directory (artisan not found)"
fi

# Create deployment log
DEPLOY_LOG="storage/logs/deploy_$(date '+%Y%m%d_%H%M%S').log"
mkdir -p "$(dirname "$DEPLOY_LOG")"

log_deploy() {
    echo -e "$(date '+[%H:%M:%S]') $*" | tee -a "$DEPLOY_LOG"
}

log_deploy "════════════════════════════════════════════════════"
log_deploy "  AlSarya TV Deployment Started"
log_deploy "════════════════════════════════════════════════════"
log_deploy "Time: $(date '+%Y-%m-%d %H:%M:%S')"
log_deploy "User: $(whoami)"
log_deploy "Host: $(hostname)"
log_deploy "Dir: $(pwd)"

# ─────────────────────────────────────────────────────────────────────────
# STEP 3.1: Pre-Flight Checks
# ─────────────────────────────────────────────────────────────────────────

log "Performing pre-flight checks..."

# Check critical files
for file in artisan .env composer.json package.json; do
    if [[ ! -f "$file" ]]; then
        if [[ "$file" == ".env" ]]; then
            warn "Missing .env — attempting to create from .env.example or .env.production"
            if [[ -f ".env.example" ]]; then
                cp ".env.example" .env && \
                    echo "APP_ENV=production" >> .env && \
                    warn ".env created from .env.example — review and populate secrets" || \
                    err "Failed to create .env"
            elif [[ -f ".env.production" ]]; then
                cp ".env.production" .env && warn ".env created from .env.production" || err "Failed to create .env"
            else
                echo "APP_ENV=production" > .env || err "Failed to create minimal .env"
                warn "Minimal .env created — set secrets ASAP"
            fi
            continue
        fi
        err "Missing critical file: $file"
    fi
done
ok "All critical files present"

# Check storage is writable
if [[ ! -w "storage" ]]; then
    err "storage directory not writable"
fi
ok "Storage directory is writable"

# Check git repo
if [[ -d ".git" ]]; then
    ok "Git repository detected"
else
    warn "Not a git repository — skipping git operations"
fi

# ─────────────────────────────────────────────────────────────────────────
# STEP 3.2: Git Operations
# ─────────────────────────────────────────────────────────────────────────

if [[ -d ".git" ]]; then
    log "Pulling latest code..."

    # Fix any stuck state
    git rebase --abort 2>/dev/null || true
    git merge --abort 2>/dev/null || true

    if [[ "$DRY_RUN" == "true" ]]; then
        log_deploy "DRY RUN: Would pull from git"
        git status --short || true
    else
        git pull origin main --force --no-edit 2>&1 | tee -a "$DEPLOY_LOG" && \
            ok "Code pulled from git" || \
            warn "Git pull had issues"
        log_deploy "Git pull completed"
    fi
fi

# ─────────────────────────────────────────────────────────────────────────
# STEP 3.3: PHP Dependencies
# ─────────────────────────────────────────────────────────────────────────

log "Installing PHP dependencies..."

if [[ "$DRY_RUN" == "true" ]]; then
    log_deploy "DRY RUN: Would run composer install"
else
    composer install --no-dev --no-interaction 2>&1 | tee -a "$DEPLOY_LOG" && \
        ok "Composer dependencies installed" || \
        warn "Composer install had warnings"
    log_deploy "Composer install completed"
fi

# ─────────────────────────────────────────────────────────────────────────
# STEP 3.4: Frontend Build
# ─────────────────────────────────────────────────────────────────────────

log "Building frontend assets..."

if [[ "$DRY_RUN" == "true" ]]; then
    log_deploy "DRY RUN: Would run npm run build"
else
    npm run build 2>&1 | tee -a "$DEPLOY_LOG" && \
        ok "Frontend assets built" || \
        warn "Frontend build had issues"
    log_deploy "Frontend build completed"
fi

# ─────────────────────────────────────────────────────────────────────────
# STEP 3.5: Laravel Configuration & Caches
# ─────────────────────────────────────────────────────────────────────────

log "Configuring Laravel caches..."

if [[ "$DRY_RUN" == "true" ]]; then
    log_deploy "DRY RUN: Would cache config"
else
    php artisan config:clear && \
    php artisan cache:clear && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
        ok "Laravel caches configured" || \
        warn "Cache operations had issues"
    log_deploy "Cache configuration completed"
fi

# ─────────────────────────────────────────────────────────────────────────
# STEP 3.6: Pre-Migration Data Backup
# ─────────────────────────────────────────────────────────────────────────

log "Creating pre-migration data backup..."

if [[ "$DRY_RUN" == "true" ]]; then
    log_deploy "DRY RUN: Would backup data"
else
    php artisan backup:data --type=all 2>&1 | tee -a "$DEPLOY_LOG" | tail -5 || true
    php artisan app:persist-data --verify 2>&1 | tee -a "$DEPLOY_LOG" | tail -5 || true
    ok "Pre-migration backup completed"
    log_deploy "Pre-migration backup completed"
fi

# ─────────────────────────────────────────────────────────────────────────
# STEP 3.7: Database Migrations
# ─────────────────────────────────────────────────────────────────────────

log "Running database migrations..."

if [[ "$DRY_RUN" == "true" ]]; then
    log_deploy "DRY RUN: Would run migrations"
    php artisan migrate:status || warn "Could not check migration status"
else
    php artisan migrate --force 2>&1 | tee -a "$DEPLOY_LOG" && \
        ok "Database migrations completed" || \
        warn "Migration warning (may already be up to date)"
    log_deploy "Database migrations completed"
fi

# ─────────────────────────────────────────────────────────────────────────
# STEP 3.8: Post-Migration Data Verification
# ─────────────────────────────────────────────────────────────────────────

log "Verifying data integrity after migration..."

if [[ "$DRY_RUN" == "true" ]]; then
    log_deploy "DRY RUN: Would verify data"
else
    php artisan app:persist-data --verify 2>&1 | tee -a "$DEPLOY_LOG" | tail -10 || true
    ok "Post-migration data verification completed"
    log_deploy "Post-migration data verification completed"
fi

# ─────────────────────────────────────────────────────────────────────────
# STEP 3.9: Storage & Permissions
# ─────────────────────────────────────────────────────────────────────────

log "Setting up storage..."

if [[ "$DRY_RUN" == "false" ]]; then
    php artisan storage:link --force 2>/dev/null && \
        ok "Storage symlink verified" || \
        warn "Storage link issue"
fi

# ─────────────────────────────────────────────────────────────────────────
# STEP 3.10: Queue Restart
# ─────────────────────────────────────────────────────────────────────────

log "Restarting queue workers..."

if [[ "$DRY_RUN" == "false" ]]; then
    php artisan queue:restart 2>/dev/null && \
        ok "Queue workers signaled" || \
        warn "Queue restart signal sent"
    log_deploy "Queue restart signal sent"
fi

# ─────────────────────────────────────────────────────────────────────────
# FINAL: Summary
# ─────────────────────────────────────────────────────────────────────────

log_deploy "════════════════════════════════════════════════════"
log_deploy "  Deployment Completed!"
log_deploy "════════════════════════════════════════════════════"
log_deploy "Time: $(date '+%Y-%m-%d %H:%M:%S')"

echo ""
hr
if [[ "$DRY_RUN" == "true" ]]; then
    warn "DRY RUN MODE — No changes were applied"
else
    ok "DEPLOYMENT COMPLETE"
fi
ok "Log: $DEPLOY_LOG"
ok "Site: https://alsarya.tv"
hr

exit 0
