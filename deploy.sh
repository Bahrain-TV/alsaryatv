#!/bin/bash

# AlSarya TV - Deployment Script with Data Persistence
# Supports automated data backup/restore during deployment
# For emergency restore: ./deploy.sh restore <backup-filename>

# --- Colors for Terminal ---
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color
BOLD='\033[1m'

# --- Configuration ---
if [ -z "$APP_ENV" ]; then
    APP_ENV="staging"
else
    APP_ENV="production"
fi

# Configuration variables
SERVER="root@h6.doy.tech"
APP_DIR="/home/alsarya.tv/public_html"
ART_CMD="php artisan"
APP_USER="alsar4210"
DISCORD_WEBHOOK="https://discord.com/api/webhooks/1248966065417883659/hAnbGrEOLw9fWF6UCObAuuXHzW6ZM5I1babbBC4rBAdbUAB6YcfHqHhxZXEU4LYIyZp2"
SUDO_PREFIX="sudo -u $APP_USER"

# Data persistence variables
BACKUP_ENABLE=true
BACKUP_DIR="$APP_DIR/storage/app/backups/callers"
DEPLOYMENT_ID="deploy_$(date +%s)"

# Version management
VERSION_FILE="$APP_DIR/version.txt"
if [ -f "$VERSION_FILE" ]; then
    CURRENT_VERSION=$(cat "$VERSION_FILE")
else
    CURRENT_VERSION="1.0.0"
fi

# --- Helper Functions ---

log_info() { echo -e "${BLUE}${BOLD}INFO:${NC} $1"; }
log_success() { echo -e "${GREEN}${BOLD}SUCCESS:${NC} $1"; }
log_warn() { echo -e "${YELLOW}${BOLD}WARNING:${NC} $1"; }
log_error() { echo -e "${RED}${BOLD}ERROR:${NC} $1"; }

send_discord_message() {
    local title="$1"
    local description="$2"
    local color="$3" # e.g., 5763719 (Green), 15548997 (Red), 3447003 (Blue)

    if [ -z "$color" ]; then color=5763719; fi
    TIMESTAMP=$(date -u +%Y-%m-%dT%H:%M:%S.000Z)

    PAYLOAD=$(cat <<EOF
{
  "embeds": [
    {
      "title": "$title",
      "description": "$description",
      "color": $color,
      "timestamp": "$TIMESTAMP",
      "footer": { "text": "AlSarya TV Server Deployment" }
    }
  ]
}
EOF
)
    curl -s -H "Content-Type: application/json" -X POST -d "$PAYLOAD" "$DISCORD_WEBHOOK" > /dev/null 2>&1
}

increment_version() {
    local version=$1
    local major minor patch
    IFS='.' read -r major minor patch <<< "$version"
    patch=$((patch + 1))
    if [ $patch -ge 100 ]; then
        patch=0
        minor=$((minor + 1))
        if [ $minor -ge 100 ]; then
            minor=0
            major=$((major + 1))
        fi
    fi
    echo "${major}.${minor}.${patch}"
}

# Execute command and only show output on error
execute_silent() {
    local cmd="$1"
    local msg="$2"
    
    echo -ne "  → $msg... "
    output=$(eval "$cmd" 2>&1)
    status=$?
    
    if [ $status -eq 0 ]; then
        echo -e "${GREEN}DONE${NC}"
    else
        echo -e "${RED}FAILED${NC}"
        echo -e "${YELLOW}--- Error Output ---${NC}"
        echo -e "$output"
        echo -e "${YELLOW}--------------------${NC}"
        return $status
    fi
}

# --- Main Deployment Steps ---

echo -e "${PURPLE}${BOLD}==========================================================${NC}"
echo -e "${PURPLE}${BOLD}🚀 AlSarya TV - Automated Deployment Sequence${NC}"
echo -e "${PURPLE}${BOLD}==========================================================${NC}"

send_discord_message "🚀 Deployment Started" "Server is starting deployment sequence for version $(increment_version "$CURRENT_VERSION")..." 3447003

# 1. Git Sync
log_info "Synchronizing code with origin/main..."
execute_silent "$SUDO_PREFIX git fetch origin main" "Fetching latest changes"
execute_silent "$SUDO_PREFIX git checkout -B main origin/main" "Checking out main branch"
if ! execute_silent "$SUDO_PREFIX git reset --hard origin/main" "Resetting to origin/main"; then
    log_error "Git sync failed. Aborting deployment."
    send_discord_message "Deployment Failed ❌" "Git synchronization failed on server." 15548997
    exit 1
fi

# 2. Cleanup (use sudo directly for root-owned files, preserving public_html ownership)
execute_silent "sudo rm -rf .github PROJECT_DOCS tests .vscode .claude .gitignore README.md CLAUDE.md TODO.md" "Cleaning up development files"

# 3. Pre-deployment Backup
if [ "$BACKUP_ENABLE" == "true" ]; then
    log_info "Creating pre-deployment data backup..."
    if ! execute_silent "$SUDO_PREFIX $ART_CMD app:persist-data --export-csv --verify" "Exporting callers database"; then
        log_warn "Backup failed or had warnings. Continuing with caution."
    fi
fi

# 4. Migrations
log_info "Running database migrations..."
if ! execute_silent "$SUDO_PREFIX $ART_CMD migrate --force" "Executing artisan migrate"; then
    log_error "Migration failed. Critical error."
    send_discord_message "Deployment Failed ❌" "Database migration failed on server." 15548997
    exit 1
fi

# 5. Data Restoration
if [ "$BACKUP_ENABLE" == "true" ]; then
    log_info "Restoring data from latest backup..."
    if ! execute_silent "$SUDO_PREFIX $ART_CMD app:callers:import --force" "Executing data import"; then
        log_error "Data restoration failed! The database might be inconsistent."
        send_discord_message "Deployment Warning ⚠️" "Data restoration failed during deployment. Manual check required." 15548997
        # Note: We don't exit here as the code is already updated and migrated.
    fi
fi

# 6. Optimization
log_info "Optimizing application performance..."
execute_silent "$SUDO_PREFIX $ART_CMD optimize:clear" "Clearing caches"
execute_silent "$SUDO_PREFIX $ART_CMD config:cache" "Caching configuration"
execute_silent "$SUDO_PREFIX $ART_CMD route:cache" "Caching routes"
execute_silent "$SUDO_PREFIX $ART_CMD view:cache" "Caching views"

# 7. Version Update
NEW_VERSION=$(increment_version "$CURRENT_VERSION")
execute_silent "echo '$NEW_VERSION' > '$VERSION_FILE' && chown $APP_USER:$APP_USER '$VERSION_FILE'" "Updating version to $NEW_VERSION"

echo -e "${GREEN}${BOLD}==========================================================${NC}"
echo -e "${GREEN}${BOLD}✅ Deployment Completed Successfully!${NC}"
echo -e "${GREEN}${BOLD}New Version: $NEW_VERSION${NC}"
echo -e "${GREEN}${BOLD}==========================================================${NC}"

send_discord_message "Deployment Successful ✅" "AlSarya TV successfully deployed version **$NEW_VERSION**." 5763719

exit 0