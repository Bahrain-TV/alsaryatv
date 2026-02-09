# Environment File Management Commands

Comprehensive artisan commands for validating and synchronizing `.env` files across Laravel projects.

## Overview

Two powerful commands are provided to manage environment variables:

1. **`env:validate`** - Validate and check all .env files for inconsistencies
2. **`env:sync-vars`** - Synchronize variables from master .env to all other files

---

## Command 1: `env:validate`

Comprehensive validation of all environment files with detailed reporting.

### Basic Usage

```bash
php artisan env:validate
```

### Options

| Option | Flag | Description |
|--------|------|-------------|
| Fix missing variables | `--fix` | Automatically add missing variables to all files |
| Dry run mode | `--dry-run` | Preview what would be changed without applying |
| Generate report | `--report` | Create detailed validation report file |
| Select master file | `--select-master` | Interactively choose master .env file |

### Examples

**Validate all .env files:**
```bash
php artisan env:validate
```

**Preview what would be fixed:**
```bash
php artisan env:validate --fix --dry-run
```

**Apply fixes and generate report:**
```bash
php artisan env:validate --fix --report
```

**Interactive master file selection:**
```bash
php artisan env:validate --select-master
```

### Output Example

```
🔍 Environment File Validator & Synchronizer

Found 3 environment file(s):
  • .env
  • .env.local
  • .env.production

Master file: .env

📊 VALIDATION REPORT
────────────────────────────────────────────────────────────

Summary:
  Total variables: 42
  Total files: 3

Per-File Breakdown:
  .env [MASTER]
    Variables: 42
  .env.local
    Variables: 38
    Missing: 4 - APP_DEBUG, DB_HOST, MAIL_HOST...
  .env.production
    Variables: 35
    Missing: 7 - CACHE_DRIVER, SESSION_DRIVER...

⚠️  Value Inconsistencies Found:  2 variables
  APP_ENV
    • .env: local
    • .env.production: production
  DB_HOST
    • .env: localhost
    • .env.production: db.prod.example.com

Status: ⚠️  Issues detected - use --fix to resolve
```

---

## Command 2: `env:sync-vars`

Advanced synchronization with multiple options for merging variables.

### Basic Usage

```bash
php artisan env:sync-vars
```

### Options

| Option | Flag | Description |
|--------|------|-------------|
| Specify master file | `--master=FILE` | Use specific file as master (e.g., `.env.production`) |
| Dry run mode | `--dry-run` | Preview changes without applying |
| Interactive mode | `--interactive` | Prompt for each variable to sync |
| Skip validation | `--skip-validation` | Don't validate master file |
| Preserve comments | `--include-comments` | Keep section comments from original files |

### Examples

**Basic sync from .env to all others:**
```bash
php artisan env:sync-vars
```

**Use specific file as master:**
```bash
php artisan env:sync-vars --master=.env.production
```

**Interactive mode (approve each change):**
```bash
php artisan env:sync-vars --interactive
```

**Preview changes before applying:**
```bash
php artisan env:sync-vars --dry-run
```

**Combined options:**
```bash
php artisan env:sync-vars --master=.env.production --interactive --dry-run
```

### Output Example

```
🔄 Environment Variables Synchronizer

📁 Available .env files:
  [0] .env
  [1] .env.local
  [2] .env.production

Master file selected: .env
Found 42 variables in master file

Will sync to 2 file(s)

📊 Synchronization Results:

  .env.local
    ✓ Added: 4 missing variable(s)
    ⚠ Found: 2 extra variable(s)
  .env.production
    ✓ Added: 7 missing variable(s)

Total changes: 11

✅ Synchronization completed successfully!
```

---

## Use Cases

### Scenario 1: Setup New Environment

```bash
# Copy .env as template
cp .env .env.staging

# Validate to see what's missing
php artisan env:validate

# Auto-fix all missing variables
php artisan env:validate --fix

# Review changes in report
php artisan env:validate --report
```

### Scenario 2: Add New Variable to All Environments

1. Add the variable to master `.env`:
```env
NEW_FEATURE_ENABLED=false
```

2. Sync to all other files:
```bash
php artisan env:sync-vars --dry-run  # Preview first
php artisan env:sync-vars            # Apply
```

3. Verify synchronization:
```bash
php artisan env:validate
```

