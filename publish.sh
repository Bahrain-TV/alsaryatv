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

strip_quotes() {
    local value="$1"
    value="${value%\"}"
    value="${value#\"}"
    value="${value%\'}"
    value="${value#\'}"
    echo "$value"
}

get_env_value() {
    local env_file="$1"
    local key="$2"
    local raw

    raw=$(grep -E "^${key}=" "$env_file" | tail -1 | cut -d= -f2-)
    strip_quotes "$raw"
}

get_json_version() {
    local json_file="$1"

    if command -v python3 >/dev/null 2>&1; then
        python3 - <<'PY' "$json_file"
import json
import sys

path = sys.argv[1]
try:
    with open(path, 'r', encoding='utf-8') as f:
        data = json.load(f)
    version = data.get('version', '')
    print(version)
except Exception:
    print('')
PY
        return
    fi

    php -r '$p=$argv[1]; $d=@json_decode(@file_get_contents($p), true); echo $d["version"] ?? "";' "$json_file"
}

get_json_version_from_stdin() {
    if command -v python3 >/dev/null 2>&1; then
        python3 - <<'PY'
import json
import sys

try:
    data = json.load(sys.stdin)
    print(data.get('version', ''))
except Exception:
    print('')
PY
        return
    fi

    php -r '$d=@json_decode(stream_get_contents(STDIN), true); echo $d["version"] ?? "";'
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

    if grep -qE "^${key}=" "$env_file"; then
        perl -0777 -i -pe "s/^${key}=.*/${key}=${value}/m" "$env_file"
    else
        printf '\n%s=%s\n' "$key" "$value" >> "$env_file"
    fi
}

generate_key_for_cipher() {
    local cipher="$1"
    local expected_len
    expected_len=$(expected_key_length "$cipher")

    php -r "echo 'base64:' . base64_encode(random_bytes($expected_len));"
}

repair_env_file() {
    local env_file="$1"
    local label="$2"
    local app_key
    local app_cipher
    local expected_len
    local key_len

    app_key=$(get_env_value "$env_file" "APP_KEY")
    app_cipher=$(get_env_value "$env_file" "APP_CIPHER")

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
        echo "⚠️  $label missing APP_KEY. Generating a new one..."
        app_key=$(generate_key_for_cipher "$app_cipher")
        set_env_value "$env_file" "APP_KEY" "$app_key"
        return 0
    fi

    expected_len=$(expected_key_length "$app_cipher")
    key_len=$(decode_key_length "$app_key")
    if [ "$key_len" -ne "$expected_len" ]; then
        echo "⚠️  $label APP_KEY length mismatch. Regenerating..."
        app_key=$(generate_key_for_cipher "$app_cipher")
        set_env_value "$env_file" "APP_KEY" "$app_key"
    fi
}

