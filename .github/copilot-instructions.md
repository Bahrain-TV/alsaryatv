# GitHub Copilot Instructions for Alsaryatv (AlSarya TV)

## Quick summary
- Purpose: Laravel app for TV show caller registration (individual & family modes). Key concerns: CSRF, rate-limiting, caller-hit integrity, and Filament admin UI.
- Primary languages/tools: PHP (Laravel 12.x), Vite, Tailwind, Alpine.js, Livewire, Pest, Filament.

## Big picture & important boundaries
- Public registration flow: splash → registration form(s) → store/update `callers` → thank-you screen. See `routes/web.php` and `resources/views/welcome.blade.php`.
- Security-critical domain logic lives in the `Caller` model boot (protects updates) and `SecureOperations` trait (rate-limits and IP checks). Always review these when touching registration or hit logic:
  - `app/Models/Caller.php` (boot hook, throws `DceSecurityException` for unauthorized updates)
  - `app/Traits/SecureOperations.php` (per-CPR / per-IP limits)
- Admin UI & operations: Filament resources in `app/Filament/` manage callers, winners, and reports (access via `/admin`).
- Real-time and logging: Laravel Reverb + Pusher + `php artisan pail` for real-time logs. Tests and local dev rely on `composer dev` which starts multiple services.

## Commands you'll need (use exact commands in CI / scripts)
- Setup: `composer install`, `cp .env.example .env`, `php artisan key:generate`, `php artisan migrate --seed`.
- Dev: `composer dev` (preferred: starts server, queue, logs, vite), or run components individually: `php artisan serve`, `npm run dev`, `php artisan queue:listen`, `php artisan pail`.
- Frontend build: `npm run build` (or `pnpm build`).
- Tests: `php artisan test` or `./vendor/bin/pest`. Watch mode: `./vendor/bin/pest --watch`.
- Lint/format: `./vendor/bin/pint` and static upgrades with `./vendor/bin/rector`.
- Deployment: follow `./deploy.sh` and `./publish.sh` (see scripts for exact steps: build assets, migrate, cache/config).

## Project-specific conventions & gotchas
- Caller updates are guarded. Any code that updates `callers.hits` must either:
  - pass the controlled `increment_hits` flag in request/logic, or
  - be performed by an authenticated user or explicitly named updater. Changing this requires updating `Caller` boot checks and adding tests.
- Rate limits: per-CPR = 1 registration/5 minutes; per-IP = 10 registrations/hour. These live in `SecureOperations` and use the cache backend. If you change limits, update tests and seed data.
- CSRF: forms use `@csrf` and sessions are database-backed (`SESSION_DRIVER=database`), so tests need proper session setup. Fixing 419 errors often means checking `config/session.php` and cookie settings.
- Database for testing: SQLite locking can occur if multiple processes operate on DB. For CI/test changes requiring DB schema, use `php artisan migrate:fresh --seed` in the appropriate environment.

## Tests & PR guidance
- Add feature tests for any change to registration, security, or rate-limiting. Place tests under `tests/Feature/`.
- For model/security changes, include at least one test showing an unauthorized update is blocked (assert exception) and one that demonstrates the authorized path.
- If a migration is added, include a rollback-qualified migration and add a schema test if appropriate.

## Files to inspect for context (start here)
- `app/Models/Caller.php` — model boot, hit-update guard
- `app/Traits/SecureOperations.php` — rate limiting and IP checks
- `app/Http/Controllers/CallerController.php` — registration flow and validation
- `app/Http/Requests/StoreCallerRequest.php` — request flags and validation rules
- `resources/views/` and `resources/js/` — front-end entry points (`welcome.blade.php`, `app.js`)
- `app/Filament/` — admin resources and role access
- `CLAUDE.md` — extended repository notes and commands (use as detailed reference)

## When unsure, follow these safe behaviors
- Run tests locally (`php artisan test`) and add failing tests reproducing the issue before fixing.
- Do not bypass `Caller` boot checks. If a change must bypass security, add an explicit flag and test that documents the reason.
- Update `CLAUDE.md` if you discover new long-running processes or developer commands (e.g., if `composer dev` changes behavior).

---
If you'd like, I can open a PR that adds this file and include a tiny checklist in the README linking to it. Any areas you want emphasized or added examples for? ✅