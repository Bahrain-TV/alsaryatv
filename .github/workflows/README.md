# GitHub Actions Workflows

This directory contains GitHub Actions workflows for the AlSarya TV registration system.

## Available Workflows

### 1. CI/CD Pipeline (`ci.yml`)

**Trigger**: Push to `main` branch or Pull Request to `main`

**Purpose**: Automated testing and code quality checks

**Jobs**:
- **Laravel Tests** (`laravel-tests`)
  - Runs on Ubuntu latest with PHP 8.5
  - Steps:
    1. Checkout code
    2. Setup PHP with required extensions
    3. Cache Composer dependencies for faster builds
    4. Install Composer dependencies
    5. Setup Laravel environment (.env)
    6. Generate application key
    7. Create SQLite database
    8. Run database migrations
    9. Run code style check (Laravel Pint)
    10. Execute PEST tests in parallel
    11. Send Discord notifications (success/failure)

**Features**:
- ✅ Dependency caching for faster builds
- ✅ Parallel test execution
- ✅ Code style validation
- ✅ Discord notifications
- ✅ Database migrations before tests

**Configuration**:
```yaml
PHP Version: 8.5
Extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite, pdo_sqlite, bcmath, soap, intl, gd, exif, iconv
Database: SQLite (for testing)
```

## Secrets Required

| Secret Name | Description | Used In |
|-------------|-------------|---------|
| `DISCORD_WEBHOOK` | Discord webhook URL for notifications | `ci.yml` |

## Recent Fixes (2026-02-05)

### 1. Fixed PHP Fatal Error in Analytics.php
**Issue**: Type mismatch in `app/Filament/Pages/Analytics.php`
```php
// Before (incorrect)
protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
protected static ?string $navigationGroup = 'إدارة المتصلين';

// After (correct)
protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';
protected static \UnitEnum|string|null $navigationGroup = 'إدارة المتصلين';
```

**Root Cause**: Type declarations didn't match the parent class requirements from Filament.

**Solution**: Updated type declarations to match parent class signature using fully qualified names (`\BackedEnum` and `\UnitEnum`).

### 2. Enhanced CI Workflow
- Added Composer dependency caching
- Added database migrations step
- Added Pint code style checking
- Improved Discord notification handling with conditional checks
- Better step naming for clarity

## Workflow Status

### CI/CD Pipeline Status
![CI/CD Pipeline](https://github.com/Bahrain-TV/alsaryatv/workflows/AI%20CI/CD%20Pipeline/badge.svg)

## Local Testing

To test workflow changes locally before pushing:

```bash
# Run code style check
./vendor/bin/pint --test

# Run tests
php artisan test

# Run tests in parallel
php artisan test --parallel

# Run specific test file
php artisan test tests/Feature/ExampleTest.php
```

## Debugging Failed Workflows

### View Recent Runs
```bash
# List recent workflow runs
gh run list --workflow=ci.yml

# View logs for a specific run
gh run view <run-id> --log

# Re-run a failed workflow
gh run rerun <run-id>
```

### Common Issues

#### 1. PHP Fatal Error During Composer Install
**Symptom**: Composer install fails with PHP errors
**Solution**: Check for type mismatches in PHP classes (especially Filament classes)

#### 2. Test Failures
**Symptom**: Tests fail in CI but pass locally
**Solution**: 
- Ensure database migrations are run before tests
- Check environment configuration differences
- Verify all dependencies are installed

#### 3. Cache Issues
**Symptom**: Slow builds despite caching
**Solution**: Clear cache in GitHub Actions settings or update cache key

## Best Practices

1. **Always run tests locally** before pushing
   ```bash
   php artisan test
   ./vendor/bin/pint --test
   ```

2. **Keep workflows simple** - One workflow per purpose
3. **Use caching** for dependencies to speed up builds
4. **Add meaningful step names** for easier debugging
5. **Use conditional notifications** to prevent errors when secrets are missing

## Workflow Development

### Testing Workflow Changes

1. Create a feature branch
2. Modify workflow file
3. Push to trigger workflow
4. Monitor workflow run in GitHub Actions tab
5. Iterate as needed

### Adding New Workflows

1. Create new `.yml` file in `.github/workflows/`
2. Define triggers (`on:` section)
3. Define jobs and steps
4. Test thoroughly
5. Document in this README

## Resources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [Filament Documentation](https://filamentphp.com/docs)
- [PEST Testing Framework](https://pestphp.com/)

## Maintenance

**Last Updated**: 2026-02-05  
**Maintained By**: Development Team  
**Status**: ✅ Active and Functional