repair_remote_env() {
    local label="$1"

    $SSH_COMMAND "$SERVER" "APP_DIR='$APP_DIR' php -r '
        $env = $APP_DIR . "/.env";
        if (!file_exists($env)) { fwrite(STDERR, "missing_env\n"); exit(2); }
        $lines = file($env, FILE_IGNORE_NEW_LINES);
        $map = [];
        foreach ($lines as $line) {
            if ($line === "" || $line[0] === "#") { continue; }
            $parts = explode("=", $line, 2);
            if (count($parts) === 2) { $map[$parts[0]] = $parts[1]; }
        }
        $cipher = $map["APP_CIPHER"] ?? "aes-256-cbc";
        if (!in_array($cipher, ["aes-128-cbc","aes-256-cbc","aes-128-gcm","aes-256-gcm"], true)) { $cipher = "aes-256-cbc"; }
        $len = in_array($cipher, ["aes-128-cbc","aes-128-gcm"], true) ? 16 : 32;
        $key = $map["APP_KEY"] ?? "";
        $valid = false;
        if (strpos($key, "base64:") === 0) {
            $raw = base64_decode(substr($key, 7), true);
            $valid = $raw !== false && strlen($raw) === $len;
        } else {
            $valid = strlen($key) === $len;
        }
        if (!$valid) {
            $key = "base64:" . base64_encode(random_bytes($len));
        }
        $map["APP_CIPHER"] = $cipher;
        $map["APP_KEY"] = $key;
        $out = [];
        $seen = [];
        foreach ($lines as $line) {
            if (strpos($line, "APP_KEY=") === 0) { $out[] = "APP_KEY=" . $map["APP_KEY"]; $seen["APP_KEY"] = true; continue; }
            if (strpos($line, "APP_CIPHER=") === 0) { $out[] = "APP_CIPHER=" . $map["APP_CIPHER"]; $seen["APP_CIPHER"] = true; continue; }
            $out[] = $line;
        }
        if (empty($seen["APP_KEY"])) { $out[] = "APP_KEY=" . $map["APP_KEY"]; }
        if (empty($seen["APP_CIPHER"])) { $out[] = "APP_CIPHER=" . $map["APP_CIPHER"]; }
        file_put_contents($env, implode("\n", $out) . "\n");
    '"

    if [ $? -ne 0 ]; then
        echo "❌ Error: $label .env repair failed"
        send_discord_notification "Publish Failed ❌" "Remote .env repair failed." 15548997
        exit 1
    fi
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
            echo "❌ Error: $label has unsupported APP_CIPHER: $app_cipher"
            send_discord_notification "Publish Failed ❌" "Unsupported APP_CIPHER in $label." 15548997
            return 1
            ;;
    esac

    if [ -z "$app_key" ]; then
        echo "❌ Error: $label missing APP_KEY"
        send_discord_notification "Publish Failed ❌" "Missing APP_KEY in $label." 15548997
        return 1
    fi

    local expected_len
    expected_len=$(expected_key_length "$app_cipher")

    if [ "$expected_len" -eq 0 ]; then
        echo "❌ Error: $label has invalid APP_CIPHER: $app_cipher"
        send_discord_notification "Publish Failed ❌" "Invalid APP_CIPHER in $label." 15548997
        return 1
    fi

    local key_len
    key_len=$(decode_key_length "$app_key")
    if [ "$key_len" -lt 0 ]; then
        echo "❌ Error: $label APP_KEY base64 decode failed"
        send_discord_notification "Publish Failed ❌" "APP_KEY base64 decode failed in $label." 15548997
        return 1
    fi

    if [ "$key_len" -ne "$expected_len" ]; then
        echo "❌ Error: $label APP_KEY length $key_len does not match cipher $app_cipher (expected $expected_len)"
        send_discord_notification "Publish Failed ❌" "APP_KEY length mismatch in $label." 15548997
        return 1
    fi

    return 0
}

