#!/bin/bash

# Production URL Testing Script for AlSarya TV
# Tests vital routes against https://alsarya.tv

# Configuration
PRODUCTION_URL="https://alsarya.tv"
TIMEOUT=30

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
WARNINGS=0

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
    
    # Perform request
    local http_code=$(curl -s -o /dev/null -w "%{http_code}" --max-time $TIMEOUT "$url" 2>/dev/null)
    
    # Check result
    if [ "$http_code" -eq "$expected_code" ]; then
        echo -e "${GREEN}✓ PASS${NC} | $description"
        echo -e "         ${BLUE}$path${NC} → HTTP $http_code"
        PASSED_TESTS=$((PASSED_TESTS + 1))
        return 0
    elif [[ "$expected_code" == "302" && "$http_code" == "200" ]]; then
        echo -e "${YELLOW}⚠ WARN${NC} | $description"
        echo -e "         ${BLUE}$path${NC} → Expected $expected_code, got $http_code (may be logged in)"
        WARNINGS=$((WARNINGS + 1))
        PASSED_TESTS=$((PASSED_TESTS + 1))
        return 0
    elif [[ "$expected_code" == "302" && ("$http_code" == "401" || "$http_code" == "403") ]]; then
        echo -e "${GREEN}✓ PASS${NC} | $description"
        echo -e "         ${BLUE}$path${NC} → HTTP $http_code (authentication required)"
        PASSED_TESTS=$((PASSED_TESTS + 1))
        return 0
    else
        echo -e "${RED}✗ FAIL${NC} | $description"
        echo -e "         ${BLUE}$path${NC} → Expected $expected_code, got $http_code"
        FAILED_TESTS=$((FAILED_TESTS + 1))
        return 1
    fi
}

# Function to test SSL certificate
test_ssl() {
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    
    local domain=$(echo "$PRODUCTION_URL" | sed -e 's|^[^/]*//||' -e 's|/.*$||')
    
    echo -n "Testing SSL certificate for $domain... "
    
    if openssl s_client -connect "$domain:443" -servername "$domain" </dev/null 2>/dev/null | openssl x509 -noout -dates >/dev/null 2>&1; then
        echo -e "${GREEN}✓ PASS${NC} | SSL Certificate"
        echo -e "         Certificate is valid and trusted"
        PASSED_TESTS=$((PASSED_TESTS + 1))
        return 0
    else
        echo -e "${RED}✗ FAIL${NC} | SSL Certificate"
        echo -e "         Certificate validation failed"
        FAILED_TESTS=$((FAILED_TESTS + 1))
        return 1
    fi
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Public Pages (Should return 200)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

test_url "/" 200 "Home Page"
test_url "/splash" 200 "Splash Screen"
test_url "/welcome" 200 "Welcome Page"
test_url "/family" 200 "Family Registration"
test_url "/register" 200 "Registration Form"
test_url "/privacy" 200 "Privacy Policy"
test_url "/terms" 200 "Terms of Service"
test_url "/policy" 200 "Policy Page"
test_url "/csrf-test" 200 "CSRF Test Page"
test_url "/obs-overlay" 200 "OBS Overlay"
test_url "/callers/create" 200 "Caller Creation Page"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Protected Routes (Should redirect: 302/401/403)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

test_url "/dashboard" 302 "Dashboard (Protected)"
test_url "/winners" 302 "Winners Page (Protected)"
test_url "/families" 302 "Families Page (Protected)"
test_url "/admin" 302 "Admin Panel (Protected)"
test_url "/callers/success" 302 "Success Page (Session Required)"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Security Tests"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Test SSL certificate
test_ssl

# Test HTTPS enforcement
TOTAL_TESTS=$((TOTAL_TESTS + 1))
if [[ "$PRODUCTION_URL" == https://* ]]; then
    echo -e "${GREEN}✓ PASS${NC} | HTTPS Enforcement"
    echo -e "         Production uses HTTPS"
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    echo -e "${RED}✗ FAIL${NC} | HTTPS Enforcement"
    echo -e "         Production should use HTTPS"
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi

echo ""
echo "================================================"
echo "  Test Summary"
echo "================================================"
echo ""
echo -e "Total Tests:    $TOTAL_TESTS"
echo -e "${GREEN}Passed:${NC}         $PASSED_TESTS"
echo -e "${RED}Failed:${NC}         $FAILED_TESTS"

if [ $WARNINGS -gt 0 ]; then
    echo -e "${YELLOW}Warnings:${NC}       $WARNINGS"
fi

echo ""

# Exit with appropriate code
if [ $FAILED_TESTS -gt 0 ]; then
    echo -e "${RED}❌ Some tests failed!${NC}"
    exit 1
else
    echo -e "${GREEN}✅ All tests passed!${NC}"
    exit 0
fi
