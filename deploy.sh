#!/usr/bin/env bash
###############################################################################
# deploy.sh — AlSarya TV Show Registration System (ENHANCED VERSION)
#
# Production deployment script with enhanced image handling, cache busting,
# asset optimization, and comprehensive deployment verification.
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
#   ./deploy.sh --sync-images # Force sync all images to production
#   ./deploy.sh --optimize-images # Optimize images during deployment
###############################################################################

# Configuration
APP_USER="alsar4210"
SUDO_PREFIX="sudo -u $APP_USER"

set -eo pipefail

# ── Local Execution Wrapper ──────────────────────────────────────────────────
if [[ "$(uname -s)" == "Darwin" ]]; then
    local_info()  { echo -e "\033[0;36m[LOCAL]\033[0m  $*"; }
    local_error() { echo -e "\033[0;31m[ERROR]\033[0m $*"; }
    local_ok()    { echo -e "\033[0;32m[  OK ]\033[0m  $*"; }
    local_fail()  { echo -e "\033[0;31m[FAIL]\033[0m  $*"; }
    local_warn()  { echo -e "\033[1;33m[WARN]\033[0m  $*"; }
    local_success() { echo -e "\033[0;32m[SUCCESS]\033[0m $*"; }

    local_cleanup() { true; }
    trap local_cleanup EXIT INT TERM

    echo "----------------------------------------------------------------"
    echo "  🚀 AlSarya TV Deployment Launcher (ENHANCED)"
    echo "  Detected Local Environment (macOS)"
    echo "----------------------------------------------------------------"

    # Load .env for config
    if [[ -f .env ]]; then
        set -a
        [ -f .env ] && . .env
        set +a
    fi

    # Set defaults with validation
    PROD_SSH_USER="${PROD_SSH_USER:-root}"
    PROD_SSH_HOST="${PROD_SSH_HOST:-alsarya.tv}"
    PROD_SSH_PORT="${PROD_SSH_PORT:-22}"
    PROD_APP_DIR="${PROD_APP_DIR:-/home/alsarya.tv/public_html}"
    SSH_KEY_PATH="${SSH_KEY_PATH:-${HOME}/.ssh/id_rsa}"

    # Validate SSH key exists
    if [[ ! -f "$SSH_KEY_PATH" ]]; then
        local_error "SSH key not found: $SSH_KEY_PATH"
        local_error "Please configure SSH_KEY_PATH in .env or ensure default key exists"
        exit 1
    fi

    # 1. ESTABLISH CONNECTION FIRST
    local_info "Testing connection to production server ($PROD_SSH_HOST)..."
    if ! ssh -q -p "$PROD_SSH_PORT" -i "$SSH_KEY_PATH" -o BatchMode=yes -o ConnectTimeout=10 "$PROD_SSH_USER@$PROD_SSH_HOST" "echo '✓ Connection Established'" 2>/dev/null; then
        local_error "Could not connect to $PROD_SSH_USER@$PROD_SSH_HOST"
        local_error "Please check your VPN, internet connection, or SSH keys."
        exit 1
    fi
    local_ok "SSH connection established to $PROD_SSH_HOST"

    # ── DIAGNOSE MODE ───────────────────────────────────────────────────────
    for arg in "$@"; do
        if [[ "$arg" == "--diagnose" ]]; then
            echo ""
            local_info "🔍 Running Production Diagnostics..."
            echo ""

            ssh -p "$PROD_SSH_PORT" -i "$SSH_KEY_PATH" "$PROD_SSH_USER@$PROD_SSH_HOST" bash << 'DIAG_SCRIPT'
cd /home/alsarya.tv/public_html

C='\033[0;36m'
G='\033[0;32m'
R='\033[0;31m'
Y='\033[1;33m'
N='\033[0m'

ok()   { echo -e "${G}[  OK ]${N}  $*"; }
fail() { echo -e "${R}[FAIL]${N}  $*"; }
warn() { echo -e "${Y}[WARN]${N}  $*"; }
hdr()  { echo -e "\n${C}━━━ $* ━━━${N}"; }

ISSUES=0

# ─── 1. .env Configuration ──────────────────────────────
hdr "1. .env Configuration"

APP_URL_VAL=$(grep '^APP_URL=' .env | cut -d= -f2- | tr -d '"' || echo "")
APP_ENV_VAL=$(grep '^APP_ENV=' .env | cut -d= -f2- | tr -d '"' || echo "")
APP_DEBUG_VAL=$(grep '^APP_DEBUG=' .env | cut -d= -f2- | tr -d '"' || echo "")
FS_DISK_VAL=$(grep '^FILESYSTEM_DISK=' .env | cut -d= -f2- | tr -d '"' || echo "")

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

# ─── 2. Cached Config vs Live Config ────────────────────
hdr "2. Cached Config vs .env"

if [[ -f bootstrap/cache/config.php ]]; then
    CACHED_URL=$(php -r "echo (include 'bootstrap/cache/config.php')['app']['url'] ?? 'N/A';" 2>/dev/null || echo "N/A")
    if [[ "$CACHED_URL" != "$APP_URL_VAL" ]]; then
        fail "CACHED APP_URL ($CACHED_URL) ≠ .env APP_URL ($APP_URL_VAL)"
        fail "Fix: php artisan config:cache"
        ISSUES=$((ISSUES+1))
    else
        ok "Cached config matches .env (APP_URL = $CACHED_URL)"
    fi
else
    warn "No cached config (bootstrap/cache/config.php missing)"
fi

# ─── 3. Image Files ─────────────────────────────────────
hdr "3. Image Files on Disk"

IMAGE_COUNT=0
MISSING_COUNT=0

while IFS= read -r -d '' img; do
    IMAGE_COUNT=$((IMAGE_COUNT + 1))
    if [[ -f "$img" ]]; then
        SIZE=$(stat -c%s "$img" 2>/dev/null || stat -f%z "$img" 2>/dev/null || echo "?")
        OWNER=$(stat -c'%U:%G' "$img" 2>/dev/null || stat -f'%Su:%Sg' "$img" 2>/dev/null || echo "?")
        PERMS=$(stat -c'%a' "$img" 2>/dev/null || stat -f'%Lp' "$img" 2>/dev/null || echo "?")
        HASH=$(md5sum "$img" 2>/dev/null | cut -d' ' -f1 || md5 -q "$img" 2>/dev/null || echo "?")
        ok "$img"
        echo "     └─ Size: ${SIZE}B | Owner: $OWNER | Perms: $PERMS"
        echo "     └─ MD5: $HASH"
    else
        fail "$img — FILE MISSING!"
        MISSING_COUNT=$((MISSING_COUNT + 1))
        ISSUES=$((ISSUES+1))
    fi
done < <(find public/images -type f \( -name "*.png" -o -name "*.jpg" -o -name "*.jpeg" -o -name "*.svg" -o -name "*.webp" \) -print0 2>/dev/null)

if [[ $IMAGE_COUNT -eq 0 ]]; then
    warn "No image files found in public/images/"
