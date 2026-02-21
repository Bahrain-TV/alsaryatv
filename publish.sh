#!/usr/bin/env bash
###############################################################################
# publish.sh — AlSarya TV Show Registration System
#
# Local deployment publisher script. Validates git state, pushes to remote,
# connects to production server via SSH, and triggers remote deployment.
#
# Usage:
#   ./publish.sh                 # Full deploy to production (default)
#   ./publish.sh --up            # Bring site up mode (runs vitals check first)
#   ./publish.sh --quick-up      # Fast recovery: vitals check + --up --force --no-build
#   ./publish.sh --fresh         # Fresh database deploy
#   ./publish.sh --seed          # Add seeding after migration
#   ./publish.sh --no-build      # Skip npm build on remote
#   ./publish.sh --force         # Force all steps even if no changes
#   ./publish.sh --reset-db      # Reset production database (migrate:fresh)
#   ./publish.sh --dry-run       # Validate without executing
#   ./publish.sh --no-backup-sync  # Skip pulling DB/CSV backups to local
#   ./publish.sh --sync-assets-only  # Only sync assets (no deploy)
#   ./publish.sh --help          # Show this help message
#
# Requirements:
#   - Git installed locally
#   - SSH access to production server configured
#   - SSH key in ~/.ssh (or SSH_KEY_PATH environment variable)
#   - rsync installed locally (for asset + backup sync)
#
# Asset sync (local → production, after every successful deployment):
#   public/images/, public/lottie/, public/sounds/, public/fonts/ are pushed
#   via rsync AFTER the remote deploy finishes (so git-pull can't undo them).
#
# Backup sync (production → local, after every successful deployment):
#   1. A fresh DB dump (SQLite copy or mysqldump) is triggered on the server
#   2. CSV + DB dumps are pulled to ./storage/backups/
#   Only files newer than local copies are transferred (rsync --update).
#   Set LOCAL_BACKUP_DIR in .env to override the destination path.
#
# Configuration:
#   Set these in your .env or environment:
#   - PROD_SSH_USER=alsar4210         # Remote SSH user
#   - PROD_SSH_HOST=alsarya.tv        # Production server hostname/IP
#   - PROD_SSH_PORT=22                # SSH port (default 22)
#   - PROD_APP_DIR=/path/to/app       # Remote app directory
#   - PROD_GIT_BRANCH=main            # Deploy branch (default: main)
#   - LOCAL_BACKUP_DIR=storage/backups # Local destination for backup sync
#
###############################################################################

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BLUE='\033[0;34m'
NC='\033[0m' # No colour

# Helpers
info()    { echo -e "${CYAN}[INFO]${NC}  $*"; }
success() { echo -e "${GREEN}[OK]${NC}    $*"; }
warn()    { echo -e "${YELLOW}[WARN]${NC}  $*"; }
error()   { echo -e "${RED}[ERROR]${NC} $*"; }
debug()   { [[ "${DEBUG:-false}" == "true" ]] && echo -e "${BLUE}[DEBUG]${NC} $*"; }

# Set initial error handling (strict mode after variable init)
set -o pipefail  # Remove 'e' flag - handle errors explicitly

# Initialize flags
FRESH=false
SEED=false
NO_BUILD=false
FORCE=false
DRY_RUN=false
SHOW_HELP=false
RESET_DB=false
NO_BACKUP_SYNC=false
NO_MEDIA_SYNC=false
UP_MODE=false
QUICK_UP=false
DEBUG=${DEBUG:-false}
DIRTY_WORKTREE=false
DIRTY_TRACKED_FILES=""
SYNC_ASSETS_ONLY=false

# Parse command line arguments
for arg in "$@"; do
    case "$arg" in
        --fresh)           FRESH=true; SEED=true ;;
        --seed)            SEED=true ;;
        --no-build)        NO_BUILD=true ;;
        --force)           FORCE=true ;;
        --dry-run)         DRY_RUN=true ;;
        --up)              UP_MODE=true ;;
        --quick-up)        QUICK_UP=true; UP_MODE=true; FORCE=true; NO_BUILD=true ;;
        --reset-db)        RESET_DB=true ;;
        --no-backup-sync)  NO_BACKUP_SYNC=true ;;
        --no-media-sync)   NO_MEDIA_SYNC=true ;;
        --sync-assets-only) SYNC_ASSETS_ONLY=true ;;
        --debug)           DEBUG=true ;;
        --help|-h)         SHOW_HELP=true ;;
        *)                 error "Unknown flag: $arg"; SHOW_HELP=true ;;
    esac
done

# Show help if requested
if [[ "$SHOW_HELP" == "true" ]]; then
    head -n 30 "$0" | tail -n +4
    exit 0
fi

# Enable strict mode for undefined variables
set -u

# Get local app directory
APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$APP_DIR" || {
    error "Failed to change to app directory: $APP_DIR"
    exit 1
}

debug "App directory: $APP_DIR"

