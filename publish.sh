#!/bin/bash

# Configuration (matching deploy.sh)
SERVER="root@h6.doy.tech"
SSH_COMMAND="ssh -i ~/.ssh/id_oct24"

APP_DIR="/home/alsarya.tv/public_html"
DISCORD_WEBHOOK="https://discord.com/api/webhooks/1248966065417883659/hAnbGrEOLw9fWF6UCObAuuXHzW6ZM5I1babbBC4rBAdbUAB6YcfHqHhxZXEU4LYIyZp2"

# Function to send "Dramatic" Discord Notifications
send_discord_notification() {
    local title="$1"
    local description="$2"
    local color="$3" # e.g., 5763719 (Green), 15548997 (Red), 3447003 (Blue)

    # Default to Blue if no color provided
    if [ -z "$color" ]; then
        color=3447003
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
        "text": "AlSarya TV Deployment System"
      }
    }
  ]
}
EOF
)

    curl -H "Content-Type: application/json" -X POST -d "$PAYLOAD" "$DISCORD_WEBHOOK" > /dev/null 2>&1
}

# Function to SCP .env.production AND deploy.sh to production server
upload_files_to_production() {
    echo "📤 Uploading configuration and scripts to production server..."
    
    # Get the directory where this script is located
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    ENV_FILE="$SCRIPT_DIR/.env.production"
    DEPLOY_SCRIPT="$SCRIPT_DIR/deploy.sh"
    
    # Validate .env
    if [ ! -f "$ENV_FILE" ]; then
        echo "❌ Error: .env.production file not found at $ENV_FILE"
        send_discord_notification "Publish Failed ❌" "Could not find .env.production file." 15548997
        exit 1
    fi

    # Validate deploy.sh
    if [ ! -f "$DEPLOY_SCRIPT" ]; then
        echo "❌ Error: deploy.sh file not found at $DEPLOY_SCRIPT"
        send_discord_notification "Publish Failed ❌" "Could not find deploy.sh file." 15548997
        exit 1
    fi
    
    # SCP the .env.production file
    scp -i ~/.ssh/id_oct24 "$ENV_FILE" "$SERVER:$APP_DIR/.env"
    ENV_STATUS=$?

    # SCP the deploy.sh file and make it executable
    scp -i ~/.ssh/id_oct24 "$DEPLOY_SCRIPT" "$SERVER:$APP_DIR/deploy.sh"
    DEPLOY_STATUS=$?
    $SSH_COMMAND "$SERVER" "chmod +x $APP_DIR/deploy.sh"

    if [ $ENV_STATUS -eq 0 ] && [ $DEPLOY_STATUS -eq 0 ]; then
        echo "✅ Files successfully uploaded to production"
        send_discord_notification "Phase 1: Upload Complete 📤" "Environment variables and deployment scripts have been securely uploaded to the server." 5763719
    else
        echo "❌ Failed to upload files to production"
        send_discord_notification "Publish Failed ❌" "Failed to upload files via SCP." 15548997
        exit 1
    fi
}

# Configuration (matching deploy.sh)
APP_USER="alsar4210"
SUDO_PREFIX="sudo -u $APP_USER"

# Function to handle Maintenance Mode
maintenance_mode() {
    local mode="$1" # 'up' or 'down'
    local message="$2"
    local color="$3"

    echo "🔧 Switching maintenance mode to: $mode..."
    send_discord_notification "Maintenance Mode 🔧" "Switching site to **$mode** mode..." 3447003

    if [ "$mode" == "down" ]; then
        # Activate Maintenance Mode with 'down' view and secret bypass
        COMMAND="cd $APP_DIR && $SUDO_PREFIX php artisan down --render='down' --secret='ramadan2026'"
        SUCCESS_MSG="🔴 Maintenance Mode ENABLED"
        SUCCESS_DESC="Site is now in maintenance mode. Bypass secret: ramadan2026"
        SUCCESS_COLOR=15548997 # Red
    else
        # Deactivate Maintenance Mode
        COMMAND="cd $APP_DIR && $SUDO_PREFIX php artisan up"
        SUCCESS_MSG="🟢 Maintenance Mode DISABLED"
        SUCCESS_DESC="Site is now LIVE."
        SUCCESS_COLOR=5763719 # Green
    fi

    # Execute SSH Command
    $SSH_COMMAND "$SERVER" "$COMMAND"
    EXIT_CODE=$?

    if [ $EXIT_CODE -eq 0 ]; then
        echo "✅ Maintenance mode updated successfully."
        send_discord_notification "$SUCCESS_MSG" "$SUCCESS_DESC" $SUCCESS_COLOR
    else
        echo "❌ Failed to update maintenance mode."
        send_discord_notification "Maintenance Update Failed ❌" "Could not run 'php artisan $mode'." 15548997
        exit 1
    fi
}

