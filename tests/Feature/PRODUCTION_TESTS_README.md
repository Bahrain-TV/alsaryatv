# Production URL Tests

This directory contains tests for verifying that all production URLs on https://alsarya.tv are accessible and working properly.

## Test Files

### 1. ProductionUrlTest.php
PHPUnit/Laravel test class that uses Laravel's HTTP client to test production URLs.
- Uses Laravel's `$this->get()` method
- Requires Laravel test environment
- Good for integration with existing test suite

### 2. ProductionUrlCurlTest.php
PHPUnit test class that uses cURL directly to test production URLs.
- Uses raw cURL requests
- More flexible for external site testing
- Tests HTTPS, SSL certificates, security headers
- Can be run independently

### 3. test-production-urls.sh
Standalone bash script for testing production URLs.
- Uses curl command-line tool
- No PHP/Laravel dependencies
- Easy to run in CI/CD pipelines
- Provides colored output and summary

## Running the Tests

### Prerequisites
- Network access to https://alsarya.tv
- For PHP tests: Laravel application setup with dependencies installed
- For bash script: curl command available

### Run PHPUnit Tests
```bash
# Run the Laravel HTTP client tests
php artisan test --filter=ProductionUrlTest

# Run the cURL-based tests
php artisan test --filter=ProductionUrlCurlTest
```

### Run Bash Script
```bash
# Make script executable (if not already)
chmod +x test-production-urls.sh

# Run the tests
./test-production-urls.sh
```

### Run in CI/CD Pipeline
Add to your GitHub Actions workflow:

```yaml
- name: Test Production URLs
  run: ./test-production-urls.sh
```

Or with PHPUnit:

```yaml
- name: Test Production URLs
  run: php artisan test --filter=ProductionUrl
```

## What Gets Tested

### Public Pages (Should return 200)
- `/` - Home page
- `/splash` - Splash screen
- `/welcome` - Welcome page
- `/family` - Family registration page
- `/privacy` - Privacy policy
- `/register` - Registration form
- `/csrf-test` - CSRF test page
- `/callers/create` - Caller creation page

### API Endpoints
- `/api/version` - Version information (should return 200 with JSON)
- `/api/version/changelog` - Changelog (may return 200 or 404)

### Protected Routes (Should redirect or deny: 302, 401, or 403)
- `/dashboard` - Dashboard (requires authentication)
- `/winners` - Winners page (requires authentication)
- `/families` - Families page (requires authentication)
- `/admin` - Admin panel (requires authentication)

### Security Tests
- HTTPS enforcement
- SSL certificate validation
- Security headers presence

## Network Requirements

**Important:** These tests require network access to https://alsarya.tv

If running in a restricted environment (e.g., CI without external network access), these tests may fail with connection errors. In such cases:

1. Run tests from an environment with production access
2. Configure network access in your CI environment
3. Use these tests as manual verification tools
4. Run on a schedule from a server with production access

## Troubleshooting

### "Could not resolve host" Error
- The environment cannot reach https://alsarya.tv
- Check DNS resolution: `nslookup alsarya.tv`
- Verify network connectivity: `ping alsarya.tv`
- Check firewall rules

### "Connection timeout" Error
- Network is very slow or blocked
- Increase timeout in tests (default: 30 seconds)
- Check if production site is down

### SSL Certificate Errors
- Certificate may be expired or invalid
- Verify certificate: `openssl s_client -connect alsarya.tv:443`
- Check with your hosting provider

## Test Configuration

### Changing Production URL
To test a different URL (e.g., staging), update the `$productionUrl` variable:

**In PHP tests:**
```php
protected string $productionUrl = 'https://staging.alsarya.tv';
```

**In bash script:**
```bash
PRODUCTION_URL="https://staging.alsarya.tv"
```

### Adding New URLs to Test
1. Add the route to `routes/web.php` or `routes/api.php`
2. Add a test method in ProductionUrlTest.php
3. Add a test_url call in test-production-urls.sh

## Best Practices

1. **Run regularly:** Set up automated tests to run on a schedule (e.g., daily)
2. **Monitor failures:** Set up notifications for test failures
3. **Test after deployments:** Always verify URLs after deploying
4. **Document changes:** Update tests when adding/removing routes
5. **Security first:** Always test that protected routes require authentication

## Integration with CI/CD

### GitHub Actions Example
```yaml
name: Production URL Check

on:
  schedule:
    - cron: '0 */6 * * *'  # Every 6 hours
  workflow_dispatch:  # Manual trigger

jobs:
  test-urls:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      
      - name: Test Production URLs
        run: ./test-production-urls.sh
      
      - name: Notify on failure
        if: failure()
        run: |
          # Send notification (Discord, Slack, email, etc.)
          echo "Production URL tests failed!"
```

## License
This test suite is part of the AlSarya TV project and follows the same license.
