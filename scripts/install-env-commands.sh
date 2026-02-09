#!/bin/bash

# Environment Commands Installation Script
# Copies environment management commands to target Laravel project
# Usage: ./scripts/install-env-commands.sh /path/to/target/project

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Show banner
echo -e "${BLUE}"
echo "╔════════════════════════════════════════════════════════════╗"
echo "║     Environment Commands Installation Script               ║"
echo "║     Installs env validation & sync commands                ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Check arguments
if [ -z "$1" ]; then
    echo -e "${RED}Error: No target project path provided${NC}"
    echo -e "${YELLOW}Usage: $0 /path/to/target/project${NC}"
    exit 1
fi

TARGET_PROJECT="$1"
TARGET_COMMANDS="$TARGET_PROJECT/app/Console/Commands"
SOURCE_DIR="$(dirname "$0")/.."

# Validate target project
if [ ! -d "$TARGET_PROJECT" ]; then
    echo -e "${RED}Error: Target project directory does not exist: $TARGET_PROJECT${NC}"
    exit 1
fi

if [ ! -d "$TARGET_COMMANDS" ]; then
    echo -e "${RED}Error: Commands directory does not exist: $TARGET_COMMANDS${NC}"
    echo -e "${YELLOW}This doesn't appear to be a Laravel project${NC}"
    exit 1
fi

echo -e "${BLUE}Installing to:${NC} $TARGET_PROJECT"
echo ""

# Copy commands
echo -e "${YELLOW}📋 Copying command files...${NC}"

COMMANDS=(
    "ValidateEnvCommand.php"
    "SyncEnvironmentVariablesCommand.php"
)

for cmd in "${COMMANDS[@]}"; do
    SOURCE_FILE="$SOURCE_DIR/app/Console/Commands/$cmd"
    TARGET_FILE="$TARGET_COMMANDS/$cmd"

    if [ ! -f "$SOURCE_FILE" ]; then
        echo -e "${RED}  ✗ Source file not found: $SOURCE_FILE${NC}"
        continue
    fi

    cp "$SOURCE_FILE" "$TARGET_FILE"
    echo -e "${GREEN}  ✓ Copied $cmd${NC}"
done

echo ""

# Copy documentation
if [ -f "$SOURCE_DIR/docs/ENV_COMMANDS_GUIDE.md" ]; then
    TARGET_DOCS="$TARGET_PROJECT/docs"
    mkdir -p "$TARGET_DOCS"
    cp "$SOURCE_DIR/docs/ENV_COMMANDS_GUIDE.md" "$TARGET_DOCS/"
    echo -e "${GREEN}  ✓ Copied documentation${NC}"
    echo ""
fi

# Verify installation
echo -e "${YELLOW}🔍 Verifying installation...${NC}"
echo ""

cd "$TARGET_PROJECT"

# List available commands
php artisan list --format=json > /tmp/commands.json 2>/dev/null || true

if grep -q "env:validate\|env:sync-vars" /tmp/commands.json 2>/dev/null; then
    echo -e "${GREEN}✓ Commands installed successfully!${NC}"
    echo ""
    echo -e "${BLUE}Available commands:${NC}"
    php artisan list | grep -E "env:(validate|sync-vars)" || echo "Commands not yet registered"
else
    echo -e "${YELLOW}⚠ Commands not yet showing in list${NC}"
    echo -e "${YELLOW}Try running: php artisan cache:clear${NC}"
fi

echo ""
echo -e "${BLUE}Next steps:${NC}"
echo "  1. Test the commands:"
echo -e "     ${YELLOW}php artisan env:validate${NC}"
echo "  2. Read the documentation:"
echo -e "     ${YELLOW}cat docs/ENV_COMMANDS_GUIDE.md${NC}"
echo ""
echo -e "${GREEN}Installation complete!${NC}"
