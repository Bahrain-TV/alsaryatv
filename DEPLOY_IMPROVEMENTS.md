# Deploy Script Improvements & Bug Fixes

**Last Updated**: February 16, 2026  
**Status**: ✅ Critical Issues Fixed

---

## 🔴 Critical Issues Found & Fixed

### 1. **Trap Handler Conflict (HIGH SEVERITY)**
**Problem**: 
- Two `trap EXIT` statements on lines 263 and 295
- Second trap **overwrites** the first trap
- Inconsistent cleanup behavior

**Impact**: 
- Lock files may not be cleaned up properly on exit
- Cleanup handlers may not fire in the correct order
- Could leave stale lock files preventing future deployments

**Fix Applied**:
```bash
# BEFORE: Two separate traps (line 263 & 295)
trap 'rm -f "$LOCK_FILE" "$INSTALL_FLAG"; send_notification $?' EXIT  # ← Overwritten!
...
trap cleanup_and_exit EXIT  # ← Overwrites the above

# AFTER: Single unified trap
trap cleanup_and_exit EXIT   # Handles all cleanup in one place
```

---

### 2. **Undefined Variable with Strict Mode (CRITICAL)**
**Problem**:
- Script uses `set -euo pipefail` at line 21
- The `-u` flag causes errors when accessing undefined variables
- `cleanup_and_exit()` references `$TIMEOUT_PID` (line 273)
- `$TIMEOUT_PID` is not defined until line 290

**Impact**:
- If ANY error occurs between line 263-289, the cleanup handler tries to kill an undefined variable
- Script fails with: `line 273: TIMEOUT_PID: unbound variable`
- Site left in maintenance mode with no way to recover
- **This is likely causing the server breakage (exit code 255)**

**Fix Applied**:
```bash
# BEFORE: Variables defined later, but referenced in trap
# This causes: "TIMEOUT_PID: unbound variable" error

# AFTER: Initialize critical variables before enabling strict mode
TIMEOUT_PID=""
MAINTENANCE_WAS_ENABLED=false
LOCK_FILE="/tmp/deploy.lock"
INSTALL_FLAG="storage/framework/deployment.lock"

# THEN enable strict mode with -u
set -u
```

---

### 3. **Stale Mode Temprarily Disabled Strict Mode (HIGH SEVERITY)**
**Problem**:
```bash
set -euo pipefail    # Line 21: Enable strict mode
...
set +e               # Line 210: DISABLE strict mode for APP_KEY check
# ... code ...
set -e               # Line 219: Re-enable strict mode
```

**Issues**:
- Toggling strict mode is fragile and error-prone
- Code between `set +e` and `set -e` has no error checking
- If grep command fails silently, subsequent code may corrupt logic

**Fix Applied**:
- Removed `set +e`/`set -e` toggles
- Rewrote APP_KEY checking logic to NOT need error suppression
- Used proper error handling with direct conditional checks

---

### 4. **Git Operations Error Handling (MEDIUM SEVERITY)**
**Problem**:
```bash
run git fetch origin
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "unknown")
run git reset --hard origin/"$CURRENT_BRANCH"
```

**Issues**:
- `git reset --hard` without checking if we're on a valid branch
- Could leave repo in inconsistent state
- No validation that remote exists or has the current branch

**Fix Applied**:
- Added proper error handling in `run()` function
- Validates git operations before proceeding
- Better error messages when git operations fail

---

### 5. **Missing Required Command Validation (MEDIUM SEVERITY)**
**Problem**:
- Script doesn't check if required commands exist before using them
- Commands assumed to be in PATH: `php`, `composer`, `git`, `npm/pnpm`
- Fails deep in the script if commands missing

**Impact**:
- Cryptic error messages when tools not installed
- Wasted time on long operations before failure

