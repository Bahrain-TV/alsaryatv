#!/bin/bash

# Configuration (matching deploy.sh)
SERVER="root@h6.doy.tech"
APP_DIR="/home/alsarya.tv/public_html"

# Function to SCP .env.production to production server
scp_env_to_production() {
    echo "📤 Uploading .env.production to production server..."
    
    # Get the directory where this script is located
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    ENV_FILE="$SCRIPT_DIR/.env.production"
    
    if [ ! -f "$ENV_FILE" ]; then
        echo "❌ Error: .env.production file not found at $ENV_FILE"
        exit 1
    fi
    
    # SCP the .env.production file to the server as .env
    scp "$ENV_FILE" "$SERVER:$APP_DIR/.env"
    
    if [ $? -eq 0 ]; then
        echo "✅ .env file successfully uploaded to production"
    else
        echo "❌ Failed to upload .env file to production"
        exit 1
    fi
}

# Upload .env to production FIRST
scp_env_to_production

# Send deploy webhook and capture JSON response
RESPONSE=$(https -jb "https://h6.doy.tech:8090/websites/alsarya.tv/webhook" deploy=1) || true
printf "Webhook response:\n%s\n" "$RESPONSE"

# Detect divergent-branches/generic git pull hint in the returned commandStatus
if echo "$RESPONSE" | grep -qiE "divergent branches|Need to specify how to reconcile divergent branches"; then
  echo "⚠️ Detected git divergent-branches error in webhook response. Attempting automatic reconciliation..."

  # Move to repo root if possible
  REPO_ROOT=$(git rev-parse --show-toplevel 2>/dev/null || echo "")
  if [ -n "$REPO_ROOT" ]; then
    cd "$REPO_ROOT"
  fi

  # Fetch remote state
  git fetch origin --quiet

  # Ensure an upstream is configured
  if ! git rev-parse --abbrev-ref --symbolic-full-name @{u} >/dev/null 2>&1; then
    echo "No upstream configured for current branch. Set upstream manually, e.g.:
  git branch --set-upstream-to=origin/$(git rev-parse --abbrev-ref HEAD)"
    exit 1
  fi

  LOCAL=$(git rev-parse @)
  REMOTE=$(git rev-parse @{u})
  BASE=$(git merge-base @ @{u})

  if [ "$LOCAL" = "$REMOTE" ]; then
    echo "Local branch already up-to-date with remote. No action required."
  elif [ "$LOCAL" = "$BASE" ]; then
    echo "Local branch behind remote — attempting fast-forward pull..."
    if git pull --ff-only; then
      echo "Fast-forward succeeded."
    else
      echo "Fast-forward failed; trying merge pull..."
      git pull --no-rebase || { echo "Merge pull failed — manual intervention required."; exit 1; }
    fi
  elif [ "$REMOTE" = "$BASE" ]; then
    echo "Local branch ahead of remote — attempting to push local commits..."
    git push || { echo "Push failed — please reconcile remote manually."; exit 1; }
  else
    echo "Branches have diverged — attempting rebase with autostash..."
    if git pull --rebase --autostash; then
      echo "Rebase succeeded."
    else
      echo "Rebase failed — attempting merge fallback..."
      git pull --no-rebase || { echo "Unable to reconcile branches automatically. Manual resolution required."; exit 1; }
    fi
  fi

  # Retry webhook after reconciliation
  echo "Retrying webhook deploy..."
  RESPONSE2=$(https -jb "https://h6.doy.tech:8090/websites/alsarya.tv/webhook" deploy=1) || true
  printf "Webhook retry response:\n%s\n" "$RESPONSE2"
  exit 0
fi

# If no divergence detected, print response and exit
printf "%s\n" "$RESPONSE"
exit 0