#!/usr/bin/env bash
###############################################################################
# deploy.sh — AlSarya TV Show Registration System (FIXED VERSION)
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
#   ./deploy.sh --reset-db   # Reset database (migrate:fresh without seeding)
#   ./deploy.sh --up         # Force deploy even if maintenance mode is active
#   ./deploy.sh --dry-run    # Print steps without executing
###############################################################################

# Configuration
# APP_USER is the OS user that owns the files and should run artisan commands
APP_USER="alsar4210"

# PROD_SSH_USER is the user we use to connect (keep as root if SSH requires it)
# We will "su" or "sudo" to APP_USER when running commands on the server
SUDO_PREFIX="sudo -u $APP_USER"

# Start with basic error handling, we'll enable -u later after variable init
set -eo pipefail

# ── Local Execution Wrapper ──────────────────────────────────────────────────
# Detect if running locally (e.g. macOS/Darwin) and proxy to remote
if [[ "$(uname -s)" == "Darwin" ]]; then
    # Helper for local reporting
    local_info()  { echo -e "\033[0;36m[LOCAL]\033[0m  $*"; }
    local_error() { echo -e "\033[0;31m[ERROR]\033[0m $*"; }
    
    # Setup cleanup for local wrapper
    local_cleanup() {
        # Restore terminal if needed, kill child processes
        true
    }
    trap local_cleanup EXIT INT TERM

    echo "----------------------------------------------------------------"
    echo "  🚀 AlSarya TV Deployment Launcher"
    echo "  Detected Local Environment (macOS)"
    echo "----------------------------------------------------------------"

    # Load .env for config
    if [[ -f .env ]]; then
        set -a
        [ -f .env ] && . .env
        set +a
    fi

    # Set defaults
    PROD_SSH_USER="${PROD_SSH_USER:-root}"
    PROD_SSH_HOST="${PROD_SSH_HOST:-alsarya.tv}"
    PROD_SSH_PORT="${PROD_SSH_PORT:-22}"
    PROD_APP_DIR="${PROD_APP_DIR:-/home/alsarya.tv/public_html}"
    SSH_KEY_PATH="${SSH_KEY_PATH:-${HOME}/.ssh/id_rsa}"

    # 1. ESTABLISH CONNECTION FIRST ("Open up the production server")
    # We verify we can talk to the server before doing any heavy lifting.
    local_info "Testing connection to production server ($PROD_SSH_HOST)..."
    if ! ssh -q -p "$PROD_SSH_PORT" -i "$SSH_KEY_PATH" -o BatchMode=yes -o ConnectTimeout=5 "$PROD_SSH_USER@$PROD_SSH_HOST" "echo '✓ Connection Established'"; then
        local_error "Could not connect to $PROD_SSH_USER@$PROD_SSH_HOST"
        local_error "Please check your VPN, internet connection, or SSH keys."
        exit 1
    fi

    # 2. Sync Assets (Images & Storage) - OPTIMIZED
    # We use rsync with checksum (-c) to avoid re-uploading identical files
    local_info "Syncing Assets (Images & Storage)..."
    RSYNC_SSH="ssh -p $PROD_SSH_PORT -i $SSH_KEY_PATH -o StrictHostKeyChecking=accept-new"

    # Sync public/images
    if [[ -d "public/images" ]]; then
        # -c checksum, -u update (skip newer), --delete (optional, maybe too dangerous?)
        rsync -avzc --no-o --no-g -e "$RSYNC_SSH" "public/images/" "$PROD_SSH_USER@$PROD_SSH_HOST:$PROD_APP_DIR/public/images/" >/dev/null || local_error "Image sync warning"
    fi

    # Sync storage/app/public
    if [[ -d "storage/app/public" ]]; then
        # Ensure remote directory exists
        ssh -p "$PROD_SSH_PORT" -i "$SSH_KEY_PATH" "$PROD_SSH_USER@$PROD_SSH_HOST" "mkdir -p $PROD_APP_DIR/storage/app/public" || true
        rsync -avzc --no-o --no-g -e "$RSYNC_SSH" "storage/app/public/" "$PROD_SSH_USER@$PROD_SSH_HOST:$PROD_APP_DIR/storage/app/public/" >/dev/null || local_error "Storage sync warning"
    fi
    local_info "✓ Assets Synced (Differential)"

    # 3. Update Remote Configuration & Script
    local_info "Updating remote configuration and deployment script..."
    
    # Sync .env (Differential sync using rsync instead of scp)
    if [[ -f .env ]]; then
        rsync -avzc --no-o --no-g -e "$RSYNC_SSH" ".env" "$PROD_SSH_USER@$PROD_SSH_HOST:$PROD_APP_DIR/.env" >/dev/null || local_error ".env sync failed"
    fi

    # Upload clean deploy script (Atomic upload: upload to tmp -> mv to target)
    scp -q -P "$PROD_SSH_PORT" -i "$SSH_KEY_PATH" "$0" "$PROD_SSH_USER@$PROD_SSH_HOST:$PROD_APP_DIR/deploy.sh.new"
    
    # Fix permissions and swap script
    FIX_CMD="
        chmod +x $PROD_APP_DIR/deploy.sh.new
        mv $PROD_APP_DIR/deploy.sh.new $PROD_APP_DIR/deploy.sh
        chown $APP_USER:$APP_USER $PROD_APP_DIR/deploy.sh
        
        # Aggressive Permission Fixes
        chown -R $APP_USER:$APP_USER $PROD_APP_DIR/public/images $PROD_APP_DIR/storage/app/public $PROD_APP_DIR/.env
        chmod -R 775 $PROD_APP_DIR/storage
        chmod -R 755 $PROD_APP_DIR/public/images
    "
    ssh -p "$PROD_SSH_PORT" -i "$SSH_KEY_PATH" "$PROD_SSH_USER@$PROD_SSH_HOST" "$FIX_CMD" || local_error "Permission fix warning"

    # 4. Handover to Remote Server
    local_info "Handing over to production server..."
    echo ""
    
    # Interactive execution to preserve colors and see real-time output
    REMOTE_CMD="cd '$PROD_APP_DIR' && ./deploy.sh $@"
    ssh -t -p "$PROD_SSH_PORT" -i "$SSH_KEY_PATH" "$PROD_SSH_USER@$PROD_SSH_HOST" "$REMOTE_CMD"
    
    EXIT_CODE=$?
    echo ""
    if [ $EXIT_CODE -eq 0 ]; then
        local_info "✓ Deployment Cycle Complete."
    else
        local_error "✗ Remote Deployment Finished with Error (Code: $EXIT_CODE)"
    fi

    exit $EXIT_CODE
