#!/bin/bash
#
# Production URL Test Script
# Tests all production URLs for https://alsarya.tv
#
# Usage: ./test-production-urls.sh
#
# This script verifies that all critical URLs on the production site
# are accessible and returning appropriate HTTP status codes.

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Production URL
PRODUCTION_URL="https://alsarya.tv"

# Counter for results
PASSED=0
FAILED=0
TOTAL=0

# Function to test a URL
test_url() {
    local url="$1"
    local expected_status="$2"
    local description="$3"
    
    TOTAL=$((TOTAL + 1))
    
    echo -n "Testing: $description ($url)... "
    
    # Make request and capture status code
    status_code=$(curl -s -o /dev/null -w "%{http_code}" -L --max-time 30 "$url" || echo "000")
    
    # Check if status code matches expected (or is in expected range)
    if [[ "$expected_status" == *"|"* ]]; then
        # Multiple acceptable status codes
        IFS='|' read -ra CODES <<< "$expected_status"
        match=false
        for code in "${CODES[@]}"; do
            if [ "$status_code" -eq "$code" ]; then
                match=true
                break
            fi
        done
        
        if $match; then
            echo -e "${GREEN}✓ PASS${NC} (Status: $status_code)"
            PASSED=$((PASSED + 1))
        else
            echo -e "${RED}✗ FAIL${NC} (Expected: $expected_status, Got: $status_code)"
            FAILED=$((FAILED + 1))
        fi
    else
        # Single expected status code
        if [ "$status_code" -eq "$expected_status" ]; then
            echo -e "${GREEN}✓ PASS${NC} (Status: $status_code)"
            PASSED=$((PASSED + 1))
        else
            echo -e "${RED}✗ FAIL${NC} (Expected: $expected_status, Got: $status_code)"
            FAILED=$((FAILED + 1))
        fi
    fi
}

# Banner
echo "================================================"
echo "  AlSarya TV Production URL Tests"
echo "  Base URL: $PRODUCTION_URL"
echo "================================================"
echo ""

# Test HTTPS
echo -e "${YELLOW}Testing HTTPS Configuration...${NC}"
test_url "$PRODUCTION_URL" "200|301|302" "Root URL (HTTPS)"
echo ""

# Test Public Pages
echo -e "${YELLOW}Testing Public Pages...${NC}"
test_url "$PRODUCTION_URL/splash" "200" "Splash Screen"
test_url "$PRODUCTION_URL/" "200" "Home Page"
test_url "$PRODUCTION_URL/welcome" "200" "Welcome Page"
test_url "$PRODUCTION_URL/family" "200" "Family Registration"
test_url "$PRODUCTION_URL/privacy" "200" "Privacy Policy"
test_url "$PRODUCTION_URL/register" "200" "Registration Form"
test_url "$PRODUCTION_URL/csrf-test" "200" "CSRF Test Page"
test_url "$PRODUCTION_URL/callers/create" "200" "Caller Create Page"
echo ""

# Test API Endpoints
echo -e "${YELLOW}Testing API Endpoints...${NC}"
test_url "$PRODUCTION_URL/api/version" "200" "Version API"
test_url "$PRODUCTION_URL/api/version/changelog" "200|404" "Changelog API"
echo ""

# Test Protected Routes (should redirect or deny)
echo -e "${YELLOW}Testing Protected Routes...${NC}"
test_url "$PRODUCTION_URL/dashboard" "302|401|403" "Dashboard (Protected)"
test_url "$PRODUCTION_URL/winners" "302|401|403" "Winners (Protected)"
test_url "$PRODUCTION_URL/families" "302|401|403" "Families (Protected)"
test_url "$PRODUCTION_URL/admin" "302|401|403" "Admin Panel (Protected)"
echo ""

# Summary
echo "================================================"
echo "  Test Summary"
echo "================================================"
echo -e "Total Tests: $TOTAL"
echo -e "${GREEN}Passed: $PASSED${NC}"
echo -e "${RED}Failed: $FAILED${NC}"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ All tests passed!${NC}"
    exit 0
else
    echo -e "${RED}✗ Some tests failed!${NC}"
    exit 1
fi
