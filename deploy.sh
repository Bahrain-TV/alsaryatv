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

# ── Pre-flight checks ───────────────────────────────────────────────────────
APP_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$APP_DIR"

info "Deploying from $APP_DIR"

if [[ ! -f artisan ]]; then
    error "artisan not found — are you in the Laravel project root?"
    exit 1
fi

if [[ ! -f .env ]]; then
    error ".env file not found. Copy .env.example and configure it first."
    exit 1
fi

# ── Step 1: Maintenance mode ────────────────────────────────────────────────
info "Enabling maintenance mode..."
run php artisan down --retry=30 --refresh=15 || true

# ── Step 2: Pull latest code (if in a git repo) ─────────────────────────────
if [[ -d .git ]]; then
    info "Pulling latest changes from git..."
    run git pull --ff-only || warn "git pull failed — continuing with local state"
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
        run php artisan db:seed --force
        success "Database seeded (UserSeeder + CallerSeeder)."
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

# ── Step 12: Disable maintenance mode ────────────────────────────────────────
info "Disabling maintenance mode..."
run php artisan up
success "Application is live."

# ── Summary ──────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Deployment complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
info "Migration status:"
if [[ "$DRY_RUN" == "false" ]]; then
    php artisan migrate:status
fi
echo ""
[[ "$SEED" == "true" ]] && info "Seeders executed: UserSeeder, CallerSeeder"
[[ "$FRESH" == "true" ]] && warn "Database was freshly rebuilt (all previous data dropped)."
echo ""
success "Deploy finished at $(date '+%Y-%m-%d %H:%M:%S')"
