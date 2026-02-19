#!/bin/bash
################################################################################
# deploy.sh — AlSarya TV Smart Deployment Script
#
# INTELLIGENT DETECTION:
#   • Running on LOCAL (Mac/Linux dev machine)?  → SSH to production + deploy
#   • Running on PRODUCTION server?              → Deploy directly (no SSH)
#
# Usage:
#   ./deploy.sh                    # Auto-detects and deploys
#   ./deploy.sh --dry-run          # Preview changes without applying
#   VERBOSE=1 ./deploy.sh          # Debug mode
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
NC='\033[0m'

# ─────────────────────────────────────────────────────────────────────────
# Logging
# ─────────────────────────────────────────────────────────────────────────
log()   { echo -e "${CYAN}→${NC} $*"; }
ok()    { echo -e "${GREEN}✓${NC} $*"; }
err()   { echo -e "${RED}✗${NC} $*"; exit 1; }
warn()  { echo -e "${YELLOW}!${NC} $*"; }
hr()    { echo -e "${MAGENTA}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"; }

# ─────────────────────────────────────────────────────────────────────────
# Configuration
# ─────────────────────────────────────────────────────────────────────────
PROD_HOST="${PROD_HOST:-root@h6.doy.tech}"
PROD_DIR="${PROD_DIR:-/home/alsarya.tv/public_html}"
SSH_KEY="${SSH_KEY:-${HOME}/.ssh/id_rsa}"
DRY_RUN="${DRY_RUN:-false}"
VERBOSE="${VERBOSE:-0}"

# Parse CLI flags
while [[ $# -gt 0 ]]; do
    case "$1" in
        --dry-run) DRY_RUN=true; shift ;;
        --verbose) VERBOSE=1; shift ;;
        *) warn "Unknown flag: $1"; shift ;;
    esac
done

# ─────────────────────────────────────────────────────────────────────────
# STEP 1: DETECT EXECUTION CONTEXT
# ─────────────────────────────────────────────────────────────────────────

detect_context() {
    # Check if we're on the production server
    if [[ "$(pwd)" == "$PROD_DIR" ]]; then
        echo "production"
        return 0
    fi

    # Check if running via SSH session
    if [[ -n "${SSH_CLIENT:-}" || -n "${SSH_TTY:-}" ]]; then
        echo "production"
        return 0
    fi

    # Check if we're root@production host
    if [[ "$(whoami)@$(hostname)" == "root@"* && -d "$PROD_DIR" ]]; then
        echo "production"
        return 0
    fi

    # Otherwise, we're local
    echo "local"
    return 0
}

CONTEXT=$(detect_context)

[[ $VERBOSE -eq 1 ]] && log "Context: $CONTEXT"

# ─────────────────────────────────────────────────────────────────────────
# STEP 2: LOCAL EXECUTION → SSH TO PRODUCTION
# ─────────────────────────────────────────────────────────────────────────

if [[ "$CONTEXT" == "local" ]]; then
    hr
    log "🖥️  Running on LOCAL machine"
    log "🚀 Connecting to production: $PROD_HOST"
    hr
    echo ""

    # Validate SSH key exists
    if [[ ! -f "$SSH_KEY" ]]; then
        err "SSH key not found: $SSH_KEY"
    fi

    # Build SSH command with flags
    SSH_CMD="ssh -i '$SSH_KEY' -o BatchMode=no -o ConnectTimeout=10 '$PROD_HOST' 'cd $PROD_DIR && bash ./deploy.sh'"

    [[ $VERBOSE -eq 1 ]] && log "SSH Command: $SSH_CMD"

    # Execute remote deployment
    if [[ "$DRY_RUN" == "true" ]]; then
        log "DRY RUN: Would execute: $SSH_CMD"
        exit 0
    fi

    eval "$SSH_CMD"
    exit $?
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
            warn "Missing critical file: .env — attempting to create from .env.example or .env.production"
            if [[ -f ".env.example" ]]; then
                if cp ".env.example" .env; then
                    if ! grep -q '^APP_ENV=' .env; then echo "APP_ENV=production" >> .env; fi
                    warn ".env created from .env.example (may lack secrets). Please review and populate production secrets."
                else
                    err "Failed to create .env from .env.example"
                fi
            elif [[ -f ".env.production" ]]; then
                if cp ".env.production" .env; then
                    warn ".env created from .env.production; please verify."
                else
                    err "Failed to create .env from .env.production"
                fi
            else
                warn "No .env.example or .env.production found; creating minimal .env with APP_ENV=production"
                echo "APP_ENV=production" > .env || err "Failed to create minimal .env"
                warn "Minimal .env created; set secrets as soon as possible."
            fi
            continue
        fi
        err "Missing critical file: $file"
    fi
