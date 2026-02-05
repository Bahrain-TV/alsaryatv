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

# Argument Handling
if [ "$1" == "--down" ]; then
    maintenance_mode "down"
    exit 0
elif [ "$1" == "--up" ]; then
    maintenance_mode "up"
    exit 0
fi

# START STANDARD DEPLOYMENT (No arguments)
echo "🚀 Starting Publish Process..."

# Auto-increment version before deployment
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
VERSION_FILE="$SCRIPT_DIR/VERSION"

if [ -f "$VERSION_FILE" ]; then
    OLD_VERSION=$(cat "$VERSION_FILE")
    # Parse version: major.minor.patch-build
    if [[ $OLD_VERSION =~ ^([0-9]+)\.([0-9]+)\.([0-9]+)-([0-9]+)$ ]]; then
        MAJOR="${BASH_REMATCH[1]}"
        MINOR="${BASH_REMATCH[2]}"
        PATCH="${BASH_REMATCH[3]}"
        BUILD="${BASH_REMATCH[4]}"
        NEW_BUILD=$((BUILD + 1))
        NEW_VERSION="$MAJOR.$MINOR.$PATCH-$NEW_BUILD"
    else
        # Fallback: just append -1 or increment if no build number
        NEW_VERSION="1.0.0-1"
    fi
    echo "$NEW_VERSION" > "$VERSION_FILE"
    echo "📦 Version bumped: $OLD_VERSION → $NEW_VERSION"

    # Commit the version bump
    cd "$SCRIPT_DIR"
    git add VERSION
    git commit -m "chore: bump version to $NEW_VERSION [skip ci]" 2>/dev/null || true
else
    echo "1.0.0-1" > "$VERSION_FILE"
    NEW_VERSION="1.0.0-1"
    echo "📦 Created VERSION file: $NEW_VERSION"
fi

send_discord_notification "🚀 Publish Started (v$NEW_VERSION)" "Initiating version **$NEW_VERSION** publish from local machine..." 3447003

# Upload files
upload_files_to_production

# Execute deploy.sh via SSH
echo "⚡ Executing deploy.sh on server via SSH..."
send_discord_notification "Phase 2: Execution ⚡" "Triggering ./deploy.sh on remote server..." 3447003

ssh "$SERVER" "cd $APP_DIR && ./deploy.sh"
DEPLOY_EXIT_CODE=$?

if [ $DEPLOY_EXIT_CODE -eq 0 ]; then
    echo "✅ Remote deployment executed successfully!"
    send_discord_notification "Publish Finished ✅" "Deployment script completed successfully on server." 5763719
else
    echo "❌ Remote deployment failed (Exit Code: $DEPLOY_EXIT_CODE)"
    send_discord_notification "Publish Failed ❌" "Remote deployment script returned non-zero exit code." 15548997
    exit 1
fi

exit 0