# Function to check if site is in maintenance mode
check_maintenance_status() {
    local status=$($SSH_COMMAND "$SERVER" "test -f $APP_DIR/storage/framework/down && echo 'down' || echo 'up'")
    echo "$status"
}

# Function to switch branch on production server (data-preserving)
switch_branch() {
    local target_branch="$1"
    local description="$2"

    echo "🔄 Switching to $description branch..."
    send_discord_notification "🔄 Branch Switch Started" "Switching production to **$target_branch** branch..." 3447003

    # Execute branch switch on server with data preservation
    $SSH_COMMAND "$SERVER" "cd /home/alsarya.tv/public_html && \
    echo '📍 Current branch:' && \
    git rev-parse --abbrev-ref HEAD && \
    echo '🔄 Fetching latest from origin...' && \
    sudo -u alsar4210 git fetch origin $target_branch && \
    echo '💾 Switching to $target_branch (data-safe)...' && \
    sudo -u alsar4210 git checkout -B $target_branch origin/$target_branch && \
    echo '🔄 Running migrations (if any)...' && \
    sudo -u alsar4210 php artisan migrate --force 2>&1 | grep -E 'Migrating|Migration completed|nothing to migrate' && \
    echo '🧹 Clearing caches...' && \
    sudo -u alsar4210 php artisan config:cache > /dev/null 2>&1 && \
    sudo -u alsar4210 php artisan route:cache > /dev/null 2>&1 && \
    sudo -u alsar4210 php artisan view:cache > /dev/null 2>&1 && \
    echo '✅ Branch switch completed successfully!'"

    if [ $? -eq 0 ]; then
        send_discord_notification "✅ Branch Switch Complete" "Production is now on **$target_branch** branch. Database and caller data preserved." 5763719
        echo "✅ Successfully switched to $description"
        return 0
    else
        send_discord_notification "❌ Branch Switch Failed" "Could not switch to $target_branch. Check server logs." 15548997
        echo "❌ Failed to switch branch"
        return 1
    fi
}

# Argument Handling
if [ "$1" == "--down" ]; then
    maintenance_mode "down"
    exit 0
elif [ "$1" == "--up" ]; then
    maintenance_mode "up"
    exit 0
elif [ "$1" == "--prod" ]; then
    echo "🚀 Switching to PRODUCTION branch..."
    echo ""
    switch_branch "production" "Production (Live)"
    if [ $? -eq 0 ]; then
        echo ""
        echo "🟢 Bringing site BACK ONLINE..."
        maintenance_mode "up"
        echo ""
        echo "✅ Production branch is now LIVE!"
        echo "📝 All caller data has been preserved."
    fi
    exit 0
elif [ "$1" == "--main" ]; then
    echo "🚀 Switching to MAIN branch..."
    echo ""
    switch_branch "main" "Main (Development)"
    if [ $? -eq 0 ]; then
        echo ""
        echo "🟢 Bringing site BACK ONLINE..."
        maintenance_mode "up"
        echo ""
        echo "✅ Main branch is now active!"
        echo "📝 All caller data has been preserved."
    fi
    exit 0
