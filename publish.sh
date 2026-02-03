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

# Send deploy webhook and capture JSON response
echo "📡 Triggering Server Deployment Webhook..."
RESPONSE=$(https -jb "https://h6.doy.tech:8090/websites/alsarya.tv/webhook" deploy=1) || true
printf "Webhook response:\n%s\n" "$RESPONSE"

# Detect divergent-branches/generic git pull hint in the returned commandStatus
if echo "$RESPONSE" | grep -qiE "divergent branches|Need to specify how to reconcile divergent branches"; then
  echo "⚠️ Detected git divergent-branches error. Attempting reconciliation..."
  send_discord_notification "Git Divergence Detected ⚠️" "Attempting automatic reconciliation of git branches..." 16776960

  # Move to repo root if possible
  REPO_ROOT=$(git rev-parse --show-toplevel 2>/dev/null || echo "")
  if [ -n "$REPO_ROOT" ]; then
    cd "$REPO_ROOT"
  fi

  # Fetch remote state
  git fetch origin --quiet

  # Ensure an upstream is configured
  if ! git rev-parse --abbrev-ref --symbolic-full-name @{u} >/dev/null 2>&1; then
      echo "No upstream configured."
      send_discord_notification "Publish Failed ❌" "No upstream git branch configured." 15548997
      exit 1
  fi

  LOCAL=$(git rev-parse @)
  REMOTE=$(git rev-parse @{u})
  BASE=$(git merge-base @ @{u})

  if [ "$LOCAL" = "$REMOTE" ]; then
    echo "Local branch up-to-date."
  elif [ "$LOCAL" = "$BASE" ]; then
    echo "Fast-forwarding..."
    git pull --ff-only
  elif [ "$REMOTE" = "$BASE" ]; then
    echo "Pushing local commits..."
    git push || { send_discord_notification "Publish Failed ❌" "Git push failed." 15548997; exit 1; }
  else
    echo "Rebasing..."
    git pull --rebase --autostash || { send_discord_notification "Publish Failed ❌" "Git rebase failed." 15548997; exit 1; }
  fi

  # Retry webhook after reconciliation
  echo "Retrying webhook deploy..."
  send_discord_notification "Retrying Deployment 🔄" "Git reconciliation complete. Retrying webhook..." 3447003
  RESPONSE2=$(https -jb "https://h6.doy.tech:8090/websites/alsarya.tv/webhook" deploy=1) || true
  printf "Webhook retry response:\n%s\n" "$RESPONSE2"
  
  send_discord_notification "Publish Finished ✅" "Deployment webhook triggered successfully after reconciliation." 5763719
  exit 0
fi

# If no divergence detected
send_discord_notification "Publish Finished ✅" "Deployment webhook triggered successfully." 5763719
printf "%s\n" "$RESPONSE"
exit 0