validate_env_file() {
    local env_file="$1"
    local label="$2"

    local app_key
    local app_cipher

    app_key=$(get_env_value "$env_file" "APP_KEY")
    app_cipher=$(get_env_value "$env_file" "APP_CIPHER")

    validate_env_values "$label" "$app_key" "$app_cipher"
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

    repair_env_file "$ENV_FILE" "Local .env.production"

    if ! validate_env_file "$ENV_FILE" "Local .env.production"; then
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

    echo "🔍 Validating remote .env before deploy..."
    REMOTE_APP_KEY=$($SSH_COMMAND "$SERVER" "grep -E '^APP_KEY=' $APP_DIR/.env | tail -1 | cut -d= -f2-")
    REMOTE_APP_CIPHER=$($SSH_COMMAND "$SERVER" "grep -E '^APP_CIPHER=' $APP_DIR/.env | tail -1 | cut -d= -f2-")

    if ! validate_env_values "Remote .env" "$(strip_quotes "$REMOTE_APP_KEY")" "$(strip_quotes "$REMOTE_APP_CIPHER")"; then
        echo "⚠️  Remote .env invalid. Attempting repair..."
        repair_remote_env "Remote .env"
        REMOTE_APP_KEY=$($SSH_COMMAND "$SERVER" "grep -E '^APP_KEY=' $APP_DIR/.env | tail -1 | cut -d= -f2-")
        REMOTE_APP_CIPHER=$($SSH_COMMAND "$SERVER" "grep -E '^APP_CIPHER=' $APP_DIR/.env | tail -1 | cut -d= -f2-")
        if ! validate_env_values "Remote .env" "$(strip_quotes "$REMOTE_APP_KEY")" "$(strip_quotes "$REMOTE_APP_CIPHER")"; then
            exit 1
        fi
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
        if [ "$mode" == "up" ]; then
            echo "🔍 Verifying required files before bringing site up..."
            if ! $SSH_COMMAND "$SERVER" "test -f $APP_DIR/app/Providers/MailEnvironmentServiceProvider.php"; then
                echo "❌ Error: Required file missing on server: app/Providers/MailEnvironmentServiceProvider.php"
                send_discord_notification "Maintenance Update Failed ❌" "Required provider missing on server. Site remains down." 15548997
                exit 1
            fi
        fi

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

# Function to send welcome emails to callers on production server
send_remote_welcome_emails() {
    local count="${1:-0}" # Optional: number of emails to send (0 = all pending)
    local description="sending welcome emails"

    if [ "$count" -gt 0 ]; then
        description="sending $count welcome email(s)"
    fi

    echo "📧 Sending welcome emails to callers..."
    send_discord_notification "📧 Welcome Email Send Started" "Initiating $description on production server..." 3447003

    # Execute the welcome email command on the remote server
    if [ "$count" -gt 0 ]; then
        $SSH_COMMAND "$SERVER" "cd $APP_DIR && $SUDO_PREFIX php artisan send:welcome-email --count=$count 2>&1"
    else
        $SSH_COMMAND "$SERVER" "cd $APP_DIR && $SUDO_PREFIX php artisan send:welcome-email 2>&1"
    fi

    SEND_EMAIL_EXIT_CODE=$?

    if [ $SEND_EMAIL_EXIT_CODE -eq 0 ]; then
        echo "✅ Welcome emails sent successfully!"
        send_discord_notification "✅ Welcome Emails Sent" "Successfully completed $description on production." 5763719
        return 0
    else
        echo "❌ Failed to send welcome emails (Exit Code: $SEND_EMAIL_EXIT_CODE)"
        send_discord_notification "❌ Welcome Email Send Failed" "Could not complete $description. Check server logs." 15548997
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
elif [ "$1" == "--email" ] || [ "$1" == "--send-emails" ]; then
    echo "📧 Sending Welcome Emails..."
    echo ""
    if [ -n "$2" ] && [ "$2" -gt 0 ] 2>/dev/null; then
        # Send specific number of emails
        send_remote_welcome_emails "$2"
    else
        # Send all pending emails
        send_remote_welcome_emails
    fi
    exit $?
elif [ "$1" == "--help" ] || [ "$1" == "-h" ]; then
    echo "AlSarya TV Publish Script"
    echo ""
    echo "Usage: ./publish.sh [OPTION] [ARGUMENT]"
    echo ""
    echo "Options:"
    echo "  (no args)         Standard deployment (version check + full deploy)"
    echo "  --down            Put site in maintenance mode"
    echo "  --up              Bring site back online"
    echo "  --main            Switch to main branch (preserves data)"
    echo "  --prod            Switch to production branch (preserves data)"
    echo "  --email [N]       Send welcome emails (N = optional number, 0 or empty = all pending)"
    echo "  --send-emails [N] Alias for --email"
    echo "  --help, -h        Show this help message"
    echo ""
    echo "Examples:"
    echo "  ./publish.sh              # Standard deployment"
    echo "  ./publish.sh --email      # Send all pending welcome emails"
    echo "  ./publish.sh --email 10   # Send 10 welcome emails"
    echo "  ./publish.sh --prod       # Switch to production and bring online"
    echo ""
    exit 0
fi

# START STANDARD DEPLOYMENT (No arguments)
echo "🚀 Starting Publish Process..."
echo ""

# Synchronize version files before deployment
echo "🔄 Synchronizing version files..."
if command -v php >/dev/null 2>&1 && [ -f "artisan" ]; then
    php artisan version:sync --from=VERSION
    SYNC_EXIT_CODE=$?

    if [ $SYNC_EXIT_CODE -ne 0 ]; then
        echo "⚠️  Warning: Version sync command failed, but continuing..."
    else
        echo "✅ Version files synchronized successfully"
    fi
else
    echo "⚠️  Warning: PHP or artisan not found, skipping version sync"
fi

echo ""

# Synchronize environment variables between .env and .env.production
echo "🔄 Synchronizing environment variables (.env ↔ .env.production)..."
if command -v php >/dev/null 2>&1 && [ -f "artisan" ]; then
    php artisan env:sync-vars --master=.env --dry-run > /dev/null 2>&1
    SYNC_STATUS=$?

    if [ $SYNC_STATUS -eq 0 ]; then
        php artisan env:sync-vars --master=.env
        ENV_SYNC_EXIT=$?

        if [ $ENV_SYNC_EXIT -eq 0 ]; then
            echo "✅ Environment variables synchronized successfully"
        else
            echo "⚠️  Warning: Environment sync had issues, but continuing..."
        fi
    else
        echo "⚠️  Warning: Environment sync validation failed, but continuing..."
    fi
else
    echo "⚠️  Warning: PHP or artisan not found, skipping environment sync"
fi

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
    LOCAL_JSON_VERSION=$(get_json_version "$VERSION_JSON")

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
REMOTE_VERSION_JSON_RAW=$($SSH_COMMAND "$SERVER" "cat '$APP_DIR/version.json' 2>/dev/null" 2>&1)
REMOTE_VERSION_JSON_STATUS=$?
if [ $REMOTE_VERSION_JSON_STATUS -ne 0 ]; then
    echo "❌ Error: SSH failed reading remote version.json"
    echo "$REMOTE_VERSION_JSON_RAW"
    send_discord_notification "Publish Failed ❌" "SSH failed reading remote version.json." 15548997
    exit 1
fi

REMOTE_VERSION_JSON=$(printf '%s' "$REMOTE_VERSION_JSON_RAW" | get_json_version_from_stdin)

REMOTE_VERSION_FILE_RAW=$($SSH_COMMAND "$SERVER" "cat '$APP_DIR/VERSION' 2>/dev/null" 2>&1)
REMOTE_VERSION_FILE_STATUS=$?
if [ $REMOTE_VERSION_FILE_STATUS -ne 0 ]; then
    echo "❌ Error: SSH failed reading remote VERSION"
    echo "$REMOTE_VERSION_FILE_RAW"
    send_discord_notification "Publish Failed ❌" "SSH failed reading remote VERSION." 15548997
    exit 1
fi

REMOTE_VERSION_FILE=$(printf '%s' "$REMOTE_VERSION_FILE_RAW" | head -1)

if [ -z "$REMOTE_VERSION_JSON" ] || [ -z "$REMOTE_VERSION_FILE" ]; then
    echo "❌ Error: Could not read remote version.json or VERSION"
    $SSH_COMMAND "$SERVER" "ls -la '$APP_DIR/version.json' '$APP_DIR/VERSION'" 2>/dev/null || true
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

if [ "$REMOTE_BUILD" == "$CURRENT_BUILD" ]; then
    echo "❌ Error: Remote build ($REMOTE_BUILD) matches local build ($CURRENT_BUILD) - no new version to deploy"
    send_discord_notification "Publish Failed ❌" "No version change detected - deploy aborted." 15548997
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

    echo ""
    echo "🟢 Bringing site back ONLINE..."
    maintenance_mode "up"
else
    echo "❌ Remote deployment failed (Exit Code: $DEPLOY_EXIT_CODE)"
    send_discord_notification "Publish Failed ❌" "Remote deployment script returned non-zero exit code." 15548997

    # Keep site down if we put it into maintenance mode
    if [ "$WAS_DOWN" == "false" ]; then
        echo ""
        echo "🔴 Deployment failed! Site remains in MAINTENANCE MODE"
        echo "    Fix the issue and then run: ./publish.sh --up"
    fi

    exit 1
fi

exit 0