fi

# ── Remote / Server Execution Logic Starts Here ──────────────────────────────

# ── Colours ──────────────────────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No colour

# ── Helpers ──────────────────────────────────────────────────────────────────
info()    { echo -e "${CYAN}[INFO]${NC}  $*"; }
success() { echo -e "${GREEN}[OK]${NC}    $*"; }
warn()    { echo -e "${YELLOW}[WARN]${NC}  $*"; }
error()   { echo -e "${RED}[ERROR]${NC} $*"; }

run() {
    local cmd_str="$*"

    # Check if we are physically running as root (ID 0)
    if [[ "$(id -u)" == "0" ]]; then
        # Commands that touch application files/db should be run as APP_USER
        if [[ ! "$cmd_str" =~ ^sudo ]]; then
            if [[ "$cmd_str" == *"php"* ]] || [[ "$cmd_str" == *"composer"* ]] || [[ "$cmd_str" == *"npm"* ]] || [[ "$cmd_str" == *"pnpm"* ]] || [[ "$cmd_str" == *"git"* ]]; then
                # Wrap in sudo -u APP_USER
                # We use 'bash -c' to handle complex commands (pipes, redirects) if any, though risky.
                # simpler: just prefix.
                cmd_str="sudo -u $APP_USER $cmd_str"
            fi
        fi
    fi

    if [[ "${DRY_RUN:-false}" == "true" ]]; then
        echo -e "${YELLOW}[DRY-RUN]${NC} $cmd_str"
        return 0
    fi

    # Execute
    eval "$cmd_str" || {
        local exit_code=$?
        error "Command failed with exit code $exit_code: $cmd_str"
        return "$exit_code"
    }
}

