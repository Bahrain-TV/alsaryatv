# Deploy Script - Key Code Fixes

## Fix #1: Variable Initialization Before Strict Mode

### BEFORE (Broken - lines 21-75)
```bash
set -euo pipefail    # ← Line 21: Enables -u flag immediately!

# ... 50+ lines of code ...

# ── Defaults ─────────────────────────────────────────────────────────────────
FRESH=false
SEED=false
NO_BUILD=false
FORCE=false
DRY_RUN=false
IGNORE_MAINTENANCE=false

# Problem: Variables like TIMEOUT_PID are used before being defined
```

### AFTER (Fixed - lines 21-68)
```bash
set -eo pipefail     # ← Line 23: ONLY -e and -o, NOT -u yet!

# ── Initialize critical variables BEFORE enabling -u ──────────────────────────
FRESH=false
SEED=false
NO_BUILD=false
FORCE=false
DRY_RUN=false
IGNORE_MAINTENANCE=false
WEBHOOK_TRIGGER="${WEBHOOK_TRIGGER:-false}"
TIMEOUT_PID=""       # ← CRITICAL: Initialize before using in cleanup_and_exit
MAINTENANCE_WAS_ENABLED=false
LOCK_FILE="/tmp/deploy.lock"
INSTALL_FLAG="storage/framework/deployment.lock"

# NOW enable strict mode for undefined variables
set -u               # ← Line 40: Enable -u AFTER all variables initialized
```

---

## Fix #2: Unified Trap Handler

### BEFORE (Broken - lines 263 & 295)
```bash
# Line 263:
trap 'rm -f "$LOCK_FILE" "$INSTALL_FLAG"; send_notification $?' EXIT

# ... 32 lines later ...

# Line 295:
trap cleanup_and_exit EXIT    # ← This OVERWRITES line 263's trap!
```

### AFTER (Fixed - single trap at line ~325)
```bash
# Single unified trap - handles all cleanup in one place
trap cleanup_and_exit EXIT

# The cleanup_and_exit function handles:
# 1. Kill timeout process
# 2. Remove lock files  
# 3. Restore site to live if deployment failed
# 4. Send notifications
```

---

## Fix #3: Safe TIMEOUT_PID Usage

### BEFORE (Broken - line 273 in cleanup_and_exit)
```bash
cleanup_and_exit() {
    local exit_code=$?

    # Kill timeout process
    kill $TIMEOUT_PID 2>/dev/null || true
    # ↑ ERROR: TIMEOUT_PID is undefined because:
    #   1. It's not initialized at script start
    #   2. It's only defined at line 290
    #   3. With set -u, this causes: "unbound variable" error
```

### AFTER (Fixed)
```bash
cleanup_and_exit() {
    local exit_code=$?
    
    # Kill timeout process safely
    if [[ -n "$TIMEOUT_PID" && "$TIMEOUT_PID" != "" ]]; then
        kill "$TIMEOUT_PID" 2>/dev/null || true
        wait "$TIMEOUT_PID" 2>/dev/null || true
    fi
    # ✅ Safe: Variable is initialized at script start as ""
    # ✅ Only kills if it has a valid PID
    # ✅ No "unbound variable" error
```

---

## Fix #4: Removed Unsafe Mode Toggling

### BEFORE (Broken - lines 210-219)
```bash
info "Checking application encryption key..."
APP_KEY_EXISTS=false
set +e  # ← Disable exit on error - FRAGILE!
if [[ -f .env ]]; then
    APP_KEY_LINE=$(grep '^APP_KEY=' .env 2>/dev/null | head -1 || echo "")
    if [[ -n "$APP_KEY_LINE" ]]; then
        APP_KEY_VALUE=$(echo "$APP_KEY_LINE" | cut -d'=' -f2- | sed 's/^"//' | sed 's/"$//')
        if [[ -n "$APP_KEY_VALUE" ]]; then
            APP_KEY_EXISTS=true
        fi
    fi
fi
set -e  # ← Re-enable exit on error
# Problem: Code between set +e and set -e has no error checking
```

### AFTER (Fixed)
```bash
info "Checking application encryption key..."
APP_KEY_EXISTS=false

if [[ -f .env ]]; then
    APP_KEY_LINE=$(grep '^APP_KEY=' .env 2>/dev/null || echo "")
    if [[ -n "$APP_KEY_LINE" ]]; then
        APP_KEY_VALUE=$(echo "$APP_KEY_LINE" | cut -d'=' -f2- | sed 's/^"//' | sed 's/"$//')
        if [[ -n "$APP_KEY_VALUE" ]]; then
            APP_KEY_EXISTS=true
        fi
    fi
fi
# ✅ No mode toggling needed
# ✅ Safe inline OR expressions with [ || echo "" ]
# ✅ Maintains consistent error handling throughout
```

---

## Fix #5: Improved run() Function

