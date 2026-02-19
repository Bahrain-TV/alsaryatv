# Test Files Audit & Cleanup Guide

## Summary
Based on AlSarya TV's core business (caller registration, hit tracking, winner management), the following test files have been identified for deletion.

---

## ✅ KEEP - Core Business Tests (8 files)

### Individual Tests
- `tests/Feature/CallerRegistrationTest.php` - Individual & family registration flows
- `tests/Feature/CallerRegistrationSecurityTest.php` - Security validation for registrations
- `tests/Feature/CallerModelTest.php` - Caller model behavior and methods
- `tests/Feature/FormValidationTest.php` - Registration form validation rules
- `tests/Feature/MainFunctionalityTest.php` - End-to-end registration functionality
- `tests/Feature/FilamentDashboardFeatureTest.php` - Admin dashboard features

### Admin Tests  
- `tests/Feature/Admin/AdminPanelTest.php` - Admin panel functionality
- `tests/Feature/Admin/DashboardWidgetsTest.php` - Dashboard widget display

---

## 🗑️ DELETE - Non-Core Tests

### Infrastructure & Deployment Tests (Delete 4 files + documentation)
- `tests/Feature/VersionManagerTest.php` - Version management (build-time infrastructure)
- `tests/Feature/VersionSyncCommandTest.php` - Version sync (build-time infrastructure)
- `tests/Feature/ProductionUrlTest.php` - Production environment testing
- `tests/Feature/ProductionUrlCurlTest.php` - cURL production testing
- `tests/Feature/PRODUCTION_TESTS_README.md` - Associated documentation

### Implementation Details (Delete 1 file)
- `tests/Feature/CprHashingServiceTest.php` - CPR hashing is internal implementation, not business behavior

### Low-Value Tests (Delete 1 file)
- `tests/Feature/SplashRoutingTest.php` - Basic routing (not testing business logic, just view response)

### Duplicate Tests (Delete 1 file)
- `tests/Feature/AdminPanelTest.php` - Duplicate of `/Admin/AdminPanelTest.php`

### Standard Jetstream Auth Tests (Delete entire directory - 5 files)
- `tests/Feature/Auth/AuthenticationTest.php` - User login (Jetstream default)
- `tests/Feature/Auth/EmailVerificationTest.php` - Email verification (not used for callers)
- `tests/Feature/Auth/PasswordConfirmationTest.php` - Password confirmation (Jetstream default)
- `tests/Feature/Auth/PasswordResetTest.php` - Password reset (Jetstream default)
- `tests/Feature/Auth/RegistrationTest.php` - Admin user registration (not caller registration)

**Rationale:** These test admin user authentication via Jetstream, not TV show caller registration.

### User Account Settings Tests (Delete entire directory - 2 files)
- `tests/Feature/Settings/PasswordUpdateTest.php` - Admin password update
- `tests/Feature/Settings/ProfileUpdateTest.php` - Admin profile update

**Rationale:** These test Jetstream admin account features, not core TV show business.

### Browser/Dusk UI Automation Tests (Delete entire directory - 4 files + subdirs)
- `tests/Browser/AdminPanelNavigationTest.php` - UI navigation testing
- `tests/Browser/ExampleTest.php` - Example Dusk test
- `tests/Browser/FilamentDashboardTest.php` - Filament UI automation
- `tests/Browser/FormToggleTest.php` - Form UI toggle testing
- `tests/Browser/console/` - Temp test files
- `tests/Browser/Pages/` - Dusk page objects
- `tests/Browser/screenshots/` - Test screenshots
- `tests/Browser/source/` - Test source files

**Rationale:** Browser tests are low-priority for MVP. Functional/feature tests are sufficient.

---

## Files to Delete - Complete List

### Bash Command (Run from project root)
```bash
#!/bin/bash

# Infrastructure tests
rm -f tests/Feature/VersionManagerTest.php
rm -f tests/Feature/VersionSyncCommandTest.php
rm -f tests/Feature/ProductionUrlTest.php
rm -f tests/Feature/ProductionUrlCurlTest.php
rm -f tests/Feature/PRODUCTION_TESTS_README.md
rm -f tests/Feature/CprHashingServiceTest.php
rm -f tests/Feature/SplashRoutingTest.php
rm -f tests/Feature/AdminPanelTest.php

# Jetstream Auth tests
rm -rf tests/Feature/Auth

# User settings tests
rm -rf tests/Feature/Settings

# Browser Dusk tests
rm -rf tests/Browser

echo "✅ Test cleanup complete!"
```

---

## Verification

After deletion, your remaining test structure should be:

```
tests/
├── Feature/
│   ├── CallerRegistrationTest.php          ✓ CORE: Registration flows
│   ├── CallerRegistrationSecurityTest.php  ✓ CORE: Security validation
│   ├── CallerModelTest.php                 ✓ CORE: Model behavior
│   ├── FormValidationTest.php              ✓ CORE: Form rules
│   ├── MainFunctionalityTest.php           ✓ CORE: E2E flows
│   ├── FilamentDashboardFeatureTest.php    ✓ CORE: Admin dashboard
│   └── Admin/
│       ├── AdminPanelTest.php              ✓ CORE: Admin UI
│       └── DashboardWidgetsTest.php        ✓ CORE: Dashboard widgets
├── DuskTestCase.php
├── Pest.php
└── TestCase.php
```

---

## Next Steps

1. Run the bash command above to delete non-core tests
2. Run test suite: `php artisan test`
3. All remaining tests should pass (they're all core functionality)
4. Commit changes: `git add tests/ && git commit -m "refactor: remove non-core tests, keep only business logic"`

