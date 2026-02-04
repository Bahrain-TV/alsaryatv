# GitHub Copilot Instructions for Alsaryatv (AlSarya TV)

## Quick summary
- **Purpose**: Laravel 12 TV show caller registration system (individual & family modes). Ramadan countdown, hit counter, winner selection, and admin dashboard.
- **Stack**: PHP 8.5 | Laravel 12.x | Livewire/Flux | Filament | Vite + Tailwind | Alpine.js | Pest | SQLite (dev) / MySQL (prod).
- **Key concerns**: CSRF protection, rate-limiting (per-CPR/IP), caller-hit integrity enforcement, and Filament admin security.

## Architecture & data flow

### Registration Pipeline
1. **Entry**: Splash screen (`/splash`) → Individual (`/`) or Family (`/family`) form
2. **Rate-limit check**: Per-CPR (1 reg/5 min) + per-IP (10 reg/hour) via `SecureOperations::checkRateLimit()`
3. **Caller lookup/create**: Find by CPR or create new record; increment `hits` if returning
4. **Submit & persist**: Store via `CallerController::store()` → `Caller` model (guarded boot hook fires)
5. **Response**: Session data → Thank-you screen with stats display

**Key files**: [`routes/web.php`](routes/web.php#L1) (entry routes), [`app/Http/Controllers/CallerController.php`](app/Http/Controllers/CallerController.php) (store logic).

### Security-Critical Boundaries
All `callers` table updates are protected by a **boot hook** that enforces authorization:
- **Allowed**: Authenticated (admin), `increment_hits` flag in request, or hits-only updates
- **Blocked**: Any other update from public endpoints (throws `DceSecurityException`)

**Never bypass this check** without an explicit flag and test. Files:
- [`app/Models/Caller.php`](app/Models/Caller.php#L36-L67) — boot hook, update guards
- [`app/Traits/SecureOperations.php`](app/Traits/SecureOperations.php) — rate limits + security logging

### Admin & Real-time
- **Filament**: Resources in [`app/Filament/`](app/Filament/) (callers, winners, reports); access via `/admin` (auth required).
- **Real-time logging**: `php artisan pail` streams `security` and `single` channels; Laravel Reverb + Echo for potential live updates.

## Essential commands

| Task | Command |
|------|---------|
| **Setup** | `composer install && npm install && cp .env.example .env && php artisan key:generate && php artisan migrate --seed` |
| **Dev (all services)** | `composer dev` ← **preferred** (runs: Laravel serve + queue + pail logs + Vite) |
| **Dev (individual)** | `php artisan serve` \| `npm run dev` \| `php artisan queue:listen` \| `php artisan pail` |
| **Build assets** | `npm run build` (or `pnpm build` if monorepo) |
| **Tests** | `php artisan test` or `./vendor/bin/pest --watch` |
| **Format/lint** | `./vendor/bin/pint` (code), `./vendor/bin/rector` (refactors) |
| **Deploy** | `./deploy.sh` (prod) or `./publish.sh` (upload .env + scripts); see scripts for backup/restore |

## Project conventions & gotchas

### Caller Hit Integrity
- **Any update to `callers.hits`** must pass `increment_hits` or `increment_if_exists` flag in request, or be authenticated.
- **Test pattern**: Assert `DceSecurityException` on unauthorized update, then test authorized path separately.
- **Example**: `StoreCallerRequest` checks `$request->boolean('increment_hits')` before calling controller.

### Rate Limiting Details
- **Per-CPR**: 1 registration every 5 minutes (cache key: `caller.cpr.{cpr}`)
- **Per-IP**: 10 registrations per hour (cache key: `caller.ip.{ip}`)
- **Implementation**: `SecureOperations::checkRateLimit()` uses Laravel cache; see [`CallerController::store()`](app/Http/Controllers/CallerController.php#L45-L80) for integration.
- **Changing limits**: Update cache TTL in controller → add test case → update seed data if needed.

### CSRF & Session Handling
- Forms use `@csrf` directive; middleware validates `_token` in POST.
- Sessions are **database-backed** (`SESSION_DRIVER=database` in `.env`), so tests must have proper session setup.
- **419 errors**: Check `config/session.php` (COOKIE_SECURE, COOKIE_HTTP_ONLY) and `.env` (CSRF_TRUSTED_HOSTS).

### Database & Testing
- **Dev DB**: SQLite (file: `database/database.sqlite`). Multiple processes → locking; use `php artisan migrate:fresh --seed` in test env.
- **Test isolation**: Use `RefreshDatabase` trait in Pest tests; ensure migrations rollback cleanly.
- **Seeding**: Populate test callers with known CPRs/IPs in `database/seeders/` to support rate-limit tests.

## Code patterns & requirements

### Feature Testing (required for registration/security changes)
```php
// tests/Feature/RegistrationTest.php
test('unauthorized caller update throws exception', function () {
    $caller = Caller::create(['cpr' => '12345678', 'name' => 'Test']);
    expect(fn() => $caller->update(['hits' => 999]))->toThrow(DceSecurityException::class);
});

test('increment_hits flag allows authorized update', function () {
    $caller = Caller::create(['cpr' => '12345678', 'name' => 'Test']);
    $this->post('/caller/store', ['cpr' => '12345678', 'increment_hits' => true]);
    expect($caller->fresh()->hits)->toBeGreaterThan(0);
});
```

### Model Updates & Validation
- Validation rules live in [`app/Http/Requests/StoreCallerRequest.php`](app/Http/Requests/StoreCallerRequest.php); keep Arabic error messages in sync.
- Use form request `authorize()` method only to check policy; security is enforced in controller + model boot.

## Files to read first
1. [`app/Models/Caller.php`](app/Models/Caller.php) — understand boot hook + update guards
2. [`app/Traits/SecureOperations.php`](app/Traits/SecureOperations.php) — rate limit logic + security events
3. [`app/Http/Controllers/CallerController.php`](app/Http/Controllers/CallerController.php#L45) — `store()` method flow
4. [`routes/web.php`](routes/web.php#L1-L50) — entry routes + countdown logic
5. [`CLAUDE.md`](CLAUDE.md) — extended notes on tech stack, project structure, common commands

## Safe fallback behaviors
1. **Unsure about a change**: Write a failing test first, then fix code to pass it.
2. **Modifying security**: Add explicit flag + test showing unauthorized path is blocked AND authorized path works.
3. **Rate limit changes**: Update `SecureOperations`, test with multiple CPRs/IPs, adjust seed data.
4. **New long-running process**: Document in `composer dev` script and `CLAUDE.md` (build commands, env vars, etc.).
5. **Database schema**: Use `php artisan make:migration` + rollback test; avoid manual SQL.