# ── Load environment configuration ───────────────────────────────────────────
load_env_config() {
    local env_file=".env"

    if [[ ! -f "$env_file" ]]; then
        error ".env file not found. Cannot proceed."
        exit 1
    fi

    # Production server configuration
    PROD_SSH_USER="${PROD_SSH_USER:-$(grep '^PROD_SSH_USER=' "$env_file" 2>/dev/null | cut -d'=' -f2- | tr -d '\"' || echo 'alsar4210')}"
    PROD_SSH_HOST="${PROD_SSH_HOST:-$(grep '^PROD_SSH_HOST=' "$env_file" 2>/dev/null | cut -d'=' -f2- | tr -d '\"' || echo '')}"
    PROD_SSH_PORT="${PROD_SSH_PORT:-$(grep '^PROD_SSH_PORT=' "$env_file" 2>/dev/null | cut -d'=' -f2- | tr -d '\"' || echo '22')}"
    PROD_APP_DIR="${PROD_APP_DIR:-$(grep '^PROD_APP_DIR=' "$env_file" 2>/dev/null | cut -d'=' -f2- | tr -d '\"' || echo '/home/alsarya.tv/public_html')}"
    PROD_GIT_BRANCH="${PROD_GIT_BRANCH:-$(grep '^PROD_GIT_BRANCH=' "$env_file" 2>/dev/null | cut -d'=' -f2- | tr -d '\"' || echo 'main')}"

    # Optional: SSH key path
    SSH_KEY_PATH="${SSH_KEY_PATH:-${HOME}/.ssh/id_rsa}"
    # Local directory to receive remote backups (relative to APP_DIR or absolute)
    LOCAL_BACKUP_DIR="${LOCAL_BACKUP_DIR:-$(grep '^LOCAL_BACKUP_DIR=' "$env_file" 2>/dev/null | cut -d'=' -f2- | tr -d '"' || echo 'storage/backups')}"
}

load_env_config

# ── Validate prerequisites ───────────────────────────────────────────────────
validate_prerequisites() {
    info "Validating prerequisites..."

    # Check required commands
    for cmd in git ssh; do
        if ! command -v "$cmd" &>/dev/null; then
            error "Required command not found: $cmd"
            exit 1
        fi
    done
    success "Required commands available (git, ssh)"

    # Check if we're in a git repo
    if [[ ! -d .git ]]; then
        error "Not a git repository. Run this from the Laravel project root."
        exit 1
    fi
    success "Git repository detected"

    # Check SSH connectivity
    if [[ -z "$PROD_SSH_HOST" ]]; then
        error "PROD_SSH_HOST not configured in .env file."
        error "Add: PROD_SSH_HOST=your.production.server"
        exit 1
    fi

    debug "SSH User: $PROD_SSH_USER"
    debug "SSH Host: $PROD_SSH_HOST"
    debug "SSH Port: $PROD_SSH_PORT"
    debug "Remote App Dir: $PROD_APP_DIR"
    debug "Deploy Branch: $PROD_GIT_BRANCH"
}

# ── Validate local git state ────────────────────────────────────────────────
validate_git_state() {
    info "Validating git state..."

    # Check current branch
    CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
    if [[ "$CURRENT_BRANCH" != "$PROD_GIT_BRANCH" ]]; then
        error "Current branch ($CURRENT_BRANCH) doesn't match deploy branch ($PROD_GIT_BRANCH)"
        warn "Switch to '$PROD_GIT_BRANCH' or set PROD_GIT_BRANCH in .env"
        exit 1
    fi
    success "On correct branch: $CURRENT_BRANCH"

    # Check for uncommitted changes
    if ! git diff-index --quiet HEAD --; then
        if [[ "$FORCE" == "true" ]]; then
            DIRTY_WORKTREE=true
            DIRTY_TRACKED_FILES="$(git diff --name-only HEAD --)"
            warn "Uncommitted changes detected, but continuing due to --force"
            warn "Modified tracked files to sync after deploy:"
            while IFS= read -r changed_file; do
                [[ -n "$changed_file" ]] && warn "  - $changed_file"
            done <<< "$DIRTY_TRACKED_FILES"
        else
            error "You have uncommitted changes. Please commit or stash them first:"
            git status
            exit 1
        fi
    else
        success "No uncommitted changes"
    fi

    # Check for untracked files (warn only)
    if [[ -n "$(git ls-files --others --exclude-standard)" ]]; then
        warn "You have untracked files (they won't be deployed):"
        git ls-files --others --exclude-standard | sed 's/^/  /'
    fi

    # Get current commit hash
    COMMIT_HASH=$(git rev-parse HEAD)
    COMMIT_MSG=$(git log -1 --pretty=%B | head -1)
    success "Current commit: $COMMIT_HASH"
    debug "Commit message: $COMMIT_MSG"
}

# ── Push to remote ──────────────────────────────────────────────────────────
push_to_remote() {
    info "Pushing code to remote repository..."

    if [[ "$DRY_RUN" == "true" ]]; then
        info "[DRY-RUN] Would push $CURRENT_BRANCH to origin"
        return 0
    fi

    if git push origin "$CURRENT_BRANCH"; then
        success "Code pushed to remote ($CURRENT_BRANCH)"
    else
        error "Failed to push code to remote. Check your git configuration."
        exit 1
    fi
}

