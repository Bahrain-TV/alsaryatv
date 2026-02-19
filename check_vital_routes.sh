#!/bin/bash
###############################################################################
# check_vital_routes.sh — Production Health Check
#
# Tests critical production routes. Fails if ANY route is broken.
#
# Usage:
#   ./check_vital_routes.sh https://alsarya.tv
#
###############################################################################

set -e

if [[ -z "$1" ]]; then
    echo "Usage: ./check_vital_routes.sh <url>"
    echo "Example: ./check_vital_routes.sh https://alsarya.tv"
    exit 1
fi

SITE_URL="$1"
TIMEOUT=10

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
CYAN='\033[0;36m'
NC='\033[0m'

PASSED=0
FAILED=0
ROUTES=("/" "/register" "/family" "/splash" "/api/caller/status")

echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${CYAN}Health Check: $SITE_URL${NC}"
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

for route in "${ROUTES[@]}"; do
    full_url="${SITE_URL}${route}"
    
    if timeout $TIMEOUT curl -s -f "$full_url" > /dev/null 2>&1; then
        echo -e "${GREEN}✓${NC} $route"
        ((PASSED++))
    else
        echo -e "${RED}✗${NC} $route"
        ((FAILED++))
    fi
done

echo ""
if [[ $FAILED -gt 0 ]]; then
    echo -e "${RED}FAILED: $FAILED of $((PASSED + FAILED)) routes are down!${NC}"
    exit 1
fi

echo -e "${GREEN}✅ All routes UP!${NC}"
exit 0

# 8. Families Page (Protected)
check_route "/families" 302 "Families Page"

echo "----------------------------------------"
echo "Checks complete."