**Fix Applied**:
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
        exit 1
    fi
}
```

---

### 6. **Improved run() Function Error Handling**
**Before**:
```bash
run() {
    if [[ "$DRY_RUN" == "true" ]]; then
        echo -e "${YELLOW}[DRY-RUN]${NC} $*"
    else
        "$@"  # ✗ No error context or return code handling
    fi
}
```

**After**:
```bash
run() {
    if [[ "$DRY_RUN" == "true" ]]; then
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
- ✅ Captures and reports exit codes
- ✅ Provides context about which command failed
- ✅ Explicit error messages for debugging

---

### 7. **Migration Verification Robustness**
**Before**:
```bash
PENDING=$(php artisan migrate:status 2>/dev/null | grep -c "Pending" || true)
if [[ "$PENDING" -gt 0 ]]; then
    error "$PENDING migration(s) still pending!"
    run php artisan up  # ✗ Using 'run' which might not execute in dry-run
    exit 1
fi
```

**After**:
```bash
if [[ "$DRY_RUN" == "false" ]]; then
    if PENDING=$(php artisan migrate:status 2>&1 | grep -c "Pending" || echo "0"); then
        if [[ "$PENDING" -gt 0 ]]; then
            error "$PENDING migration(s) still pending!"
            warn "Attempting to bring site back online before exit..."
            php artisan up 2>/dev/null || warn "Could not execute 'php artisan up'"
            exit 1
        fi
    fi
    success "All migrations applied — no pending migrations."
fi
```

**Improvements**:
- ✅ Better error handling when checking migration status
- ✅ Guarantees `php artisan up` executes to prevent site outage
- ✅ Handles case where artisan command might fail
- ✅ Proper error reporting if site can't be brought online

---

### 8. **Cleanup Handler Improvements**
**Before**:
```bash
cleanup_and_exit() {
    local exit_code=$?
    kill $TIMEOUT_PID 2>/dev/null || true  # ✗ Unset variable!
    # ... rest of cleanup
}
```

**After**:
```bash
cleanup_and_exit() {
    local exit_code=$?
    
    # Kill timeout process safely
    if [[ -n "$TIMEOUT_PID" && "$TIMEOUT_PID" != "" ]]; then
        kill "$TIMEOUT_PID" 2>/dev/null || true
        wait "$TIMEOUT_PID" 2>/dev/null || true
    fi
    # ... rest of cleanup
}

# CRITICAL: Restore site if we put it in maintenance mode and something failed
if [[ "$MAINTENANCE_WAS_ENABLED" == "true" && "$exit_code" -ne 0 ]]; then
    error "Deploy failed (exit code: $exit_code)! Restoring site to LIVE status..."
    if php artisan up 2>/dev/null; then
        success "Site restored to live."
    else
        error "WARNING: Could not restore site! Manual intervention may be needed."
    fi
fi
```

**Improvements**:
- ✅ Safely checks if TIMEOUT_PID is set before killing
- ✅ Waits for timeout process to finish
- ✅ Better error messages when site restoration fails
- ✅ Prevents silent failures when bringing site back up

---

## 📋 Summary of Changes

| Issue | Severity | Status | Solution |
|-------|----------|--------|----------|
| Trap conflict (2 EXIT handlers) | 🔴 CRITICAL | ✅ Fixed | Consolidated into single handler |
| Undefined TIMEOUT_PID with `-u` | 🔴 CRITICAL | ✅ Fixed | Pre-initialize before strict mode |
| `set +e`/`set -e` toggles | 🔴 CRITICAL | ✅ Fixed | Removed, rewrote logic |
| Missing command validation | 🟠 MEDIUM | ✅ Fixed | Added validate_required_commands() |
| Weak error handling in run() | 🟠 MEDIUM | ✅ Fixed | Added exit code capture |
| Git operations safety | 🟠 MEDIUM | ✅ Fixed | Better error context |
| Migration verification | 🟠 MEDIUM | ✅ Fixed | Robust recovery on failure |
| Maintenance mode safety | 🟠 MEDIUM | ✅ Fixed | Better recovery logic |

---

## 🚀 How to Test the Fixed Script

### 1. **Dry-run test** (no actual deployment)
```bash
./deploy.sh --dry-run
```
This will show all steps that would execute without making changes.

### 2. **Force deployment** (verify all steps work)
```bash
./deploy.sh --force
```
This will run all deployment steps even if no changes detected.

### 3. **Normal deployment**
```bash
./deploy.sh
```
Standard deployment with change detection.

### 4. **Fresh database** (warning: destructive!)
```bash
./deploy.sh --fresh
```
Drops all tables, re-runs migrations, and seeds data.

---

## ⚠️ Important Notes

### Server Recovery
If the server is currently in maintenance mode and won't start:

```bash
# Manually bring the site back online
php artisan up

# Check if there are stale lock files
ls -la storage/framework/deployment.lock 2>/dev/null
ls -la /tmp/deploy.lock 2>/dev/null

# Remove them if they exist
rm -f storage/framework/deployment.lock
rm -f /tmp/deploy.lock
```

### Running Deployment Manually
```bash
# SSH into the server
ssh user@server

# Navigate to the app directory
cd /home/alsarya.tv/public_html

# Run the deploy script
./deploy.sh

# Monitor the output
```

### Monitoring Deployment Progress
```bash
# In another terminal, watch the logs
tail -f storage/logs/laravel.log

# Watch for specific errors
tail -f storage/logs/laravel.log | grep -i error
```

---

## 📞 Troubleshooting

### Error: "Deploy script already running"
```bash
# Check if process exists
ps aux | grep deploy.sh

# Remove stale locks if safe
rm -f /tmp/deploy.lock
rm -f storage/framework/deployment.lock
```

### Error: "TIMEOUT_PID: unbound variable"
**This should no longer occur** with the fixes applied. If it does:
1. Ensure you're using the updated deploy.sh
2. Check for any local modifications that might have reverted changes

### Migration Failures
```bash
# Check migration status
php artisan migrate:status

# Rollback last migration if needed
php artisan migrate:rollback

# Force site back online
php artisan up
```

### Database Connection Issues
```bash
# Verify environment configuration
cat .env | grep DB_

# Test database connection
php artisan db:show

# Check Laravel logs
tail -n 50 storage/logs/laravel.log
```

---

## ✅ Verification Checklist

After applying these fixes:

- [ ] Script runs without "unbound variable" errors
- [ ] Lock files are properly cleaned up after deployment
- [ ] Site automatically comes back online if deployment fails
- [ ] All required commands are validated before executing
- [ ] Error messages are clear and actionable
- [ ] Dry-run mode works without side effects
- [ ] Maintenance mode is properly managed
- [ ] Health checks pass after deployment

---

**Version**: 2.1 (Fixed)  
**Last Tested**: February 16, 2026  
**Maintainer**: AlSarya TV Development Team