# ── Initialize critical variables BEFORE enabling -u ──────────────────────────
FRESH=false
SEED=false
NO_BUILD=false
FORCE=false
DRY_RUN=false
RESET_DB=false
IGNORE_MAINTENANCE=false
WEBHOOK_TRIGGER="${WEBHOOK_TRIGGER:-false}"
TIMEOUT_PID=""
MAINTENANCE_WAS_ENABLED=false
LOCK_FILE="/tmp/deploy.lock"
INSTALL_FLAG="storage/framework/deployment.lock"
NOTIFY_ENABLED="true"
DISCORD_WEBHOOK=""
NTFY_URL=""
NOTIFY_DISCORD=""

# Load .env variables if file exists (so DB_CONNECTION etc are available)
if [[ -f .env ]]; then
    set -a
    source .env
    set +a
fi

# NOW enable strict mode for undefined variables
set -u

# ── Parse flags ──────────────────────────────────────────────────────────────
for arg in "$@"; do
    case "$arg" in
        --fresh)     FRESH=true; SEED=true ;;
        --seed)      SEED=true ;;
        --no-build)  NO_BUILD=true ;;
        --force)     FORCE=true ;;
        --dry-run)   DRY_RUN=true ;;
        --reset-db)  RESET_DB=true ;;
        --up)        IGNORE_MAINTENANCE=true ;;
        *)           warn "Unknown flag: $arg" ;;
    esac
done

