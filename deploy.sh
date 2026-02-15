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
#   ./deploy.sh --dry-run    # Print steps without executing
###############################################################################

set -euo pipefail

# ── Colours ──────────────────────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No colour

# ── Logging Setup ────────────────────────────────────────────────────────────
LOG_FILE=$(mktemp)
# Store original file descriptors to restore later
exec 3>&1 4>&2
exec > >(tee -a "$LOG_FILE") 2>&1
TEE_PID=$!


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
DRY_RUN=false
WEBHOOK_TRIGGER="${WEBHOOK_TRIGGER:-false}"

# ── Parse flags ──────────────────────────────────────────────────────────────
for arg in "$@"; do
    case "$arg" in
        --fresh)    FRESH=true; SEED=true ;;
        --seed)     SEED=true ;;
        --no-build) NO_BUILD=true ;;
        --dry-run)  DRY_RUN=true ;;
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

    # Restore original file descriptors and close tee process
    exec 1>&3 2>&4
    exec 3>&- 4>&-

    # Wait for tee to finish writing
    if [[ -n "${TEE_PID:-}" ]]; then
        wait "$TEE_PID" 2>/dev/null || true
    fi
    sleep 0.5  # Brief delay to ensure file is written

    if [[ "$NOTIFY_ENABLED" != "true" ]]; then
        rm -f "$LOG_FILE"
        rm -f resources/views/errors/temp_503.blade.php 2>/dev/null || true
        return
    fi

    # Use PHP to construct safe JSON payload
    php -r "
        \$log = file_get_contents('$LOG_FILE');
        // Strip ANSI codes for cleaner Discord display
        \$log = preg_replace('/\\x1b\[[0-9;]*m/', '', \$log);
        // Limit to last 1500 chars to fit in embed
        if (strlen(\$log) > 1500) {
            \$log = '...' . substr(\$log, -1500);
        }

        \$json = json_encode([
            'username' => 'Deployment Bot',
            'embeds' => [[
                'title' => \"Deployment $status\",
                'description' => \"\`\`\`\\n\" . \$log . \"\\n\`\`\`\",
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
        tail -n 30 "$LOG_FILE" 2>/dev/null | curl -s --max-time 10 -H "Title: Deployment $status" -H "Priority: 4" -d @- "$NTFY_URL" >/dev/null 2>&1 || true
    fi

    rm -f "$LOG_FILE"
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
trap "kill $TIMEOUT_PID 2>/dev/null || true; rm -f '$LOCK_FILE' '$INSTALL_FLAG'; send_notification \$?" EXIT

# ── Cleanup zombie processes ────────────────────────────────────────────────
info "Checking for zombie tee processes..."
# Kill stale tee processes from previous deployments, but exclude current shell
# Get our own PID and filter it out to avoid killing our own tee process
CURRENT_PID=$$
pgrep -f "tee -a /tmp/tmp\." 2>/dev/null | grep -v "^${CURRENT_PID}$" | xargs -r kill -9 2>/dev/null || true

# ── Load notification settings from .env ─────────────────────────────────────
DISCORD_WEBHOOK=$(grep "^DISCORD_WEBHOOK=" .env 2>/dev/null | cut -d '=' -f 2- | tr -d '"'"'" || true)
NTFY_URL=$(grep "^NTFY_URL=" .env 2>/dev/null | cut -d '=' -f 2- | tr -d '"'"'" || true)
NOTIFY_DISCORD=$(grep "^NOTIFY_DISCORD=" .env 2>/dev/null | cut -d '=' -f 2- | tr -d '"'"'" || true)

# ── Log deployment trigger source ───────────────────────────────────────────
if [[ "$WEBHOOK_TRIGGER" == "true" ]]; then
    info "Deployment triggered by GitHub webhook"
fi

# ── Step 1: Maintenance mode ────────────────────────────────────────────────
if [[ -z "${PUBLISH_VERSION:-}" ]]; then
    info "Enabling maintenance mode..."
    # Use the downtime template WITHOUT auto-refresh to prevent client-side loops
    run php artisan down --retry=60 --render="down" || true
else
    NOTIFY_ENABLED="false"
    info "Skipping maintenance mode (handled by publish.sh)."
fi


# ── Step 2: Pull latest code (if in a git repo) ─────────────────────────────
if [[ -d .git ]]; then
    info "Pulling latest changes from git..."

    # Try fast-forward only first (safest)
    if run git pull --ff-only 2>/dev/null; then
        success "Pulled latest changes (fast-forward)"
    else
        warn "Fast-forward failed — local and remote branches have diverged"

        # Fetch remote to see current state
        run git fetch origin || warn "git fetch failed"

        # Get current branch
        CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "unknown")

        # Reset to remote HEAD (deployment should use what's on GitHub/remote)
        info "Resetting to remote/$CURRENT_BRANCH..."
        if run git reset --hard origin/"$CURRENT_BRANCH" 2>/dev/null; then
            success "Reset to remote branch (local commits discarded if any)"
        else
            warn "Could not reset to remote — continuing with local state"
        fi
    fi
fi

# ── Step 3: Install PHP dependencies ────────────────────────────────────────
info "Installing Composer dependencies..."
if [[ "${APP_ENV:-production}" == "production" ]]; then
    run composer install --no-dev --optimize-autoloader --no-interaction
else
    run composer install --no-interaction
fi
success "Composer dependencies installed."

# ── Step 4: Install & build frontend assets ─────────────────────────────────
if [[ "$NO_BUILD" == "false" ]]; then
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
    info "Skipping frontend build (--no-build)."
fi

# ── Step 5: Database migrations ─────────────────────────────────────────────
info "Running database migrations..."

if [[ "$FRESH" == "true" ]]; then
    warn "Running migrate:fresh — this will DROP all tables!"
    run php artisan migrate:fresh --force --seed
    success "Fresh migration completed with seeding."
else
    run php artisan migrate --force
    success "Migrations applied."

    # ── Step 5b: Seed (if requested) ─────────────────────────────────────────
    if [[ "$SEED" == "true" ]]; then
        info "Running database seeders..."
        info "  → UserSeeder: Creates/updates admin users"
        info "  → CallerSeeder: Imports from CSV (only if table is empty)"
        run php artisan db:seed --force
        success "Database seeding completed."
        warn "Note: CallerSeeder imports from database/seeders/data/callers_seed.csv"
        warn "      and only runs when callers table is completely empty."
    fi
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
if [[ "$(id -u)" -eq 0 ]]; then
    info "Ensuring ownership for $APP_DIR..."
    run chown -R alsar4210:alsar4210 "$APP_DIR"
    success "Ownership set to alsar4210:alsar4210."
else
    warn "Skipping ownership fix (requires root)."
fi

# ── Step 12: Disable maintenance mode ────────────────────────────────────────
if [[ -z "${PUBLISH_VERSION:-}" ]]; then
    info "Disabling maintenance mode..."
    run php artisan up
    success "Application is live."
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
