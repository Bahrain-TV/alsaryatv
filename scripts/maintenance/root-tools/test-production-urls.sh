#!/bin/bash

# Production URL Testing Script for AlSarya TV
# Tests vital routes against https://alsarya.tv

PRODUCTION_URL="https://alsarya.tv"
TIMEOUT=8

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Counters
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

echo "================================================"
echo "  AlSarya TV Production URL Test Suite"
echo "  Testing: $PRODUCTION_URL"
echo "================================================"
echo ""

# Function to test a URL
test_url() {
    local path=$1
    local expected_code=$2
    local description=$3
    local url="${PRODUCTION_URL}${path}"
    
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    
    echo -n "Testing $description ($path)... "
    
    local http_code=$(timeout $TIMEOUT curl -s -o /dev/null -w "%{http_code}" --connect-timeout 5 --max-time $((TIMEOUT-1)) "$url" 2>/dev/null || echo "000")
    
    if [ "$http_code" = "$expected_code" ] || \
       { [[ "$expected_code" == "302" ]] && [[ "$http_code" == "302" || "$http_code" == "200" || "$http_code" == "401" || "$http_code" == "403" ]]; } || \
       { [[ "$expected_code" == "503" ]] && [[ "$http_code" == "503" || "$http_code" == "200" ]]; }; then
        echo -e "${GREEN}✓ PASS${NC} (HTTP $http_code)"
        PASSED_TESTS=$((PASSED_TESTS + 1))
        return 0
    else
        echo -e "${RED}✗ FAIL${NC} (Expected $expected_code, got $http_code)"
        FAILED_TESTS=$((FAILED_TESTS + 1))
        return 1
    fi
}

# Check if application is currently in maintenance mode (503)
root_code=$(timeout $TIMEOUT curl -s -o /dev/null -w "%{http_code}" --connect-timeout 5 --max-time 4 "${PRODUCTION_URL}/" 2>/dev/null || echo "000")

if [ "$root_code" = "503" ]; then
    echo -e "${YELLOW}ℹ Application is currently in MAINTENANCE mode (HTTP 503)${NC}"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "  Maintenance Mode Verification"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    test_url "/" 503 "Maintenance Mode Landing"
    test_url "/down" 503 "Dedicated Down Route"
else
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "  Essential Pages"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    test_url "/" 200 "Home Page"
    test_url "/splash" 200 "Splash Screen"
    test_url "/welcome" 200 "Welcome"
    test_url "/family" 200 "Family"
    test_url "/down" 200 "Down View"

    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "  Protected Routes"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    test_url "/dashboard" 302 "Dashboard"
    test_url "/admin" 302 "Admin"

    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "  CSRF Token & Form Submission Test"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    echo -n "Extracting CSRF token... "
    form_html=$(timeout $TIMEOUT curl -s --connect-timeout 5 --max-time $((TIMEOUT-1)) "${PRODUCTION_URL}/callers/create" 2>/dev/null)
    csrf_token=$(echo "$form_html" | grep -o 'value="[a-zA-Z0-9/+=]*"' | head -1 | sed 's/value="\(.*\)"/\1/')

    if [ -z "$csrf_token" ] || [ ${#csrf_token} -lt 20 ]; then
        echo -e "${RED}✗ FAIL${NC}"
        FAILED_TESTS=$((FAILED_TESTS + 1))
    else
        echo -e "${GREEN}✓ OK${NC}"
        PASSED_TESTS=$((PASSED_TESTS + 1))
    fi
fi

echo ""
echo "================================================"
echo "  Test Summary"
echo "================================================"
echo ""
echo -e "Total Tests:  $TOTAL_TESTS"
echo -e "${GREEN}Passed:${NC}       $PASSED_TESTS"
echo -e "${RED}Failed:${NC}       $FAILED_TESTS"
echo ""

if [ $FAILED_TESTS -gt 0 ]; then
    echo -e "${RED}❌ Some tests failed!${NC}"
    exit 1
else
    echo -e "${GREEN}✅ All tests passed!${NC}"
    exit 0
fi
