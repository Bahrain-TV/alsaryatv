#!/usr/bin/env bash
###############################################################################
# deploy.sh — AlSarya TV Show Registration System
#
# Production deployment script. Handles dependency installation, asset
# compilation, database migrations, seeding, and Laravel cache optimisation.
#
# Usage:
#   ./deploy.sh              # Full deploy (default)
#   ./deploy.sh --fresh      # Drop all tables, re-migrate and seed
#   ./deploy.sh --seed       # Run seeders after migration
#   ./deploy.sh --no-build   # Skip npm build step
#   ./deploy.sh --force      # Force all steps even if no changes
#   ./deploy.sh --up         # Force deploy even if maintenance mode is active
#   ./deploy.sh --dry-run    # Print steps without executing
###############################################################################

# Configuration
APP_USER="alsar4210"
SUDO_PREFIX="sudo -u $APP_USER"

set -euo pipefail

# ── Colours ──────────────────────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No colour

# ── Logging Setup ────────────────────────────────────────────────────────────
# Disabled logging to prevent tee process issues
# LOG_FILE=$(mktemp)


# ── Helpers ──────────────────────────────────────────────────────────────────
info()    { echo -e "${CYAN}[INFO]${NC}  $*"; }
success() { echo -e "${GREEN}[OK]${NC}    $*"; }
warn()    { echo -e "${YELLOW}[WARN]${NC}  $*"; }
error()   { echo -e "${RED}[ERROR]${NC} $*"; }

run() {
    if [[ "$DRY_RUN" == "true" ]]; then
        echo -e "${YELLOW}[DRY-RUN]${NC} $*"
    else
        "$@"
    fi
}

# ── Defaults ─────────────────────────────────────────────────────────────────
FRESH=false
SEED=false
NO_BUILD=false
FORCE=false
DRY_RUN=false
IGNORE_MAINTENANCE=false
WEBHOOK_TRIGGER="${WEBHOOK_TRIGGER:-false}"

# ── Parse flags ──────────────────────────────────────────────────────────────
for arg in "$@"; do
    case "$arg" in
        --fresh)    FRESH=true; SEED=true ;;
        --seed)     SEED=true ;;
        --no-build) NO_BUILD=true ;;
        --force)    FORCE=true ;;
        --dry-run)  DRY_RUN=true ;;
        --up)       IGNORE_MAINTENANCE=true ;;
        *)          warn "Unknown flag: $arg" ;;
    esac
done

# ── Notifications (defined early for send_notification function) ────────────
DISCORD_WEBHOOK=""
NTFY_URL=""
NOTIFY_DISCORD=""
NOTIFY_ENABLED="true"

send_notification() {
    local exit_code=$1
    local status="Success"
    local color=5763719
    if [[ "$exit_code" -ne 0 ]]; then
        status="Failed"
        color=15548997
    fi

    # Logging disabled - no cleanup needed
    # exec 1>&3 2>&4  # Disabled since we're not using file descriptor redirection
    # exec 3>&- 4>&-
    # Wait for tee to finish writing
    # if [[ -n "${TEE_PID:-}" ]]; then
    #     wait "$TEE_PID" 2>/dev/null || true
    # fi
    # sleep 0.5  # Brief delay to ensure file is written

    if [[ "$NOTIFY_ENABLED" != "true" ]]; then
        # rm -f "$LOG_FILE"  # Logging disabled
        rm -f resources/views/errors/temp_503.blade.php 2>/dev/null || true
        return
    fi

    # Use PHP to construct safe JSON payload (without log content since logging is disabled)
    php -r "
        \$json = json_encode([
            'username' => 'Deployment Bot',
            'embeds' => [[
                'title' => \"Deployment $status\",
                'description' => 'Deployment completed. Logging disabled.',
                'color' => $color,
                'timestamp' => date('c')
            ]]
        ]);
        file_put_contents('discord_payload.json', \$json);
    " 2>/dev/null || true

    if [[ -f discord_payload.json && "$NOTIFY_DISCORD" = "true" && -n "$DISCORD_WEBHOOK" ]]; then
        curl -s --max-time 10 -H "Content-Type: application/json" -d @discord_payload.json "$DISCORD_WEBHOOK" >/dev/null 2>&1 || true
        rm -f discord_payload.json
    else
        rm -f discord_payload.json
    fi

    if [[ -n "$NTFY_URL" ]]; then
        echo "Deployment $status - Logging disabled" | curl -s --max-time 10 -H "Title: Deployment $status" -H "Priority: 4" -d @- "$NTFY_URL" >/dev/null 2>&1 || true
    fi

    # rm -f "$LOG_FILE"  # Logging disabled
    rm -f resources/views/errors/temp_503.blade.php 2>/dev/null || true
}

