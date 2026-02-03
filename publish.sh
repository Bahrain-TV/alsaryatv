#!/bin/bash

# Configuration (matching deploy.sh)
SERVER="root@h6.doy.tech"
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
    scp "$ENV_FILE" "$SERVER:$APP_DIR/.env"
    ENV_STATUS=$?

    # SCP the deploy.sh file and make it executable
    scp "$DEPLOY_SCRIPT" "$SERVER:$APP_DIR/deploy.sh"
    DEPLOY_STATUS=$?
    ssh "$SERVER" "chmod +x $APP_DIR/deploy.sh"

    if [ $ENV_STATUS -eq 0 ] && [ $DEPLOY_STATUS -eq 0 ]; then
        echo "✅ Files successfully uploaded to production"
        send_discord_notification "Phase 1: Upload Complete 📤" "Environment variables and deployment scripts have been securely uploaded to the server." 5763719
    else
        echo "❌ Failed to upload files to production"
        send_discord_notification "Publish Failed ❌" "Failed to upload files via SCP." 15548997
        exit 1
    fi
}

# START
echo "🚀 Starting Publish Process..."
send_discord_notification "🚀 Publish Started" "Initiating new version publish from local machine..." 3447003

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