# ── Notifications ────────────────────────────────────────────────────────────
send_notification() {
    local exit_code=$1
    local status="Success"
    local color=5763719
    if [[ "$exit_code" -ne 0 ]]; then
        status="Failed"
        color=15548997
    fi

    if [[ "$NOTIFY_ENABLED" != "true" ]]; then
        rm -f resources/views/errors/temp_503.blade.php 2>/dev/null || true
        return
    fi

    php -r "
        \$json = json_encode([
            'username' => 'Deployment Bot',
            'embeds' => [[
                'title' => \"Deployment $status\",
                'description' => 'Deployment completed.',
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
        echo "Deployment $status" | curl -s --max-time 10 -H "Title: Deployment $status" -H "Priority: 4" -d @- "$NTFY_URL" >/dev/null 2>&1 || true
    fi

    rm -f resources/views/errors/temp_503.blade.php 2>/dev/null || true
}

# ── Pre-flight checks ────────────────────────────────────────────────────────
resolve_app_dir() {
    local script_source=""
    if [[ -n "${BASH_SOURCE[0]:-}" && -f "${BASH_SOURCE[0]}" ]]; then
        script_source="${BASH_SOURCE[0]}"
    elif [[ -n "${0:-}" && -f "${0:-}" ]]; then
        script_source="$0"
    fi

    if [[ -n "$script_source" ]]; then
        (cd "$(dirname "$script_source")" && pwd) || pwd
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
    return 0
}

# ── Validate required commands ───────────────────────────────────────────────
validate_required_commands() {
    local missing_commands=()
    
    for cmd in php composer git; do
        if ! command -v "$cmd" &>/dev/null; then
            missing_commands+=("$cmd")
        fi
    done
    
    if [[ ${#missing_commands[@]} -gt 0 ]]; then
        error "Missing required commands: ${missing_commands[*]}"
        error "Please install the missing dependencies and try again."
        exit 1
    fi
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

validate_required_commands

# ── Step 0: Initial Change Detection ─────────────────────────────────────────
PREV_DEPLOY_FILE="storage/framework/last_successful_deploy"
INITIAL_GIT_HASH=$(git rev-parse HEAD 2>/dev/null || echo "no-git")

if [[ "$WEBHOOK_TRIGGER" == "true" && -f "$PREV_DEPLOY_FILE" && "$FORCE" == "false" ]]; then
    LAST_SUCCESSFUL_HASH=$(cut -d'|' -f1 "$PREV_DEPLOY_FILE" 2>/dev/null || echo "")
    if [[ "$INITIAL_GIT_HASH" == "$LAST_SUCCESSFUL_HASH" ]]; then
        info "Webhook triggered, checking remote for actual changes..."
        git fetch origin >/dev/null 2>&1 || true
        REMOTE_HASH=$(git rev-parse origin/main 2>/dev/null || echo "")
        
        if [[ "$INITIAL_GIT_HASH" == "$REMOTE_HASH" ]]; then
            success "No remote changes detected. System is already at hash $REMOTE_HASH. Terminating to avoid loop."
            exit 0
        fi
    fi
fi

# ── Ensure APP_KEY is set ───────────────────────────────────────────────────
info "Checking application encryption key..."
APP_KEY_EXISTS=false

if [[ -f .env ]]; then
    APP_KEY_LINE=$(grep '^APP_KEY=' .env 2>/dev/null || echo "")
    if [[ -n "$APP_KEY_LINE" ]]; then
        APP_KEY_VALUE=$(echo "$APP_KEY_LINE" | cut -d'=' -f2- | sed 's/^"//' | sed 's/"$//')
        if [[ -n "$APP_KEY_VALUE" ]]; then
            APP_KEY_EXISTS=true
        fi
    fi
fi

if [[ "$APP_KEY_EXISTS" == "false" ]]; then
    warn "APP_KEY not found or empty in .env file. Generating new key..."
    if ! run php artisan key:generate; then
        error "Failed to generate APP_KEY. Please set APP_KEY manually in .env file."
        exit 1
    fi
    success "Application encryption key generated."
else
    success "Application encryption key is set."
fi

# Ensure storage/framework directory exists
mkdir -p storage/framework

# ── Lock mechanism ───────────────────────────────────────────────────────────
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

if [[ -f "$INSTALL_FLAG" ]]; then
    FLAG_DATA=$(cat "$INSTALL_FLAG" 2>/dev/null || echo "")
    FLAG_PID=$(echo "$FLAG_DATA" | cut -d'|' -f1 || echo "")
    FLAG_TIME=$(echo "$FLAG_DATA" | cut -d'|' -f2 || echo "0")
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

# ── Cleanup and error handler ────────────────────────────────────────────────
cleanup_and_exit() {
    local exit_code=$?
    
    # Kill timeout process safely
    if [[ -n "$TIMEOUT_PID" && "$TIMEOUT_PID" != "" ]]; then
        kill "$TIMEOUT_PID" 2>/dev/null || true
        wait "$TIMEOUT_PID" 2>/dev/null || true
    fi

    # Remove locks
    rm -f "$LOCK_FILE" "$INSTALL_FLAG"

    # CRITICAL: Restore site if maintenance mode was enabled and something failed
    # OR if --up was explicitly passed (IGNORE_MAINTENANCE), ensuring site is always brought up
    if [[ ("$MAINTENANCE_WAS_ENABLED" == "true" && "$exit_code" -ne 0) || "$IGNORE_MAINTENANCE" == "true" ]]; then
        if [[ "$exit_code" -ne 0 ]]; then
            warn "Deploy failed (exit code: $exit_code)! Restoring site to LIVE status..."
        else
             info "Ensuring site is LIVE (--up flag active)..."
        fi
        
        if run php artisan up 2>/dev/null; then
            success "Site restored to live."
        else
            error "WARNING: Could not restore site! Manual intervention may be needed."
        fi
    fi

    # Send notification
    send_notification "$exit_code"
}

# Single unified trap
trap cleanup_and_exit EXIT

# ── Timeout mechanism ────────────────────────────────────────────────────────
TIMEOUT=600  # 10 minutes max runtime
(
    sleep "$TIMEOUT"
    if kill -0 $$ 2>/dev/null; then
        # We need to write to the original stderr if possible, or just kill
        # Since we redirected output, we can't easily echo to user unless we kept a descriptor
        kill -9 $$ 2>/dev/null || true
    fi
) > /dev/null 2>&1 &
TIMEOUT_PID=$!

# ── Load notification settings ───────────────────────────────────────────────
DISCORD_WEBHOOK=$(grep "^DISCORD_WEBHOOK=" .env 2>/dev/null | cut -d '=' -f 2- | tr -d '"' || echo "")
NTFY_URL=$(grep "^NTFY_URL=" .env 2>/dev/null | cut -d '=' -f 2- | tr -d '"' || echo "")
NOTIFY_DISCORD=$(grep "^NOTIFY_DISCORD=" .env 2>/dev/null | cut -d '=' -f 2- | tr -d '"' || echo "")

# ── Log deployment trigger source ────────────────────────────────────────────
if [[ "$WEBHOOK_TRIGGER" == "true" ]]; then
    info "Deployment triggered by GitHub webhook"
fi

# ── Step 0.5: Backup database before deployment ──────────────────────────────
info "Creating database backup before deployment..."
BACKUP_DIR="storage/backups"
mkdir -p "$BACKUP_DIR"

BACKUP_TIMESTAMP=$(date '+%Y%m%d_%H%M%S')
BACKUP_FILE="$BACKUP_DIR/backup_${BACKUP_TIMESTAMP}.sql"

if [[ "${DB_CONNECTION:-}" == "sqlite" ]]; then
    # SQLite backup (just copy the database file)
    if [[ -f "$DB_DATABASE" ]]; then
        run cp "$DB_DATABASE" "${DB_DATABASE}.backup_${BACKUP_TIMESTAMP}"
        success "SQLite database backup created: ${DB_DATABASE}.backup_${BACKUP_TIMESTAMP}"
    fi
elif [[ "${DB_CONNECTION:-}" == "mysql" ]]; then
    # MySQL dump
    run mysqldump --single-transaction --quick --lock-tables=false \
        -h"${DB_HOST:-localhost}" \
        -u"${DB_USERNAME:-root}" \
        -p"${DB_PASSWORD:-}" \
        "${DB_DATABASE}" > "$BACKUP_FILE"
    success "MySQL database backup created: $BACKUP_FILE"

    # Also backup callers to CSV
    info "Exporting callers data to CSV..."
    php artisan tinker << 'TINKER'
use App\Models\Caller;
$callers = Caller::all();
$file = fopen("storage/backups/callers_backup_${BACKUP_TIMESTAMP}.csv", 'w');
if ($file) {
    fputcsv($file, ['ID', 'CPR', 'Phone', 'Name', 'Hits', 'Status', 'Is Winner', 'IP Address', 'Created At', 'Updated At']);
    foreach ($callers as $caller) {
        fputcsv($file, [
            $caller->id,
            $caller->cpr,
            $caller->phone,
            $caller->name,
            $caller->hits,
            $caller->status,
            $caller->is_winner ? 'Yes' : 'No',
            $caller->ip_address,
            $caller->created_at,
            $caller->updated_at
        ]);
    }
    fclose($file);
    echo "Callers data exported to CSV\n";
}
TINKER
    success "Callers CSV backup created"
else
    # Generic database export via artisan
    run php artisan db:show || warn "Could not verify database connection"
fi

info "Backups stored in: $BACKUP_DIR"

# ── Step 1: Maintenance mode ─────────────────────────────────────────────────
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
    run php artisan down --retry=60 --render="down" || true
    MAINTENANCE_WAS_ENABLED=true
else
    NOTIFY_ENABLED="false"
    info "Skipping maintenance mode (handled by publish.sh)."
    MAINTENANCE_WAS_ENABLED=false
fi

# ── Step 2: Pull latest code ────────────────────────────────────────────────
if [[ -d .git ]]; then
    info "Current Hash: $INITIAL_GIT_HASH. Pulling latest changes..."

    run git fetch origin
    CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "unknown")
    
    run git reset --hard origin/"$CURRENT_BRANCH"
    success "Codebase synced with remote/$CURRENT_BRANCH."

    POST_PULL_HASH=$(git rev-parse HEAD 2>/dev/null || echo "no-git")
    if [[ -f "$PREV_DEPLOY_FILE" && "$FORCE" == "false" && "$FRESH" == "false" ]]; then
        LAST_SUCCESSFUL_HASH=$(cut -d'|' -f1 "$PREV_DEPLOY_FILE" 2>/dev/null || echo "")
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
    IFS='|' read -r LAST_HASH LAST_VERSION < "$PREV_DEPLOY_FILE" || LAST_HASH=""
else
    LAST_HASH=""
fi

COMPOSER_CHANGED=true
FRONTEND_CHANGED=true
MIGRATIONS_CHANGED=true

if [[ "$FORCE" == "true" || "$FRESH" == "true" ]]; then
    info "Force deployment requested — skipping optimizations."
elif [[ -z "$LAST_HASH" || "$LAST_HASH" == "no-git" ]]; then
    info "No previous deployment record found."
else
    CHANGES=$(git diff --name-only "$LAST_HASH" "${POST_PULL_HASH:-$CURRENT_GIT_HASH}" 2>/dev/null || echo "ALL")
    
    if [[ "$CHANGES" != "ALL" ]]; then
        if ! echo "$CHANGES" | grep -qE "^(composer\.json|composer\.lock)$"; then
            COMPOSER_CHANGED=false
            info "No Composer changes detected."
        fi
        
        if ! echo "$CHANGES" | grep -qE "(package\.json|pnpm-lock\.yaml|package-lock\.json|vite\.config\.js|resources/|public/)"; then
            FRONTEND_CHANGED=false
            info "No Frontend changes detected."
        fi
        
        if ! echo "$CHANGES" | grep -q "^database/migrations/"; then
            MIGRATIONS_CHANGED=false
            info "No migration changes detected."
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

# ── Step 4: Install & build frontend assets ────────────────────────────────
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
    info "Skipping frontend build (--no-build flag set)."
fi

# ── Step 5: Database migrations ─────────────────────────────────────────────
if [[ "$MIGRATIONS_CHANGED" == "true" || "$FRESH" == "true" || "$RESET_DB" == "true" ]]; then
    info "Running database migrations..."

    if [[ "$FRESH" == "true" ]]; then
        warn "Running migrate:fresh with seeding — this will DROP all tables!"
        run php artisan migrate:fresh --force --seed
        success "Fresh migration completed with seeding."
    elif [[ "$RESET_DB" == "true" ]]; then
        warn "Running migrate:fresh — this will DROP all tables and reset database structure!"
        run php artisan migrate:fresh --force
        success "Database reset completed (structure only, no seeding)."
    else
        run php artisan migrate --force
        success "Migrations applied."
    fi
else
    info "Skipping database migrations (no changes detected)."
fi

# Always check seeding if requested
if [[ "$SEED" == "true" && "$FRESH" == "false" && "$RESET_DB" == "false" ]]; then
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
    if PENDING=$(php artisan migrate:status 2>&1 | grep -c "Pending" || echo "0"); then
        if [[ "$PENDING" -gt 0 ]]; then
            error "$PENDING migration(s) still pending! Check migration errors above."
            warn "Attempting to bring site back online before exit..."
            php artisan up 2>/dev/null || warn "Could not execute 'php artisan up'"
            exit 1
        fi
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
else
    # Force refresh of storage link to be safe
    info "Refreshing storage symlink..."
    rm public/storage
    run php artisan storage:link
    success "Storage symlink refreshed."
fi

# ── Step 11: Queue restart ───────────────────────────────────────────────────
info "Restarting queue workers..."
run php artisan queue:restart || true
success "Queue restart signal sent."

# ── Step 11b: Final Ownership Fix (Crucial for root deployment) ──────
if [[ "$(id -u)" == "0" ]]; then
   info "Ensuring $APP_USER owns all files in $APP_DIR..."
   chown -R "$APP_USER:$APP_USER" "$APP_DIR"
   chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
   success "Ownership fixed."
fi

info "Skipping recursive ownership change (prevents HTTP 403 errors)"

# ── Step 11c: Update deployment record ───────────────────────────────────────
if [[ "$DRY_RUN" == "false" ]]; then
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

    local individual_status=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "$app_url" 2>/dev/null || echo "000")
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

IS_LOCAL_EXECUTION=false
if [[ -n "${PUBLISH_VERSION:-}" ]] || [[ -n "${SSH_CLIENT:-}" ]] || [[ -n "${SSH_TTY:-}" ]]; then
    IS_LOCAL_EXECUTION=true
    info "Detected remote execution (called from local machine)"
fi

# ── Step 12b: Log hits counter data to daily CSV ─────────────────────────────
if [[ "$DRY_RUN" == "false" ]]; then
    info "Logging hit counters to daily CSV..."

    LOG_DIR="storage/logs/hits"
    mkdir -p "$LOG_DIR"

    TODAY=$(date '+%Y-%m-%d')
    DAILY_LOG="$LOG_DIR/hits_${TODAY}.csv"

    # Create header if file doesn't exist
    if [[ ! -f "$DAILY_LOG" ]]; then
        echo "timestamp,caller_id,cpr,name,phone,hits,status,ip_address" > "$DAILY_LOG"
    fi

    # Export all callers with their hit counts
    php artisan tinker << TINKER 2>/dev/null || true
use App\Models\Caller;
use Carbon\Carbon;
\$callers = Caller::all();
\$logFile = "$DAILY_LOG";
\$handle = fopen(\$logFile, 'a');
if (\$handle) {
    \$timestamp = Carbon::now()->toDateTimeString();
    foreach (\$callers as \$caller) {
        \$line = "\$timestamp," . implode(',', [
            \$caller->id,
            \$caller->cpr,
            \$caller->name,
            \$caller->phone,
            \$caller->hits,
            \$caller->status,
            \$caller->ip_address
        ]);
        fputcsv(\$handle, explode(',', \$line));
    }
    fclose(\$handle);
}
TINKER

    success "Hit counters logged to: $DAILY_LOG"
    info "Daily logs stored in: $LOG_DIR"
    info "Format: CSV with columns [timestamp, caller_id, cpr, name, phone, hits, status, ip_address]"
fi

# ── Step 13: Disable maintenance mode ────────────────────────────────────────
if [[ -z "${PUBLISH_VERSION:-}" ]]; then
    info "Disabling maintenance mode..."
    run php artisan up
    success "Application is live."
    MAINTENANCE_WAS_ENABLED=false

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

[[ "$SEED" == "true" ]] && info "Seeders executed: UserSeeder, CallerSeeder"
[[ "$FRESH" == "true" ]] && warn "Database was freshly rebuilt with seeding (all previous data dropped)."
[[ "$RESET_DB" == "true" ]] && warn "Database was reset (all tables dropped and recreated - no seeding applied)."
echo ""
success "Deploy finished at $(date '+%Y-%m-%d %H:%M:%S')"