# ── Pre-flight checks ───────────────────────────────────────────────────────
resolve_app_dir() {
    local script_source=""
    if [[ -n "${BASH_SOURCE[0]:-}" && -f "${BASH_SOURCE[0]}" ]]; then
        script_source="${BASH_SOURCE[0]}"
    elif [[ -n "${0:-}" && -f "${0:-}" ]]; then
        script_source="$0"
    fi

    if [[ -n "$script_source" ]]; then
        (cd "$(dirname "$script_source")" && pwd)
        return 0
    fi

    if [[ -n "${APP_DIR:-}" && -d "${APP_DIR:-}" ]]; then
        printf '%s\n' "$APP_DIR"
        return 0
    fi

    if [[ -d "/home/alsarya.tv/public_html" ]]; then
        printf '%s\n' "/home/alsarya.tv/public_html"
        return 0
    fi

    pwd
}

APP_DIR="$(resolve_app_dir)"
cd "$APP_DIR" || {
    error "Failed to change directory to $APP_DIR"
    exit 1
}

info "Deploying from $APP_DIR"

if [[ ! -f artisan ]]; then
    error "artisan not found — are you in the Laravel project root?"
    exit 1
fi

if [[ ! -f .env ]]; then
    error ".env file not found. Copy .env.example and configure it first."
    exit 1
fi

# ── Step 0: Initial Change Detection (Drastic Loop Prevention) ──────────────
PREV_DEPLOY_FILE="storage/framework/last_successful_deploy"
INITIAL_GIT_HASH=$(git rev-parse HEAD 2>/dev/null || echo "no-git")

# If this is a webhook trigger, compare with last success BEFORE pulling or doing anything
if [[ "$WEBHOOK_TRIGGER" == "true" && -f "$PREV_DEPLOY_FILE" && "$FORCE" == "false" ]]; then
    LAST_SUCCESSFUL_HASH=$(cut -d'|' -f1 "$PREV_DEPLOY_FILE")
    if [[ "$INITIAL_GIT_HASH" == "$LAST_SUCCESSFUL_HASH" ]]; then
        # We check remote without pulling to see if we actually need to pull
        info "Webhook triggered, checking remote for actual changes..."
        git fetch origin >/dev/null 2>&1 || true
        REMOTE_HASH=$(git rev-parse origin/$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "main") 2>/dev/null || echo "")
        
        if [[ "$INITIAL_GIT_HASH" == "$REMOTE_HASH" ]]; then
            success "No remote changes detected. System is already at hash $REMOTE_HASH. Terminating to avoid loop."
            exit 0
        fi
    fi
fi

# ── Ensure APP_KEY is set ───────────────────────────────────────────────────
info "Checking application encryption key..."
APP_KEY_EXISTS=false
set +e  # Temporarily disable exit on error for key checking
if [[ -f .env ]]; then
    APP_KEY_LINE=$(grep '^APP_KEY=' .env 2>/dev/null | head -1 || echo "")
    if [[ -n "$APP_KEY_LINE" ]]; then
        APP_KEY_VALUE=$(echo "$APP_KEY_LINE" | cut -d'=' -f2- | sed 's/^"//' | sed 's/"$//')
        if [[ -n "$APP_KEY_VALUE" ]]; then
            APP_KEY_EXISTS=true
        fi
    fi
fi
set -e  # Re-enable exit on error

if [[ "$APP_KEY_EXISTS" == "false" ]]; then
    warn "APP_KEY not found or empty in .env file. Generating new key..."
    if run php artisan key:generate; then
        success "Application encryption key generated."
    else
        error "Failed to generate APP_KEY. Please set APP_KEY manually in .env file."
        exit 1
    fi
