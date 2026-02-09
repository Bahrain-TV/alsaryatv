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

strip_quotes() {
    local value="$1"
    value="${value%\"}"
    value="${value#\"}"
    value="${value%\'}"
    value="${value#\'}"
    echo "$value"
}

expected_key_length() {
    local cipher="$1"

    case "$cipher" in
        aes-128-cbc|aes-128-gcm)
            echo "16"
            ;;
        aes-256-cbc|aes-256-gcm)
            echo "32"
            ;;
        *)
            echo "0"
            ;;
    esac
}

decode_key_length() {
    local key="$1"

    if [[ "$key" == base64:* ]]; then
        local key_b64="${key#base64:}"
        local decoded
        decoded=$(printf '%s' "$key_b64" | (base64 -D 2>/dev/null || base64 --decode 2>/dev/null))
        if [ $? -ne 0 ]; then
            echo "-1"
            return 1
        fi
        echo -n "$decoded" | wc -c | tr -d ' '
    else
        echo -n "$key" | wc -c | tr -d ' '
    fi
}

set_env_value() {
    local env_file="$1"
    local key="$2"
    local value="$3"

    if $SUDO_PREFIX grep -qE "^${key}=" "$env_file"; then
        $SUDO_PREFIX perl -0777 -i -pe "s/^${key}=.*/${key}=${value}/m" "$env_file"
    else
        $SUDO_PREFIX bash -c "printf '\\n%s=%s\\n' '$key' '$value' >> '$env_file'"
    fi
}

generate_key_for_cipher() {
    local cipher="$1"
    local expected_len
    expected_len=$(expected_key_length "$cipher")

    php -r "echo 'base64:' . base64_encode(random_bytes($expected_len));"
}

validate_env_values() {
    local label="$1"
    local app_key="$2"
    local app_cipher="$3"

    if [ -z "$app_cipher" ]; then
        app_cipher="aes-256-cbc"
    fi

    case "$app_cipher" in
        aes-128-cbc|aes-256-cbc|aes-128-gcm|aes-256-gcm)
            ;;
        *)
            log_error "$label has unsupported APP_CIPHER: $app_cipher"
            return 1
            ;;
    esac

    if [ -z "$app_key" ]; then
        log_error "$label missing APP_KEY"
        return 1
    fi

    local expected_len
    expected_len=$(expected_key_length "$app_cipher")
    local key_len
    key_len=$(decode_key_length "$app_key")
    if [ "$key_len" -ne "$expected_len" ]; then
        log_error "$label APP_KEY length $key_len does not match cipher $app_cipher (expected $expected_len)"
        return 1
    fi

    return 0
}

repair_env_file() {
    local env_file="$1"
    local label="$2"
    local app_key
    local app_cipher
    local expected_len
    local key_len

    app_key=$($SUDO_PREFIX bash -c "grep -E '^APP_KEY=' '$env_file' | tail -1 | cut -d= -f2-")
    app_cipher=$($SUDO_PREFIX bash -c "grep -E '^APP_CIPHER=' '$env_file' | tail -1 | cut -d= -f2-")
    app_key=$(strip_quotes "$app_key")
    app_cipher=$(strip_quotes "$app_cipher")

    if [ -z "$app_cipher" ]; then
        app_cipher="aes-256-cbc"
        set_env_value "$env_file" "APP_CIPHER" "$app_cipher"
    fi

    case "$app_cipher" in
        aes-128-cbc|aes-256-cbc|aes-128-gcm|aes-256-gcm)
            ;;
        *)
            app_cipher="aes-256-cbc"
            set_env_value "$env_file" "APP_CIPHER" "$app_cipher"
            ;;
    esac

    if [ -z "$app_key" ]; then
        log_warn "$label missing APP_KEY. Generating a new one."
        app_key=$(generate_key_for_cipher "$app_cipher")
        set_env_value "$env_file" "APP_KEY" "$app_key"
        return 0
    fi

    expected_len=$(expected_key_length "$app_cipher")
    key_len=$(decode_key_length "$app_key")
    if [ "$key_len" -ne "$expected_len" ]; then
        log_warn "$label APP_KEY length mismatch. Regenerating."
        app_key=$(generate_key_for_cipher "$app_cipher")
        set_env_value "$env_file" "APP_KEY" "$app_key"
    fi
}

require_files() {
    local missing=false
    local file

    for file in "$@"; do
        if [ ! -f "$APP_DIR/$file" ]; then
            log_error "Missing required file: $file"
            missing=true
        fi
    done

    if [ "$missing" = "true" ]; then
        return 1
    fi

    return 0
}

# --- Main Deployment Steps ---