# ── Test SSH connection ─────────────────────────────────────────────────────
test_ssh_connection() {
    info "Testing SSH connection to production server..."

    local ssh_key_option=""
    if [[ -f "$SSH_KEY_PATH" ]]; then
        ssh_key_option="-i $SSH_KEY_PATH"
    fi

    if ssh $ssh_key_option \
        -p "$PROD_SSH_PORT" \
        -o ConnectTimeout=10 \
        -o StrictHostKeyChecking=accept-new \
        "$PROD_SSH_USER@$PROD_SSH_HOST" \
        "exit 0" 2>/dev/null; then
        success "SSH connection successful"
        return 0
    else
        error "Failed to connect via SSH to $PROD_SSH_USER@$PROD_SSH_HOST:$PROD_SSH_PORT"
        error "Verify:"
        error "  1. SSH key exists at: $SSH_KEY_PATH"
        error "  2. Production server is reachable"
        error "  3. PROD_SSH_HOST in .env is correct"
        error "  4. SSH user ($PROD_SSH_USER) has access"
        exit 1
    fi
}

# ── Pre-deployment vitals check (non-blocking) ─────────────────────────────
run_vitals_precheck() {
    info "Running vitals precheck before deployment..."

    local ssh_key_option=""
    if [[ -f "$SSH_KEY_PATH" ]]; then
        ssh_key_option="-i $SSH_KEY_PATH"
    fi

    local app_url
    app_url=$(ssh $ssh_key_option \
        -p "$PROD_SSH_PORT" \
        "$PROD_SSH_USER@$PROD_SSH_HOST" \
        "grep '^APP_URL=' '$PROD_APP_DIR/.env' 2>/dev/null | cut -d'=' -f2- | tr -d '\"' | tr -d \"'\"" || echo "")

    if [[ -z "$app_url" ]]; then
        app_url="https://$PROD_SSH_HOST"
        warn "APP_URL not found on remote. Falling back to: $app_url"
    fi

    if [[ ! -x "$APP_DIR/check_vital_routes.sh" ]]; then
        warn "check_vital_routes.sh not executable or missing; skipping vitals precheck."
        warn "Expected: $APP_DIR/check_vital_routes.sh"
        return 0
    fi

    if "$APP_DIR/check_vital_routes.sh" "$app_url"; then
        success "Vitals precheck passed on: $app_url"
    else
        warn "Vitals precheck reported issues, continuing due to bring-up mode request."
    fi
}