else
    success "Application encryption key is set."
fi

# Ensure storage/framework directory exists for installation flag
mkdir -p storage/framework

# ── Lock mechanism (prevent concurrent runs) ────────────────────────────────
LOCK_FILE="/tmp/deploy.lock"
INSTALL_FLAG="storage/framework/deployment.lock"

# Check system-level lock
if [[ -f "$LOCK_FILE" ]]; then
    LOCK_PID=$(cat "$LOCK_FILE" 2>/dev/null || echo "")
    if [[ -n "$LOCK_PID" ]] && kill -0 "$LOCK_PID" 2>/dev/null; then
        error "Deploy script already running (PID: $LOCK_PID). Exiting."
        exit 1
    else
        warn "Stale system lock file found. Removing..."
        rm -f "$LOCK_FILE"
    fi
fi

# Check application-level installation flag
if [[ -f "$INSTALL_FLAG" ]]; then
    FLAG_DATA=$(cat "$INSTALL_FLAG" 2>/dev/null || echo "")
    FLAG_PID=$(echo "$FLAG_DATA" | cut -d'|' -f1)
    FLAG_TIME=$(echo "$FLAG_DATA" | cut -d'|' -f2)
    FLAG_AGE=$(($(date +%s) - FLAG_TIME))

    if [[ -n "$FLAG_PID" ]] && kill -0 "$FLAG_PID" 2>/dev/null; then
        error "Installation already in progress (PID: $FLAG_PID, started $FLAG_AGE seconds ago)."
        error "If this is a stuck deployment, manually remove: $APP_DIR/$INSTALL_FLAG"
        exit 1
    else
        warn "Stale installation flag found (PID: $FLAG_PID, age: ${FLAG_AGE}s). Removing..."
        rm -f "$INSTALL_FLAG"
    fi
fi

# Create both locks
info "Creating deployment locks..."
echo $$ > "$LOCK_FILE"
echo "$$|$(date +%s)|$(date '+%Y-%m-%d %H:%M:%S')" > "$INSTALL_FLAG"
trap 'rm -f "$LOCK_FILE" "$INSTALL_FLAG"; send_notification $?' EXIT

# ── Error handler (ensure site comes back up on failure) ────────────────────
MAINTENANCE_WAS_ENABLED=false

cleanup_and_exit() {
    local exit_code=$?

    # Kill timeout process
    kill $TIMEOUT_PID 2>/dev/null || true

    # Remove locks
    rm -f "$LOCK_FILE" "$INSTALL_FLAG"

    # CRITICAL: Restore site if we put it in maintenance mode and something failed
    if [[ "$MAINTENANCE_WAS_ENABLED" == "true" && "$exit_code" -ne 0 ]]; then
        error "Deploy failed! Restoring site to LIVE status..."
        php artisan up 2>/dev/null || true
        error "Site restored. Check errors above."
    fi

    # Send notification
    send_notification $exit_code
}

# ── Timeout mechanism (prevent infinite runs) ───────────────────────────────
TIMEOUT=600  # 10 minutes max runtime
(
    sleep "$TIMEOUT"
    if kill -0 $$ 2>/dev/null; then
        error "Deploy script exceeded $TIMEOUT second timeout. Killing..."
        kill -9 $$
    fi
) &
TIMEOUT_PID=$!
trap cleanup_and_exit EXIT

# ── Cleanup zombie processes ────────────────────────────────────────────────
# Logging disabled - skip zombie process check
# info "Checking for zombie tee processes..."

# ── Load notification settings from .env ─────────────────────────────────────
DISCORD_WEBHOOK=$(grep "^DISCORD_WEBHOOK=" .env 2>/dev/null | cut -d '=' -f 2- | tr -d '"' || true)
NTFY_URL=$(grep "^NTFY_URL=" .env 2>/dev/null | cut -d '=' -f 2- | tr -d '"' || true)
NOTIFY_DISCORD=$(grep "^NOTIFY_DISCORD=" .env 2>/dev/null | cut -d '=' -f 2- | tr -d '"' || true)

