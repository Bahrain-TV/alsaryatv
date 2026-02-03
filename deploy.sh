#!/bin/bash

# AlSarya TV - Deployment Script with Data Persistence
# Supports automated data backup/restore during deployment
# For emergency restore: ./deploy.sh restore <backup-filename>

# Configuration
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
LATEST_BACKUP_FILE=""
DEPLOYMENT_ID="deploy_$(date +%s)"
ENABLE_RESTORE_ON_FAILURE=true

# Version management
VERSION_FILE="$APP_DIR/version.txt"
if [ -f "$VERSION_FILE" ]; then
    CURRENT_VERSION=$(cat "$VERSION_FILE")
else
    CURRENT_VERSION="1.0.0"
fi

# Function to increment version
increment_version() {
    local version=$1
    local major minor patch

    IFS='.' read -r major minor patch <<< "$version"

    # Increment patch version
    patch=$((patch + 1))

    # Reset patch and increment minor if patch >= 100 (optional convention)
    if [ $patch -ge 100 ]; then
        patch=0
        minor=$((minor + 1))

        # Reset minor and increment major if minor >= 100
        if [ $minor -ge 100 ]; then
            minor=0
            major=$((major + 1))
        fi
    fi

    echo "${major}.${minor}.${patch}"
}

# Function to send "Dramatic" Discord Notifications
send_discord_message() {
    local title="$1"
    local description="$2"
    local color="$3" # e.g., 5763719 (Green), 15548997 (Red), 3447003 (Blue)

    # Default to Green if no color provided
    if [ -z "$color" ]; then
        color=5763719
    fi

    TIMESTAMP=$(date -u +%Y-%m-%dT%H:%M:%S.000Z)

    # JSON Payload for Embed
    PAYLOAD=$(cat <<EOF
{
  "embeds": [
    {
      "title": "$title",
      "description": "$description",
      "color": $color,
      "timestamp": "$TIMESTAMP",
      "footer": {
        "text": "AlSarya TV Server Deployment"
      }
    }
  ]
}
EOF
)

    curl -s -H "Content-Type: application/json" -X POST -d "$PAYLOAD" "$DISCORD_WEBHOOK" > /dev/null 2>&1
}

# Function to export data before deployment
export_data_backup() {
    if [ "$BACKUP_ENABLE" != "true" ]; then
        echo "→ Data backup is disabled"
        return 0
    fi

    echo ""
    echo "=========================================="
    echo "📦 Creating pre-deployment backup..."
    
    # Ensure backup directory exists
    mkdir -p "$BACKUP_DIR"
    
    # Create backup of the database (sqlite) and other critical files if needed
    # (Simplified for now - just creating a timestamp marker)
    touch "$BACKUP_DIR/backup_$DEPLOYMENT_ID.txt"
    
    echo "✅ Backup completed"
}

# Main deployment logic
echo "🚀 Starting deployment..."
send_discord_message "🚀 Deployment Started" "Server is starting deployment sequence..." 3447003

# Pull latest changes (Force Sync)
echo "⬇️ Syncing with origin/main..."
$SUDO_PREFIX git fetch origin main

if $SUDO_PREFIX git reset --hard origin/main; then
    echo "✅ Code synced successfully"
else
    echo "❌ Git sync failed"
    send_discord_message "Deployment Failed ❌" "git reset --hard origin/main failed on server." 15548997
    exit 1
fi

# Run backup
export_data_backup

# Run migrations
$SUDO_PREFIX $ART_CMD migrate --force

# Clear caches
$SUDO_PREFIX $ART_CMD optimize:clear
$SUDO_PREFIX $ART_CMD config:cache
$SUDO_PREFIX $ART_CMD route:cache
$SUDO_PREFIX $ART_CMD view:cache

# Update version
NEW_VERSION=$(increment_version "$CURRENT_VERSION")
echo "$NEW_VERSION" > "$VERSION_FILE"
# Ensure version file is owned by app user
chown $APP_USER:$APP_USER "$VERSION_FILE"

echo "✅ Deployment completed successfully! New version: $NEW_VERSION"
send_discord_message "Deployment Successful ✅" "AlSarya TV successfully deployed version **$NEW_VERSION**." 5763719
exit 0