else
    ok "Found $IMAGE_COUNT image file(s) ($MISSING_COUNT missing)"
fi

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
hdr "5. Web Server Response"

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

# Check cache-control headers
CACHE_HDR=$(curl -sI --max-time 5 "${APP_URL_VAL}/images/alsarya-logo-2026-1.png" 2>/dev/null | grep -i "cache-control" | tr -d '\r' || echo "")
if [[ -n "$CACHE_HDR" ]]; then
    if echo "$CACHE_HDR" | grep -qi "max-age=[1-9]"; then
        warn "Image caching: $CACHE_HDR"
        warn "→ Browser may show stale images. Add ?v=timestamp to bust cache"
    else
        ok "Image caching: $CACHE_HDR"
    fi
fi

# ─── 6. Blade Template References ───────────────────────
hdr "6. Blade Template Image References"

BLADE_REFS=$(grep -rn "alsarya-logo" resources/views/ 2>/dev/null || echo "")
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

# ─── 7. Permissions ─────────────────────────────────────
hdr "7. Key Directory Permissions"

for dir in "storage" "bootstrap/cache" "public/images"; do
    if [[ -d "$dir" ]]; then
        OWNER=$(stat -c'%U:%G' "$dir" 2>/dev/null || stat -f'%Su:%Sg' "$dir" 2>/dev/null || echo "?")
        PERMS=$(stat -c'%a' "$dir" 2>/dev/null || stat -f'%Lp' "$dir" 2>/dev/null || echo "?")
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

            # Compare local vs remote images
            echo ""
            local_info "📊 Comparing local vs remote images..."

            sync_images_comparison() {
                local img_path="$1"
                if [[ -f "$img_path" ]]; then
                    LOCAL_HASH=$(md5 -q "$img_path" 2>/dev/null || echo "MISSING")
                    REMOTE_HASH=$(ssh -p "$PROD_SSH_PORT" -i "$SSH_KEY_PATH" "$PROD_SSH_USER@$PROD_SSH_HOST" \
                        "md5sum $PROD_APP_DIR/$img_path 2>/dev/null | cut -d' ' -f1" || echo "MISSING")

                    if [[ "$LOCAL_HASH" == "$REMOTE_HASH" ]]; then
                        local_ok "$img_path ✓ (MD5: $LOCAL_HASH)"
                    else
                        local_fail "$img_path ✗"
                        echo "     Local:  $LOCAL_HASH"
                        echo "     Remote: $REMOTE_HASH"
                    fi
                fi
            }

            for img in public/images/*.png public/images/*.jpg public/images/*.svg; do
                [[ -f "$img" ]] && sync_images_comparison "$img"
            done

            exit 0
        fi
    done

    # ── SYNC IMAGES MODE ───────────────────────────────────────────────────
    for arg in "$@"; do
        if [[ "$arg" == "--sync-images" ]]; then
            echo ""
            local_info "🖼️  Forcing full image synchronization..."
            echo ""

            RSYNC_SSH="ssh -p $PROD_SSH_PORT -i $SSH_KEY_PATH -o StrictHostKeyChecking=accept-new"

            # Sync all image directories
            for img_dir in "public/images" "public/build"; do
                if [[ -d "$img_dir" ]]; then
                    local_info "Syncing $img_dir/..."
                    rsync -avz --delete --no-o --no-g -e "$RSYNC_SSH" "$img_dir/" "$PROD_SSH_USER@$PROD_SSH_HOST:$PROD_APP_DIR/$img_dir/" 2>&1 | while read -r line; do
                        [[ -n "$line" ]] && local_info "  $line"
                    done || local_error "Sync failed for $img_dir"
                    local_ok "$img_dir synced"
                fi
            done

            # Fix remote permissions
            local_info "Fixing remote permissions..."
            ssh -p "$PROD_SSH_PORT" -i "$SSH_KEY_PATH" "$PROD_SSH_USER@$PROD_SSH_HOST" \
                "chown -R $APP_USER:$APP_USER $PROD_APP_DIR/public/images && chmod -R 755 $PROD_APP_DIR/public/images" || local_error "Permission fix failed"

            local_success "✓ Image synchronization complete!"
            exit 0
        fi
    done

    # 2. Sync Assets (Images & Storage) - ENHANCED
    local_info "📦 Syncing Assets (Images, Storage & Build)..."

    RSYNC_SSH="ssh -p $PROD_SSH_PORT -i $SSH_KEY_PATH -o StrictHostKeyChecking=accept-new"

    # Enhanced rsync with progress and better filtering
    sync_directory() {
        local src="$1"
        local dst="$2"
        local desc="$3"

        if [[ -d "$src" ]]; then
            local_info "  → Syncing $desc..."
            rsync -avz --no-o --no-g -e "$RSYNC_SSH" \
                --exclude='.DS_Store' \
                --exclude='*.log' \
                "$src/" "$dst/" 2>&1 | grep -v "^$" || true
        fi
    }

    sync_directory "public/images" "$PROD_SSH_USER@$PROD_SSH_HOST:$PROD_APP_DIR/public/images" "Images"
    sync_directory "storage/app/public" "$PROD_SSH_USER@$PROD_SSH_HOST:$PROD_APP_DIR/storage/app/public" "Storage"

    # Sync build assets if they exist
    if [[ -d "public/build" ]]; then
        sync_directory "public/build" "$PROD_SSH_USER@$PROD_SSH_HOST:$PROD_APP_DIR/public/build" "Build Assets"
    fi

    local_ok "✓ Assets synced successfully"

    # 3. Update Remote Configuration & Script
    local_info "📝 Updating remote deployment script..."

    # Upload new deploy script atomically
    scp -q -P "$PROD_SSH_PORT" -i "$SSH_KEY_PATH" "$0" "$PROD_SSH_USER@$PROD_SSH_HOST:$PROD_APP_DIR/deploy.sh.new" || {
        local_error "Failed to upload deploy script"
        exit 1
    }

    # Fix permissions and swap script
    ssh -p "$PROD_SSH_PORT" -i "$SSH_KEY_PATH" "$PROD_SSH_USER@$PROD_SSH_HOST" << EOF
        chmod +x $PROD_APP_DIR/deploy.sh.new
        mv $PROD_APP_DIR/deploy.sh.new $PROD_APP_DIR/deploy.sh
        chown $APP_USER:$APP_USER $PROD_APP_DIR/deploy.sh
EOF
    local_ok "✓ Deployment script updated"

    # 4. Handover to Remote Server
    local_info "🔄 Handing over to production server..."
    echo ""

    REMOTE_CMD="cd '$PROD_APP_DIR' && bash ./deploy.sh"
    for arg in "$@"; do
        REMOTE_CMD="$REMOTE_CMD $(printf '%q' "$arg")"
    done

    ssh -p "$PROD_SSH_PORT" -i "$SSH_KEY_PATH" "$PROD_SSH_USER@$PROD_SSH_HOST" "$REMOTE_CMD" < /dev/null

    EXIT_CODE=$?
    echo ""
    if [ $EXIT_CODE -eq 0 ]; then
        local_success "✓ Deployment Cycle Complete!"
    else
        local_error "✗ Remote Deployment Failed (Exit Code: $EXIT_CODE)"
    fi

    exit $EXIT_CODE
fi

# ── Remote / Server Execution Logic ─────────────────────────────────────────

# ── Colours ──────────────────────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
NC='\033[0m'

# ── Deployment Logging Setup ──────────────────────────────────────────────────
DEPLOY_LOG_DIR="storage/logs/deployments"
mkdir -p "$DEPLOY_LOG_DIR"
DEPLOY_LOG="$DEPLOY_LOG_DIR/deploy_$(date '+%Y%m%d_%H%M%S').log"
DEPLOY_PERF_LOG="$DEPLOY_LOG_DIR/deploy_performance.log"

# Initialize logs
: >"$DEPLOY_LOG"  # Create empty log file
echo "====== AlSarya TV Deployment Log ======" >> "$DEPLOY_LOG"
echo "Started: $(date '+%Y-%m-%d %H:%M:%S')" >> "$DEPLOY_LOG"
echo "Hostname: $(hostname)" >> "$DEPLOY_LOG"
echo "User: $(whoami)" >> "$DEPLOY_LOG"
echo "PHP Version: $(php -v 2>&1 | head -1)" >> "$DEPLOY_LOG"
echo "==========================================" >> "$DEPLOY_LOG"
echo "" >> "$DEPLOY_LOG"

# ── Helpers ──────────────────────────────────────────────────────────────────
log() { echo "[$(date '+%H:%M:%S')] $*" >> "$DEPLOY_LOG"; }
info()    { echo -e "${CYAN}[INFO]${NC}  $*"; log "INFO: $*"; }
success() { echo -e "${GREEN}[OK]${NC}    $*"; log "SUCCESS: $*"; }
warn()    { echo -e "${YELLOW}[WARN]${NC}  $*"; log "WARN: $*"; }
error()   { echo -e "${RED}[ERROR]${NC} $*"; log "ERROR: $*"; }
step()    { echo -e "\n${MAGENTA}━━━ $* ━━━${NC}"; log "━━━ STEP: $* ━━━"; }

# Log command execution with timing
log_cmd() {
    local cmd="$*"
    local start_time=$(date '+%s.%N')
    log "Executing: $cmd"
    
    if eval "$cmd" 2>&1 | tee -a "$DEPLOY_LOG"; then
        local end_time=$(date '+%s.%N')
        local duration=$(echo "$end_time - $start_time" | bc)
        log "✓ Command succeeded (${duration}s): $cmd"
        echo "$cmd|SUCCESS|${duration}s" >> "$DEPLOY_PERF_LOG"
        return 0
    else
        local exit_code=$?
        local end_time=$(date '+%s.%N')
        local duration=$(echo "$end_time - $start_time" | bc)
        log "✗ Command failed with exit $exit_code (${duration}s): $cmd"
        echo "$cmd|FAILED|${duration}s|EXIT_CODE:$exit_code" >> "$DEPLOY_PERF_LOG"
        return "$exit_code"
    fi
}

run() {
    local cmd_str="$*"
    local start_time=$(date '+%s.%N')

    if [[ "$(id -u)" == "0" ]]; then
        if [[ ! "$cmd_str" =~ ^sudo ]]; then
            if [[ "$cmd_str" == *"php"* ]] || [[ "$cmd_str" == *"composer"* ]] || \
               [[ "$cmd_str" == *"npm"* ]] || [[ "$cmd_str" == *"pnpm"* ]] || \
               [[ "$cmd_str" == *"git"* ]]; then
                cmd_str="sudo -u $APP_USER $cmd_str"
            fi
        fi
    fi

    log "EXECUTE: $cmd_str"

    if [[ "${DRY_RUN:-false}" == "true" ]]; then
        echo -e "${YELLOW}[DRY-RUN]${NC} $cmd_str"
        log "DRY-RUN: $cmd_str"
        return 0
    fi

    if eval "$cmd_str" 2>&1 | tee -a "$DEPLOY_LOG"; then
        local exit_code=0
        local end_time=$(date '+%s.%N')
        local duration=$(echo "$end_time - $start_time" | bc 2>/dev/null || echo "0")
        log "✓ Success (${duration}s): $cmd_str"
        echo "$(date '+%H:%M:%S')|$cmd_str|SUCCESS|${duration}s" >> "$DEPLOY_PERF_LOG"
        return 0
    else
        local exit_code=$?
        local end_time=$(date '+%s.%N')
        local duration=$(echo "$end_time - $start_time" | bc 2>/dev/null || echo "0")
        error "Command failed with exit code $exit_code: $cmd_str"
        log "✗ Failed (${duration}s, EXIT $exit_code): $cmd_str"
        echo "$(date '+%H:%M:%S')|$cmd_str|FAILED|${duration}s|EXIT:$exit_code" >> "$DEPLOY_PERF_LOG"
        return "$exit_code"
    fi
}

# ── Initialize Variables ────────────────────────────────────────────────────
FRESH=false
SEED=false
NO_BUILD=false
FORCE=false
DRY_RUN=false
RESET_DB=false
DIAGNOSE=false
IGNORE_MAINTENANCE=false
SYNC_IMAGES=false
OPTIMIZE_IMAGES=false
WEBHOOK_TRIGGER="${WEBHOOK_TRIGGER:-false}"
TIMEOUT_PID=""
MAINTENANCE_WAS_ENABLED=false
LOCK_FILE="/tmp/deploy.lock"
INSTALL_FLAG="storage/framework/deployment.lock"
NOTIFY_ENABLED="true"
DISCORD_WEBHOOK=""
NTFY_URL=""
NOTIFY_DISCORD=""

if [[ -f .env ]]; then
    set -a
    source .env
    set +a
fi

set -u

# ── Parse Flags ─────────────────────────────────────────────────────────────
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
        --sync-images) SYNC_IMAGES=true ;;
        --optimize-images) OPTIMIZE_IMAGES=true ;;
        *)           warn "Unknown flag: $arg" ;;
    esac
done

# ── Notifications ───────────────────────────────────────────────────────────
send_notification() {
    local exit_code=$1
    local status="Success"
    local color=5763719
    if [[ "$exit_code" -ne 0 ]]; then
        status="Failed"
        color=15548997
    fi

    [[ "$NOTIFY_ENABLED" != "true" ]] && { rm -f resources/views/errors/temp_503.blade.php 2>/dev/null; return; }

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

    [[ -n "$NTFY_URL" ]] && echo "Deployment $status" | curl -s --max-time 10 -H "Title: Deployment $status" -H "Priority: 4" -d @- "$NTFY_URL" >/dev/null 2>&1 || true

    rm -f resources/views/errors/temp_503.blade.php 2>/dev/null || true
}

# ── Pre-flight Checks ───────────────────────────────────────────────────────
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
}

validate_required_commands() {
    local missing=()
    for cmd in php composer git; do
        command -v "$cmd" &>/dev/null || missing+=("$cmd")
    done

    if [[ ${#missing[@]} -gt 0 ]]; then
        error "Missing required commands: ${missing[*]}"
        error "Please install the missing dependencies and try again."
        exit 1
    fi
}

APP_DIR="$(resolve_app_dir)"
cd "$APP_DIR" || { error "Failed to change directory to $APP_DIR"; exit 1; }

info "Deploying from $APP_DIR"

[[ ! -f artisan ]] && { error "artisan not found — are you in the Laravel project root?"; exit 1; }
[[ ! -f .env ]] && { error ".env file not found. Copy .env.example and configure it first."; exit 1; }

validate_required_commands

# ── Pre-Deployment Safety Checks ────────────────────────────────────────────
pre_deployment_safety_check() {
    step "Pre-Deployment Safety Checks"
    
    # 1. Check critical files exist
    info "Checking critical files..."
    for file in artisan .env composer.json package.json; do
        if [[ ! -f "$file" ]]; then
            error "CRITICAL: Required file missing: $file"
            exit 1
        fi
    done
    success "All critical files present"
    
    # 2. Check storage directory is writable
    info "Checking storage permissions..."
    if [[ ! -w "storage" ]]; then
        error "storage directory is not writable! Fix permissions first."
        error "Run: chown -R \$USER:\$USER storage"
        exit 1
    fi
    success "Storage directory is writable"
    
    # 3. Check for uncommitted changes (warning only)
    if [[ -d .git ]]; then
        info "Checking for uncommitted changes..."
        if ! git diff-index --quiet HEAD -- 2>/dev/null; then
            warn "You have uncommitted changes! They will be overwritten by git reset --hard"
            warn "Consider committing or stashing changes before deployment"
            # List uncommitted files
            git status --short 2>/dev/null | head -10
        else
            success "No uncommitted changes"
        fi
    fi
    
    # 4. Check PHP version
    info "Checking PHP version..."
    PHP_VERSION=$(php -r "echo PHP_VERSION;" 2>/dev/null || echo "unknown")
    if [[ "$PHP_VERSION" =~ ^8\.[0-9]+ ]]; then
        success "PHP version: $PHP_VERSION"
    else
        warn "PHP version $PHP_VERSION may not be compatible (expected 8.x)"
    fi
    
    # 5. Check for recent errors in logs
    if [[ -f "storage/logs/laravel.log" ]]; then
        info "Checking recent errors in logs..."
        RECENT_ERRORS=$(tail -100 storage/logs/laravel.log 2>/dev/null | grep -c "ERROR") || RECENT_ERRORS=0
        if [[ $RECENT_ERRORS -gt 10 ]]; then
            warn "Found $RECENT_ERRORS recent errors in logs. Review before deploying!"
            tail -20 storage/logs/laravel.log | grep ERROR
        else
            success "Log file looks healthy ($RECENT_ERRORS recent errors)"
        fi
    fi
    
    echo ""
    success "Pre-deployment safety checks completed"
}

pre_deployment_safety_check

# ── Step 0: Change Detection ────────────────────────────────────────────────
PREV_DEPLOY_FILE="storage/framework/last_successful_deploy"
INITIAL_GIT_HASH=$(git rev-parse HEAD 2>/dev/null || echo "no-git")

if [[ "$WEBHOOK_TRIGGER" == "true" && -f "$PREV_DEPLOY_FILE" && "$FORCE" == "false" ]]; then
    LAST_SUCCESSFUL_HASH=$(cut -d'|' -f1 "$PREV_DEPLOY_FILE" 2>/dev/null || echo "")
    if [[ "$INITIAL_GIT_HASH" == "$LAST_SUCCESSFUL_HASH" ]]; then
        info "Webhook triggered, checking remote for actual changes..."
        git fetch origin >/dev/null 2>&1 || true
        REMOTE_HASH=$(git rev-parse origin/main 2>/dev/null || echo "")

        if [[ "$INITIAL_GIT_HASH" == "$REMOTE_HASH" ]]; then
            success "No remote changes detected. System is already at hash $REMOTE_HASH."
            exit 0
        fi
    fi
fi

# ── APP_KEY Check ───────────────────────────────────────────────────────────
info "Checking application encryption key..."
APP_KEY_EXISTS=false

if [[ -f .env ]]; then
    APP_KEY_LINE=$(grep '^APP_KEY=' .env 2>/dev/null || echo "")
    if [[ -n "$APP_KEY_LINE" ]]; then
        APP_KEY_VALUE=$(echo "$APP_KEY_LINE" | cut -d'=' -f2- | sed 's/^"//' | sed 's/"$//')
        [[ -n "$APP_KEY_VALUE" ]] && APP_KEY_EXISTS=true
    fi
fi

if [[ "$APP_KEY_EXISTS" == "false" ]]; then
    warn "APP_KEY not found or empty. Generating new key..."
    run php artisan key:generate || { error "Failed to generate APP_KEY"; exit 1; }
    success "Application encryption key generated."
else
    success "Application encryption key is set."
fi

mkdir -p storage/framework

# ── Recovery from Stuck Deployment ──────────────────────────────────────────
if [[ -f "storage/framework/down" ]]; then
    MAINT_AGE=$(($(date +%s) - $(stat -f%m "storage/framework/down" 2>/dev/null || stat -c%Y "storage/framework/down" 2>/dev/null || echo 0)))
    if [[ $MAINT_AGE -gt 3600 ]]; then
        warn "Maintenance mode lock older than 1 hour (likely from stuck deployment)."
        warn "Attempting recovery: removing stale down state..."
        rm -f "storage/framework/down"
        php artisan up 2>/dev/null || warn "Could not bring site up immediately"
        sleep 2
    fi
fi

# ── Lock Mechanism ──────────────────────────────────────────────────────────
if [[ -f "$LOCK_FILE" ]]; then
    LOCK_PID=$(cat "$LOCK_FILE" 2>/dev/null || echo "")
    if [[ -n "$LOCK_PID" ]] && kill -0 "$LOCK_PID" 2>/dev/null; then
        error "Deploy script already running (PID: $LOCK_PID). Exiting."
        exit 1
    else
        warn "Stale lock file found. Removing..."
        rm -f "$LOCK_FILE"
    fi
fi

if [[ -f "$INSTALL_FLAG" ]]; then
    FLAG_DATA=$(cat "$INSTALL_FLAG" 2>/dev/null || echo "")
    FLAG_PID=$(echo "$FLAG_DATA" | cut -d'|' -f1 || echo "")
    FLAG_TIME=$(echo "$FLAG_DATA" | cut -d'|' -f2 || echo "0")
    FLAG_AGE=$(($(date +%s) - FLAG_TIME))

    if [[ -n "$FLAG_PID" ]] && kill -0 "$FLAG_PID" 2>/dev/null; then
        error "Installation already in progress (PID: $FLAG_PID, age: ${FLAG_AGE}s)."
        exit 1
    else
        warn "Stale installation flag found. Removing..."
        rm -f "$INSTALL_FLAG"
    fi
fi

info "Creating deployment locks..."
echo $$ > "$LOCK_FILE"
echo "$$|$(date +%s)|$(date '+%Y-%m-%d %H:%M:%S')" > "$INSTALL_FLAG"

# ── Cleanup Handler ─────────────────────────────────────────────────────────
cleanup_and_exit() {
    local exit_code=$?

    log "═══════════════════════════════════════════════"
    log "DEPLOYMENT SUMMARY"
    log "═══════════════════════════════════════════════"
    log "End Time: $(date '+%Y-%m-%d %H:%M:%S')"
    log "Exit Code: $exit_code"
    
    if [[ $exit_code -eq 0 ]]; then
        log "STATUS: ✅ SUCCESSFUL"
    else
        log "STATUS: ❌ FAILED"
    fi

    # Append performance metrics
    if [[ -f "$DEPLOY_PERF_LOG" ]]; then
        log ""
        log "PERFORMANCE METRICS:"
        log "$(tail -20 "$DEPLOY_PERF_LOG")"
    fi

    log "═══════════════════════════════════════════════"
    log "Log file: $DEPLOY_LOG"
    log ""

    [[ -n "$TIMEOUT_PID" && "$TIMEOUT_PID" != "" ]] && { kill "$TIMEOUT_PID" 2>/dev/null || true; wait "$TIMEOUT_PID" 2>/dev/null || true; }
    rm -f "$LOCK_FILE" "$INSTALL_FLAG"

    if [[ ("$MAINTENANCE_WAS_ENABLED" == "true" && "$exit_code" -ne 0) || "$IGNORE_MAINTENANCE" == "true" ]]; then
        if [[ "$exit_code" -ne 0 ]]; then
            warn "Deploy failed (exit code: $exit_code)! Restoring site..."
            log "RECOVERY: Attempting to restore site from maintenance mode..."
        else
            info "Ensuring site is LIVE..."
            log "RECOVERY: Bringing site live..."
        fi

        for attempt in 1 2 3; do
            if php artisan up 2>&1 | tee -a "$DEPLOY_LOG"; then
                success "Site restored to live."
                log "RECOVERY: ✓ Site successfully restored to live"
                break
            elif [[ $attempt -lt 3 ]]; then
                warn "Attempt $attempt failed, retrying..."
                log "RECOVERY: Attempt $attempt failed, retrying in 2s..."
                sleep 2
            else
                error "CRITICAL: Could not restore site! Run: php artisan up"
                log "RECOVERY: ✗ CRITICAL - Could not restore site from maintenance mode!"
            fi
        done
    fi

    echo ""
    echo "📋 Deployment logs available:"
    echo "   Full log:  $DEPLOY_LOG"
    echo "   Perf log:  $DEPLOY_PERF_LOG"
    echo ""
    
    send_notification "$exit_code"
}

trap cleanup_and_exit EXIT

# ── Load Notification Settings ──────────────────────────────────────────────
DISCORD_WEBHOOK=$(grep "^DISCORD_WEBHOOK=" .env 2>/dev/null | cut -d '=' -f 2- | tr -d '"' || echo "")
NTFY_URL=$(grep "^NTFY_URL=" .env 2>/dev/null | cut -d '=' -f 2- | tr -d '"' || echo "")
NOTIFY_DISCORD=$(grep "^NOTIFY_DISCORD=" .env 2>/dev/null | cut -d '=' -f 2- | tr -d '"' || echo "")

[[ "$WEBHOOK_TRIGGER" == "true" ]] && info "Deployment triggered by GitHub webhook"

# ── Step 0.5: Database Backup ───────────────────────────────────────────────
step "Database Backup"
info "Creating database backup..."

# SAFETY FIX: Check disk space before backup
DISK_AVAILABLE_KB=$(df -P . | awk 'NR==2 {print $4}')
DISK_AVAILABLE_GB=$((DISK_AVAILABLE_KB / 1048576))
if [[ $DISK_AVAILABLE_GB -lt 1 ]]; then
    error "CRITICAL: Insufficient disk space (${DISK_AVAILABLE_GB}GB available, need at least 1GB)"
    error "Deployment aborted for safety. Please free up disk space and try again."
    exit 1
fi
info "Disk space check passed: ${DISK_AVAILABLE_GB}GB available"

BACKUP_DIR="storage/backups"
mkdir -p "$BACKUP_DIR"

BACKUP_TIMESTAMP=$(date '+%Y%m%d_%H%M%S')
BACKUP_FILE="$BACKUP_DIR/backup_${BACKUP_TIMESTAMP}.sql"
CALLERS_BACKUP_FILE="$BACKUP_DIR/callers_backup_${BACKUP_TIMESTAMP}.csv"

if [[ "${DB_CONNECTION:-}" == "sqlite" && -f "$DB_DATABASE" ]]; then
    run cp "$DB_DATABASE" "${DB_DATABASE}.backup_${BACKUP_TIMESTAMP}"
    success "SQLite backup created: ${DB_DATABASE}.backup_${BACKUP_TIMESTAMP}"
elif [[ "${DB_CONNECTION:-}" == "mysql" ]]; then
    # SAFETY FIX: Verify mysqldump command exists
    if ! command -v mysqldump &>/dev/null; then
        error "mysqldump command not found! Cannot create database backup."
        warn "Continuing deployment without backup (NOT RECOMMENDED)"
    else
        # SAFETY FIX: Verify database connection before backup
        info "Testing database connection..."
        if ! mysql -h"${DB_HOST:-localhost}" -u"${DB_USERNAME:-root}" -p"${DB_PASSWORD:-}" -e "SELECT 1" "${DB_DATABASE}" &>/dev/null; then
            error "Cannot connect to database! Backup failed."
            warn "Continuing deployment without backup (NOT RECOMMENDED)"
        else
            success "Database connection verified"
            
            run mysqldump --single-transaction --quick --lock-tables=false \
                -h"${DB_HOST:-localhost}" -u"${DB_USERNAME:-root}" -p"${DB_PASSWORD:-}" \
                "${DB_DATABASE}" > "$BACKUP_FILE"
            
            # SAFETY FIX: Verify backup was created and has content
            if [[ -s "$BACKUP_FILE" ]]; then
                BACKUP_SIZE=$(stat -c%s "$BACKUP_FILE" 2>/dev/null || stat -f%z "$BACKUP_FILE" 2>/dev/null || echo "unknown")
                success "MySQL backup created: $BACKUP_FILE (${BACKUP_SIZE} bytes)"
            else
                error "WARNING: Backup file is empty! Database backup may have failed."
                warn "Continuing deployment but manual backup recommended"
            fi
        fi
    fi
else
    run php artisan db:show || warn "Could not verify database connection"
fi

info "Backups stored in: $BACKUP_DIR"

# ── Step 1: Maintenance Mode ────────────────────────────────────────────────
step "Maintenance Mode"
if [[ -f "storage/framework/down" ]]; then
    if [[ -n "${PUBLISH_VERSION:-}" ]] || [[ "$IGNORE_MAINTENANCE" == "true" ]]; then
        info "Maintenance mode is active (continuing...)"
    else
        error "Website is in maintenance mode. Use --up to override."
        exit 1
    fi
fi

if [[ -z "${PUBLISH_VERSION:-}" ]]; then
    info "Enabling maintenance mode..."
    run php artisan down --retry=120 --render="down" --secret="deploy-$(date +%s)" || true
    MAINTENANCE_WAS_ENABLED=true
    sleep 2
else
    NOTIFY_ENABLED="false"
    info "Skipping maintenance mode (handled by publish.sh)."
fi

# ── Step 2: Pull Latest Code ────────────────────────────────────────────────
step "Code Sync"
if [[ -d .git ]]; then
    info "Current Hash: $INITIAL_GIT_HASH"
    run git fetch origin
    CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "unknown")
    run git reset --hard origin/"$CURRENT_BRANCH"
    success "Codebase synced with remote/$CURRENT_BRANCH"

    POST_PULL_HASH=$(git rev-parse HEAD 2>/dev/null || echo "no-git")
    if [[ -f "$PREV_DEPLOY_FILE" && "$FORCE" == "false" && "$FRESH" == "false" ]]; then
        LAST_SUCCESSFUL_HASH=$(cut -d'|' -f1 "$PREV_DEPLOY_FILE" 2>/dev/null || echo "")
        if [[ "$POST_PULL_HASH" == "$LAST_SUCCESSFUL_HASH" ]]; then
            success "Already at last deployed hash ($POST_PULL_HASH). Terminating."
            exit 0
        fi
    fi
fi

# ── Step 2.5: Change Detection ──────────────────────────────────────────────
info "Detecting changes for optimized build..."
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
    info "Force deployment — skipping optimizations."
elif [[ -z "$LAST_HASH" || "$LAST_HASH" == "no-git" ]]; then
    info "No previous deployment record."
else
    CHANGES=$(git diff --name-only "$LAST_HASH" "${POST_PULL_HASH:-$CURRENT_GIT_HASH}" 2>/dev/null || echo "ALL")

    if [[ "$CHANGES" != "ALL" ]]; then
        echo "$CHANGES" | grep -qE "^(composer\.json|composer\.lock)$" || COMPOSER_CHANGED=false
        echo "$CHANGES" | grep -qE "(package\.json|pnpm-lock\.yaml|package-lock\.json|vite\.config\.js|resources/|public/)" || FRONTEND_CHANGED=false
        echo "$CHANGES" | grep -q "^database/migrations/" || MIGRATIONS_CHANGED=false

        [[ "$COMPOSER_CHANGED" == "false" ]] && info "No Composer changes detected."
        [[ "$FRONTEND_CHANGED" == "false" ]] && info "No Frontend changes detected."
        [[ "$MIGRATIONS_CHANGED" == "false" ]] && info "No migration changes detected."
    fi
fi

# ── Step 3: PHP Dependencies ────────────────────────────────────────────────
step "PHP Dependencies"
if [[ "$COMPOSER_CHANGED" == "true" ]]; then
    info "Installing Composer dependencies..."
    if [[ "${APP_ENV:-production}" == "production" ]]; then
        run composer install --no-dev --optimize-autoloader --no-interaction
    else
        run composer install --no-interaction
    fi
    success "Composer dependencies installed."
else
    info "Skipping Composer (no changes)."
fi

# ── Step 4: Frontend Assets ─────────────────────────────────────────────────
step "Frontend Assets"
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
        info "Skipping Frontend build (no changes)."
    fi
else
    info "Skipping frontend build (--no-build)."
fi

# ── Step 5: Database Migrations ─────────────────────────────────────────────
step "Database Migrations"
if [[ "$MIGRATIONS_CHANGED" == "true" || "$FRESH" == "true" || "$RESET_DB" == "true" ]]; then
    info "Running database migrations..."

    if [[ "$FRESH" == "true" ]]; then
        warn "Running migrate:fresh — DROPPING all tables!"
        run php artisan migrate:fresh --force --seed
        success "Fresh migration completed with seeding."
    elif [[ "$RESET_DB" == "true" ]]; then
        warn "Running migrate:fresh — DROPPING all tables!"
        run php artisan migrate:fresh --force
        success "Database reset completed."
    else
        run php artisan migrate --force
        success "Migrations applied."
    fi
else
    info "Skipping migrations (no changes)."
fi

if [[ "$SEED" == "true" && "$FRESH" == "false" && "$RESET_DB" == "false" ]]; then
    info "Running database seeders..."
    run php artisan db:seed --force
    success "Database seeding completed."
fi

# ── Step 6: Verify Migration Status ─────────────────────────────────────────
info "Verifying migration status..."
if [[ "$DRY_RUN" == "false" ]]; then
    PENDING=$(php artisan migrate:status 2>&1 | grep -c "Pending") || PENDING=0
    if [[ "$PENDING" -gt 0 ]]; then
        error "$PENDING migration(s) pending! Check errors above."
        php artisan up 2>/dev/null || warn "Could not execute 'php artisan up'"
        exit 1
    fi
    success "All migrations applied."
fi

# ── Step 7: Version Sync ────────────────────────────────────────────────────
step "Version Sync"
if php artisan list 2>/dev/null | grep -q "version:sync"; then
    info "Synchronising version..."
    run php artisan version:sync
    success "Version synchronised."
fi

# ── Step 7.5: Image Cache Busting ───────────────────────────────────────────
step "Image Cache Busting"
info "Generating image cache busting manifest..."

# Create image checksums for cache busting
IMAGE_MANIFEST="storage/framework/image_manifest.json"
mkdir -p storage/framework

if [[ -d "public/images" ]]; then
    # Generate checksums for all images
    php -r "
        \$manifest = [];
        \$version = trim(file_get_contents(base_path('VERSION') ?: '/dev/null') ?: date('YmdHis'));
        \$iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(public_path('images'))
        );
        
        foreach (\$iterator as \$file) {
            if (\$file->isFile() && preg_match('/\.(png|jpg|jpeg|svg|webp|gif)$/i', \$file->getFilename())) {
                \$relativePath = str_replace(public_path('images') . '/', '', \$file->getPathname());
                \$hash = md5_file(\$file->getPathname());
                \$manifest[\$relativePath] = [
                    'hash' => \$hash,
                    'version' => \$version,
                    'mtime' => \$file->getMTime(),
                    'size' => \$file->getSize()
                ];
            }
        }
        
        \$output = [
            'version' => \$version,
            'generated_at' => date('c'),
            'total_images' => count(\$manifest),
            'images' => \$manifest
        ];
        
        file_put_contents(base_path('$IMAGE_MANIFEST'), json_encode(\$output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo 'Generated manifest for ' . count(\$manifest) . ' images';
    " 2>/dev/null && success "Image manifest created ($IMAGE_MANIFEST)" || warn "Could not generate image manifest"
    
    # Display image info
    if [[ -f "$IMAGE_MANIFEST" ]]; then
        IMAGE_COUNT=$(php -r "echo json_decode(file_get_contents('$IMAGE_MANIFEST'))->total_images ?? 0;" 2>/dev/null || echo "0")
        info "Total images tracked: $IMAGE_COUNT"
        
        # Show key images
        info "Key images:"
        for img in "alsarya-logo-2026-1.png" "alsarya-logo.png" "alsarya-logo-2026-tiny.png"; do
            if [[ -f "public/images/$img" ]]; then
                SIZE=$(stat -c%s "public/images/$img" 2>/dev/null || stat -f%z "public/images/$img" 2>/dev/null || echo "?")
                HASH=$(md5 -q "public/images/$img" 2>/dev/null || md5sum "public/images/$img" 2>/dev/null | cut -d' ' -f1 || echo "?")
                info "  ✓ $img (${SIZE}B, MD5: ${HASH:0:12}...)"
            fi
        done
    fi
else
    warn "public/images directory not found"
fi

# ── Step 7.6: Optional Image Optimization ───────────────────────────────────
# SAFETY FIX: Only optimize if explicitly enabled, with backup and temp file strategy
if [[ "${OPTIMIZE_IMAGES:-false}" == "true" ]]; then
    step "Image Optimization"
    info "Optimizing images (this may take a while)... WARNING: This modifies original files!"
    warn "Creating backup of images before optimization..."
    
    # Create backup directory
    OPTIMIZE_BACKUP_DIR="storage/backups/images_$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$OPTIMIZE_BACKUP_DIR"
    
    # Backup all images before optimization
    if [[ -d "public/images" ]]; then
        cp -r public/images/* "$OPTIMIZE_BACKUP_DIR/" 2>/dev/null || {
            error "Failed to create image backup! Skipping optimization for safety."
            OPTIMIZE_IMAGES=false
        }
        
        if [[ "$OPTIMIZE_IMAGES" == "true" ]]; then
            info "Image backup created: $OPTIMIZE_BACKUP_DIR"
            
            php -r "
                use Intervention\Image\ImageManager;
                use Intervention\Image\Drivers\Gd\Driver;
                
                require 'vendor/autoload.php';
                
                \$imageDir = public_path('images');
                \$optimized = 0;
                \$skipped = 0;
                \$errors = 0;
                
                if (!class_exists(ImageManager::class)) {
                    echo 'Intervention Image not available, skipping optimization\n';
                    exit(0);
                }
                
                \$manager = new ImageManager(new Driver());
                
                \$iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator(\$imageDir)
                );
                
                foreach (\$iterator as \$file) {
                    if (\$file->isFile() && preg_match('/\.(jpg|jpeg|png|webp)$/i', \$file->getFilename())) {
                        \$ext = strtolower(pathinfo(\$file->getFilename(), PATHINFO_EXTENSION));
                        \$originalSize = \$file->getSize();
                        \$originalPath = \$file->getPathname();
                        
                        // SAFETY FIX: Use temp file to avoid corruption
                        \$tempFile = tempnam(sys_get_temp_dir(), 'img_opt_');
                        if (\$tempFile === false) {
                            echo \"✗ Failed to create temp file for {\$file->getFilename()}\n\";
                            \$errors++;
                            continue;
                        }
                        
                        try {
                            \$image = \$manager->read(\$originalPath);
                            
                            // Optimize based on type
                            if (in_array(\$ext, ['jpg', 'jpeg'])) {
                                \$image->toJpeg(quality: 85, progressive: true)->save(\$tempFile);
                            } elseif (\$ext === 'png') {
                                \$image->toPng(compressionLevel: 6)->save(\$tempFile);
                            } elseif (\$ext === 'webp') {
                                \$image->toWebp(quality: 80)->save(\$tempFile);
                            }
                            
                            // Verify temp file before replacing original
                            \$newSize = filesize(\$tempFile);
                            if (\$newSize > 0 && \$newSize <= \$originalSize * 1.1) { // Allow 10% tolerance
                                // Move temp to original (atomic on same filesystem)
                                if (rename(\$tempFile, \$originalPath)) {
                                    \$savings = \$originalSize - \$newSize;
                                    if (\$savings > 0) {
                                        \$percent = round((\$savings / \$originalSize) * 100, 1);
                                        echo \"✓ Optimized: {\$file->getFilename()} (saved {\$percent}%)\n\";
                                        \$optimized++;
                                    } else {
                                        echo \"~ No savings: {\$file->getFilename()}\n\";
                                        \$skipped++;
                                    }
                                } else {
                                    echo \"✗ Failed to replace {\$file->getFilename()}\n\";
                                    unlink(\$tempFile); // Clean up temp
                                    \$errors++;
                                }
                            } else {
                                echo \"✗ Optimized file size suspicious for {\$file->getFilename()} (orig: \$originalSize, new: \$newSize)\n\";
                                unlink(\$tempFile);
                                \$errors++;
                            }
                        } catch (Exception \$e) {
                            echo \"✗ Error optimizing {\$file->getFilename()}: \" . \$e->getMessage() . \"\n\";
                            if (file_exists(\$tempFile)) {
                                unlink(\$tempFile);
                            }
                            \$errors++;
                        }
                    }
                }
                
                echo \"\n=== Optimization Summary ===\n\";
                echo \"Optimized: \$optimized\n\";
                echo \"Skipped: \$skipped\n\";
                echo \"Errors: \$errors\n\";
                echo \"Backup location: $OPTIMIZE_BACKUP_DIR\n\";
                
                if (\$errors > 0) {
                    echo \"\n⚠️  WARNING: \$errors files had errors! Check backup if issues occur.\n\";
                }
            " 2>&1 | while read -r line; do
                info "  $line"
            done
            
            success "Image optimization complete (backup: $OPTIMIZE_BACKUP_DIR)"
        fi
    else
        warn "public/images directory not found, skipping optimization"
    fi
fi

# ── Step 8: Laravel Caches ──────────────────────────────────────────────────
step "Laravel Caches"
info "Caching configuration, routes, and views..."

# SAFETY FIX: Validate configuration before caching
info "Validating configuration before caching..."
if ! php artisan config:clear 2>&1 | grep -q "OK"; then
    warn "Config clear had issues, continuing with caution..."
fi

# SAFETY FIX: Test that PHP can read config without errors
if ! php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; echo 'Config OK';" &>/dev/null; then
    error "Configuration validation failed! NOT caching config for safety."
    error "Please fix .env or config files before deploying."
    warn "Continuing deployment without config cache (site will be slower but functional)"
else
    info "Configuration validated successfully"
    run php artisan config:cache
    success "Configuration cached"
fi

# Cache other artifacts
run php artisan route:cache
run php artisan view:cache
run php artisan event:cache
success "Laravel caches rebuilt."

# ── Step 9: Clear Stale Caches ──────────────────────────────────────────────
info "Clearing stale caches..."
run php artisan cache:clear || true
success "Application cache cleared."

# ── Step 10: Storage Link ───────────────────────────────────────────────────
step "Storage Symlink"
if [[ ! -L public/storage ]]; then
    info "Creating storage symlink..."
    run php artisan storage:link
    success "Storage symlink created."
else
    info "Refreshing storage symlink..."
    rm public/storage
    run php artisan storage:link
    success "Storage symlink refreshed."
fi

# ── Step 11: Queue Restart ──────────────────────────────────────────────────
step "Queue Workers"
info "Restarting queue workers..."
run php artisan queue:restart || true
success "Queue restart signal sent."

# ── Step 11b: Ownership Fix ─────────────────────────────────────────────────
if [[ "$(id -u)" == "0" ]]; then
    info "Ensuring $APP_USER owns all files..."
    chown -R "$APP_USER:$APP_USER" "$APP_DIR"
    chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
    success "Ownership fixed."
else
    info "Skipping ownership change (not running as root)."
fi

# ── Step 11c: Update Deployment Record ──────────────────────────────────────
if [[ "$DRY_RUN" == "false" ]]; then
    echo "${CURRENT_GIT_HASH}|${CURRENT_VERSION}" > "$PREV_DEPLOY_FILE"
    info "Deployment record updated: ${CURRENT_VERSION} (${CURRENT_GIT_HASH})"
fi

# ── Step 12: Health Check ───────────────────────────────────────────────────
step "Health Check"
check_production_health() {
    local app_url=$(grep "^APP_URL=" .env 2>/dev/null | cut -d= -f2- | tr -d '"' | tr -d "'" || echo "")

    [[ -z "$app_url" ]] && { warn "APP_URL not found - skipping health check"; return 0; }

    info "Running production health checks..."

    local individual_status=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "$app_url" 2>/dev/null || echo "000")
    local family_status=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "$app_url/family" 2>/dev/null || echo "000")

    if [[ "$individual_status" == "200" || "$individual_status" == "302" ]]; then
        success "✓ Individual registration: HTTP $individual_status"
    else
        error "✗ Individual registration: HTTP $individual_status"
        return 1
    fi

    if [[ "$family_status" == "200" || "$family_status" == "302" ]]; then
        success "✓ Family registration: HTTP $family_status"
    else
        error "✗ Family registration: HTTP $family_status"
        return 1
    fi

    success "All routes are healthy!"
    return 0
}

IS_LOCAL_EXECUTION=false
[[ -n "${PUBLISH_VERSION:-}" ]] || [[ -n "${SSH_CLIENT:-}" ]] || [[ -n "${SSH_TTY:-}" ]] && IS_LOCAL_EXECUTION=true

# ── Step 12b: Log Hit Counters ──────────────────────────────────────────────
if [[ "$DRY_RUN" == "false" ]]; then
    info "Logging hit counters..."

    LOG_DIR="storage/logs/hits"
    mkdir -p "$LOG_DIR"

    TODAY=$(date '+%Y-%m-%d')
    DAILY_LOG="$LOG_DIR/hits_${TODAY}.csv"

    if [[ ! -f "$DAILY_LOG" ]]; then
        echo "timestamp,caller_id,cpr,name,phone,hits,status,ip_address" > "$DAILY_LOG"
    fi

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

    success "Hit counters logged: $DAILY_LOG"
fi

# ── Step 13: Disable Maintenance Mode ───────────────────────────────────────
step "Going Live"
if [[ -z "${PUBLISH_VERSION:-}" ]]; then
    info "Disabling maintenance mode..."
    run php artisan up
    success "Application is LIVE!"

    if [[ "$IS_LOCAL_EXECUTION" == "true" && "$DRY_RUN" == "false" ]]; then
        echo ""
        if ! check_production_health; then
            error "Health check failed! Investigate immediately."
            exit 1
        fi
    fi
    MAINTENANCE_WAS_ENABLED=false
else
    info "Skipping maintenance mode (handled by publish.sh)."
fi

# ── Step 14: Post-Deployment Verification ───────────────────────────────────
step "Post-Deployment Verification"

post_deployment_verify() {
    local all_passed=true
    
    info "Running post-deployment verification..."
    
    # 1. Verify homepage is accessible
    info "Testing homepage accessibility..."
    local app_url=$(grep "^APP_URL=" .env 2>/dev/null | cut -d= -f2- | tr -d '"' | tr -d "'" || echo "")
    
    if [[ -n "$app_url" ]]; then
        local homepage_status=$(curl -sI -o /dev/null -w "%{http_code}" --max-time 10 "$app_url" 2>/dev/null || echo "000")
        if [[ "$homepage_status" == "200" || "$homepage_status" == "302" ]]; then
            success "Homepage is accessible (HTTP $homepage_status)"
        else
            error "Homepage returned HTTP $homepage_status (expected 200 or 302)"
            all_passed=false
        fi
    else
        warn "APP_URL not set in .env, skipping homepage test"
    fi
    
    # 2. Verify registration form exists
    info "Testing registration form..."
    if [[ -n "$app_url" ]]; then
        local form_status=$(curl -sI -o /dev/null -w "%{http_code}" --max-time 10 "${app_url}/" 2>/dev/null || echo "000")
        if [[ "$form_status" == "200" || "$form_status" == "302" ]]; then
            success "Registration form is accessible"
        else
            warn "Registration form returned HTTP $form_status"
        fi
    fi
    
    # 3. Check for PHP errors in recent logs
    info "Checking for new errors in logs..."
    if [[ -f "storage/logs/laravel.log" ]]; then
        local new_errors
        new_errors=$(tail -50 storage/logs/laravel.log 2>/dev/null | grep -c "ERROR\|CRITICAL") || new_errors=0
        if [[ $new_errors -gt 0 ]]; then
            warn "Found $new_errors new errors in logs since deployment"
            tail -10 storage/logs/laravel.log 2>/dev/null | grep "ERROR\|CRITICAL"
        else
            success "No new errors in logs"
        fi
    fi
    
    # 4. Verify cache is working
    info "Verifying cache is operational..."
    if php artisan cache:table 2>&1 | grep -q "No table specified"; then
        success "Cache system is operational"
    else
        warn "Cache system may have issues"
    fi
    
    # 5. Check storage symlink
    info "Verifying storage symlink..."
    if [[ -L "public/storage" ]]; then
        success "Storage symlink is present"
    else
        error "Storage symlink is MISSING! Run: php artisan storage:link"
        all_passed=false
    fi
    
    echo ""
    if [[ "$all_passed" == "true" ]]; then
        success "✓ All post-deployment checks passed!"
        return 0
    else
        warn "⚠ Some post-deployment checks failed. Review logs above."
        return 1
    fi
}

if [[ "$DRY_RUN" == "false" ]]; then
    post_deployment_verify || {
        warn "Post-deployment verification had issues, but site is live."
        warn "Monitor logs and fix any issues manually."
    }
fi

# ── Summary ─────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  🎉 Deployment Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
[[ "$SEED" == "true" ]] && info "✓ Seeders executed"
[[ "$FRESH" == "true" ]] && warn "⚠ Database freshly rebuilt"
[[ "$RESET_DB" == "true" ]] && warn "⚠ Database reset (no seeding)"
echo ""
success "Deploy finished at $(date '+%Y-%m-%d %H:%M:%S')"
echo ""
