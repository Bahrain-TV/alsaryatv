# Laravel Dusk Admin Panel Navigation Testing

## Overview

This document explains how to run the comprehensive Laravel Dusk tests for the Admin Panel with focus on dark mode, Arabic locale, and RTL layout verification.

## Test File

- **Location**: `tests/Browser/AdminPanelNavigationTest.php`
- **Test Class**: `Tests\Browser\AdminPanelNavigationTest`

## Prerequisites

1. **Laravel Dusk Installed**: Verify with `php artisan dusk --version`
2. **Chrome/Chromium**: Required for browser automation
3. **Database Seeding**: Tests require a database with proper structure
4. **Test Admin User**: Automatically created by the test if not exists

## Test Methods

### 1. `test_admin_panel_loads_with_dark_mode_and_arabic`
- **Purpose**: Verifies admin panel loads with dark mode enabled and Arabic locale active
- **Validates**:
  - Dark mode CSS class presence
  - RTL/LTR direction
  - Arabic branding text ("السارية - لوحة التحكم")

### 2. `test_navigation_menu_items_are_functional`
- **Purpose**: Tests all navigation menu items are clickable and load without errors
- **Validates**:
  - Each menu item is accessible
  - Page loads after navigation
  - No critical errors appear
  - Sidebar remains visible

### 3. `test_sidebar_collapse_functionality`
- **Purpose**: Tests sidebar collapse/expand on desktop
- **Validates**:
  - Initial sidebar state
  - Collapse button functionality (if present)
  - Responsive behavior

### 4. `test_responsive_design_on_mobile`
- **Purpose**: Tests admin panel on mobile viewport (375x667)
- **Validates**:
  - Layout adjustment on mobile
  - Sidebar behavior on mobile
  - Content reflow

### 5. `test_dark_mode_styling`
- **Purpose**: Verifies dark mode CSS is properly applied
- **Validates**:
  - Dark mode class on root element
  - Computed background and text colors
  - Sidebar background color in dark mode

### 6. `test_rtl_layout_implementation`
- **Purpose**: Tests RTL (Right-to-Left) layout implementation
- **Validates**:
  - `dir="rtl"` attribute presence
  - Language attribute (`lang="ar"`)
  - Computed CSS direction property

### 7. `test_form_elements_and_buttons`
- **Purpose**: Tests form rendering and interactivity in admin panel
- **Validates**:
  - Form input elements presence
  - Button count
  - Select dropdowns
  - Textarea elements

### 8. `test_dashboard_widgets_render`
- **Purpose**: Tests widget rendering on dashboard
- **Validates**:
  - Widget element count
  - Statistics cards
  - Grid layout

### 9. `test_keyboard_navigation`
- **Purpose**: Tests accessibility via keyboard navigation
- **Validates**:
  - Tab key navigation
  - Focus management
  - Keyboard accessibility

### 10. `test_complete_admin_user_flow`
- **Purpose**: Complete end-to-end test of login → navigate → usage flow
- **Validates**:
  - Login form rendering
  - Successful authentication
  - Post-login redirect
  - Arabic locale is active

## Running the Tests

### Run All Admin Panel Tests
```bash
php artisan dusk tests/Browser/AdminPanelNavigationTest.php
```

### Run Specific Test Method
```bash
php artisan dusk tests/Browser/AdminPanelNavigationTest.php --filter=test_admin_panel_loads_with_dark_mode_and_arabic
```

### Run with Verbose Output
```bash
php artisan dusk tests/Browser/AdminPanelNavigationTest.php --verbose
```

### Run Headless (No Browser Window)
```bash
php artisan dusk tests/Browser/AdminPanelNavigationTest.php --headless
```

### Run All Browser Tests
```bash
php artisan dusk
```

## Screenshots

All test screenshots are saved to: `tests/Browser/screenshots/`

Screenshots are organized by test method:
- `admin-panel-initial-load.png` - Initial panel load
- `admin-dashboard-default.png` - Dashboard view
- `admin-menu-*.png` - Each menu item navigation
- `admin-sidebar-expanded.png` - Sidebar state
- `admin-sidebar-collapsed.png` - Collapsed sidebar
- `admin-mobile-view.png` - Mobile responsive view
- `admin-dark-mode-verification.png` - Dark mode validation
- `admin-rtl-layout.png` - RTL layout verification
- `admin-users-list.png` - Users resource view
- `admin-dashboard-widgets.png` - Widgets rendering
- `admin-keyboard-nav-tab*.png` - Keyboard navigation
- `admin-login-page.png` - Login page
- `admin-post-login.png` - Post-login state

## Configuration

### Environment Setup

Ensure your `.env.testing` has:
```env
APP_ENV=testing
APP_DEBUG=true
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
DUSK_DRIVER_PATH=./vendor/laravel/dusk/bin/chromedriver-linux
```

### Admin User Creation

The test automatically creates a test admin user:
- **Email**: `admin@test.com`
- **Password**: `password`
- **Name**: `Test Admin`

To use an existing admin user, modify the `setUp()` method in the test class.

## Troubleshooting

### ChromeDriver Not Found
```bash
php artisan dusk:install
```

### Port Already in Use
Change the test port in `phpunit.dusk.xml`:
```xml
<env name="APP_PORT" value="9002"/>
```

### Browser Hangs on Screenshot
Increase pause/wait times:
```php
->pause(3000)  // 3 seconds
->waitFor('.selector', 15)  // 15 seconds
```

### Test Database Issues
Reset and reseed:
```bash
php artisan migrate:fresh --env=testing
php artisan db:seed --env=testing
```

## CI/CD Integration

### GitHub Actions Example
```yaml
- name: Run Dusk Tests
  run: |
    php artisan dusk tests/Browser/AdminPanelNavigationTest.php --headless

- name: Upload Screenshots on Failure
  if: failure()
  uses: actions/upload-artifact@v2
  with:
    name: dusk-screenshots
    path: tests/Browser/screenshots
```

### GitLab CI Example
```yaml
dusk_tests:
  script:
    - php artisan dusk tests/Browser/AdminPanelNavigationTest.php --headless
  artifacts:
    paths:
      - tests/Browser/screenshots
    when: on_failure
```

## Performance Tips

1. **Reduce Pauses**: Lower pause times if tests are too slow
2. **Parallel Execution**: Use `--parallel` flag (Dusk v7+)
3. **Skip Non-Critical Tests**: Comment out tests not needed for CI/CD
4. **Use Headless Mode**: Faster without rendering browser UI

## Additional Notes

- Tests use dark mode by default (as configured in AdminPanelProvider)
- Arabic locale is enabled by default
- RTL layout is fully implemented and tested
- All tests include automatic screenshots for visual regression testing
- Focus on user interactions and visual validation, not just assertions

## Maintenance

Update these tests when:
- Adding new navigation menu items
- Changing authentication flow
- Modifying admin panel layout
- Updating color scheme or styling
- Adding new dashboard widgets

## Related Documentation

- [Laravel Dusk Documentation](https://laravel.com/docs/dusk)
- [Admin Panel Configuration](app/Providers/Filament/AdminPanelProvider.php)
- [RTL/Dark Mode CSS](public/css/filament/admin/theme.css)
