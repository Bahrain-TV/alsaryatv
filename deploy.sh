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
#   ./deploy.sh --diagnose   # Run production diagnostics (check images, config, etc)
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
    local_ok()    { echo -e "\033[0;32m[  OK ]\033[0m  $*"; }
    local_fail()  { echo -e "\033[0;31m[FAIL]\033[0m  $*"; }
    local_warn()  { echo -e "\033[1;33m[WARN]\033[0m  $*"; }
    
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

    # ── DIAGNOSE MODE (local → remote) ───────────────────────────────────
    # If --diagnose was passed, run diagnostics on production and exit
    for arg in "$@"; do
        if [[ "$arg" == "--diagnose" ]]; then
            echo ""
            local_info "🔍 Running Production Diagnostics..."
            echo ""

            ssh -p "$PROD_SSH_PORT" -i "$SSH_KEY_PATH" "$PROD_SSH_USER@$PROD_SSH_HOST" bash << 'DIAG_SCRIPT'
cd /home/alsarya.tv/public_html

C='\033[0;36m'  # Cyan
G='\033[0;32m'  # Green
R='\033[0;31m'  # Red
Y='\033[1;33m'  # Yellow
N='\033[0m'     # Reset

ok()   { echo -e "${G}[  OK ]${N}  $*"; }
fail() { echo -e "${R}[FAIL]${N}  $*"; }
warn() { echo -e "${Y}[WARN]${N}  $*"; }
hdr()  { echo -e "\n${C}━━━ $* ━━━${N}"; }

ISSUES=0

# ─── 1. .env Configuration ──────────────────────────────
hdr "1. .env Configuration"

APP_URL_VAL=$(grep '^APP_URL=' .env | cut -d= -f2- | tr -d '"')
APP_ENV_VAL=$(grep '^APP_ENV=' .env | cut -d= -f2- | tr -d '"')
APP_DEBUG_VAL=$(grep '^APP_DEBUG=' .env | cut -d= -f2- | tr -d '"')
FS_DISK_VAL=$(grep '^FILESYSTEM_DISK=' .env | cut -d= -f2- | tr -d '"')
ASSET_URL_VAL=$(grep '^ASSET_URL=' .env | cut -d= -f2- | tr -d '"')

if [[ "$APP_URL_VAL" == *"localhost"* ]]; then
    fail "APP_URL = $APP_URL_VAL  ← WRONG! Points to localhost"
    ISSUES=$((ISSUES+1))
else
    ok "APP_URL = $APP_URL_VAL"
fi

if [[ "$APP_ENV_VAL" != "production" ]]; then
    fail "APP_ENV = $APP_ENV_VAL  ← Should be 'production'"
    ISSUES=$((ISSUES+1))
else
    ok "APP_ENV = $APP_ENV_VAL"
fi

if [[ "$APP_DEBUG_VAL" == "true" ]]; then
    warn "APP_DEBUG = true  ← Should be 'false' in production"
else
    ok "APP_DEBUG = $APP_DEBUG_VAL"
fi

if [[ "$FS_DISK_VAL" == "public" ]]; then
    ok "FILESYSTEM_DISK = $FS_DISK_VAL"
else
    warn "FILESYSTEM_DISK = $FS_DISK_VAL  ← Consider 'public' for uploaded files"
fi

if [[ -n "$ASSET_URL_VAL" ]]; then
    warn "ASSET_URL = $ASSET_URL_VAL (overrides asset() helper)"
else
    ok "ASSET_URL not set (asset() uses APP_URL — correct)"
fi

# ─── 2. Cached Config vs Live Config ────────────────────
hdr "2. Cached Config vs .env"

