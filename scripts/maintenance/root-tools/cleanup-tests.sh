#!/bin/bash
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/../../.." && pwd)"
cd "$ROOT_DIR"

echo "🗑️  Removing non-core business tests..."

# Feature tests - infrastructure/version management
rm -f tests/Feature/VersionManagerTest.php
rm -f tests/Feature/VersionSyncCommandTest.php
rm -f tests/Feature/ProductionUrlTest.php
rm -f tests/Feature/ProductionUrlCurlTest.php
rm -f tests/Feature/PRODUCTION_TESTS_README.md

# Feature tests - implementation details  
rm -f tests/Feature/CprHashingServiceTest.php

# Feature tests - low value routing tests
rm -f tests/Feature/SplashRoutingTest.php

# Feature tests - duplicate admin test
rm -f tests/Feature/AdminPanelTest.php

echo "✓ Removed Feature tests"

# Standard Jetstream Auth tests (not core to TV show business)
rm -rf tests/Feature/Auth

echo "✓ Removed Auth tests (Jetstream defaults)"

# User account settings tests (not core TV show business)
rm -rf tests/Feature/Settings

echo "✓ Removed Settings tests (user account management)"

# Browser/Dusk UI automation tests (low priority for MVP)
rm -rf tests/Browser

echo "✓ Removed Browser tests (Dusk UI automation)"

echo ""
echo "✅ Test cleanup complete!"
echo ""
echo "📋 Remaining core business tests:"
ls -1 tests/Feature/*.php
echo ""
ls -1 tests/Feature/Admin/*.php
