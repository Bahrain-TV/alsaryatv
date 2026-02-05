# Export & Version Management System

## Overview

This document describes the automated export system and version management features added to AlSarya TV application.

---

## 1. Scheduled Daily Export (2:00 AM)

### Purpose
Automatically export all caller data daily at 2:00 AM with encryption for backup and archival.

### Features
- **Automated Scheduling**: Runs automatically at 2:00 AM (Asia/Bahrain timezone)
- **Encryption**: All exports are encrypted using Laravel's encryption
- **Metadata Tracking**: Each export includes a manifest with statistics
- **Automatic Cleanup**: Keeps only the latest 30 exports to save storage
- **Audit Logging**: All exports logged for compliance

### How It Works

#### Daily Schedule
The export runs automatically via the Laravel scheduler:
```bash
# The scheduler runs automatically when configured in your application
# Ensure scheduler is running in production:
php artisan schedule:run
```

#### Manual Export
You can also trigger an export manually:

```bash
# Export with encryption (default)
php artisan callers:export --encrypt=true

# Export without encryption
php artisan callers:export --encrypt=false

# Specify custom storage path
php artisan callers:export --path=exports/monthly
```

### Storage Location
Exports are stored in: `storage/app/exports/`

```
storage/app/exports/
├── callers_export_2026-02-04_020000.encrypted
├── callers_export_2026-02-04_020000.manifest.json
├── callers_export_2026-02-03_020000.encrypted
└── callers_export_2026-02-03_020000.manifest.json
```

### Export Format
Each CSV export includes:
- ID
- Name
- Phone
- CPR (Personal ID)
- Hits
- Status
- Level
- Winner Status
- IP Address
- Last Hit
- Notes
- Created At
- Updated At

### Encryption Details

#### Encrypting Exports
Exports are encrypted using Laravel's built-in encryption:
```php
$encryptedContent = Crypt::encryptString($csvContent);
```

#### Decrypting Exports
To decrypt an export file:
```php
// In a Laravel context
$encryptedContent = Storage::get('exports/callers_export_2026-02-04_020000.encrypted');
$decryptedContent = Crypt::decryptString($encryptedContent);
```

Or via command:
```bash
php artisan tinker
>>> $encrypted = Storage::get('exports/callers_export_2026-02-04_020000.encrypted');
>>> $decrypted = Crypt::decryptString($encrypted);
>>> dd($decrypted);
```

### Manifest File
Each export generates a manifest JSON file with metadata:

```json
{
  "export_timestamp": "2026-02-04T02:00:00+03:00",
  "total_records": 1250,
  "file_name": "callers_export_2026-02-04_020000.encrypted",
  "encrypted": true,
  "compression": false,
  "status": "success",
  "file_size": 45280
}
```

### Logging
All exports are logged in: `storage/logs/exports.log`

---

## 2. Automatic Version Management

### Purpose
Track application versions automatically with semantic versioning (X.Y.Z).

### Version Format
Uses **Semantic Versioning**:
- **Major (X)**: Breaking changes, significant features
- **Minor (Y)**: New features, backwards compatible
- **Patch (Z)**: Bug fixes, minor improvements

Example: `1.2.3` means Major.Minor.Patch

### Version File
Version information stored in: `version.json` (root directory)

```json
{
  "version": "1.0.0",
  "name": "AlSarya TV Show Registration System",
  "description": "Caller registration platform for TV show",
  "created_at": "2026-02-04T02:30:00+03:00",
  "updated_at": "2026-02-04T03:00:00+03:00",
  "updated_by": "system",
  "changelog": [...]
}
```

### Managing Versions

#### Get Current Version
```bash
php artisan version get
```

Output:
```
Current Version Information:
┌──────────────┬─────────────────────────────────────────┐
│ Property     │ Value                                   │
├──────────────┼─────────────────────────────────────────┤
│ Version      │ 1.0.0                                   │
│ Name         │ AlSarya TV Show Registration System     │
│ Branch       │ main                                    │
│ Commit       │ a1b2c3d4                               │
│ Environment  │ production                              │
│ Updated At   │ 2026-02-04 02:30:00                   │
│ Updated By   │ system                                  │
└──────────────┴─────────────────────────────────────────┘
```

#### Increment Patch Version (Bug Fixes)
```bash
php artisan version increment --type=patch
```
Increments: `1.0.0` → `1.0.1`

#### Increment Minor Version (New Features)
```bash
php artisan version increment --type=minor
```
Increments: `1.0.0` → `1.1.0`

#### Increment Major Version (Breaking Changes)
```bash
php artisan version increment --type=major
```
Increments: `1.0.0` → `2.0.0`

#### Set Specific Version
```bash
php artisan version set --version=2.0.0
```

#### View Changelog
```bash
php artisan version changelog --limit=50
```

---

## 3. Version Check API Endpoints

### Public Endpoints (Available to all)

#### 1. Get Current Version
```http
GET /api/version
```

**Response:**
```json
{
  "success": true,
  "current": {
    "version": "1.0.0",
    "branch": "main",
    "commit": "a1b2c3d4",
    "updated_at": "2026-02-04T02:30:00+03:00",
    "environment": "production"
  },
  "metadata": {
    "name": "AlSarya TV Show Registration System",
    "description": "Caller registration platform for TV show"
  }
}
```

#### 2. Check Version Difference (For Local Dev)
```http
POST /api/version/check-difference
Content-Type: application/json

{
  "remote_version": "1.0.0",
  "remote_branch": "main",
  "notify_on_difference": true
}
```