### BEFORE (Weak error handling)
```bash
run() {
    if [[ "$DRY_RUN" == "true" ]]; then
        echo -e "${YELLOW}[DRY-RUN]${NC} $*"
    else
        "$@"  # ← Simply executes command, no error context
    fi
}
```

### AFTER (Better error reporting)
```bash
run() {
    if [[ "${DRY_RUN:-false}" == "true" ]]; then
        echo -e "${YELLOW}[DRY-RUN]${NC} $*"
        return 0
    else
        "$@" || {
            local exit_code=$?
            error "Command failed with exit code $exit_code: $*"
            return "$exit_code"
        }
    fi
}
```

**Improvements**:
- Captures exit code of failed command
- Provides context about what command failed  
- Shows exit code for debugging
- Explicit return 0 in dry-run mode
- Uses `"${DRY_RUN:-false}"` to safely handle unset variable

---

## Fix #6: Validate Required Commands

### BEFORE
```bash
# Script didn't check for required tools
# Would fail deep into deployment if commands missing
```

### AFTER (Added early validation)
```bash
validate_required_commands() {
    local missing_commands=()
    
    for cmd in php composer git; do
        if ! command -v "$cmd" &>/dev/null; then
            missing_commands+=("$cmd")
        fi
    done
    
    if [[ ${#missing_commands[@]} -gt 0 ]]; then
        error "Missing required commands: ${missing_commands[*]}"
        error "Please install the missing dependencies and try again."
        exit 1
    fi
}

# Called early in script before any deployments
validate_required_commands
```

**Benefits**:
- Fails fast with clear message
- Identifies missing tools immediately
- Prevents wasted time on long operations

---

## Fix #7: Safer FLAG_AGE Calculation

### BEFORE (Broken bash arithmetic)
```bash
FLAG_AGE=$(($(date +%s) - (FLAG_TIME > 0 ? FLAG_TIME : 0)))
# ↑ ERROR: Bash doesn't support ternary operator in arithmetic!
```

### AFTER (Valid bash arithmetic)
```bash
FLAG_AGE=$(($(date +%s) - FLAG_TIME))
if [[ "$FLAG_AGE" -lt 0 ]]; then
    FLAG_AGE=0  # Handle clock adjustment
fi
```

**What was wrong**:
- C-style ternary `? :` is not valid in bash `$(( ))`
- Would cause syntax error in deployment flag checking

---

## Fix #8: Migration Verification with Safe Recovery

### BEFORE (Could fail silently)
```bash
if [[ "$DRY_RUN" == "false" ]]; then
    PENDING=$(php artisan migrate:status 2>/dev/null | grep -c "Pending" || true)
    if [[ "$PENDING" -gt 0 ]]; then
        error "$PENDING migration(s) still pending!"
        run php artisan up  # ← Uses 'run' function, may not execute
        exit 1
    fi
fi
```

### AFTER (Guaranteed recovery)
```bash
if [[ "$DRY_RUN" == "false" ]]; then
    if PENDING=$(php artisan migrate:status 2>&1 | grep -c "Pending" || echo "0"); then
        if [[ "$PENDING" -gt 0 ]]; then
            error "$PENDING migration(s) still pending! Check migration errors above."
            warn "Attempting to bring site back online before exit..."
            php artisan up 2>/dev/null || warn "Could not execute 'php artisan up'"
            exit 1
        fi
    fi
    success "All migrations applied — no pending migrations."
fi
```

**Improvements**:
- Direct `php artisan up` call (not through `run()` function)
- Guaranteed to execute even in error cases
- Safe error handling if artisan command fails
- Better error reporting

---

## Summary of All Changes

| Area | Change | Impact |
|------|--------|--------|
| Initialization | Move variables before `set -u` | Eliminates unbound variable errors |
| Traps | Single unified `trap cleanup_and_exit EXIT` | Consistent cleanup behavior |
| Variables | Safe initialization of `TIMEOUT_PID=""` | No crashes in cleanup handler |
| Mode toggling | Remove `set +e`/`set -e` | Consistent error handling |
| Error reporting | Enhanced `run()` function with context | Better debugging |
| Validation | Added `validate_required_commands()` | Fail fast on missing tools |
| Arithmetic | Fix bash arithmetic syntax | Valid bash expressions |
| Recovery | Guaranteed `php artisan up` on failure | Site stays online |

---

## Testing the Fixes

All changes have been applied to `deploy-fixed.sh` which can be tested with:

```bash
# Syntax check
bash -n deploy-fixed.sh

# Dry run (no changes)
./deploy-fixed.sh --dry-run

# Full deployment
./deploy-fixed.sh --force
```

## Files

- **deploy-fixed.sh** - Complete corrected script (596 lines)
- **deploy.sh** - Previous broken version (for reference)
- **deploy.sh.backup** - Timestamped backup before fix
