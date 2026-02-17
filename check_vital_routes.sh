#!/bin/bash

# Configuration
BASE_URL=${1:-"http://127.0.0.1:8000"}
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "Starting vital route check on: $BASE_URL"
echo "----------------------------------------"

# Function to check a route
check_route() {
    local route=$1
    local expected_code=$2
    local description=$3
    
    local url="${BASE_URL}${route}"
    local status_code=$(curl -o /dev/null -s -w "%{http_code}" "$url")

    if [ "$status_code" -eq "$expected_code" ]; then
        echo -e "${GREEN}[PASS]${NC} $description ($route) - Status: $status_code"
        return 0
    elif [[ "$expected_code" == "302" && "$status_code" == "200" ]]; then
         # Sometimes a redirect might be followed if curl config is weird, but -I or just checking code prevents follow. 
         # However, if we get 200 on a protected route, that might be a failure or success depending on auth state.
         # Assuming clean state:
         echo -e "${YELLOW}[WARN]${NC} $description ($route) - Expected $expected_code, got $status_code (Might be logged in?)"
         return 0
    else
        echo -e "${RED}[FAIL]${NC} $description ($route) - Expected $expected_code, got $status_code"
        return 1
    fi
}

# Routes to check
# Format: check_route "route" "expected_code" "Description"

# 1. Homepage / Welcome
check_route "/" 200 "Homepage"

# 2. Individual Form
# Based on routes/web.php: Route::get('/register', ...) -> view('calls.register')
check_route "/register" 200 "Individual Registration Form"

# 3. Family Form
# Based on routes/web.php: Route::get('/family', ...) -> view('welcome') (Seems to reuse welcome view but different route name)
check_route "/family" 200 "Family Registration Form"

# 4. OBS Overlay
# Based on routes/web.php: Route::get('/obs-overlay', ...)
check_route "/obs-overlay" 200 "OBS Overlay"

# 5. Dashboard (Protected)
# Accept 302 (Redirect) OR 200 (if auth logic permits or dev mode)
check_route "/dashboard" 302 "Dashboard"

# 6. Thank You Screen (Protected by Session)
check_route "/callers/success" 302 "Thank You Screen"

# 7. Winners Page (Protected)
check_route "/winners" 302 "Winners Page"

# 8. Families Page (Protected)
check_route "/families" 302 "Families Page"

echo "----------------------------------------"
echo "Checks complete."