**Response (Outdated):**
```json
{
  "success": true,
  "has_difference": true,
  "comparison": {
    "local_version": "0.9.8",
    "remote_version": "1.0.0",
    "version_status": "outdated",
    "local_branch": "develop",
    "remote_branch": "main",
    "branch_match": false
  },
  "notification": {
    "type": "update_available",
    "title": "Update Available",
    "message": "A new version (1.0.0) is available on main. Your local version is 0.9.8.",
    "severity": "info",
    "action": "Pull latest changes from main"
  }
}
```

#### 3. Get Changelog
```http
GET /api/version/changelog?limit=10
```

**Response:**
```json
{
  "success": true,
  "changelog": [
    {
      "version": "1.0.0",
      "type": "feature",
      "message": "Added caller export functionality",
      "timestamp": "2026-02-04T02:30:00+03:00"
    },
    ...
  ],
  "total": 25
}
```

### Protected Endpoints (Admin only, requires authentication)

#### Increment Version
```http
POST /api/version/increment
Authorization: Bearer {token}
Content-Type: application/json

{
  "type": "patch"
}
```

---

## 4. Development Workflow

### For Local Development
Use this to detect when production has been updated:

```javascript
// Check production version from local dev
async function checkForUpdates() {
  const response = await fetch('https://live-server.com/api/version');
  const liveVersion = await response.json();

  // Compare with local version
  const localVersion = '0.9.8';

  if (liveVersion.current.version !== localVersion) {
    console.warn('Production has been updated!');
    console.warn(`Local: ${localVersion}, Live: ${liveVersion.current.version}`);
  }
}

// Check with notification
async function checkWithNotification() {
  const response = await fetch('https://live-server.com/api/version/check-difference', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      remote_version: await getProductionVersion(),
      remote_branch: 'production', // Your production branch name
      notify_on_difference: true
    })
  });

  const result = await response.json();

  if (result.has_difference && result.notification) {
    showNotification(result.notification);
  }
}
```

### Production Branch Strategy
The system supports multiple branches:

```
main (development)
  └─ Features in development

production (production)
  └─ Live, stable code

staging (staging/testing)
  └─ Pre-production testing
```

Each branch can have its own version:
- `main`: 1.0.0
- `production`: 0.9.8
- `staging`: 1.0.0-rc1

### Git Workflow
After incrementing version:
```bash
# 1. Increment version
php artisan version increment --type=patch

# 2. Commit version change
git add version.json
git commit -m "chore: bump version to 1.0.1"

# 3. Push to remote
git push origin main

# 4. For production release, merge to production branch
git checkout production
git merge main
git push origin production
```

---

## 5. Security Considerations

### Export Encryption
- **Method**: AES-256 encryption via Laravel
- **Key**: Uses `APP_KEY` from `.env`
- **Storage**: Encrypted files stored in `storage/app/exports/`
- **Access**: Only users with file system access can retrieve

### Production Exports
For production environments:
1. Exports are always encrypted
2. Access is logged and auditable
3. Old exports (>30 days) are automatically deleted
4. Manifest files track integrity

### Version File
- `version.json` is committed to git for version control
- Tracked in git history for audit trail
- Contains update_by field for accountability

---

## 6. Troubleshooting

### Export Not Running at 2:00 AM
Check if scheduler is running:
```bash
# Test scheduler
php artisan schedule:work

# Check logs
tail -f storage/logs/exports.log
```

### Cannot Decrypt Export
Ensure you're using the same `APP_KEY`:
```bash
# Show current key
php artisan key:show

# If key changed, you cannot decrypt old exports
# This is a security feature to prevent unauthorized access
```

### Version Mismatch Between Branches
```bash
# On each branch, check version
git checkout main
php artisan version get

git checkout production
php artisan version get
```

---

## 7. Example Use Cases

### Daily Backup Workflow
1. ✅ Export runs automatically at 2:00 AM
2. ✅ Encrypted CSV saved with manifest
3. ✅ Old exports (>30) cleaned up automatically
4. ✅ Logged in `exports.log` for audit

### Release Management
1. Create feature branch from `main`
2. Make changes, commit
3. Create PR and review
4. Merge to `main`
5. Increment patch version: `php artisan version increment --type=patch`
6. Tag release: `git tag v1.0.1`
7. Merge `main` to `production`
8. Production auto-detects version change via API

### Monitor Updates in Development
```bash
# Set up cron job to check production version every hour
# Add to development machine crontab:
0 * * * * curl -s https://live.example.com/api/version | jq '.current.version'
```

---

## 8. Configuration

### Customize Export Path
In `Kernel.php`:
```php
$schedule->command('callers:export --path=custom/path')
    ->dailyAt('02:00');
```

### Change Export Time
In `Kernel.php`:
```php
// Change 02:00 to any time
$schedule->command('callers:export --encrypt=true')
    ->dailyAt('03:30'); // 3:30 AM instead
```

### Encryption Toggle
```php
// For development (no encryption)
$schedule->command('callers:export --encrypt=false')
    ->dailyAt('02:00');
```

---

## 9. API Rate Limiting

All version API endpoints are rate limited:
- **Public**: 60 requests per minute
- **Admin**: 100 requests per minute (authenticated)

---

## 10. Monitoring & Alerts

### Log Files
- **Exports**: `storage/logs/exports.log`
- **Version Changes**: `storage/logs/laravel.log`
- **API Access**: `storage/logs/laravel.log`

### Recommended Alerts
- Version changed on production
- Export failed
- Decryption failed
- Version mismatch detected