if [[ -f bootstrap/cache/config.php ]]; then
    CACHED_URL=$(php -r "echo (include 'bootstrap/cache/config.php')['app']['url'] ?? 'N/A';" 2>/dev/null)
    if [[ "$CACHED_URL" != "$APP_URL_VAL" ]]; then
        fail "CACHED APP_URL ($CACHED_URL) ≠ .env APP_URL ($APP_URL_VAL)"
        fail "Fix: php artisan config:cache"
        ISSUES=$((ISSUES+1))
    else
        ok "Cached config matches .env (APP_URL = $CACHED_URL)"
    fi
    
    CACHED_ENV=$(php -r "echo (include 'bootstrap/cache/config.php')['app']['env'] ?? 'N/A';" 2>/dev/null)
    if [[ "$CACHED_ENV" != "$APP_ENV_VAL" ]]; then
        fail "CACHED APP_ENV ($CACHED_ENV) ≠ .env APP_ENV ($APP_ENV_VAL)"
        ISSUES=$((ISSUES+1))
    fi
else
    warn "No cached config (bootstrap/cache/config.php missing)"
fi

# ─── 3. Image Files ─────────────────────────────────────
hdr "3. Image Files on Disk"

for img in "public/images/alsarya-logo-2026-1.png" "public/images/alsarya-logo.png"; do
    if [[ -f "$img" ]]; then
        SIZE=$(stat -c%s "$img" 2>/dev/null || stat -f%z "$img" 2>/dev/null)
        OWNER=$(stat -c'%U:%G' "$img" 2>/dev/null || stat -f'%Su:%Sg' "$img" 2>/dev/null)
        PERMS=$(stat -c'%a' "$img" 2>/dev/null || stat -f'%Lp' "$img" 2>/dev/null)
        ok "$img (${SIZE} bytes, $OWNER, mode $PERMS)"
    else
        fail "$img — FILE MISSING!"
        ISSUES=$((ISSUES+1))
    fi
done

# ─── 4. Storage Symlink ─────────────────────────────────
hdr "4. Storage Symlink"

if [[ -L "public/storage" ]]; then
    TARGET=$(readlink public/storage)
    if [[ -d "$TARGET" ]]; then
        ok "public/storage → $TARGET (valid symlink)"
    else
        fail "public/storage → $TARGET (BROKEN — target doesn't exist!)"
        ISSUES=$((ISSUES+1))
    fi
else
    fail "public/storage symlink MISSING! Run: php artisan storage:link"
    ISSUES=$((ISSUES+1))
fi

# ─── 5. Web Server Response ─────────────────────────────
hdr "5. Web Server Response (from server itself)"

for path in "/" "/images/alsarya-logo-2026-1.png"; do
    STATUS=$(curl -sI -o /dev/null -w "%{http_code}" --max-time 5 "${APP_URL_VAL}${path}" 2>/dev/null || echo "000")
    if [[ "$STATUS" == "200" || "$STATUS" == "302" ]]; then
        ok "GET ${path} → HTTP $STATUS"
    elif [[ "$STATUS" == "000" ]]; then
        fail "GET ${path} → UNREACHABLE (connection failed)"
        ISSUES=$((ISSUES+1))
    else
        fail "GET ${path} → HTTP $STATUS"
        ISSUES=$((ISSUES+1))
    fi
done

# Check cache-control headers on images (browser caching issue?)
CACHE_HDR=$(curl -sI --max-time 5 "${APP_URL_VAL}/images/alsarya-logo-2026-1.png" 2>/dev/null | grep -i "cache-control" | tr -d '\r')
if [[ -n "$CACHE_HDR" ]]; then
    if echo "$CACHE_HDR" | grep -qi "max-age=[1-9]"; then
        warn "Image caching: $CACHE_HDR"
        warn "→ Browser may show stale images. Add ?v=timestamp to bust cache"
    else
        ok "Image caching: $CACHE_HDR"
    fi
fi

ETAG_HDR=$(curl -sI --max-time 5 "${APP_URL_VAL}/images/alsarya-logo-2026-1.png" 2>/dev/null | grep -i "etag" | tr -d '\r')
if [[ -n "$ETAG_HDR" ]]; then
    ok "ETag header present: $ETAG_HDR"
fi

# ─── 6. Blade Template References ───────────────────────
hdr "6. Blade Template Image References"

BLADE_REFS=$(grep -rn "alsarya-logo" resources/views/ 2>/dev/null)
if [[ -n "$BLADE_REFS" ]]; then
    echo "$BLADE_REFS" | while read -r line; do
        if echo "$line" | grep -q "alsarya-logo-2026-1"; then
            ok "$line"
        else
            warn "$line  ← NOT using 2026 logo?"
        fi
    done