elif [ "$1" == "--help" ] || [ "$1" == "-h" ]; then
    echo "AlSarya TV Publish Script"
    echo ""
    echo "Usage: ./publish.sh [OPTION]"
    echo ""
    echo "Options:"
    echo "  (no args)     Standard deployment (version check + full deploy)"
    echo "  --down        Put site in maintenance mode"
    echo "  --up          Bring site back online"
    echo "  --main        Switch to main branch (preserves data)"
    echo "  --prod        Switch to production branch (preserves data)"
    echo "  --help, -h    Show this help message"
    echo ""
    exit 0
fi

# START STANDARD DEPLOYMENT (No arguments)
echo "🚀 Starting Publish Process..."
echo ""

# Check if already in maintenance mode
echo "🔍 Checking current maintenance status..."
CURRENT_STATUS=$(check_maintenance_status)
WAS_DOWN=$([[ "$CURRENT_STATUS" == "down" ]] && echo "true" || echo "false")

if [ "$WAS_DOWN" == "true" ]; then
    echo "⚠️  Site is currently in MAINTENANCE MODE"
    echo "📝 Will bring it back UP after deployment"
else
    echo "✅ Site is LIVE - putting into maintenance mode for safe deployment"
    maintenance_mode "down"
fi

echo ""

# Compute next version without touching local VERSION (keep it pristine)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
VERSION_FILE="$SCRIPT_DIR/VERSION"
VERSION_JSON="$SCRIPT_DIR/version.json"

parse_version_components() {
    local version="$1"

    if [[ $version =~ ^([0-9]+)\.([0-9]+)\.([0-9]+)(-([0-9]+))?$ ]]; then
        local base="${BASH_REMATCH[1]}.${BASH_REMATCH[2]}.${BASH_REMATCH[3]}"
        local build="${BASH_REMATCH[5]}"
        if [ -z "$build" ]; then
            build="0"
        fi
        echo "$base|$build"
        return 0
    fi

    return 1
}

if [ ! -f "$VERSION_JSON" ]; then
    echo "❌ Error: version.json not found at $VERSION_JSON"
    send_discord_notification "Publish Failed ❌" "Missing version.json locally." 15548997
    exit 1
fi

if [ -f "$VERSION_FILE" ]; then
    OLD_VERSION=$(cat "$VERSION_FILE")
    LOCAL_JSON_VERSION=$(grep -E '"version"\s*:' "$VERSION_JSON" | head -1 | sed -E 's/.*"version"\s*:\s*"([^"]+)".*/\1/')

    if [ -z "$LOCAL_JSON_VERSION" ]; then
        echo "❌ Error: Could not read version from version.json"
        send_discord_notification "Publish Failed ❌" "version.json missing version field." 15548997
        exit 1
    fi

    PARSED_LOCAL_VERSION=$(parse_version_components "$OLD_VERSION")
    if [ -z "$PARSED_LOCAL_VERSION" ]; then
        echo "❌ Error: VERSION file has invalid format: $OLD_VERSION"
        send_discord_notification "Publish Failed ❌" "Local VERSION format invalid." 15548997
        exit 1
    fi

    IFS='|' read -r BASE_VERSION CURRENT_BUILD <<< "$PARSED_LOCAL_VERSION"

    if [ "$BASE_VERSION" != "$LOCAL_JSON_VERSION" ]; then
        echo "❌ Error: version.json ($LOCAL_JSON_VERSION) and VERSION ($BASE_VERSION) do not match"
        send_discord_notification "Publish Failed ❌" "Local version.json and VERSION mismatch." 15548997
        exit 1
    fi

    MAJOR_VERSION=$(echo "$BASE_VERSION" | cut -d. -f1)
    if [ "$MAJOR_VERSION" -lt 3 ]; then
        echo "❌ Error: Base version must be 3.x or higher. Found: $BASE_VERSION"
        send_discord_notification "Publish Failed ❌" "Base version below 3.x." 15548997
        exit 1
    fi

    NEW_BUILD=$((CURRENT_BUILD + 1))
    NEW_VERSION="$BASE_VERSION-$NEW_BUILD"

    echo "📦 Version computed (local VERSION unchanged): $OLD_VERSION -> $NEW_VERSION"