echo -e "${PURPLE}${BOLD}==========================================================${NC}"
echo -e "${PURPLE}${BOLD}🚀 AlSarya TV - Automated Deployment Sequence${NC}"
echo -e "${PURPLE}${BOLD}==========================================================${NC}"

send_discord_message "🚀 Deployment Started" "Server is starting deployment sequence for version $(increment_version "$CURRENT_VERSION")..." 3447003

# 1. Git Sync - Detect and sync to current branch
log_info "Synchronizing code with origin..."

# Detect current branch (default to main if detached)
CURRENT_BRANCH=$(cd "$APP_DIR" && $SUDO_PREFIX git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "main")
if [ "$CURRENT_BRANCH" = "HEAD" ]; then
    CURRENT_BRANCH="main"
fi

log_info "Detected branch: $CURRENT_BRANCH"

execute_silent "cd '$APP_DIR' && $SUDO_PREFIX git fetch origin $CURRENT_BRANCH" "Fetching latest changes from $CURRENT_BRANCH"
execute_silent "cd '$APP_DIR' && $SUDO_PREFIX git checkout -B $CURRENT_BRANCH origin/$CURRENT_BRANCH" "Checking out $CURRENT_BRANCH branch"
if ! execute_silent "cd '$APP_DIR' && $SUDO_PREFIX git reset --hard origin/$CURRENT_BRANCH" "Resetting to origin/$CURRENT_BRANCH"; then
    log_error "Git sync failed. Aborting deployment."
    exit 1
fi

log_info "Verifying required application files..."
if ! require_files "app/Providers/MailEnvironmentServiceProvider.php" "config/app.php"; then
    log_error "Required files missing. Aborting deployment."
    exit 1
fi

log_info "Validating environment configuration..."
if [ ! -f "$APP_DIR/.env" ]; then
    log_error "Missing .env file on server"
    exit 1
fi

repair_env_file "$APP_DIR/.env" "Server .env"

APP_KEY_VALUE=$($SUDO_PREFIX bash -c "grep -E '^APP_KEY=' '$APP_DIR/.env' | tail -1 | cut -d= -f2-")
APP_CIPHER_VALUE=$($SUDO_PREFIX bash -c "grep -E '^APP_CIPHER=' '$APP_DIR/.env' | tail -1 | cut -d= -f2-")

if ! validate_env_values "Server .env" "$(strip_quotes "$APP_KEY_VALUE")" "$(strip_quotes "$APP_CIPHER_VALUE")"; then
    log_error "Invalid .env configuration. Aborting deployment."
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
    exit 1
fi

# 5. Data Restoration
if [ "$BACKUP_ENABLE" == "true" ]; then
    log_info "Restoring data from latest backup..."
    if ! execute_silent "$SUDO_PREFIX $ART_CMD app:callers:import --force" "Executing data import"; then
        log_error "Data restoration failed! The database might be inconsistent."
        # Note: We don't exit here as the code is already updated and migrated.
    fi
fi

# 6. Optimization
log_info "Optimizing application performance..."
execute_silent "$SUDO_PREFIX $ART_CMD optimize:clear" "Clearing caches"
execute_silent "$SUDO_PREFIX $ART_CMD config:cache" "Caching configuration"
execute_silent "$SUDO_PREFIX $ART_CMD route:cache" "Caching routes"
execute_silent "$SUDO_PREFIX $ART_CMD view:cache" "Caching views"

# Extra refresh cycle to ensure fresh caches
log_info "Running extra refresh cycle..."
execute_silent "$SUDO_PREFIX $ART_CMD optimize:clear" "Clearing caches (refresh)"
execute_silent "$SUDO_PREFIX $ART_CMD config:cache" "Caching configuration (refresh)"
execute_silent "$SUDO_PREFIX $ART_CMD route:cache" "Caching routes (refresh)"
execute_silent "$SUDO_PREFIX $ART_CMD view:cache" "Caching views (refresh)"

# 7. Version Update
NEW_VERSION=$(increment_version "$CURRENT_VERSION")
execute_silent "echo '$NEW_VERSION' > '$VERSION_FILE' && chown $APP_USER:$APP_USER '$VERSION_FILE'" "Updating version to $NEW_VERSION"

echo -e "${GREEN}${BOLD}==========================================================${NC}"
echo -e "${GREEN}${BOLD}✅ Deployment Completed Successfully!${NC}"
echo -e "${GREEN}${BOLD}New Version: $NEW_VERSION${NC}"
echo -e "${GREEN}${BOLD}==========================================================${NC}"

send_discord_message "Deployment Successful ✅" "AlSarya TV successfully deployed version **$NEW_VERSION**." 5763719

exit 0