### Scenario 3: Deploy to Production

```bash
# Before deployment, ensure all envs are in sync
php artisan env:validate --report

# Check for inconsistencies
php artisan env:sync-vars --dry-run

# If everything looks good, sync
php artisan env:sync-vars
```

### Scenario 4: Team Collaboration

Team member adds `.env.local` but forgets some variables:

```bash
# Another developer runs validation
php artisan env:validate

# Output shows what's missing in .env.local
# They can then fix it:
php artisan env:validate --fix --dry-run  # Preview
php artisan env:validate --fix             # Apply
```

---

## Installation in Other Projects

### Step 1: Copy Commands

Copy these files to your project:

```bash
# From this project
cp app/Console/Commands/ValidateEnvCommand.php /path/to/other/project/app/Console/Commands/
cp app/Console/Commands/SyncEnvironmentVariablesCommand.php /path/to/other/project/app/Console/Commands/
```

### Step 2: Update Namespace (if needed)

If your project uses a different namespace, update the namespace in both command files:

```php
// Change from:
namespace App\Console\Commands;

// To your namespace if different:
namespace Your\Custom\Namespace\Console\Commands;
```

### Step 3: Verify Installation

```bash
php artisan list | grep env
```

You should see:
```
  env:sync-vars            Synchronize environment variables...
  env:validate             Validate and synchronize all .env files...
```

---

## Configuration

### Auto-Discovery (Laravel 5.3+)

Commands are automatically discovered. No manual registration needed.

### Manual Registration (if auto-discovery fails)

Edit `app/Console/Kernel.php`:

```php
protected $commands = [
    \App\Console\Commands\ValidateEnvCommand::class,
    \App\Console\Commands\SyncEnvironmentVariablesCommand::class,
];
```

---

## Files Supported

The commands automatically discover and manage:

- `.env`
- `.env.local`
- `.env.*.local` (e.g., `.env.staging.local`)
- `.env.production`
- `.env.staging`
- `.env.development`
- `.env.testing`

---

## Best Practices

### 1. Version Control

```bash
# Track .env.example or .env.default
git add .env.example

# Never commit actual .env files (add to .gitignore)
echo ".env*" >> .gitignore
echo "!.env.example" >> .gitignore
```

### 2. Regular Validation

Add to your deployment script:

```bash
# Deploy script
php artisan env:validate --report || exit 1
php artisan env:sync-vars --dry-run || exit 1
```

### 3. Documentation

Keep this template documented:

```env
# .env.example
APP_NAME=MyApp
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=app_db
# ... etc
```

### 4. CI/CD Integration

**GitHub Actions Example:**

```yaml
- name: Validate Environment Files
  run: php artisan env:validate --skip-validation

- name: Sync Environment Variables
  run: php artisan env:sync-vars --dry-run
```

**GitLab CI Example:**

```yaml
validate_env:
  script:
    - php artisan env:validate --report
    - php artisan env:sync-vars --dry-run
```

---

## Troubleshooting

### Command not found

```bash
# Clear cache
php artisan cache:clear

# List commands
php artisan list
```

### Permission denied when syncing

```bash
# Ensure storage directory is writable
chmod -R 775 storage

# Check logs
tail -f storage/logs/laravel.log
```

### File locked errors

```bash
# Close other processes that might be holding the file
# On macOS/Linux
lsof | grep .env

# Close the editor/IDE that has the .env file open
```

### Namespace not found

Verify the command class namespace matches your project structure and update accordingly.

---

## Advanced: Creating Custom Categories

Edit either command file to add custom variable categories for better organization:

```php
// In ValidateEnvCommand::categorizeAndSortVariables()
$priorityOrder = [
    'APP',
    'DB',
    'MAIL',
    'CUSTOM_FEATURE',  // Add your custom category
    // ... rest
];
```

---

## Summary

| Task | Command |
|------|---------|
| Check all .env files | `php artisan env:validate` |
| Fix missing variables | `php artisan env:validate --fix` |
| Sync from master | `php artisan env:sync-vars` |
| Preview changes | `php artisan env:sync-vars --dry-run` |
| Generate report | `php artisan env:validate --report` |
| Interactive mode | `php artisan env:sync-vars --interactive` |

---

## License

These commands are part of the AlSarya TV project and can be freely used and modified in any Laravel project.
