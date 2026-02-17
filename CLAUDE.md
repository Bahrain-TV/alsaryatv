# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**AlSarya TV Show Registration System** - A Laravel-based caller registration platform for a TV show (برنامج السارية المباشر من تلفزيون البحرين). The system manages individual and family caller registrations with hit counters, winner selection, and comprehensive security measures.

**Key Features:**
- Caller registration (individual and family modes)
- Hit counter system for show participation tracking
- Winner selection and management
- Admin dashboard with Filament
- Security: CSRF protection, rate limiting, IP validation
- Real-time features via Laravel Echo/Pusher
- **Basmala (بسم الله الرحمن الرحيم) displayed at the top of all pages** ⭐

---

## ⭐ IMPORTANT: Basmala Placement

**The Basmala (بسم الله الرحمن الرحيم - "In the name of Allah, the Most Gracious, the Most Merciful") MUST ALWAYS appear at the top of every page/view.**

This is a fundamental Islamic principle in this project. When implementing any view:
1. The basmala MUST be positioned fixed/absolutely at the top
2. It should have the highest z-index to never be hidden
3. Use consistent styling across all pages
4. Mobile-responsive sizing
5. Never allow other content to appear above it

**Implementation:**
- Add `<div class="basmala" id="basmala">بسم الله الرحمن الرحيم</div>` at top of layout
- Include CSS for positioning: `position: fixed; top: 20px; z-index: 200;`
- Apply theme colors: Light cream (#F5DEB3) text with maroon shadows
- Test on all screen sizes to ensure visibility

---

## Tech Stack

| Layer | Technologies |
|-------|--------------|
| **Backend** | Laravel 12.x, PHP 8.5, SQLite (dev)/MySQL (prod) |
| **Frontend** | Vite, Alpine.js, Tailwind CSS, Livewire/Flux |
| **Real-time** | Laravel Reverb, Laravel Echo, Pusher |
| **Admin** | Filament v5.1 (Laravel admin panel) |
| **Testing** | Pest v3.7, PHPUnit |
| **Build** | npm/pnpm, Vite build system |
| **Database** | Migrations + Seeding |

---

## Project Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── CallerController.php         # Main registration logic
│   │   │   └── CallerStatusController.php   # Hit counter + status updates
│   │   ├── Requests/                        # Form validation
│   │   └── Middleware/
│   ├── Models/
│   │   ├── Caller.php                       # Caller model with security boot
│   │   └── User.php
│   ├── Traits/
│   │   └── SecureOperations.php             # Rate limiting & security utilities
│   ├── Filament/                            # Admin UI resources
│   ├── Services/                            # Business logic
│   └── Providers/
├── routes/
│   ├── web.php                              # Web routes (registration forms)
│   ├── api.php                              # API endpoints
│   └── jetstream.php
├── resources/
│   ├── js/                                  # Frontend components & scripts
│   ├── css/                                 # Tailwind + Filament styles
│   ├── views/                               # Blade templates
│   │   ├── splash.blade.php                 # Entry splash screen
│   │   ├── welcome.blade.php                # Registration forms
│   │   └── thank-you-screen.blade.php       # Post-registration
│   └── lottie/                              # Animation assets
├── database/
│   ├── migrations/                          # Schema definitions
│   └── seeders/
├── tests/                                   # Pest/PHPUnit tests
├── config/                                  # Laravel configuration
├── public/                                  # Static assets & app entry
└── storage/                                 # Logs, cache, sessions
```

---

## Architecture Highlights

### Registration Flow
1. **Entry Point**: Splash screen (`/splash`) → Registration forms (`/` for individual, `/family` for family)
2. **Form Submission** → CSRF token verification → Rate limiting (per-CPR, per-IP)
3. **Lookup/Create**: Check existing caller by CPR → Create new or update hits
4. **Success**: Store session data → Display thank-you screen with stats
5. **Security**: All updates protected by `Caller` model's boot method

### Caller Model Security
- **Boot Hook** in `Caller.php` (line 36-67): Validates all updates
  - Allows updates if: authenticated user, special flags (`increment_hits`), or hits-only updates
  - Throws `DceSecurityException` for unauthorized updates
- **Hit Increment**: Protected via flag checks in `StoreCallerRequest`

### Rate Limiting (in `SecureOperations` trait)
- **Per-CPR**: 1 registration every 5 minutes (prevents duplicate entries)
- **Per-IP**: 10 registrations per hour (prevents batch abuse)
- **Cache-based**: Uses Laravel cache (configurable in `.env`)

### CSRF Protection
- All forms include `@csrf` directive
- Middleware validates `_token` in POST requests
- Session-based token stored in database (configured in `config/session.php`)

---

## Common Development Commands

### Setup
```bash
# Install dependencies
composer install
npm install  # or: pnpm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate
php artisan db:seed

# Build frontend assets
npm run build  # or: pnpm build
```

### Development
```bash
# Run all services concurrently (artisan dev command)
composer dev
# This starts: Laravel server, queue listener, logs, and Vite dev server

# OR run individually:
php artisan serve              # Laravel dev server (port 8000)
npm run dev                    # Vite dev server (port 5173)
php artisan queue:listen       # Background jobs
php artisan pail               # Real-time logs
```

### Testing
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/RegistrationTest.php

# Run with Pest
./vendor/bin/pest

# Run specific Pest test
./vendor/bin/pest tests/Feature/RegistrationTest.php

# Watch mode (re-run on file changes)
./vendor/bin/pest --watch
```

### Code Quality
```bash
# PHP code style (Pint)
./vendor/bin/pint

# Static analysis with Rector
./vendor/bin/rector

# Code style check (dry-run)
./vendor/bin/pint --test
```

### Database
```bash
# Run migrations
php artisan migrate

# Rollback
php artisan migrate:rollback

# Fresh database (migrate:fresh = rollback + migrate)
php artisan migrate:fresh --seed

# Tinker (interactive shell)
php artisan tinker
```

### Version Management
```bash
# Synchronize version.json with VERSION file
php artisan version:sync

# Sync with dry-run (shows changes without applying)
php artisan version:sync --dry-run

# Also update APP_VERSION in .env files
php artisan version:sync --update-env

# Sync from version.json to VERSION (reverse direction)
php artisan version:sync --from=version.json
```

### Deployment
```bash
# See deploy.sh for full deployment workflow
./deploy.sh

# Manual steps:
php artisan config:cache
php artisan view:cache
npm run build
php artisan migrate --force

# Publish to production (includes version sync)
./publish.sh
```

---

## Key Files & Responsibilities

| File | Purpose |
|------|---------|
| `app/Http/Controllers/CallerController.php` | Registration logic, validation, rate limiting |
| `app/Models/Caller.php` | Caller entity, security boot hook, hit methods |
| `app/Traits/SecureOperations.php` | Rate limiting, IP validation, logging |
| `routes/web.php` | Public routes (splash, registration forms, welcome) |
| `routes/api.php` | API endpoints (if used) |
| `resources/views/welcome.blade.php` | Registration form templates |
| `resources/js/app.js` | Frontend entry point, Alpine.js setup |
| `resources/css/app.css` | Tailwind styles, form styling |
| `database/migrations/*` | Schema: users, callers, throttle (rate limiting) |
| `config/session.php` | Session driver (database for CSRF tokens) |
| `.env` | Environment variables (DB, API keys, debug mode) |

---

## Form Validation & Security

### Registration Request Validation
Files: `app/Http/Requests/StoreCallerRequest.php`

Validates:
- Name, Phone, CPR (national ID format)
- `is_new_caller` flag
- `increment_if_exists` flag
- Form action type

### Middleware Stack
- `web`: Session, CSRF, cookies
- `throttle:60,1`: Global rate limit (60 requests/min)
- `auth`: Admin-only routes

---

## Environment Variables

Key `.env` settings:
```
APP_ENV=local|production
APP_DEBUG=true|false
DB_CONNECTION=sqlite|mysql
DB_DATABASE=database.sqlite|app_db
BROADCAST_CONNECTION=log|pusher
QUEUE_CONNECTION=database|redis
CACHE_STORE=database|redis|array
SESSION_DRIVER=database|array
```

See `.env.example` for all options.

---

## Testing Coverage

Existing tests in `tests/`:
- **Auth**: Registration, authentication, email verification, password reset
- **CSRF**: Token validation, form submissions
- **Features**: Dashboard, profile updates, account deletion
- **Settings**: Password & profile updates, permissions

Run with:
```bash
php artisan test --filter=CsrfProtectionTest
php artisan test tests/Feature/Auth/RegistrationTest.php
```

---

## Database Schema

### Key Tables
- `callers`: Phone, CPR, name, is_winner, hits, status, ip_address
- `users`: Jetstream users (admin)
- `sessions`: CSRF tokens (database session driver)
- `cache`: Rate limiting data
- `throttle`: Rate limit records

Migrations located in `database/migrations/`.

---

## Frontend Architecture

### Entry Points
- `resources/js/app.js`: Main app initialization
- `resources/css/app.css`: Main stylesheet (Tailwind)
- `resources/js/Components/`: Reusable Vue/Alpine components
- `resources/js/Pages/`: Page-level components

### Alpine.js Usage
- Form submissions with CSRF token
- Hit counter animations (GSAP)
- Dynamic form state management
- Client-side validation feedback

### Vite Integration
- HMR (Hot Module Replacement) in dev
- CSS/JS bundling in production
- Entry points defined in `vite.config.js`

---

## Admin Panel (Filament)

Located in `app/Filament/`. Filament v5.1 provides:
- User management
- Caller management
- Winner selection
- Statistics/reporting
- Role-based access control

Access at `/admin` after authentication.

---

## Security Considerations

### CSRF Protection
- All POST/PUT/DELETE forms must include `@csrf`
- Token validated by middleware before reaching controller
- Session stored in database (not cookies alone)

### Rate Limiting
- Implemented in `SecureOperations` trait
- Cache-based counters with expiration
- Per-CPR: Prevents duplicate registrations
- Per-IP: Prevents batch abuse

### Model Protection
- `Caller` model boot method validates all updates
- Unauthorized updates throw exception
- Only specific flags/authenticated users bypass restriction

### Input Validation
- Form requests validate all user input
- Sanitization via Laravel's validation rules
- CPR format validation

---

## Common Issues & Solutions

### CSRF Token Mismatch (419 Error)
- Ensure form includes `@csrf` directive
- Check session configuration (`config/session.php`)
- Verify cookie settings in `.env`

### Rate Limit Errors
- Check cache backend in `.env` (CACHE_STORE)
- Verify database has `cache` table for cache driver
- See `app/Traits/SecureOperations.php` for limit values

### Database Locked (SQLite Dev)
- Close all artisan commands (serve, queue, etc.)
- Run `php artisan migrate:fresh --seed`
- Switch to MySQL for production

### Vite Assets Not Loaded
- Run `npm run build` for production
- Ensure `vite.config.js` entry points match reality
- Check `public/build/manifest.json` exists after build

---

## Useful Documentation Links

- **Laravel**: https://laravel.com/docs/12.x
- **Filament**: https://filamentphp.com/docs
- **Livewire**: https://livewire.laravel.com
- **Tailwind**: https://tailwindcss.com/docs
- **Alpine.js**: https://alpinejs.dev
- **Pest**: https://pestphp.com/docs

---

## Notes for Future Development

1. **Persistence**: Session data stored in database (`SESSION_DRIVER=database`)
2. **Real-time**: Laravel Reverb configured for WebSocket support
3. **Queue**: Background jobs use database queue (`QUEUE_CONNECTION=database`)
4. **Caching**: Rate limiting uses cache layer (configurable backend)
5. **Logging**: All security events logged (see `SecureOperations` trait)