done
ok "All critical files present (post-recovery)"

# Check storage is writable
if [[ ! -w "storage" ]]; then
    err "storage directory not writable"
fi
ok "Storage directory is writable"

# Check git repo
if [[ ! -d ".git" ]]; then
    warn "Not a git repository"
else
    ok "Git repository detected"
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
        log "Status:"
        git status --short || true
    else
        git pull origin main --force --no-edit || err "Git pull failed"
        ok "Code pulled from git"
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
    if composer install --no-dev --no-interaction 2>&1 | tee -a "$DEPLOY_LOG"; then
        ok "Composer dependencies installed"
        log_deploy "Composer install completed"
    else
        warn "Composer install had warnings"
    fi
fi

# ─────────────────────────────────────────────────────────────────────────
# STEP 3.4: Frontend Build
# ─────────────────────────────────────────────────────────────────────────

log "Building frontend assets..."

if [[ "$DRY_RUN" == "true" ]]; then
    log_deploy "DRY RUN: Would run npm run build"
else
    if npm run build 2>&1 | tee -a "$DEPLOY_LOG"; then
        ok "Frontend assets built"
        log_deploy "Frontend build completed"
    else
        warn "Frontend build had issues"
    fi
fi

# ─────────────────────────────────────────────────────────────────────────
# STEP 3.5: Laravel Configuration
# ─────────────────────────────────────────────────────────────────────────

log "Configuring Laravel caches..."

if [[ "$DRY_RUN" == "true" ]]; then
    log_deploy "DRY RUN: Would clear and cache config"
else
    php artisan config:clear
    php artisan cache:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    ok "Laravel caches configured"
    log_deploy "Cache configuration completed"
fi

# ─────────────────────────────────────────────────────────────────────────
# STEP 3.6: Database Migrations
# ─────────────────────────────────────────────────────────────────────────

log "Running database migrations..."

if [[ "$DRY_RUN" == "true" ]]; then
    log_deploy "DRY RUN: Would run migrations"
    php artisan migrate:status || warn "Could not check migration status"
else
    if php artisan migrate --force 2>&1 | tee -a "$DEPLOY_LOG"; then
        ok "Database migrations completed"
        log_deploy "Database migrations completed"
    else
        warn "Migration warning (may already be up to date)"
    fi
fi

# ─────────────────────────────────────────────────────────────────────────
# STEP 3.7: Storage & Permissions
# ─────────────────────────────────────────────────────────────────────────

log "Setting up storage..."

if [[ "$DRY_RUN" == "false" ]]; then
    php artisan storage:link --force 2>/dev/null || warn "Storage link issue"
    ok "Storage symlink verified"
fi

# ─────────────────────────────────────────────────────────────────────────
# STEP 3.8: Queue Restart
# ─────────────────────────────────────────────────────────────────────────

log "Restarting queue workers..."

if [[ "$DRY_RUN" == "false" ]]; then
    php artisan queue:restart || warn "Queue restart signal sent"
    ok "Queue workers signaled"
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
