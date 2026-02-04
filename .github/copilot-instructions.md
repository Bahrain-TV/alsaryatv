# Copilot Instructions — Alsaryatv (AlSarya TV)

## Project snapshot
- Laravel 12 TV show caller registration system (individual + family flows) with Ramadan countdown, hit counter, winners, and Filament admin.
- Stack: PHP 8.5, Laravel 12.x, Livewire/Flux, Filament v5.1, Vite + Tailwind, Alpine.js, Pest, SQLite dev / MySQL prod.

## Architecture & data flow (know these before changes)
- Entry routes: splash → registration forms (`/splash`, `/`, `/family`) in `routes/web.php`.
- Store flow: `CallerController::store()` → `SecureOperations::checkRateLimit()` → `Caller` model boot hook enforces update rules → session data for thank-you screen.
- Admin: Filament resources under `app/Filament/`, access via `/admin`.
- Realtime/logging: `php artisan pail` streams `security` and `single` channels; Reverb/Echo configured for WebSockets.

## Security & integrity (non‑negotiable patterns)
- `Caller` model boot hook blocks public updates unless authenticated or request flags allow hit increments (`increment_hits`/`increment_if_exists`). Unauthorized updates throw `DceSecurityException`.
- Rate limits: per‑CPR (1/5 min) + per‑IP (10/hour) via `SecureOperations` cache keys `caller.cpr.{cpr}` and `caller.ip.{ip}`.
- CSRF: all forms use `@csrf`; sessions are database‑backed (`SESSION_DRIVER=database`). 419 errors usually trace to session config.

## Developer workflows
- Setup: `composer install && npm install && cp .env.example .env && php artisan key:generate && php artisan migrate --seed`.
- Preferred dev: `composer dev` (serve + queue + pail + Vite). Individual: `php artisan serve`, `npm run dev`, `php artisan queue:listen`, `php artisan pail`.
- Tests: `php artisan test` or `./vendor/bin/pest --watch` (use `RefreshDatabase`).
- Quality: `./vendor/bin/pint` and `./vendor/bin/rector`.
- Filament assets: use `php artisan vendor:publish --force --tag=filament-views` or `--tag=filament-assets` (do not edit vendor views directly).

## Project‑specific conventions
- Any change touching `callers.hits` must pass an allowed flag or be authenticated; add a feature test that asserts the unauthorized update throws and the authorized path works.
- Validation rules live in `app/Http/Requests/StoreCallerRequest.php` (keep Arabic messages in sync).
- SQLite dev can lock with multiple processes; prefer `php artisan migrate:fresh --seed` for clean test data.

## Files to read first
- `app/Models/Caller.php` (boot hook guard), `app/Traits/SecureOperations.php` (rate limits/logging), `app/Http/Controllers/CallerController.php`, `routes/web.php`, `resources/views/*` (splash + forms + thank‑you), `CLAUDE.md` for extended notes.