# ── Log deployment trigger source ───────────────────────────────────────────
if [[ "$WEBHOOK_TRIGGER" == "true" ]]; then
    info "Deployment triggered by GitHub webhook"
fi

# ── Step 1: Maintenance mode ────────────────────────────────────────────────
if [[ -f "storage/framework/down" ]]; then
    if [[ -n "${PUBLISH_VERSION:-}" ]] || [[ "$IGNORE_MAINTENANCE" == "true" ]]; then
        info "Maintenance mode is active (continuing due to active flag or publisher)."
    else
        error "Website is currently in maintenance mode. Aborting deployment to prevent conflicts."
        error "To override and deploy anyway, use: ./deploy.sh --up"
        exit 1
    fi
fi

if [[ -z "${PUBLISH_VERSION:-}" ]]; then
    info "Enabling maintenance mode..."
    # Use the downtime template WITHOUT auto-refresh to prevent client-side loops
    run php artisan down --retry=60 --render="down" || true
    MAINTENANCE_WAS_ENABLED=true
else
    NOTIFY_ENABLED="false"
    info "Skipping maintenance mode (handled by publish.sh)."
    MAINTENANCE_WAS_ENABLED=false
fi


# ── Step 2: Pull latest code (if in a git repo) ─────────────────────────────
if [[ -d .git ]]; then
    info "Current Hash: $INITIAL_GIT_HASH. Pulling latest changes..."

    # Always fetch and reset to ensure exact match with remote
    # This avoids "diverged" warnings and ensures a clean state
    run git fetch origin
    CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "unknown")
    
    # Resetting hard ensures local changes (like previous build artifacts) don't conflict
    run git reset --hard origin/"$CURRENT_BRANCH"
    success "Codebase synced with remote/$CURRENT_BRANCH."

    # Post-pull check: If nothing changed and not forced, STOP HERE
    POST_PULL_HASH=$(git rev-parse HEAD 2>/dev/null || echo "no-git")
    if [[ -f "$PREV_DEPLOY_FILE" && "$FORCE" == "false" && "$FRESH" == "false" ]]; then
        LAST_SUCCESSFUL_HASH=$(cut -d'|' -f1 "$PREV_DEPLOY_FILE")
        if [[ "$POST_PULL_HASH" == "$LAST_SUCCESSFUL_HASH" ]]; then
            success "System is already at the last successfully deployed hash ($POST_PULL_HASH)."
            success "No new changes to process. Terminating deployment to protect resources."
            exit 0
        fi
    fi
fi

# ── Step 2.5: Change Detection ──────────────────────────────────────────────
info "Detecting specific changes for optimized build..."
CURRENT_GIT_HASH=$(git rev-parse HEAD 2>/dev/null || echo "no-git")
CURRENT_VERSION=$(cat VERSION 2>/dev/null || echo "no-version")

if [[ -f "$PREV_DEPLOY_FILE" ]]; then
    IFS='|' read -r LAST_HASH LAST_VERSION < "$PREV_DEPLOY_FILE"
else
    LAST_HASH=""
fi

# Re-evaluate change status based on the potentially updated POST_PULL_HASH
COMPOSER_CHANGED=true
FRONTEND_CHANGED=true
MIGRATIONS_CHANGED=true

if [[ "$FORCE" == "true" || "$FRESH" == "true" ]]; then
    info "Force deployment requested — skipping optimizations."
elif [[ -z "$LAST_HASH" || "$LAST_HASH" == "no-git" ]]; then
    info "No previous deployment record found."
else
    # Detect what actually changed
    CHANGES=$(git diff --name-only "$LAST_HASH" "$POST_PULL_HASH" 2>/dev/null || echo "ALL")
    
    if [[ "$CHANGES" == "ALL" ]]; then
        info "Assuming everything changed."
    else
        # Composer check
        if ! echo "$CHANGES" | grep -qE "^(composer\.json|composer\.lock)$"; then
            COMPOSER_CHANGED=false
            info "No Composer changes."
        fi
        
        # Frontend check
        if ! echo "$CHANGES" | grep -qE "^(package\.json|pnpm-lock\.yaml|package-lock\.json|vite\.config\.js|tailwind\.config\.js|postcss\.config\.js|resources/|public/)"; then
            FRONTEND_CHANGED=false
            info "No Frontend changes."
        fi
        
        # Migrations check
        if ! echo "$CHANGES" | grep -q "^database/migrations/"; then
            MIGRATIONS_CHANGED=false
            info "No migration changes."
        fi
    fi
