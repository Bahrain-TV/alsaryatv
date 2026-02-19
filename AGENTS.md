# AGENTS.md - AI Agent Guidelines

> Guidelines for agentic coding agents operating in the AlSarya TV Registration System codebase.

## Build/Lint/Test Commands

### Development
```bash
composer dev                  # Start all services (server, queue, logs, vite)
php artisan serve             # Laravel dev server (port 8000)
npm run dev                   # Vite dev server with HMR
```

### Testing
```bash
php artisan test                                # All tests
php artisan test tests/Feature/CallerRegistrationTest.php   # Single test file
./vendor/bin/pest                               # Pest runner
./vendor/bin/pest --watch                       # Watch mode
php artisan test --filter=test_individual_registration  # Filter by name
```

### Code Quality
```bash
./vendor/bin/pint             # Auto-fix PHP code style (PSR-12)
./vendor/bin/pint --test      # Check without modifying
./vendor/bin/rector           # Apply refactoring rules
```

### Database
```bash
php artisan migrate           # Run migrations
php artisan migrate:fresh --seed   # Reset database with seeders
php artisan tinker            # Interactive REPL
```

### Frontend
```bash
npm run build                 # Production build
npm run dev                   # Development with HMR
```

## Code Style Guidelines

### PHP Conventions

**Imports**: Group by type, alphabetically sorted
```php
use App\Http\Requests\StoreCallerRequest;
use App\Models\Caller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
```

**Controllers**: Single responsibility, dependency injection via constructor
```php
class CallerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['store', 'create']);
    }
    
    public function store(StoreCallerRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        // ...
    }
}
```

**Models**: Use `$fillable` arrays, explicit casts, and scopes
```php
protected $fillable = ['name', 'phone', 'cpr', 'hits'];
protected $casts = ['is_winner' => 'boolean', 'last_hit' => 'datetime'];

public function scopeWinners(Builder $query): Builder
{
    return $query->where('is_winner', true);
}
```

**Validation**: Use Form Request classes with bilingual messages
```php
public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'cpr' => 'required|string|max:255',
    ];
}

public function messages(): array
{
    return [
        'name.required' => 'الاسم مطلوب',  // Arabic
    ];
}
```

### Naming Conventions

| Type | Convention | Example |
|------|------------|---------|
| Classes | PascalCase | `CallerController` |
| Methods | camelCase | `incrementHits()` |
| Variables | camelCase | `$validated` |
| Constants | UPPER_SNAKE | `MAX_FAMILY_MEMBERS` |
| Database columns | snake_case | `is_winner`, `ip_address` |
| Routes | kebab-case | `/family-registration` |
| Blade files | kebab-case | `thank-you-screen.blade.php` |

### Error Handling

- Use Laravel's built-in exceptions
- Log security events to dedicated channel: `Log::channel('security')->info(...)`
- Return user-friendly JSON responses for API errors
- Validation errors use session flash for web, JSON for API

### Security Requirements

**CSRF**: All forms MUST include `@csrf` directive
```blade
<form method="POST" action="/callers">
    @csrf
    ...
</form>
```

**Rate Limiting**: Applied to public endpoints
```php
RateLimiter::tooManyAttempts('caller-registration:'.$cpr, 1)
RateLimiter::hit('caller-registration:'.$cpr, 60); // 1 min TTL
```

**Model Protection**: Caller model has boot hook that restricts updates
```php
// Only hits column can be updated publicly
// Admin updates require authenticated user with is_admin flag
```

### Frontend Conventions

**Alpine.js**: Use for interactivity and state management
```html
<div x-data="{ showForm: false }">
    <button @click="showForm = true">Register</button>
</div>
```

**Tailwind CSS**: Utility-first, avoid custom CSS when possible

**Vite**: Entry points in `resources/js/app.js` and `resources/css/app.css`

## Testing Patterns

### Feature Tests (Pest/PHPUnit)
```php
class CallerRegistrationTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_individual_registration_form_can_be_submitted(): void
    {
        $response = $this->post('/callers', [
            'name' => 'أحمد محمد',
            'cpr' => '12345678901',
            'phone_number' => '+97366123456',
        ]);
        
        $response->assertRedirect(route('callers.success'));
        $this->assertTrue(Caller::where('cpr', '12345678901')->exists());
    }
}
```

### Test Organization
- `tests/Feature/` - Integration tests with DB/HTTP
- `tests/Unit/` - Isolated logic tests
- `tests/Browser/` - Dusk browser tests

## Critical Patterns

### Hit Counter Updates
Always use the `incrementHits()` method:
```php
$caller->incrementHits();  // Correct - uses atomic increment
$caller->hits++; $caller->save();  // Wrong - bypasses protection
```

### UpdateOrCreate Pattern
```php
$caller = Caller::updateOrCreate(
    ['cpr' => $cpr],
    ['name' => $name, 'phone' => $phone]
);
```

### Session Flash Messages
```php
return redirect()->route('dashboard')
    ->with('success', 'Caller updated successfully.');
```

## Files to Reference

| Purpose | File |
|---------|------|
| Registration logic | `app/Http/Controllers/CallerController.php` |
| Model with security | `app/Models/Caller.php` |
| Validation rules | `app/Http/Requests/StoreCallerRequest.php` |
| Routes | `routes/web.php` |
| Frontend entry | `resources/js/app.js` |
| Styles | `resources/css/app.css` |
| Extended docs | `CLAUDE.md`, `.github/copilot-instructions.md` |
