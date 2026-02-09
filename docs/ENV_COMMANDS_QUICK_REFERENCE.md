# Environment Commands - Quick Reference

Fast lookup guide for environment variable management commands.

## Two Commands

### 1️⃣ `env:validate` - Check & Fix Issues

**Syntax:**
```bash
php artisan env:validate [--fix] [--dry-run] [--report] [--select-master]
```

| What | Command |
|------|---------|
| **Audit all .env files** | `php artisan env:validate` |
| **Fix missing variables** | `php artisan env:validate --fix` |
| **Preview fixes** | `php artisan env:validate --fix --dry-run` |
| **Create report file** | `php artisan env:validate --report` |
| **Choose master file** | `php artisan env:validate --select-master` |

**Output:** Shows missing variables, value inconsistencies, and validation status

---

### 2️⃣ `env:sync-vars` - Synchronize Variables

**Syntax:**
```bash
php artisan env:sync-vars [--master=FILE] [--dry-run] [--interactive] [--skip-validation]
```

| What | Command |
|------|---------|
| **Sync from .env to others** | `php artisan env:sync-vars` |
| **Use specific master** | `php artisan env:sync-vars --master=.env.production` |
| **Preview changes** | `php artisan env:sync-vars --dry-run` |
| **Ask before each change** | `php artisan env:sync-vars --interactive` |
| **Skip validation checks** | `php artisan env:sync-vars --skip-validation` |

**Output:** Shows added/removed variables per file

---

## Common Workflows

### ✅ Setup New Environment
```bash
cp .env .env.staging
php artisan env:validate --fix
```

### ✅ Add Variable to All Envs
```bash
# 1. Add to .env
echo "NEW_VAR=value" >> .env

# 2. Sync to others
php artisan env:sync-vars --dry-run
php artisan env:sync-vars

# 3. Verify
php artisan env:validate
```

### ✅ Pre-Deployment Check
```bash
php artisan env:validate --report
php artisan env:sync-vars --dry-run
```

### ✅ Fix Team Member's Setup
```bash
php artisan env:validate --fix
```

---

## Installation in Other Projects

### Quick Copy
```bash
cp app/Console/Commands/ValidateEnvCommand.php /target/app/Console/Commands/
cp app/Console/Commands/SyncEnvironmentVariablesCommand.php /target/app/Console/Commands/
```

### Automated Installation
```bash
./scripts/install-env-commands.sh /path/to/target/project
```

---

## Detected File Patterns

Commands automatically find these files:
- `.env`
- `.env.local`
- `.env.production`
- `.env.staging`
- `.env.development`
- `.env.testing`
- `.env.*.local`

---

## Exit Codes

| Code | Meaning |
|------|---------|
| `0` | Success |
| `1` | Error (file not found, permission denied, etc.) |

---

## Typical Output Examples

### env:validate Output
```
🔍 Environment File Validator & Synchronizer
Found 3 environment file(s):
  • .env [MASTER]
  • .env.local
  • .env.production

📊 VALIDATION REPORT
Summary:
  Total variables: 42
  Total files: 3

Per-File Breakdown:
  .env [MASTER]
    Variables: 42
  .env.local
    Variables: 38
    Missing: 4 - APP_DEBUG, DB_HOST...

Status: ⚠️  Issues detected - use --fix to resolve
```

### env:sync-vars Output
```
🔄 Environment Variables Synchronizer
Master file selected: .env
Found 42 variables in master file
Will sync to 2 file(s)

📊 Synchronization Results:
  .env.local
    ✓ Added: 4 missing variable(s)
  .env.production
    ✓ Added: 7 missing variable(s)

Total changes: 11
✅ Synchronization completed successfully!
```

---

## Helpful Aliases

Add to `.bash_aliases` or `.zshrc`:

```bash
# Validate environment files
alias env:check='php artisan env:validate'

# Sync and preview
alias env:preview='php artisan env:sync-vars --dry-run'

# Quick sync
alias env:sync='php artisan env:sync-vars'

# Interactive sync
alias env:ask='php artisan env:sync-vars --interactive'

# Generate report
alias env:report='php artisan env:validate --report'
```

Then use:
```bash
env:check
env:sync
env:preview
```

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Command not found | `php artisan cache:clear` |
| Permission denied | `chmod -R 755 storage/` |
| File locked | Close editor/IDE holding the .env file |
| Wrong namespace | Update namespace in command files to match your project |

---

## Key Features

✅ **Automatic file discovery** - Finds all .env* files
✅ **Validation reports** - Detailed inconsistency reports
✅ **Dry-run mode** - Preview before applying changes
✅ **Interactive mode** - Approve each change individually
✅ **Categorization** - Organizes variables by prefix (APP, DB, MAIL, etc.)
✅ **Backup awareness** - Shows empty/null values
✅ **Project-agnostic** - Works in any Laravel project

---

## Source Files

Located in this project at:
- `app/Console/Commands/ValidateEnvCommand.php`
- `app/Console/Commands/SyncEnvironmentVariablesCommand.php`
- `docs/ENV_COMMANDS_GUIDE.md` (full documentation)
- `scripts/install-env-commands.sh` (installation script)

---

**Last Updated:** February 2026
**Tested On:** Laravel 12.x, PHP 8.3+