fi

# ── Step 3: Install PHP dependencies ────────────────────────────────────────
if [[ "$COMPOSER_CHANGED" == "true" ]]; then
    info "Installing Composer dependencies..."
    if [[ "${APP_ENV:-production}" == "production" ]]; then
        run composer install --no-dev --optimize-autoloader --no-interaction
    else
        run composer install --no-interaction
    fi
    success "Composer dependencies installed."
else
    info "Skipping Composer installation (no changes detected)."
fi

# ── Step 4: Install & build frontend assets ─────────────────────────────────
if [[ "$NO_BUILD" == "false" ]]; then
    if [[ "$FRONTEND_CHANGED" == "true" ]]; then
        info "Installing Node dependencies..."
        if command -v pnpm &>/dev/null; then
            run pnpm install --frozen-lockfile
        elif command -v npm &>/dev/null; then
            run npm ci
        else
            warn "Neither pnpm nor npm found — skipping frontend build."
            NO_BUILD=true
        fi

        if [[ "$NO_BUILD" == "false" ]]; then
            info "Building frontend assets..."
            if command -v pnpm &>/dev/null; then
                run pnpm build
            else
                run npm run build
            fi
            success "Frontend assets built."
        fi
    else
        info "Skipping Frontend build (no changes detected)."
    fi
else
    info "Skipping frontend build (--no-build or skipped due to changes)."
fi

# ── Step 5: Database migrations ─────────────────────────────────────────────
if [[ "$MIGRATIONS_CHANGED" == "true" || "$FRESH" == "true" ]]; then
    info "Running database migrations..."

    if [[ "$FRESH" == "true" ]]; then
        warn "Running migrate:fresh — this will DROP all tables!"
        run php artisan migrate:fresh --force --seed
        success "Fresh migration completed with seeding."
    else
        run php artisan migrate --force
        success "Migrations applied."
    fi
else
    info "Skipping database migrations (no changes detected)."
fi

# Always check seeding if requested
if [[ "$SEED" == "true" && "$FRESH" == "false" ]]; then
    info "Running database seeders..."
    info "  → UserSeeder: Creates/updates admin users"
    info "  → CallerSeeder: Imports from CSV (only if table is empty)"
    run php artisan db:seed --force
    success "Database seeding completed."
    warn "Note: CallerSeeder imports from database/seeders/data/callers_seed.csv"
    warn "      and only runs when callers table is completely empty."
fi

# ── Step 6: Verify migration status ─────────────────────────────────────────
info "Verifying migration status..."
if [[ "$DRY_RUN" == "false" ]]; then
    PENDING=$(php artisan migrate:status 2>/dev/null | grep -c "Pending" || true)
    if [[ "$PENDING" -gt 0 ]]; then
        error "$PENDING migration(s) still pending! Check migration errors above."
        run php artisan up
        exit 1
    fi
    success "All migrations applied — no pending migrations."
else
    echo -e "${YELLOW}[DRY-RUN]${NC} php artisan migrate:status"
fi

# ── Step 7: Version sync ────────────────────────────────────────────────────
if php artisan list 2>/dev/null | grep -q "version:sync"; then
    info "Synchronising version..."
    run php artisan version:sync
    success "Version synchronised."
fi

# ── Step 8: Laravel optimisation caches ──────────────────────────────────────
info "Caching configuration, routes, and views..."
run php artisan config:cache
run php artisan route:cache
run php artisan view:cache
run php artisan event:cache
success "Laravel caches rebuilt."

# ── Step 9: Clear stale caches ───────────────────────────────────────────────
info "Clearing stale application caches..."
run php artisan cache:clear || true
success "Application cache cleared."

