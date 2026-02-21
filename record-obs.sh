#!/bin/bash
###############################################################################
# record-obs.sh — Quick OBS Overlay Recording
#
# Starts the Laravel server (if not running) and records the OBS overlay
#
###############################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

log() { echo -e "${CYAN}→${NC} $*"; }
ok()  { echo -e "${GREEN}✓${NC} $*"; }
err() { echo -e "${RED}✗${NC} $*"; }

cd "$(dirname "$0")"

# Load environment variables from .env
if [ -f .env ]; then
    export $(grep "^APP_PORT=" .env | xargs)
fi

# Configuration - read actual port from env or .env, default to 8122
PORT="${APP_PORT:-8122}"
HOST="127.0.0.1"
URL="http://${HOST}:${PORT}/obs-overlay"

echo ""
echo -e "${GREEN}╔════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  OBS Overlay Recorder                  ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════╝${NC}"
echo ""

# Check if server is already running
log "Checking if server is running on port $PORT..."
if curl -s -o /dev/null -w '' --connect-timeout 2 "http://${HOST}:${PORT}"; then
    ok "Server is already running at http://${HOST}:${PORT}"
else
    log "Starting Laravel server on port $PORT..."
    php artisan serve --host=$HOST --port=$PORT &
    SERVER_PID=$!
    sleep 3
    
    # Verify server started
    if ! curl -s -o /dev/null -w '' --connect-timeout 2 "http://${HOST}:${PORT}"; then
        err "Failed to start server. Is port $PORT already in use?"
        exit 1
    fi
    ok "Server started (PID: $SERVER_PID)"
fi

echo ""
log "OBS Overlay URL: ${URL}"
echo ""

# Run the recording command
log "Starting recording..."
php artisan obs:record --url="$URL"
EXIT_CODE=$?

# Cleanup: Kill server if we started it
if [[ -n "${SERVER_PID:-}" ]]; then
    log "Stopping server (PID: $SERVER_PID)..."
    kill $SERVER_PID 2>/dev/null || true
fi

echo ""
if [[ $EXIT_CODE -eq 0 ]]; then
    ok "✅ Recording completed successfully!"
else
    err "❌ Recording failed"
fi

exit $EXIT_CODE