else
    warn "No logo references found in blade templates"
fi

# ─── 7. View Cache Age ──────────────────────────────────
hdr "7. View Cache"

VIEW_COUNT=$(ls storage/framework/views/*.php 2>/dev/null | wc -l)
if [[ "$VIEW_COUNT" -gt 0 ]]; then
    OLDEST=$(ls -t storage/framework/views/*.php | tail -1)
    OLDEST_DATE=$(stat -c'%Y' "$OLDEST" 2>/dev/null || stat -f'%m' "$OLDEST" 2>/dev/null)
    NOW=$(date +%s)
    AGE_HOURS=$(( (NOW - OLDEST_DATE) / 3600 ))
    if [[ "$AGE_HOURS" -gt 24 ]]; then
        warn "$VIEW_COUNT cached views (oldest is ${AGE_HOURS}h old — might be stale)"
        warn "Fix: php artisan view:clear && php artisan view:cache"
    else
        ok "$VIEW_COUNT cached views (oldest is ${AGE_HOURS}h old)"
    fi
else
    ok "No cached views (Blade compiles on-the-fly)"
fi

# ─── 8. Permissions ─────────────────────────────────────
hdr "8. Key Directory Permissions"

for dir in "storage" "bootstrap/cache" "public/images"; do
    if [[ -d "$dir" ]]; then
        OWNER=$(stat -c'%U:%G' "$dir" 2>/dev/null || stat -f'%Su:%Sg' "$dir" 2>/dev/null)
        PERMS=$(stat -c'%a' "$dir" 2>/dev/null || stat -f'%Lp' "$dir" 2>/dev/null)
        if [[ "$OWNER" == *"alsar4210"* ]]; then
            ok "$dir/ ($OWNER, mode $PERMS)"
        else
            fail "$dir/ owned by $OWNER — should be alsar4210"
            ISSUES=$((ISSUES+1))
        fi
    fi
done

# ─── Summary ────────────────────────────────────────────
hdr "SUMMARY"

if [[ "$ISSUES" -eq 0 ]]; then
    echo -e "${G}✅ All checks passed! No issues found.${N}"
else
    echo -e "${R}❌ Found $ISSUES issue(s) that need attention.${N}"
fi
echo ""
DIAG_SCRIPT

            local_info "Diagnostics complete."

            # Now compare local vs remote images
            echo ""
            local_info "Comparing local vs remote image checksums..."
            
            LOCAL_HASH=$(md5 -q public/images/alsarya-logo-2026-1.png 2>/dev/null || echo "MISSING")
            REMOTE_HASH=$(ssh -p "$PROD_SSH_PORT" -i "$SSH_KEY_PATH" "$PROD_SSH_USER@$PROD_SSH_HOST" "md5sum /home/alsarya.tv/public_html/public/images/alsarya-logo-2026-1.png 2>/dev/null | cut -d' ' -f1" || echo "MISSING")
            
            if [[ "$LOCAL_HASH" == "$REMOTE_HASH" ]]; then
                local_ok "Logo checksums match: $LOCAL_HASH"
            else
                local_fail "Logo checksums DIFFER! Local=$LOCAL_HASH Remote=$REMOTE_HASH"
            fi

            # Check local .env vs remote .env for dangerous differences
            echo ""
            local_info "Checking .env differences (critical keys only)..."
            for key in APP_URL APP_ENV APP_DEBUG FILESYSTEM_DISK; do
                L_VAL=$(grep "^${key}=" .env 2>/dev/null | cut -d= -f2- | tr -d '"')
                R_VAL=$(ssh -p "$PROD_SSH_PORT" -i "$SSH_KEY_PATH" "$PROD_SSH_USER@$PROD_SSH_HOST" "grep '^${key}=' /home/alsarya.tv/public_html/.env 2>/dev/null | cut -d= -f2- | tr -d '\"'")
                if [[ "$L_VAL" == "$R_VAL" ]]; then
                    local_warn "$key: local=$L_VAL == remote=$R_VAL (SAME — might be wrong for prod!)"
                else
                    local_ok "$key: local=$L_VAL | remote=$R_VAL (different — expected)"
                fi
            done

            exit 0
        fi
    done

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
    
    # Sync .env - DISABLED TO PREVENT OVERWRITING PROD CONFIG
    # if [[ -f .env ]]; then
    #    rsync -avzc --no-o --no-g -e "$RSYNC_SSH" ".env" "$PROD_SSH_USER@$PROD_SSH_HOST:$PROD_APP_DIR/.env" >/dev/null || local_error ".env sync failed"
    # fi

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

    # Non-interactive execution (avoid TTY deadlock when piped)
    # Use shell redirection to prevent stdin from being consumed
    REMOTE_CMD="cd '$PROD_APP_DIR' && bash ./deploy.sh"
    for arg in "$@"; do
        REMOTE_CMD="$REMOTE_CMD $(printf '%q' "$arg")"
    done

    # Execute without -t flag to avoid TTY conflicts when piped via stdin
    ssh -p "$PROD_SSH_PORT" -i "$SSH_KEY_PATH" "$PROD_SSH_USER@$PROD_SSH_HOST" "$REMOTE_CMD" < /dev/null

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
DIAGNOSE=false
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
        --diagnose)  DIAGNOSE=true ;;
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

# ── Recovery from stuck deployment ───────────────────────────────────────────
# If a deployment got interrupted, the site might be stuck in maintenance mode
# This section attempts to recover gracefully
if [[ -f "storage/framework/down" ]]; then
    MAINT_AGE=$(($(date +%s) - $(stat -f%m "storage/framework/down" 2>/dev/null || stat -c%Y "storage/framework/down" 2>/dev/null || echo 0)))
    if [[ $MAINT_AGE -gt 3600 ]]; then
        # Maintenance mode file is older than 1 hour - likely from a stuck deployment
        warn "Detected maintenance mode lock older than 1 hour (likely from stuck deployment)."
        warn "Attempting recovery: removing stale down state and bringing site online..."
        rm -f "storage/framework/down"
        php artisan up 2>/dev/null || warn "Could not bring site up immediately"
        sleep 2
    fi
fi

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

        # Attempt to bring site up with retries
        for attempt in 1 2 3; do
            if php artisan up 2>/dev/null; then
                success "Site restored to live."
                break
            elif [[ $attempt -lt 3 ]]; then
                warn "Attempt $attempt failed, retrying..."
                sleep 2
            else
                error "CRITICAL: Could not restore site after 3 attempts! Manual intervention required."
                error "Run: php artisan up"
            fi
        done
    fi

    # Send notification
    send_notification "$exit_code"
}

# Single unified trap
trap cleanup_and_exit EXIT

# ── Timeout mechanism ────────────────────────────────────────────────────────
# DISABLED: Timeout was causing premature kills during long operations,
# leaving the site in maintenance mode. Deployments should complete naturally.
# If needed, configure timeouts at the CI/CD platform level instead.
TIMEOUT_PID=""
# To re-enable, use a longer duration (e.g., 1800 seconds = 30 min):
# TIMEOUT=1800
# (
#     sleep "$TIMEOUT"
#     if kill -0 $$ 2>/dev/null; then
#         kill -TERM $$ 2>/dev/null || true  # Use SIGTERM, not SIGKILL
#     fi
# ) > /dev/null 2>&1 &
# TIMEOUT_PID=$!

# ── Load notification settings ───────────────────────────────────────────────
DISCORD_WEBHOOK=$(grep "^DISCORD_WEBHOOK=" .env 2>/dev/null | cut -d '=' -f 2- | tr -d '"' || echo "")
NTFY_URL=$(grep "^NTFY_URL=" .env 2>/dev/null | cut -d '=' -f 2- | tr -d '"' || echo "")
NOTIFY_DISCORD=$(grep "^NOTIFY_DISCORD=" .env 2>/dev/null | cut -d '=' -f 2- | tr -d '"' || echo "")

# ── Log deployment trigger source ────────────────────────────────────────────
if [[ "$WEBHOOK_TRIGGER" == "true" ]]; then
    info "Deployment triggered by GitHub webhook"
fi

# ── Step 0.5: Backup database + callers before deployment ───────────────────
info "Creating database backup before deployment..."
BACKUP_DIR="storage/backups"
mkdir -p "$BACKUP_DIR"

BACKUP_TIMESTAMP=$(date '+%Y%m%d_%H%M%S')
BACKUP_FILE="$BACKUP_DIR/backup_${BACKUP_TIMESTAMP}.sql"
CALLERS_BACKUP_FILE="$BACKUP_DIR/callers_backup_${BACKUP_TIMESTAMP}.csv"

if [[ "${DB_CONNECTION:-}" == "sqlite" ]]; then
    # SQLite backup (copy database file)
    if [[ -f "$DB_DATABASE" ]]; then
        SQLITE_BACKUP_FILE="$BACKUP_DIR/sqlite_backup_${BACKUP_TIMESTAMP}.sqlite"
        run cp "$DB_DATABASE" "$SQLITE_BACKUP_FILE"
        success "SQLite database backup created: $SQLITE_BACKUP_FILE"
    else
        warn "SQLite database file not found: ${DB_DATABASE:-unset}"
    fi
elif [[ "${DB_CONNECTION:-}" == "mysql" ]]; then
    # MySQL dump
    run mysqldump --single-transaction --quick --lock-tables=false \
        -h"${DB_HOST:-localhost}" \
        -u"${DB_USERNAME:-root}" \
        -p"${DB_PASSWORD:-}" \
        "${DB_DATABASE}" > "$BACKUP_FILE"
    success "MySQL database backup created: $BACKUP_FILE"
else
    # Generic database check for unsupported drivers
    run php artisan db:show || warn "Could not verify database connection"
fi

# Always backup callers data to CSV (regardless of DB driver)
info "Exporting callers data to CSV..."
if [[ "$DRY_RUN" == "true" ]]; then
    echo -e "${YELLOW}[DRY-RUN]${NC} php artisan tinker (export callers to $CALLERS_BACKUP_FILE)"
else
    export CALLERS_BACKUP_FILE
    php artisan tinker << 'TINKER'
use App\Models\Caller;

$filePath = getenv('CALLERS_BACKUP_FILE') ?: 'storage/backups/callers_backup.csv';
$callers = Caller::all();
$file = fopen($filePath, 'w');

if (! $file) {
    throw new RuntimeException("Unable to create callers backup file at {$filePath}");
}

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
        $caller->updated_at,
    ]);
}

fclose($file);
echo "Callers data exported to CSV: {$filePath}\n";
TINKER
    unset CALLERS_BACKUP_FILE
fi
success "Callers CSV backup created: $CALLERS_BACKUP_FILE"

# Keep only last 5 callers CSV backups
if [[ "$DRY_RUN" == "true" ]]; then
    echo -e "${YELLOW}[DRY-RUN]${NC} rotate callers backups in $BACKUP_DIR (keep latest 5)"
else
    mapfile -t CALLERS_BACKUPS < <(ls -1t "$BACKUP_DIR"/callers_backup_*.csv 2>/dev/null || true)
    if [[ ${#CALLERS_BACKUPS[@]} -gt 5 ]]; then
        for old_backup in "${CALLERS_BACKUPS[@]:5}"; do
            rm -f "$old_backup"
            info "Removed old callers backup: $old_backup"
        done
    fi
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
    # Put site down with a longer retry and secret passphrase for testing
    run php artisan down --retry=120 --render="down" --secret="deploy-$(date +%s)" || true
    MAINTENANCE_WAS_ENABLED=true
    sleep 2  # Give nginx/php time to recognize the down state
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
else
   info "Skipping recursive ownership change (not running as root)"
fi

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

    if [[ "$IS_LOCAL_EXECUTION" == "true" && "$DRY_RUN" == "false" ]]; then
        echo ""
        if ! check_production_health; then
            error "Health check failed! Site is live but routes may not be working correctly."
            error "Please investigate immediately."
            # Keep MAINTENANCE_WAS_ENABLED=true so cleanup can restore if needed
            exit 1
        fi
    fi
    # Only set flag to false after health check passes
    MAINTENANCE_WAS_ENABLED=false
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