# ── Step 10: Storage link ────────────────────────────────────────────────────
if [[ ! -L public/storage ]]; then
    info "Creating storage symlink..."
    run php artisan storage:link
    success "Storage symlink created."
fi

# ── Step 11: Queue restart ───────────────────────────────────────────────────
info "Restarting queue workers..."
run php artisan queue:restart || true
success "Queue restart signal sent."

# ── Step 11b: Ensure ownership ───────────────────────────────────────────────
# DISABLED: Recursive chown breaks web server permissions (HTTP 403 errors)
# All operations above already run as the correct user via SUDO_PREFIX
# if [[ "$(id -u)" -eq 0 ]]; then
#     info "Ensuring ownership for $APP_DIR..."
#     run chown -R alsar4210:alsar4210 "$APP_DIR"
#     success "Ownership set to alsar4210:alsar4210."
# else
#     warn "Skipping ownership fix (requires root)."
# fi
info "Skipping recursive ownership change (prevents HTTP 403 errors)"

# ── Step 11c: Update deployment record ───────────────────────────────────────
if [[ "$DRY_RUN" == "false" ]]; then
    # PREV_DEPLOY_FILE, CURRENT_GIT_HASH, CURRENT_VERSION are defined in Step 2.5
    echo "${CURRENT_GIT_HASH}|${CURRENT_VERSION}" > "$PREV_DEPLOY_FILE"
    info "Deployment record updated: ${CURRENT_VERSION} (${CURRENT_GIT_HASH})"
fi

# ── Step 12: Health check ────────────────────────────────────────────────────
check_production_health() {
    local app_url=$(grep "^APP_URL=" .env 2>/dev/null | cut -d= -f2- | tr -d '"' | tr -d "'" || echo "")

    if [[ -z "$app_url" ]]; then
        warn "APP_URL not found in .env - skipping health check"
        return 0
    fi

    info "Running production health checks..."

    # Check individual registration route
    local individual_status=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "$app_url" 2>/dev/null || echo "000")

    # Check family registration route
    local family_status=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "$app_url/family" 2>/dev/null || echo "000")

    if [[ "$individual_status" == "200" || "$individual_status" == "302" ]]; then
        success "✓ Individual registration route: HTTP $individual_status"
    else
        error "✗ Individual registration route: HTTP $individual_status (expected 200/302)"
        return 1
    fi

    if [[ "$family_status" == "200" || "$family_status" == "302" ]]; then
        success "✓ Family registration route: HTTP $family_status"
    else
        error "✗ Family registration route: HTTP $family_status (expected 200/302)"
        return 1
    fi

    success "All production routes are healthy!"
    return 0
}

# Detect if running from local machine (via publish.sh or direct SSH)
IS_LOCAL_EXECUTION=false
if [[ -n "${PUBLISH_VERSION:-}" ]] || [[ -n "${SSH_CLIENT:-}" ]] || [[ -n "${SSH_TTY:-}" ]]; then
    IS_LOCAL_EXECUTION=true
    info "Detected remote execution (called from local machine)"
fi

# ── Step 13: Disable maintenance mode ────────────────────────────────────────
if [[ -z "${PUBLISH_VERSION:-}" ]]; then
    info "Disabling maintenance mode..."
    run php artisan up
    success "Application is live."
    MAINTENANCE_WAS_ENABLED=false  # Site is now up, don't restore in trap

    # Run health check if executed remotely from local machine
    if [[ "$IS_LOCAL_EXECUTION" == "true" && "$DRY_RUN" == "false" ]]; then
        echo ""
        if ! check_production_health; then
            error "Health check failed! Site is live but routes may not be working correctly."
            error "Please investigate immediately."
            exit 1
        fi
    fi
else
    info "Skipping maintenance mode restore (handled by publish.sh)."
fi

# ── Summary ──────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Deployment complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

echo ""
[[ "$SEED" == "true" ]] && info "Seeders executed: UserSeeder, CallerSeeder"
[[ "$FRESH" == "true" ]] && warn "Database was freshly rebuilt (all previous data dropped)."
echo ""
success "Deploy finished at $(date '+%Y-%m-%d %H:%M:%S')"