# ── Trigger remote deployment ───────────────────────────────────────────────
trigger_remote_deployment() {
    info "Triggering deployment on production server..."

    local ssh_key_option=""
    if [[ -f "$SSH_KEY_PATH" ]]; then
        ssh_key_option="-i $SSH_KEY_PATH"
    fi

    # Build deploy command
    local deploy_cmd="cd '$PROD_APP_DIR'"
    if [[ "$FORCE" == "true" ]]; then
        deploy_cmd="$deploy_cmd && git reset --hard HEAD && git clean -fd"
    fi

    if [[ "$NO_BUILD" == "true" ]]; then
        deploy_cmd="$deploy_cmd && git pull origin '$PROD_GIT_BRANCH'"
        deploy_cmd="$deploy_cmd && php artisan optimize:clear"
        deploy_cmd="$deploy_cmd && php artisan config:cache"
        deploy_cmd="$deploy_cmd && php artisan route:cache"
        deploy_cmd="$deploy_cmd && php artisan view:cache"
        [[ "$UP_MODE" == "true" ]] && deploy_cmd="$deploy_cmd && php artisan up || true"
    else
        deploy_cmd="$deploy_cmd && ./deploy.sh"
        [[ "$DRY_RUN" == "true" ]] && deploy_cmd="$deploy_cmd --dry-run"
    fi

    # Attempt to remediate missing or mismatched .env on remote before running deploy.sh
    info "Ensuring remote .env exists and matches local .env.production (if present)..."
    if [[ "$DRY_RUN" != "true" ]]; then
        # If local .env.production exists, compare with remote .env and upload if missing/different
        if [[ -f ".env.production" ]]; then
            info "Local .env.production found; comparing with remote .env"

            # compute local hash (portable)
            if command -v shasum >/dev/null 2>&1; then
                local_hash=$(shasum -a 256 .env.production | awk '{print $1}')
            elif command -v sha256sum >/dev/null 2>&1; then
                local_hash=$(sha256sum .env.production | awk '{print $1}')
            elif command -v openssl >/dev/null 2>&1; then
                local_hash=$(openssl dgst -sha256 .env.production | awk '{print $2}')
            else
                local_hash=""
            fi
            debug "Local .env.production hash: ${local_hash:-<none>}"

            # gather remote hash (or missing flag)
            remote_hash=$(ssh $ssh_key_option -p "$PROD_SSH_PORT" "$PROD_SSH_USER@$PROD_SSH_HOST" \
                "if [ -f '$PROD_APP_DIR/.env' ]; then \
                    if command -v shasum >/dev/null 2>&1; then shasum -a 256 '$PROD_APP_DIR/.env' | awk '{print \$1}'; \
                    elif command -v sha256sum >/dev/null 2>&1; then sha256sum '$PROD_APP_DIR/.env' | awk '{print \$1}'; \
                    elif command -v openssl >/dev/null 2>&1; then openssl dgst -sha256 '$PROD_APP_DIR/.env' | awk '{print \$2}'; \
                    else echo ''; fi; \
                 else echo '__MISSING__'; fi" 2>/dev/null || echo "__SSH_FAIL__")

            debug "Remote .env hash: ${remote_hash:-<none>}"

            if [[ "$remote_hash" == "__MISSING__" ]] || [[ -z "$remote_hash" ]] || [[ -n "$local_hash" && "$local_hash" != "$remote_hash" ]]; then
                info "Remote .env is missing or differs from local .env.production; uploading local .env.production → $PROD_APP_DIR/.env"
                # Try scp first
                scp_cmd=(scp -P "$PROD_SSH_PORT")
                if [[ -f "$SSH_KEY_PATH" ]]; then
                    scp_cmd+=( -i "$SSH_KEY_PATH" )
                fi
                scp_cmd+=( ".env.production" "${PROD_SSH_USER}@${PROD_SSH_HOST}:${PROD_APP_DIR}/.env" )

                if [[ "$DRY_RUN" == "true" ]]; then
                    info "[DRY-RUN] Would run: ${scp_cmd[*]}"
                else
                    if "${scp_cmd[@]}" 2>/dev/null; then
                        success "Uploaded .env.production to remote .env"
                    else
                        warn "scp failed; attempting upload via ssh+stdin"
                        # Fallback: write file contents via ssh stdin
                        if ssh $ssh_key_option -p "$PROD_SSH_PORT" "$PROD_SSH_USER@$PROD_SSH_HOST" "cat > '$PROD_APP_DIR/.env'" < .env.production; then
                            success "Wrote local .env.production to remote .env via ssh"
                        else
                            warn "Failed to transfer .env.production to remote; remote deploy may fail if .env is required"
                        fi
                    fi
                fi
            else
                success "Remote .env matches local .env.production; no upload required"
            fi
        else
            # No local .env.production — ensure remote .env exists (fallback to .env.example)
            ssh $ssh_key_option -p "$PROD_SSH_PORT" "$PROD_SSH_USER@$PROD_SSH_HOST" \
                "if [ ! -f '$PROD_APP_DIR/.env' ]; then \
                     if [ -f '$PROD_APP_DIR/.env.example' ]; then \
                         cp '$PROD_APP_DIR/.env.example' '$PROD_APP_DIR/.env' && echo '[OK] .env created from .env.example' || echo '[WARN] Failed to copy .env.example to .env'; \
                     else \
                         echo '[WARN] No .env.example present on remote to create .env'; \
                     fi; \
                 fi" 2>/dev/null || true
        fi
    fi

    # Ensure remote .env has an APP_KEY; generate one if missing/empty
    info "Checking remote APP_KEY..."
    if [[ "$DRY_RUN" != "true" ]]; then
        remote_app_key=$(ssh $ssh_key_option -p "$PROD_SSH_PORT" "$PROD_SSH_USER@$PROD_SSH_HOST" \
            "grep '^APP_KEY=' '$PROD_APP_DIR/.env' 2>/dev/null | cut -d'=' -f2- | tr -d '\"'" 2>/dev/null || echo "")

        if [[ -z "$remote_app_key" || "$remote_app_key" == "base64:" && ${#remote_app_key} -le 8 ]]; then
            warn "APP_KEY is missing or empty on remote; generating a new one..."
            ssh $ssh_key_option -p "$PROD_SSH_PORT" "$PROD_SSH_USER@$PROD_SSH_HOST" \
                "cd '$PROD_APP_DIR' && php artisan key:generate --force --no-interaction" 2>/dev/null \
                && success "APP_KEY generated on remote" \
                || warn "Failed to generate APP_KEY on remote — you may need to do this manually"
        else
            success "Remote APP_KEY is set"
        fi
    else
        info "[DRY-RUN] Would check/generate APP_KEY on remote"
    fi

    debug "Remote command: $deploy_cmd"

    if [[ "$DRY_RUN" == "true" ]]; then
        info "[DRY-RUN] Would execute on remote:"
        info "[DRY-RUN]   $deploy_cmd"
        return 0
    fi

    # Execute deployment remotely
    if ssh $ssh_key_option \
        -p "$PROD_SSH_PORT" \
        -o ConnectTimeout=10 \
        "$PROD_SSH_USER@$PROD_SSH_HOST" \
        "$deploy_cmd"; then
        success "Remote deployment completed successfully"
        return 0
    else
        local exit_code=$?
        # Provide a hint if this is likely caused by missing .env
        if [[ $exit_code -eq 1 ]]; then
            warn "Remote deploy returned exit code 1; check for missing .env on remote or errors in deploy.sh"
        fi
        error "Remote deployment failed with exit code: $exit_code"
        return "$exit_code"
    fi
}

# ── Post-deployment health check ────────────────────────────────────────────
check_deployment_health() {
    info "Checking deployment health..."

    local ssh_key_option=""
    if [[ -f "$SSH_KEY_PATH" ]]; then
        ssh_key_option="-i $SSH_KEY_PATH"
    fi

    # Get APP_URL from remote .env
    local app_url=$(ssh $ssh_key_option \
        -p "$PROD_SSH_PORT" \
        "$PROD_SSH_USER@$PROD_SSH_HOST" \
        "grep '^APP_URL=' '$PROD_APP_DIR/.env' 2>/dev/null | cut -d'=' -f2- | tr -d '\"' | tr -d \"'\"" || echo "")

    if [[ -z "$app_url" ]]; then
        warn "Could not determine APP_URL from remote .env"
        return 0
    fi

    debug "Checking APP_URL: $app_url"

    local status_code=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "$app_url" 2>/dev/null || echo "000")

    if [[ "$status_code" == "200" ]] || [[ "$status_code" == "302" ]]; then
        success "✓ Application health check passed (HTTP $status_code)"
        return 0
    else
        warn "✗ Application returned HTTP $status_code (expected 200/302)"
        warn "  Check the application logs on the server"
        return 1
    fi
}

# ── Sync Assets ─────────────────────────────────────────────────────────────
sync_assets() {
    info "Syncing assets to production..."

    # Check if rsync is installed
    if ! command -v rsync &>/dev/null; then
        warn "rsync not found. Skipping asset sync."
        return 0
    fi

    local ssh_key_option=""
    if [[ -f "$SSH_KEY_PATH" ]]; then
        ssh_key_option="-i $SSH_KEY_PATH"
    fi
    
    # Construct SSH command for rsync
    # We use StrictHostKeyChecking=accept-new to avoid prompts on first connect if not known
    local rsync_ssh="ssh -p $PROD_SSH_PORT $ssh_key_option -o StrictHostKeyChecking=accept-new"

    if [[ "$DRY_RUN" == "true" ]]; then
        info "[DRY-RUN] Would sync to production:"
        info "[DRY-RUN]   public/images/ → $PROD_APP_DIR/public/images/"
        info "[DRY-RUN]   public/lottie/ → $PROD_APP_DIR/public/lottie/"
        info "[DRY-RUN]   public/sounds/ → $PROD_APP_DIR/public/sounds/"
        info "[DRY-RUN]   public/fonts/  → $PROD_APP_DIR/public/fonts/"
        info "[DRY-RUN]   storage/app/public/ → $PROD_APP_DIR/storage/app/public/"
        return 0
    fi

    # 1. Sync public/images (background, logos, sponsor images, etc.)
    if [[ -d "public/images" ]]; then
        info "Syncing public/images..."
        if rsync -avz --no-o --no-g -e "$rsync_ssh" \
            --exclude='.DS_Store' \
            "public/images/" "$PROD_SSH_USER@$PROD_SSH_HOST:$PROD_APP_DIR/public/images/"; then
            success "public/images synced."
        else
            warn "Failed to sync public/images"
        fi
    fi

    # 2. Sync public/lottie (animation assets)
    if [[ -d "public/lottie" ]]; then
        info "Syncing public/lottie..."
        if rsync -avz --no-o --no-g -e "$rsync_ssh" \
            --exclude='.DS_Store' \
            "public/lottie/" "$PROD_SSH_USER@$PROD_SSH_HOST:$PROD_APP_DIR/public/lottie/"; then
            success "public/lottie synced."
        else
            warn "Failed to sync public/lottie"
        fi
    fi

    # 3. Sync public/sounds (audio assets)
    if [[ -d "public/sounds" ]]; then
        info "Syncing public/sounds..."
        if rsync -avz --no-o --no-g -e "$rsync_ssh" \
            --exclude='.DS_Store' \
            "public/sounds/" "$PROD_SSH_USER@$PROD_SSH_HOST:$PROD_APP_DIR/public/sounds/"; then
            success "public/sounds synced."
        else
            warn "Failed to sync public/sounds"
        fi
    fi

    # 4. Sync public/fonts (webfonts)
    if [[ -d "public/fonts" ]]; then
        info "Syncing public/fonts..."
        if rsync -avz --no-o --no-g -e "$rsync_ssh" \
            --exclude='.DS_Store' \
            "public/fonts/" "$PROD_SSH_USER@$PROD_SSH_HOST:$PROD_APP_DIR/public/fonts/"; then
            success "public/fonts synced."
        else
            warn "Failed to sync public/fonts"
        fi
    fi

    # 5. Sync storage/app/public (user uploads)
    if [[ -d "storage/app/public" ]]; then
        info "Syncing storage/app/public..."
        if rsync -avz --no-o --no-g -e "$rsync_ssh" \
            --exclude='.DS_Store' \
            "storage/app/public/" "$PROD_SSH_USER@$PROD_SSH_HOST:$PROD_APP_DIR/storage/app/public/"; then
            success "storage/app/public synced."
        else
            warn "Failed to sync storage/app/public"
        fi
    fi
    
    success "Asset sync process completed."
}

# ── Sync force-overrides (dirty tracked files) ─────────────────────────────
sync_force_overrides() {
    if [[ "$FORCE" != "true" || "$DIRTY_WORKTREE" != "true" ]]; then
        return 0
    fi

    if [[ -z "$DIRTY_TRACKED_FILES" ]]; then
        info "Force mode enabled with dirty tree, but no tracked files to sync."
        return 0
    fi

    info "Force mode: syncing uncommitted tracked files to production..."

    if ! command -v rsync &>/dev/null; then
        error "rsync is required to sync uncommitted files in --force mode"
        return 1
    fi

    local ssh_key_option=""
    if [[ -f "$SSH_KEY_PATH" ]]; then
        ssh_key_option="-i $SSH_KEY_PATH"
    fi

    local rsync_ssh="ssh -p $PROD_SSH_PORT $ssh_key_option -o StrictHostKeyChecking=accept-new"
    local synced_count=0

    while IFS= read -r rel_path; do
        [[ -z "$rel_path" ]] && continue

        if [[ ! -e "$APP_DIR/$rel_path" ]]; then
            warn "Skipping deleted/missing file in working tree: $rel_path"
            continue
        fi

        local remote_parent
        remote_parent="$(dirname "$rel_path")"

        if ! ssh $ssh_key_option -p "$PROD_SSH_PORT" "$PROD_SSH_USER@$PROD_SSH_HOST" "mkdir -p '$PROD_APP_DIR/$remote_parent'"; then
            warn "Could not prepare remote directory for: $rel_path"
            continue
        fi

        if rsync -az --no-o --no-g -e "$rsync_ssh" "$APP_DIR/$rel_path" "$PROD_SSH_USER@$PROD_SSH_HOST:$PROD_APP_DIR/$rel_path"; then
            success "Synced override: $rel_path"
            synced_count=$((synced_count + 1))
        else
            warn "Failed to sync override: $rel_path"
        fi
    done <<< "$DIRTY_TRACKED_FILES"

    info "Refreshing Laravel caches after force override sync..."
    ssh $ssh_key_option -p "$PROD_SSH_PORT" "$PROD_SSH_USER@$PROD_SSH_HOST" \
        "cd '$PROD_APP_DIR' && php artisan optimize:clear >/dev/null 2>&1 || true && php artisan view:cache >/dev/null 2>&1 || true" \
        && success "Laravel caches refreshed on remote" \
        || warn "Could not refresh Laravel caches on remote"

    success "Force override sync completed ($synced_count file(s) synced)."
}

# ── Sync backups from remote to local ──────────────────────────────────────
sync_backups_from_remote() {
    if [[ "$NO_BACKUP_SYNC" == "true" ]]; then
        info "Backup sync skipped (--no-backup-sync)."
        return 0
    fi

    info "Syncing production backups to local..."

    if ! command -v rsync &>/dev/null; then
        warn "rsync not found — cannot sync backups. Install rsync or use --no-backup-sync."
        return 0
    fi

    local ssh_key_option=""
    if [[ -f "$SSH_KEY_PATH" ]]; then
        ssh_key_option="-i $SSH_KEY_PATH"
    fi

    # Resolve local backup dir (make absolute if relative)
    local local_dir="$LOCAL_BACKUP_DIR"
    if [[ "$local_dir" != /* ]]; then
        local_dir="$APP_DIR/$local_dir"
    fi

    local remote_backup_dir="$PROD_APP_DIR/storage/backups/"
    local rsync_ssh="ssh -p $PROD_SSH_PORT $ssh_key_option -o StrictHostKeyChecking=accept-new"

    if [[ "$DRY_RUN" == "true" ]]; then
        info "[DRY-RUN] Would rsync production backups:"
        info "[DRY-RUN]   $PROD_SSH_USER@$PROD_SSH_HOST:$remote_backup_dir"
        info "[DRY-RUN]   → $local_dir/"
        return 0
    fi

    # Create local target directory
    mkdir -p "$local_dir" || {
        warn "Could not create local backup directory: $local_dir"
        return 1
    }

    # Ensure remote backup directory exists (create silently if missing)
    ssh $ssh_key_option \
        -p "$PROD_SSH_PORT" -o ConnectTimeout=10 \
        "$PROD_SSH_USER@$PROD_SSH_HOST" \
        "mkdir -p '$remote_backup_dir'" 2>/dev/null || true

    # ── Trigger a fresh database dump on production before pulling ────────────
    info "Creating database snapshot on production server..."
    # Heredoc WITHOUT quoting the delimiter: $PROD_APP_DIR expands locally;
    # \$ variables are escaped so they expand only on the remote side.
    # shellcheck disable=SC2087
    ssh $ssh_key_option \
        -p "$PROD_SSH_PORT" \
        -o ConnectTimeout=30 \
        -o StrictHostKeyChecking=accept-new \
        "$PROD_SSH_USER@$PROD_SSH_HOST" \
        "bash -s" << REMOTE_DUMP
set -e
APP="${PROD_APP_DIR}"
cd "\$APP" || exit 1
mkdir -p storage/backups
TS=\$(date +%Y%m%d_%H%M%S)
DB_CON=\$(grep '^DB_CONNECTION=' .env 2>/dev/null | cut -d= -f2- | tr -d '"' || echo 'sqlite')
if [ "\$DB_CON" = "sqlite" ]; then
    DB_FILE=\$(grep '^DB_DATABASE=' .env 2>/dev/null | cut -d= -f2- | tr -d '"')
    case "\$DB_FILE" in /*) ;; *) DB_FILE="\$APP/\$DB_FILE" ;; esac
    cp "\$DB_FILE" "storage/backups/db_\${TS}.sqlite" \
        && echo "[OK]  SQLite snapshot: db_\${TS}.sqlite" \
        || echo "[WARN] SQLite copy failed — check DB_DATABASE in remote .env"
else
    H=\$(grep '^DB_HOST='     .env 2>/dev/null | cut -d= -f2- | tr -d '"'); H=\${H:-127.0.0.1}
    P=\$(grep '^DB_PORT='     .env 2>/dev/null | cut -d= -f2- | tr -d '"'); P=\${P:-3306}
    N=\$(grep '^DB_DATABASE=' .env 2>/dev/null | cut -d= -f2- | tr -d '"')
    U=\$(grep '^DB_USERNAME=' .env 2>/dev/null | cut -d= -f2- | tr -d '"')
    W=\$(grep '^DB_PASSWORD=' .env 2>/dev/null | cut -d= -f2- | tr -d '"')
    mysqldump -h "\$H" -P "\$P" -u "\$U" -p"\$W" \
        --single-transaction --quick --routines --events \
        "\$N" > "storage/backups/db_\${TS}.sql" 2>/dev/null \
        && echo "[OK]  MySQL dump: db_\${TS}.sql" \
        || { echo "[WARN] mysqldump failed — check DB credentials"; rm -f "storage/backups/db_\${TS}.sql"; }
fi
REMOTE_DUMP
    [[ $? -ne 0 ]] && warn "Remote DB dump returned non-zero — backup may be incomplete"

    # Pull all backup files (SQL, SQLite, CSV) — only newer than local copies
    # --update  : skip files that are newer locally
    # --no-o/g  : don't try to preserve remote owner/group (avoids permission errors)
    # -avz      : archive, verbose, compress in transit
    if rsync -avz --update --no-o --no-g \
        --include='*.sql' \
        --include='*.sqlite' \
        --include='*.csv' \
        --exclude='*' \
        -e "$rsync_ssh" \
        "$PROD_SSH_USER@$PROD_SSH_HOST:$remote_backup_dir" \
        "$local_dir/"; then
        success "Backups synced to: $local_dir"
        # List what we have locally now
        local count
        count=$(find "$local_dir" -maxdepth 1 \( -name '*.sql' -o -name '*.sqlite' -o -name '*.csv' \) 2>/dev/null | wc -l | tr -d ' ')
        info "Local backup store: $count file(s) in $local_dir"
    else
        warn "rsync returned a non-zero exit code — some backups may not have transferred."
        warn "Check SSH access and remote path: $remote_backup_dir"
    fi
}

# ── Sync user-uploaded media from production to local ──────────────────────
sync_media_from_remote() {
    if [[ "$NO_MEDIA_SYNC" == "true" ]]; then
        info "Media sync skipped (--no-media-sync)."
        return 0
    fi

    info "Syncing production media → local (storage/app/public)..."

    if ! command -v rsync &>/dev/null; then
        warn "rsync not found — cannot sync media. Install rsync or use --no-media-sync."
        return 0
    fi

    local ssh_key_option=""
    if [[ -f "$SSH_KEY_PATH" ]]; then
        ssh_key_option="-i $SSH_KEY_PATH"
    fi

    local local_media_dir="$APP_DIR/storage/app/public"
    local remote_media_dir="$PROD_APP_DIR/storage/app/public/"
    local rsync_ssh="ssh -p $PROD_SSH_PORT $ssh_key_option -o StrictHostKeyChecking=accept-new"

    if [[ "$DRY_RUN" == "true" ]]; then
        info "[DRY-RUN] Would rsync production media:"
        info "[DRY-RUN]   $PROD_SSH_USER@$PROD_SSH_HOST:$remote_media_dir"
        info "[DRY-RUN]   → $local_media_dir/"
        return 0
    fi

    # Verify remote media dir exists
    if ! ssh $ssh_key_option \
        -p "$PROD_SSH_PORT" \
        -o ConnectTimeout=10 \
        "$PROD_SSH_USER@$PROD_SSH_HOST" \
        "[[ -d '$remote_media_dir' ]]" 2>/dev/null; then
        warn "Remote media directory does not exist yet: $remote_media_dir"
        return 0
    fi

    mkdir -p "$local_media_dir" || {
        warn "Could not create local media directory: $local_media_dir"
        return 1
    }

    # Pull all uploaded files from production — only newer than local copies
    if rsync -avz --update --no-o --no-g \
        -e "$rsync_ssh" \
        "$PROD_SSH_USER@$PROD_SSH_HOST:$remote_media_dir" \
        "$local_media_dir/"; then
        success "Media synced: production → $local_media_dir"
        local count
        count=$(find "$local_media_dir" -type f 2>/dev/null | wc -l | tr -d ' ')
        info "Local media store: $count file(s)"
    else
        warn "rsync returned non-zero — some media files may not have transferred."
        warn "Check SSH access and remote path: $remote_media_dir"
    fi
}

# ── Print summary ──────────────────────────────────────────────────────────
print_summary() {
    local exit_code=$1

    echo ""
    if [[ $exit_code -eq 0 ]]; then
        echo -e "${GREEN}========================================${NC}"
        echo -e "${GREEN}  ✓ Publication successful!${NC}"
        echo -e "${GREEN}========================================${NC}"
        echo ""
        success "Deployed: $COMMIT_HASH ($COMMIT_MSG)"
        success "To: $PROD_SSH_USER@$PROD_SSH_HOST:$PROD_APP_DIR"
        success "Branch: $PROD_GIT_BRANCH"
        [[ "$FRESH" == "true" ]] && info "Database: Fresh (all tables dropped and re-created)"
        [[ "$SEED" == "true" ]] && info "Seeders: Executed"
        [[ "$NO_BACKUP_SYNC" != "true" ]] && info "DB backups synced to: ${LOCAL_BACKUP_DIR}"
        info "Images/assets pushed to production"
        echo ""
    else
        echo -e "${RED}========================================${NC}"
        echo -e "${RED}  ✗ Publication failed!${NC}"
        echo -e "${RED}========================================${NC}"
        echo ""
        error "Deployment to production did not complete successfully"
        error "Exit code: $exit_code"
        echo ""
        warn "Next steps:"
        warn "  1. Check deployment logs on the production server"
        warn "  2. SSH into: $PROD_SSH_USER@$PROD_SSH_HOST"
        warn "  3. Check: tail -f $PROD_APP_DIR/storage/logs/laravel.log"
        echo ""
    fi
}

# ── Cleanup and exit handler ─────────────────────────────────────────────────
cleanup_and_exit() {
    local exit_code=$?
    print_summary "$exit_code"
    exit "$exit_code"
}

trap cleanup_and_exit EXIT

# ── Main execution ───────────────────────────────────────────────────────────

echo ""
echo -e "${CYAN}╔════════════════════════════════════════╗${NC}"
echo -e "${CYAN}║  AlSarya TV - Production Deployment    ║${NC}"
echo -e "${CYAN}╚════════════════════════════════════════╝${NC}"
echo ""

# Handle --sync-assets-only mode
if [[ "$SYNC_ASSETS_ONLY" == "true" ]]; then
    info "Running in --sync-assets-only mode"
    info "Syncing local assets to production..."
    validate_prerequisites
    test_ssh_connection
    sync_assets
    sync_force_overrides
    success "Asset sync completed"
    exit 0
fi

# Run validation and deployment steps
validate_prerequisites
validate_git_state
test_ssh_connection

echo ""
echo -e "${YELLOW}Deployment Configuration:${NC}"
echo "  Server: $PROD_SSH_USER@$PROD_SSH_HOST:$PROD_SSH_PORT"
echo "  App Dir: $PROD_APP_DIR"
echo "  Branch: $PROD_GIT_BRANCH"
echo "  Commit: $COMMIT_HASH"
[[ "$NO_BACKUP_SYNC" != "true" ]] && echo "  DB backup sync → local: ${LOCAL_BACKUP_DIR}"
echo "  Image/asset push → remote: public/images, public/lottie, public/sounds, public/fonts"
[[ "$DIRTY_WORKTREE" == "true" ]] && echo "  Force override sync: enabled (dirty tracked files will be uploaded)"
[[ "$FRESH" == "true" ]] && echo "  Mode: FRESH database (destructive - drops all data)"
[[ "$RESET_DB" == "true" ]] && echo "  Mode: RESET database (migrate:fresh)"
[[ "$UP_MODE" == "true" ]] && echo "  Mode: UP (force bring site online even if maintenance is active)"
[[ "$QUICK_UP" == "true" ]] && echo "  Quick-Up: Enabled (--up --force --no-build)"
[[ "$DRY_RUN" == "true" ]] && echo "  Mode: DRY-RUN (no changes)"
[[ "$NO_BUILD" == "true" ]] && echo "  Build: Skipped (--no-build)"
echo ""

# Always run vitals before explicit bring-up modes
if [[ "$UP_MODE" == "true" || "$QUICK_UP" == "true" ]]; then
    run_vitals_precheck
    echo ""
fi

if [[ "$DRY_RUN" != "true" ]]; then
    if [[ "$FORCE" == "true" ]]; then
        warn "--force enabled: auto-confirming deployment prompt"
    else
        read -p "Continue with deployment? (y/n) " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            warn "Deployment cancelled by user"
            exit 0
        fi
    fi
fi

push_to_remote
trigger_remote_deployment

# Only run asset push + backup sync + health check if deployment succeeded
if [[ $? -eq 0 ]]; then
    sync_assets
    sync_force_overrides
    sync_backups_from_remote
    check_deployment_health || warn "Health check failed - check server logs"
fi
