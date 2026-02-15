#!/bin/bash

############################################################
# AlSarya Production Monitoring Stack Installer
# Stack:
#   - ntfy (macOS desktop notification server on port 8999)
#   - Vector (Production log shipper)
#   - Automatic publish.sh ntfy integration
############################################################

set -e

STATUS_ONLY=false
if [ "${1:-}" = "--status" ]; then
  STATUS_ONLY=true
fi

########################################
# CONFIGURATION
########################################

SERVER="root@h6.doy.tech"
SSH_KEY="~/.ssh/id_oct24"
APP_DIR="/home/alsarya.tv/public_html"
APP_USER="alsar4210"

TOPIC="alsarya-prod"
PORT="8999"

MAC_IP=$(ipconfig getifaddr en0 || ipconfig getifaddr en1)

if [ -z "$MAC_IP" ]; then
  echo "❌ Could not detect Mac local IP"
  exit 1
fi

echo "🧠 Using Mac IP: $MAC_IP"
echo "🌐 Using ntfy port: $PORT"
echo ""

require_cmd() {
  if ! command -v "$1" >/dev/null 2>&1; then
    echo "❌ Missing required command: $1"
    exit 1
  fi
}

ensure_brew_pkg() {
  local pkg="$1"
  if ! brew list --formula "$pkg" >/dev/null 2>&1; then
    brew install "$pkg"
  fi
}

require_ntfy_server() {
  if ntfy serve --help >/dev/null 2>&1; then
    NTFY_RUNTIME="native"
    NTFY_LISTEN_PORT="$PORT"
    return 0
  fi

  if command -v docker >/dev/null 2>&1; then
    NTFY_RUNTIME="docker"
    NTFY_LISTEN_PORT="80"
    return 0
  fi

  echo "❌ The installed ntfy binary is client-only (no 'serve' command)."
  echo "   Install the server build and retry:"
  echo "   brew uninstall ntfy"
  echo "   brew tap binwiederhier/tap"
  echo "   brew install binwiederhier/tap/ntfy"
  echo ""
  echo "   Alternative: install Docker and rerun this script."
  exit 1
}

ensure_ntfy_config() {
  local config_path="$HOME/.config/ntfy/server.yml"
  local tmpfile
  tmpfile=$(mktemp)

  cat > "$tmpfile" <<EOF
base-url: "http://localhost:$PORT"
listen-http: ":$NTFY_LISTEN_PORT"
auth-default-access: "read-write"
EOF

  if [ ! -f "$config_path" ] || ! cmp -s "$tmpfile" "$config_path"; then
    mkdir -p "$(dirname "$config_path")"
    mv "$tmpfile" "$config_path"
  else
    rm -f "$tmpfile"
  fi
}

ensure_ntfy_running() {
  local plist="$HOME/Library/LaunchAgents/com.alsarya.ntfy.plist"
  if [ "$NTFY_RUNTIME" = "native" ]; then
    if brew services list >/dev/null 2>&1 && brew services list | awk '$1 == "ntfy" {exit 0} END {exit 1}'; then
      local status
      status=$(brew services list | awk '$1 == "ntfy" {print $2}')
      if [ "$status" != "started" ]; then
        brew services restart ntfy
      fi
    else
      mkdir -p "$HOME/Library/LaunchAgents"
      cat > "$plist" <<EOF
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
  <key>Label</key>
  <string>com.alsarya.ntfy</string>
  <key>ProgramArguments</key>
  <array>
    <string>$(command -v ntfy)</string>
    <string>serve</string>
    <string>--config</string>
    <string>$HOME/.config/ntfy/server.yml</string>
  </array>
  <key>RunAtLoad</key>
  <true/>
  <key>KeepAlive</key>
  <true/>
  <key>StandardOutPath</key>
  <string>$HOME/Library/Logs/ntfy.log</string>
  <key>StandardErrorPath</key>
  <string>$HOME/Library/Logs/ntfy.log</string>
</dict>
</plist>
EOF

      launchctl unload "$plist" >/dev/null 2>&1 || true
      launchctl load "$plist"
    fi
  else
    local container_name="alsarya-ntfy"
    if ! docker ps -a --format '{{.Names}}' | grep -q "^${container_name}$"; then
      docker run -d \
        --name "$container_name" \
        --restart unless-stopped \
        -p "$PORT:80" \
        -v "$HOME/.config/ntfy:/etc/ntfy" \
        binwiederhier/ntfy \
        serve --config /etc/ntfy/server.yml
    else
      docker start "$container_name" >/dev/null
    fi
  fi

  if ! curl -s "http://localhost:$PORT" >/dev/null 2>&1; then
    echo "❌ ntfy is not responding on http://localhost:$PORT"
    exit 1
  fi
}

