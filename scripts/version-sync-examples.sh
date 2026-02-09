#!/bin/bash

# Version Sync Command - Usage Examples
# =====================================
# This script demonstrates how to use the version:sync artisan command

echo "=================================================="
echo "   Version Sync Command - Usage Examples"
echo "=================================================="
echo ""

echo "📌 Basic Usage"
echo "----------------------------------------"
echo "# Synchronize version.json with VERSION file (default)"
echo "$ php artisan version:sync"
echo ""

echo "📌 Dry Run"
echo "----------------------------------------"
echo "# Preview changes without modifying files"
echo "$ php artisan version:sync --dry-run"
echo ""

echo "📌 Update Environment Files"
echo "----------------------------------------"
echo "# Also update APP_VERSION in .env files"
echo "$ php artisan version:sync --update-env"
echo ""

echo "📌 Reverse Sync"
echo "----------------------------------------"
echo "# Sync VERSION file to match version.json"
echo "$ php artisan version:sync --from=version.json"
echo ""

echo "📌 Integration with Publish Script"
echo "----------------------------------------"
echo "# The publish.sh script automatically runs:"
echo "$ php artisan version:sync --from=VERSION"
echo "# This happens before version validation to prevent mismatch errors"
echo ""

echo "=================================================="
echo "   Version File Format"
echo "=================================================="
echo ""
echo "VERSION file format:"
echo "  - Simple version: 3.3.1"
echo "  - With build number: 3.3.1-32"
echo ""
echo "version.json format:"
echo '  {'
echo '    "version": "3.3.1",'
echo '    "name": "AlSarya TV Show Registration System",'
echo '    "changelog": [...]'
echo '  }'
echo ""

echo "=================================================="
echo "   Common Scenarios"
echo "=================================================="
echo ""
echo "1️⃣  Fix version mismatch before deployment:"
echo "   $ php artisan version:sync"
echo ""
echo "2️⃣  Preview what would change:"
echo "   $ php artisan version:sync --dry-run"
echo ""
echo "3️⃣  Update all version references:"
echo "   $ php artisan version:sync --update-env"
echo ""
echo "4️⃣  Automated in publish.sh:"
echo "   $ ./publish.sh"
echo "   (version:sync runs automatically)"
echo ""

echo "✅ Done!"
