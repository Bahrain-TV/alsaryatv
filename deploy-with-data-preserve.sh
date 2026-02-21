#!/bin/bash
###############################################################################
# Data Persistence Wrapper for Deployments
# Ensures critical data (winners, selected, levels) is preserved during migrations
#
# Usage:
#   ./deploy-with-data-preserve.sh
#
# What it does:
#   1. Backup critical data (winners, selected, levels)
#   2. Run migrations
#   3. Verify and restore data if needed
#   4. Log results
###############################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

log() { echo -e "${CYAN}→${NC} $*"; }
ok()  { echo -e "${GREEN}✓${NC} $*"; }
err() { echo -e "${RED}✗${NC} $*"; exit 1; }
warn() { echo -e "${YELLOW}!${NC} $*"; }

cd "$(dirname "$0")"

log "═══════════════════════════════════════════════════════════"
log "  Data Preservation for Deployment"
log "═══════════════════════════════════════════════════════════"
log ""

# ──────────────────────────────────────────────────────────────
# STEP 1: Pre-Migration Backup
# ──────────────────────────────────────────────────────────────
log "STEP 1: Creating pre-migration data backup..."

BACKUP_TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="storage/backups/pre_migration_${BACKUP_TIMESTAMP}"

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Export critical data using artisan command
log "  Backing up winners and selected callers..."
php artisan callers:export --only-critical --output="$BACKUP_DIR/critical_data.csv" 2>&1 | tail -3

# Also create a full backup
log "  Creating full caller backup..."
php artisan backup:data --type=all 2>&1 | tail -3

# Verify backup was created
if [ -f "$BACKUP_DIR/critical_data.csv" ] || [ -f "storage/backups/callers/callers_backup_${BACKUP_TIMESTAMP}.csv" ]; then
    ok "Pre-migration backup created: $BACKUP_DIR"
else
    warn "Backup file not found, but continuing..."
fi

# ──────────────────────────────────────────────────────────────
# STEP 2: Record Current State
# ──────────────────────────────────────────────────────────────
log ""
log "STEP 2: Recording current database state..."

# Get counts before migration
WINNERS_BEFORE=$(php artisan tinker --execute="echo App\Models\Caller::where('is_winner', true)->count();" 2>&1 | tail -1)
SELECTED_BEFORE=$(php artisan tinker --execute="echo App\Models\Caller::where('is_selected', true)->where('is_winner', false)->count();" 2>&1 | tail -1)
TOTAL_CALLERS=$(php artisan tinker --execute="echo App\Models\Caller::count();" 2>&1 | tail -1)

log "  Winners before: $WINNERS_BEFORE"
log "  Selected (pending) before: $SELECTED_BEFORE"
log "  Total callers: $TOTAL_CALLERS"

# Save state to file for post-migration comparison
cat > "$BACKUP_DIR/pre_migration_state.json" << EOF
{
    "timestamp": "$(date -Iseconds)",
    "winners_count": $WINNERS_BEFORE,
    "selected_count": $SELECTED_BEFORE,
    "total_callers": $TOTAL_CALLERS
}
EOF

ok "State recorded: $BACKUP_DIR/pre_migration_state.json"

# ──────────────────────────────────────────────────────────────
# STEP 3: Run Migrations
# ──────────────────────────────────────────────────────────────
log ""
log "STEP 3: Running database migrations..."

if php artisan migrate --force 2>&1 | tee -a "$BACKUP_DIR/migration.log"; then
    ok "Migrations completed successfully"
else
    err "Migrations failed! Check $BACKUP_DIR/migration.log"
    exit 1
fi

# ──────────────────────────────────────────────────────────────
# STEP 4: Verify Data Integrity
# ──────────────────────────────────────────────────────────────
log ""
log "STEP 4: Verifying data integrity after migration..."

# Get counts after migration
WINNERS_AFTER=$(php artisan tinker --execute="echo App\Models\Caller::where('is_winner', true)->count();" 2>&1 | tail -1)
SELECTED_AFTER=$(php artisan tinker --execute="echo App\Models\Caller::where('is_selected', true)->where('is_winner', false)->count();" 2>&1 | tail -1)

log "  Winners after: $WINNERS_AFTER"
log "  Selected (pending) after: $SELECTED_AFTER"

# Check if data was lost
WINNERS_LOST=$((WINNERS_BEFORE - WINNERS_AFTER))
SELECTED_LOST=$((SELECTED_BEFORE - SELECTED_AFTER))

if [ "$WINNERS_LOST" -eq 0 ] && [ "$SELECTED_LOST" -eq 0 ]; then
    ok "✓ All critical data preserved!"
    log "  No data loss detected"
else
    warn "⚠ Data loss detected!"
    log "  Winners lost: $WINNERS_LOST"
    log "  Selected lost: $SELECTED_LOST"
    
    # ──────────────────────────────────────────────────────────
    # STEP 5: Restore Lost Data (if needed)
    # ──────────────────────────────────────────────────────────
    log ""
    log "STEP 5: Attempting to restore lost data..."
    
    # Use the persist-data command to verify and restore
    if php artisan app:persist-data --verify 2>&1 | tail -10; then
        ok "Data verification completed"
    else
        warn "Data verification had issues"
    fi
    
    # Re-check counts
    WINNERS_RESTORED=$(php artisan tinker --execute="echo App\Models\Caller::where('is_winner', true)->count();" 2>&1 | tail -1)
    SELECTED_RESTORED=$(php artisan tinker --execute="echo App\Models\Caller::where('is_selected', true)->where('is_winner', false)->count();" 2>&1 | tail -1)
    
    log "  Winners after restore: $WINNERS_RESTORED"
    log "  Selected after restore: $SELECTED_RESTORED"
    
    if [ "$WINNERS_RESTORED" -eq "$WINNERS_BEFORE" ] && [ "$SELECTED_RESTORED" -eq "$SELECTED_BEFORE" ]; then
        ok "✓ Data successfully restored!"
    else
        err "⚠ WARNING: Could not fully restore data!"
        log "  Manual intervention may be required"
        log "  Backup location: $BACKUP_DIR"
    fi
fi

# ──────────────────────────────────────────────────────────────
# STEP 6: Save Final State
# ──────────────────────────────────────────────────────────────
log ""
log "STEP 6: Saving final state..."

cat > "$BACKUP_DIR/post_migration_state.json" << EOF
{
    "timestamp": "$(date -Iseconds)",
    "winners_count": $WINNERS_AFTER,
    "selected_count": $SELECTED_AFTER,
    "total_callers": $(php artisan tinker --execute="echo App\Models\Caller::count();" 2>&1 | tail -1)
}
EOF

ok "Final state saved"

# ──────────────────────────────────────────────────────────────
# SUMMARY
# ──────────────────────────────────────────────────────────────
log ""
log "═══════════════════════════════════════════════════════════"
log "  Data Preservation Complete"
log "═══════════════════════════════════════════════════════════"
log ""
log "Summary:"
log "  ✓ Pre-migration backup: $BACKUP_DIR"
log "  ✓ Migrations: Completed"
log "  ✓ Data integrity: Verified"
log ""
log "Backup files:"
ls -lh "$BACKUP_DIR" | tail -5

exit 0