check_status() {
  echo "🔎 Status check"

  if curl -s "http://localhost:$PORT" >/dev/null 2>&1; then
    echo "✅ ntfy reachable at http://localhost:$PORT"
  else
    echo "❌ ntfy not reachable at http://localhost:$PORT"
  fi

  ssh -i "$SSH_KEY" "$SERVER" "systemctl is-active --quiet vector" >/dev/null 2>&1
  if [ $? -eq 0 ]; then
    echo "✅ vector service active on production"
  else
    echo "❌ vector service not active on production"
  fi

  echo ""
  echo "Open in browser:"
  echo "http://localhost:$PORT/$TOPIC"
}

if [ "$STATUS_ONLY" = true ]; then
  check_status
  exit 0
fi

########################################
# INSTALL macOS SERVICES
########################################

echo "🍺 Installing Homebrew packages..."
require_cmd brew
brew tap vectordotdev/brew || true
ensure_brew_pkg vector
ensure_brew_pkg ntfy
ensure_brew_pkg curl
ensure_brew_pkg jq
require_ntfy_server

echo "🚀 Configuring ntfy on port $PORT..."
ensure_ntfy_config
ensure_ntfy_running

echo "✅ ntfy running at http://localhost:$PORT"
echo ""

########################################
# INSTALL VECTOR ON PRODUCTION
########################################

echo "📡 Installing Vector on production server..."

ssh -i $SSH_KEY $SERVER <<EOF
set -e

if [ ! -d "$APP_DIR" ]; then
  echo "❌ APP_DIR not found: $APP_DIR" >&2
  exit 1
fi

if ! command -v vector >/dev/null 2>&1; then
  curl -1sLf https://repositories.timber.io/public/vector/cfg/setup/bash.deb.sh | bash
  apt-get install -y vector
fi

mkdir -p /etc/vector

tmpfile="/tmp/vector.yaml"
cat > "
${tmpfile}
" <<VECTORCONF
sources:
  laravel_logs:
    type: file
    include:
      - $APP_DIR/storage/logs/*.log
    read_from: beginning
    ignore_older: 86400

transforms:
  only_errors:
    type: remap
    inputs: ["laravel_logs"]
    source: |
      if !contains!(.message, "ERROR") && !contains!(.message, "CRITICAL") {
        abort
      }

sinks:
  ntfy_sink:
    type: http
    inputs: ["only_errors"]
    uri: "http://$MAC_IP:$PORT/$TOPIC"
    method: post
    encoding:
      codec: text
    request:
      headers:
        Title: "AlSarya Production Error"
        Priority: "5"
VECTORCONF

if [ ! -f /etc/vector/vector.yaml ] || ! cmp -s "$tmpfile" /etc/vector/vector.yaml; then
  mv "$tmpfile" /etc/vector/vector.yaml
  systemctl enable vector
  systemctl restart vector
else
  rm -f "$tmpfile"
fi

if ! systemctl is-active --quiet vector; then
  echo "❌ Vector service is not active" >&2
  systemctl status vector --no-pager >&2 || true
  exit 1
fi
EOF

echo ""
echo "✅ Production Vector configured"
echo ""

########################################
# UPDATE publish.sh
########################################

echo "🔄 Updating publish.sh to remove Discord..."

if [ -f "./publish.sh" ]; then

  sed -i '' '/DISCORD_WEBHOOK/d' publish.sh
  sed -i '' '/send_discord_notification()/,/^}/d' publish.sh
  sed -i '' 's/send_discord_notification/send_notification/g' publish.sh

  perl -0777 -i -pe 's/\n# BEGIN NTFY NOTIFY.*?# END NTFY NOTIFY\n//s' publish.sh

  cat >> publish.sh <<EOF

# BEGIN NTFY NOTIFY
########################################
# ntfy Notification Function
########################################
NTFY_URL="http://$MAC_IP:$PORT/$TOPIC"

send_notification() {
    local title="\$1"
    local message="\$2"
    curl -H "Title: \$title" \
         -H "Priority: 4" \
         -d "\$message" \
         "\$NTFY_URL" > /dev/null 2>&1
}
# END NTFY NOTIFY
EOF

  echo "✅ publish.sh updated"
else
  echo "⚠️ publish.sh not found in current directory"
fi

echo ""
echo "🎉 COMPLETE."
echo ""
echo "Open in browser:"
echo "http://localhost:$PORT/$TOPIC"
echo ""
echo "You now have:"
echo "• Real-time Laravel error streaming"
echo "• Clean desktop notifications"
echo "• No Discord"
echo "• Running on port 8999"
echo ""
echo "Done."