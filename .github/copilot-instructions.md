# 🎯 Copilot Instructions — AlSarya TV Registration System

> **Mission-Critical TV Show Caller Registration Platform**  
> Laravel 12 • PHP 8.5 • Bilingual (Arabic/English) • High-Security Architecture

---

## 📋 Table of Contents

1. [🎬 Project Overview](#-project-overview)
2. [🏗️ Architecture & Data Flow](#️-architecture--data-flow)
3. [🔒 Security Model (Non-Negotiable)](#-security-model-non-negotiable)
4. [⚡ Quick Start Guide](#-quick-start-guide)
5. [🛠️ Developer Workflows](#️-developer-workflows)
6. [🧪 Testing Strategy](#-testing-strategy)
7. [📐 Project Conventions](#-project-conventions)
8. [🚨 Critical Patterns](#-critical-patterns)
9. [🔍 Troubleshooting Guide](#-troubleshooting-guide)
10. [📚 Essential Files Reference](#-essential-files-reference)

---

## 🎬 Project Overview

### What This System Does
**AlSarya TV** (برنامج السارية المباشر من تلفزيون البحرين) is a high-traffic, real-time caller registration platform for Bahrain TV's live show. The system handles:

- **Dual Registration Flows**: Individual + Family caller registration
- **Real-time Hit Tracking**: Per-caller participation counters with visual feedback
- **Winner Management**: Admin-controlled winner selection via Filament dashboard
- **Ramadan Countdown**: Seasonal feature with custom animations
- **Multi-language Support**: Arabic (primary) + English interface

### Technology Stack

```
┌─────────────────────────────────────────────────────────────┐
│                     FRONTEND LAYER                          │
├─────────────────────────────────────────────────────────────┤
│ Vite 6.x │ Tailwind CSS │ Alpine.js │ Livewire/Flux │ GSAP │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                     BACKEND LAYER                           │
├─────────────────────────────────────────────────────────────┤
│ Laravel 12.x │ PHP 8.5 │ Filament v5.1 │ Jetstream        │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                  REAL-TIME & EVENTS                         │
├─────────────────────────────────────────────────────────────┤
│ Laravel Reverb │ Laravel Echo │ WebSockets │ Event Logging │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                    DATA LAYER                               │
├─────────────────────────────────────────────────────────────┤
│ SQLite (dev) │ MySQL (prod) │ Database Sessions │ Cache    │
└─────────────────────────────────────────────────────────────┘
```

### Core Features Matrix

| Feature | Implementation | Security Layer |
|---------|---------------|----------------|
| Caller Registration | `CallerController::store()` | Rate limiting + CSRF |
| Hit Counter | `Caller::incrementHits()` | Model boot hook guard |
| Family Registration | `/family` route → form validation | CPR uniqueness check |
| Admin Dashboard | Filament resources @ `/admin` | Authentication + RBAC |
| Real-time Logging | `php artisan pail` + Reverb | Channel authorization |

---

## 🏗️ Architecture & Data Flow

### Request Lifecycle (Registration Flow)

```
┌──────────────┐      ┌──────────────┐      ┌──────────────┐
│   Browser    │      │  Middleware  │      │  Controller  │
│   (CSRF)     │─────▶│  (Throttle)  │─────▶│    Store     │
└──────────────┘      └──────────────┘      └──────────────┘
                                                     │
                                                     ▼
                           ┌──────────────────────────────────┐
                           │    SecureOperations Trait        │
                           │  ✓ checkRateLimit(CPR, IP)       │
                           │  ✓ logSecurityEvent()            │
                           └──────────────────────────────────┘
                                                     │
                                                     ▼
                           ┌──────────────────────────────────┐
                           │      Caller Model Boot           │
                           │  ✓ Validate update permissions   │
                           │  ✓ Enforce security flags        │
                           │  ✓ Throw DceSecurityException    │
                           └──────────────────────────────────┘
                                                     │
                                                     ▼
                           ┌──────────────────────────────────┐
                           │     Database Transaction         │
                           │  ✓ Create/Update caller record   │
                           │  ✓ Store session data            │
                           └──────────────────────────────────┘
                                                     │
                                                     ▼
                           ┌──────────────────────────────────┐
                           │      Thank You Screen            │
                           │  ✓ Display caller stats          │
                           │  ✓ Show hit counter animation    │
                           └──────────────────────────────────┘
```

### Key Routes & Controllers

| Route | Controller Method | Purpose | Auth Required |
|-------|------------------|---------|---------------|
| `/splash` | - | Entry splash screen | No |
| `/` | `CallerController@create` | Individual registration form | No |
| `/family` | `CallerController@createFamily` | Family registration form | No |
| `POST /store` | `CallerController@store` | Process registration | No (CSRF) |
| `/admin` | Filament resources | Admin dashboard | Yes |
| `/api/caller/status` | `CallerStatusController` | Hit counter updates | No (rate limited) |

### Database Schema (Critical Tables)

```sql
-- callers: Core registration data
CREATE TABLE callers (
    id BIGINT PRIMARY KEY,
    cpr VARCHAR(9) UNIQUE NOT NULL,     -- National ID (rate limit key)
    phone VARCHAR(20) NOT NULL,
    name VARCHAR(255) NOT NULL,
    hits INT DEFAULT 0,                  -- Participation counter (protected)
    is_winner BOOLEAN DEFAULT 0,
    status VARCHAR(50) DEFAULT 'active',
    ip_address VARCHAR(45),              -- Rate limit key
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- sessions: CSRF token storage
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT NULL,
    payload TEXT,
    last_activity INT
);

-- cache: Rate limiting storage
CREATE TABLE cache (
    key VARCHAR(255) PRIMARY KEY,
    value TEXT,
    expiration INT
);
```

---

## 🔒 Security Model (Non-Negotiable)

### 🛡️ Three-Layer Defense Strategy

#### Layer 1: Rate Limiting (SecureOperations Trait)
```php
// app/Traits/SecureOperations.php
Per-CPR:  1 registration / 1 minute  → cache key: caller.cpr.{cpr}
Per-IP:   10 registrations / hour     → cache key: caller.ip.{ip}
```

**Why**: Prevents abuse, duplicate submissions, and bot attacks.  
**Implementation**: Cache-based with atomic counters.  
**Failure Mode**: Returns 429 Too Many Requests with clear error message.

#### Layer 2: CSRF Protection
```php
// All forms MUST include:
@csrf  // Generates hidden _token field
```

**Why**: Prevents cross-site request forgery attacks.  
**Implementation**: Database-backed sessions (`SESSION_DRIVER=database`).  
**Common Issue**: 419 errors → Check session config and cookie settings.

#### Layer 3: Model-Level Guard (Caller Boot Hook)
```php
// app/Models/Caller.php (lines 36-67)
protected static function booted(): void
{
    static::updating(function ($caller) {
        // ONLY ALLOW updates if:
        // 1. User is authenticated (admin)
        // 2. Request has flag: increment_hits=true
        // 3. Request has flag: increment_if_exists=true
        // 4. Only updating hits column
        
        if (unauthorized) {
            throw new DceSecurityException('Unauthorized update');
        }
    });
}
```

**Why**: Prevents malicious updates to caller data (especially hit manipulation).  
**Critical**: ANY code touching `callers.hits` must pass an allowed flag.  
**Testing**: Always add feature test asserting unauthorized updates throw exception.

### 🚫 Security Checklist (Required for All Changes)

- [ ] CSRF token present in all forms (`@csrf`)
- [ ] Rate limiting applied to public endpoints
- [ ] Input validation via Form Request classes
- [ ] Caller model updates use authorized flags
- [ ] IP addresses logged for audit trail
- [ ] Arabic messages match English for validation errors
- [ ] Session driver is database-backed
- [ ] Sensitive data never logged or exposed

---

## ⚡ Quick Start Guide

### 🚀 First-Time Setup (5 minutes)

```bash
# 1. Install dependencies
composer install && npm install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Setup database
php artisan migrate --seed

# 4. Build frontend assets
npm run build

# 5. Verify installation
php artisan test
```

### 🎯 Development Mode (One Command)

```bash
# Recommended: Run all services concurrently
composer dev

# This starts:
# ✓ Laravel dev server (http://localhost:8000)
# ✓ Queue listener (background jobs)
# ✓ Vite dev server (http://localhost:5173)
# ✓ Pail log streaming (real-time)
```

### 🔧 Individual Services (Alternative)

```bash
# Terminal 1: Backend server
php artisan serve

# Terminal 2: Frontend dev server (HMR)
npm run dev

# Terminal 3: Background jobs
php artisan queue:listen

# Terminal 4: Real-time logs
php artisan pail
```

---

## 🛠️ Developer Workflows

### 📝 Making Changes (Standard Process)

```bash
# 1. Create feature branch
git checkout -b feature/your-feature

# 2. Make changes
# ... edit files ...

# 3. Run code quality tools
./vendor/bin/pint              # Auto-fix code style
./vendor/bin/rector            # Apply refactoring rules

# 4. Run tests
php artisan test               # Full test suite
./vendor/bin/pest --watch      # Watch mode (recommended)

# 5. Commit and push
git add .
git commit -m "feat: your feature"
git push origin feature/your-feature
```

### 🧹 Code Quality Standards

| Tool | Command | Purpose |
|------|---------|---------|
| **Pint** | `./vendor/bin/pint` | Auto-format code (PSR-12) |
| **Rector** | `./vendor/bin/rector` | Apply refactoring rules |
| **Pest** | `./vendor/bin/pest` | Run test suite |
| **PHPStan** | (optional) | Static analysis |

### 🎨 Frontend Development

```bash
# Development mode (HMR enabled)
npm run dev

# Production build
npm run build

# Watch mode
npm run dev -- --watch
```

### 🗄️ Database Management

```bash
# Fresh start (destroys all data)
php artisan migrate:fresh --seed

# Run migrations only
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Interactive shell
php artisan tinker
```

### 📌 Version Management

**Purpose**: Keep VERSION file and version.json synchronized to prevent deployment errors.

```bash
# Synchronize version.json with VERSION file (default)
php artisan version:sync

# Preview changes without applying them
php artisan version:sync --dry-run

# Also update APP_VERSION in .env files
php artisan version:sync --update-env

# Reverse sync: Update VERSION from version.json
php artisan version:sync --from=version.json
```

**Integration with Deployment**:
- The `publish.sh` script automatically runs `php artisan version:sync --from=VERSION`
- This prevents "version mismatch" errors during deployment
- Runs before maintenance mode is enabled

**Version File Formats**:
- `VERSION`: `3.3.1-32` (base version + build number)
- `version.json`: `{"version": "3.3.1", ...}` (base version only)

### 📦 Filament Admin Customization

```bash
# Republish Filament views (when needed)
php artisan vendor:publish --force --tag=filament-views

# Republish Filament assets
php artisan vendor:publish --force --tag=filament-assets

# ⚠️ WARNING: Never edit vendor files directly!
```

---

## 🧪 Testing Strategy

### Test Pyramid

```
         ┌─────────────┐
         │   Feature   │  ← Integration tests (DB, HTTP)
         ├─────────────┤
         │    Unit     │  ← Isolated logic tests
         └─────────────┘
```

### Running Tests

```bash
# All tests
php artisan test

# Specific test file
php artisan test tests/Feature/RegistrationTest.php

# Watch mode (auto-run on changes)
./vendor/bin/pest --watch

# With coverage
php artisan test --coverage

# Filter by test name
php artisan test --filter=test_caller_registration
```

### Test Organization

```
tests/
├── Feature/
│   ├── Auth/                     # Authentication tests
│   ├── RegistrationTest.php      # Caller registration
│   ├── CsrfProtectionTest.php    # CSRF validation
│   └── RateLimitTest.php         # Rate limiting
└── Unit/
    ├── CallerTest.php            # Caller model logic
    └── SecureOperationsTest.php  # Security trait
```

### Required Test Coverage for Changes

| Change Type | Required Tests |
|-------------|---------------|
| Caller model changes | Unit test + Feature test |
| Hit counter updates | Security flag test + Unauthorized test |
| Form validation | Request validation test |
| API endpoints | Integration test with rate limit |
| Admin features | Authentication test + Authorization test |

---

## 📐 Project Conventions

### 🔐 Hit Counter Protection Pattern

**Rule**: ANY change touching `callers.hits` column requires:

1. **Authorized flag** in request:
   ```php
   // Option 1: Increment flag
   $request->merge(['increment_hits' => true]);
   
   // Option 2: Exists flag
   $request->merge(['increment_if_exists' => true]);
   ```

2. **Feature test** asserting security:
   ```php
   test('unauthorized hit update throws exception', function () {
       $caller = Caller::factory()->create();
       
       expect(fn() => $caller->update(['hits' => 999]))
           ->toThrow(DceSecurityException::class);
   });
   
   test('authorized hit update succeeds', function () {
       request()->merge(['increment_hits' => true]);
       $caller = Caller::factory()->create();
       
       $caller->incrementHits();
       expect($caller->hits)->toBe(1);
   });
   ```

### 🌐 Bilingual Validation Messages

**Rule**: Keep Arabic and English messages in sync.

**Location**: `app/Http/Requests/StoreCallerRequest.php`

```php
public function messages(): array
{
    return [
        'cpr.required' => 'الرقم الشخصي مطلوب',  // Arabic
        'cpr.unique' => 'هذا الرقم مسجل مسبقاً',
        // English versions in same array
    ];
}
```

### 🗃️ SQLite Development Caveats

**Issue**: SQLite can lock with concurrent processes.

**Solutions**:
```bash
# Option 1: Fresh start (preferred)
php artisan migrate:fresh --seed

# Option 2: Close all artisan commands
# Kill serve, queue, pail processes
# Then retry

# Option 3: Use MySQL for dev (recommended for multi-user)
# Update .env: DB_CONNECTION=mysql
```

### 📁 File Naming Conventions

| Type | Pattern | Example |
|------|---------|---------|
| Controllers | `*Controller.php` | `CallerController.php` |
| Models | PascalCase | `Caller.php` |
| Traits | PascalCase | `SecureOperations.php` |
| Requests | `*Request.php` | `StoreCallerRequest.php` |
| Tests | `*Test.php` | `RegistrationTest.php` |
| Migrations | `timestamp_name.php` | `2024_01_01_create_callers_table.php` |

---

## 🚨 Critical Patterns

### ⛔ What NOT To Do

```php
// ❌ NEVER: Direct hit update without authorization
$caller->update(['hits' => $caller->hits + 1]);
// → Throws DceSecurityException

// ❌ NEVER: Edit vendor files directly
// vendor/filament/forms/resources/views/...
// → Use php artisan vendor:publish instead

// ❌ NEVER: Skip CSRF token
<form method="POST">
    <!-- Missing @csrf -->
</form>
// → Results in 419 error

// ❌ NEVER: Commit .env file
git add .env
// → Contains sensitive credentials
```

### ✅ Correct Patterns

```php
// ✅ Authorized hit increment
request()->merge(['increment_hits' => true]);
$caller->incrementHits();

// ✅ Republish vendor assets
php artisan vendor:publish --force --tag=filament-views

// ✅ Always include CSRF
<form method="POST" action="/store">
    @csrf
    <!-- form fields -->
</form>

// ✅ Use .env.example for templates
cp .env.example .env
# Then customize locally
```

---

## 🔍 Troubleshooting Guide

### 🔴 Common Issues & Solutions

#### 419 CSRF Token Mismatch

**Symptoms**: Form submission returns 419 error.

**Diagnosis**:
```bash
# Check session configuration
php artisan config:show session

# Verify session driver
grep SESSION_DRIVER .env
# Should be: SESSION_DRIVER=database

# Check sessions table exists
php artisan migrate:status | grep sessions
```

**Solutions**:
1. Ensure `@csrf` directive in form
2. Verify `SESSION_DRIVER=database` in `.env`
3. Clear config cache: `php artisan config:clear`
4. Check cookie settings: `SESSION_DOMAIN` and `SESSION_SECURE_COOKIE`

#### 429 Rate Limit Exceeded

**Symptoms**: Registration blocked with "Too many requests".

**Diagnosis**:
```bash
# Check cache driver
grep CACHE_STORE .env

# Inspect rate limit keys
php artisan tinker
>>> Cache::get('caller.cpr.123456789');
>>> Cache::get('caller.ip.192.168.1.1');
```

**Solutions**:
1. Clear rate limit cache: `php artisan cache:clear`
2. Adjust limits in `app/Traits/SecureOperations.php`
3. Wait for TTL expiration (5 min for CPR, 1 hour for IP)

#### Database Locked (SQLite)

**Symptoms**: "Database is locked" error during operations.

**Diagnosis**:
```bash
# Check running artisan processes
ps aux | grep artisan
```

**Solutions**:
1. Close all artisan commands (serve, queue, pail)
2. Run: `php artisan migrate:fresh --seed`
3. Consider switching to MySQL for development:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_DATABASE=alsaryatv
   ```

#### Vite Assets Not Loading

**Symptoms**: 404 errors for CSS/JS files in production.

**Diagnosis**:
```bash
# Check build manifest exists
ls -la public/build/manifest.json

# Verify Vite config
cat vite.config.js
```

**Solutions**:
1. Run production build: `npm run build`
2. Verify `public/build/` directory exists
3. Check `vite.config.js` entry points
4. Ensure `APP_ENV=production` in `.env`

### 🔧 Diagnostic Commands

```bash
# System health check
php artisan about

# View all configuration
php artisan config:show

# Check routes
php artisan route:list

# Database status
php artisan migrate:status

# Queue monitoring
php artisan queue:work --once --verbose

# Real-time logs
php artisan pail --filter=security
```

---

## 📚 Essential Files Reference

### 🎯 Start Here (Priority Files)

| File | Purpose | Critical Sections |
|------|---------|------------------|
| `app/Models/Caller.php` | Core model with security boot hook | Lines 36-67 (boot method) |
| `app/Traits/SecureOperations.php` | Rate limiting & security utilities | `checkRateLimit()`, `logSecurityEvent()` |
| `app/Http/Controllers/CallerController.php` | Registration logic | `store()`, validation flow |
| `app/Http/Requests/StoreCallerRequest.php` | Form validation rules | Bilingual messages, CPR validation |
| `routes/web.php` | Public route definitions | `/splash`, `/`, `/family` routes |
| `resources/views/welcome.blade.php` | Registration forms | CSRF tokens, Alpine.js bindings |
| `resources/views/thank-you-screen.blade.php` | Post-registration success | Hit counter display |
| `CLAUDE.md` | Extended documentation | Full system reference |

### 📂 Directory Map

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── CallerController.php          ← Registration logic
│   │   └── CallerStatusController.php    ← Hit counter updates
│   ├── Requests/
│   │   └── StoreCallerRequest.php        ← Validation rules
│   └── Middleware/                       ← Custom middleware
├── Models/
│   └── Caller.php                        ← Core model (security boot)
├── Traits/
│   └── SecureOperations.php              ← Rate limiting utilities
└── Filament/
    └── Resources/                        ← Admin dashboard

resources/
├── js/
│   ├── app.js                            ← Frontend entry point
│   └── Components/                       ← Alpine.js components
├── css/
│   └── app.css                           ← Tailwind styles
└── views/
    ├── splash.blade.php                  ← Entry screen
    ├── welcome.blade.php                 ← Registration forms
    └── thank-you-screen.blade.php        ← Success screen

routes/
├── web.php                               ← Public routes
├── api.php                               ← API endpoints
└── jetstream.php                         ← Auth routes

database/
├── migrations/                           ← Schema definitions
└── seeders/                              ← Test data

tests/
├── Feature/                              ← Integration tests
└── Unit/                                 ← Isolated tests
```

### 🔗 External Documentation

- **Laravel 12.x**: [laravel.com/docs/12.x](https://laravel.com/docs/12.x)
- **Filament v5**: [filamentphp.com/docs](https://filamentphp.com/docs)
- **Livewire 3.x**: [livewire.laravel.com](https://livewire.laravel.com)
- **Alpine.js**: [alpinejs.dev](https://alpinejs.dev)
- **Tailwind CSS**: [tailwindcss.com/docs](https://tailwindcss.com/docs)
- **Pest Testing**: [pestphp.com/docs](https://pestphp.com/docs)

---

## 🎓 Learning Path for New Developers

### Week 1: Foundation
1. Read this document thoroughly
2. Read `CLAUDE.md` for extended context
3. Setup local development environment
4. Run `composer dev` and explore UI at `localhost:8000`
5. Submit test registration and observe database changes

### Week 2: Code Exploration
1. Trace registration flow from route → controller → model
2. Understand `SecureOperations` trait implementation
3. Study `Caller` model boot hook security logic
4. Review existing tests in `tests/Feature/`
5. Practice writing simple feature tests

### Week 3: Hands-On Development
1. Fix a small bug from issue tracker
2. Add validation rule to existing form
3. Write tests for your changes
4. Submit PR following contribution guidelines

---

## 📊 Performance Considerations

### Optimization Checklist

- [ ] **Cache Strategy**: Use Laravel cache for rate limiting (configurable backend)
- [ ] **Database Indexing**: CPR and IP columns indexed for fast lookups
- [ ] **Query Optimization**: Eager load relationships to prevent N+1 queries
- [ ] **Asset Bundling**: Vite builds optimized production bundles
- [ ] **Session Management**: Database sessions for horizontal scaling
- [ ] **Queue Processing**: Background jobs for non-critical tasks

### Monitoring

```bash
# Real-time performance logs
php artisan pail --filter=performance

# Query monitoring
php artisan db:monitor

# Cache statistics
php artisan cache:stats
```

---

## 🚀 Deployment Checklist

```bash
# Pre-deployment
[ ] Run full test suite: php artisan test
[ ] Code quality check: ./vendor/bin/pint --test
[ ] Build production assets: npm run build
[ ] Clear development cache: php artisan cache:clear

# Deployment
[ ] Pull latest code: git pull origin main
[ ] Install dependencies: composer install --no-dev
[ ] Run migrations: php artisan migrate --force
[ ] Cache optimization: php artisan config:cache
[ ] Cache optimization: php artisan route:cache
[ ] Cache optimization: php artisan view:cache
[ ] Restart queue workers: php artisan queue:restart

# Post-deployment
[ ] Verify site loads: curl -I https://your-domain.com
[ ] Check error logs: tail -f storage/logs/laravel.log
[ ] Monitor real-time: php artisan pail
```

---

## 📞 Support & Resources

- **Technical Issues**: See `CLAUDE.md` for extended troubleshooting
- **Security Concerns**: Review security model section above
- **Feature Requests**: Open GitHub issue with detailed requirements
- **Code Review**: All PRs require passing tests + Pint formatting

---

**Last Updated**: 2026-02-05  
**Version**: 2.0  
**Maintainer**: AlSarya TV Development Team