else
    echo "❌ Error: VERSION file not found at $VERSION_FILE"
    send_discord_notification "Publish Failed ❌" "Missing VERSION file locally." 15548997
    exit 1
fi

echo "🔍 Checking remote version alignment..."
REMOTE_VERSION_JSON=$($SSH_COMMAND "$SERVER" "cat $APP_DIR/version.json 2>/dev/null" | grep -E '"version"\s*:' | head -1 | sed -E 's/.*"version"\s*:\s*"([^"]+)".*/\1/')
REMOTE_VERSION_FILE=$($SSH_COMMAND "$SERVER" "cat $APP_DIR/VERSION 2>/dev/null" | head -1)

if [ -z "$REMOTE_VERSION_JSON" ] || [ -z "$REMOTE_VERSION_FILE" ]; then
    echo "❌ Error: Could not read remote version.json or VERSION"
    send_discord_notification "Publish Failed ❌" "Remote version files missing or unreadable." 15548997
    exit 1
fi

PARSED_REMOTE_VERSION=$(parse_version_components "$REMOTE_VERSION_FILE")
if [ -z "$PARSED_REMOTE_VERSION" ]; then
    echo "❌ Error: Remote VERSION format invalid: $REMOTE_VERSION_FILE"
    send_discord_notification "Publish Failed ❌" "Remote VERSION format invalid." 15548997
    exit 1
fi

IFS='|' read -r REMOTE_BASE_VERSION REMOTE_BUILD <<< "$PARSED_REMOTE_VERSION"

if [ "$REMOTE_VERSION_JSON" != "$BASE_VERSION" ] || [ "$REMOTE_BASE_VERSION" != "$BASE_VERSION" ]; then
    echo "❌ Error: Remote base version mismatch. Local: $BASE_VERSION, Remote JSON: $REMOTE_VERSION_JSON, Remote VERSION: $REMOTE_BASE_VERSION"
    send_discord_notification "Publish Failed ❌" "Remote base version mismatch." 15548997
    exit 1
fi

if [ "$REMOTE_BUILD" != "$CURRENT_BUILD" ]; then
    echo "❌ Error: Remote build ($REMOTE_BUILD) does not match local build ($CURRENT_BUILD)"
    send_discord_notification "Publish Failed ❌" "Remote build mismatch." 15548997
    exit 1
fi

send_discord_notification "🚀 Publish Started (v$NEW_VERSION)" "Initiating version **$NEW_VERSION** publish from local machine..." 3447003

# Upload files
upload_files_to_production

# Execute deploy.sh via SSH
echo "⚡ Executing deploy.sh on server via SSH..."
send_discord_notification "Phase 2: Execution ⚡" "Triggering ./deploy.sh on remote server..." 3447003

$SSH_COMMAND "$SERVER" "cd $APP_DIR && PUBLISH_VERSION='$NEW_VERSION' PUBLISH_BASE_VERSION='$BASE_VERSION' PUBLISH_BUILD='$NEW_BUILD' ./deploy.sh"
DEPLOY_EXIT_CODE=$?

if [ $DEPLOY_EXIT_CODE -eq 0 ]; then
    echo "✅ Remote deployment executed successfully!"
    send_discord_notification "Publish Finished ✅" "Deployment script completed successfully on server." 5763719

    # Bring site back online if it wasn't already in maintenance mode
    if [ "$WAS_DOWN" == "false" ]; then
        echo ""
        echo "🟢 Bringing site back ONLINE..."
        maintenance_mode "up"
    else
        echo ""
        echo "⚠️  Site remains in MAINTENANCE MODE"
        echo "    To bring it online, run: ./publish.sh --up"
    fi
else
    echo "❌ Remote deployment failed (Exit Code: $DEPLOY_EXIT_CODE)"
    send_discord_notification "Publish Failed ❌" "Remote deployment script returned non-zero exit code." 15548997

    # Bring site back online if we put it down and deployment failed
    if [ "$WAS_DOWN" == "false" ]; then
        echo ""
        echo "🔴 Deployment failed! Bringing site back ONLINE..."
        maintenance_mode "up"
    fi

    exit 1
fi

exit 0