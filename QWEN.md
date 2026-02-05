# Comprehensive Qwen Context File for Alsarya TV Project

## Project Overview

Alsarya TV is a sophisticated Laravel-based web application for the live "Alsarya" television program broadcast on Bahrain TV during Ramadan. The application serves as a comprehensive platform for viewer registration, hit counting, winner selection, and live audience management for the Ramadan show.

### Key Features
- **Live viewer registration** with CPR validation and deduplication
- **Advanced hit counter system** to track participation
- **Sophisticated winner selection** functionality with CPR-based uniqueness
- **Real-time broadcasting** capabilities using Laravel Echo and Pusher
- **Responsive countdown timer** to Ramadan with dynamic updates
- **Multi-tier admin dashboard** with role-based access control
- **Comprehensive security monitoring** with IP blocking and violation tracking
- **Automated data persistence** with backup/restore during deployments
- **Multilingual support** (Arabic interface with RTL layout)

### Technologies Stack
- **Backend**: Laravel 12.x, PHP 8.5+, MySQL/PostgreSQL
- **Admin Panel**: Filament PHP v5.1 with custom Arabic localization
- **Frontend**: Alpine.js, Tailwind CSS v3.4, GSAP animations, Vite
- **Authentication**: Laravel Sanctum, Laravel Jetstream with Fortify
- **Real-time**: Laravel Echo, Pusher, WebSockets via Laravel Reverb
- **Build Tools**: Vite, PostCSS, Tailwind CSS, Pnpm
- **Additional**: Lottie animations, FlipDown countdown, GSAP animations, Livewire Volt

## Detailed Architecture

### Core Components

#### 1. Data Models
- **Caller Model**: Central entity representing registered participants
  - Properties: name, phone, cpr (Civil Personal Registration), hits, is_winner, status, level, notes
  - Scopes: `winners()`, `eligible()` for filtering participants
  - Methods: `incrementHits()`, `selectRandomWinnerByCpr()` for business logic
  - Security: Boot method prevents unauthorized updates in production

- **User Model**: Authentication and authorization for admin panel
  - Properties: name, email, password, is_admin, role
  - Features: Two-factor authentication, profile photos, teams

#### 2. Service Layer
- **CprHashingService**: Secure CPR handling with hashing, verification, and masking
- **SecurityService**: Operation validation, request validation, and throttling
- **SecurityMonitorService**: IP blocking, violation tracking, and security event logging
- **CsvExportService**: Data export functionality for reporting
- **GoogleSheetsCallerService**: Integration with Google Sheets for data synchronization
- **DirtyFileManager**: File management with security checks

#### 3. Event System
- **CallerApproved Event**: Real-time broadcasting to "live-stage" channel
- **Custom events**: For winner selection, registration notifications, and admin alerts

#### 4. Request Validation
- **StoreCallerRequest**: Validates name, CPR, phone number, and caller type
- **UpdateCallerRequest**: Ensures data integrity during updates
- **Custom validation messages**: Fully localized in Arabic

### Admin Panel (Filament)

#### Resources
- **CallerResource**: Comprehensive CRUD for participants
  - Features: Search, filtering, bulk actions, winner selection
  - Bulk actions: Select multiple random winners, toggle winner status
  - Header actions: Single random winner selection
  - Custom actions: Toggle winner status with confirmation

- **UserResource**: User management with role assignment
  - Roles: user, editor, manager, admin
  - Features: Password management, admin privileges

#### Pages
- **ListWinners Page**: Dedicated view for winner management
- **Custom navigation**: Arabic labels and intuitive grouping

### Frontend Architecture

#### JavaScript Components
- **GSAP Animations**: Advanced timeline-based animations
  - Master timeline coordinating intro, buttons, sponsors, and forms
  - Glitch effects for dynamic visual interest
  - Scroll-triggered animations

- **Lottie Integration**: High-quality vector animations
  - Crescent moon animation for Ramadan theme
  - Configurable playback with bounce mode

- **FlipDown Timer**: Customizable countdown to Ramadan
  - Automatic state change when countdown ends
  - Theme customization for dark mode

#### Styling
- **Tailwind CSS**: Utility-first approach with custom components
- **RTL Support**: Full right-to-left layout compatibility
- **Responsive Design**: Mobile-first approach with breakpoints
- **Glassmorphism Effects**: Modern UI with backdrop filters
- **Custom CSS Variables**: Theming for gold/emerald color scheme

### Security Measures

#### Rate Limiting & Throttling
- **Per-CPR Limiting**: Prevents duplicate registrations
- **Per-IP Limiting**: Controls registration volume
- **Advanced Throttling**: Redis-backed rate limiting with atomic operations
- **Blacklisting**: Temporary IP blocking for excessive requests

#### Security Monitoring
- **Violation Tracking**: Logs security events with context
- **IP Blocking**: Automatic blocking after threshold violations
- **Attack Pattern Detection**: Identifies and responds to repeated attacks
- **Security Logging**: Dedicated security log channel

#### Data Protection
- **CPR Masking**: Partial hiding of sensitive identification numbers
- **Secure Hashing**: Industry-standard hashing for sensitive data
- **Access Control**: Role-based permissions and policy enforcement

### Data Persistence & Backup

#### Automated Backup System
- **Pre-deployment Export**: CSV export before migrations
- **Post-migration Import**: Data restoration after schema updates
- **Verification Checks**: Validation of backup integrity
- **Event-Driven Hooks**: Automatic backup during migration commands

#### Deployment Strategy
- **Zero-Downtime Deployments**: Maintenance mode switching
- **Version Management**: Automated version incrementing
- **Discord Notifications**: Real-time deployment status updates
- **Rollback Capability**: Emergency restore functionality

## Configuration & Environment

### Key Configuration Files
- **config/alsarya.php**: Ramadan dates, registration settings, Arabic translations
- **config/filament.php**: Admin panel customization
- **config/security.php**: Security thresholds and limits (if exists)
- **config/logging.php**: Multiple log channels including security logging

### Environment Variables
- **RAMADAN_START_DATE**: Configurable Ramadan start date
- **REGISTRATION_OPEN_DATE**: When registration becomes available
- **APP_VERSION**: Auto-incremented during deployments
- **Security settings**: Rate limiting, blacklist durations, etc.

## Development Workflow

### Local Development
```bash
# Install dependencies
composer install
pnpm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --seed

# Development server
pnpm dev
php artisan serve
```

### Testing
```bash
# Run tests
php artisan test

# Feature tests for critical flows
php artisan test --testsuite=Feature
```

### Code Quality
- **Pint**: Laravel's code formatter
- **Rector**: Automated refactoring and upgrades
- **ESLint**: JavaScript linting
- **PHPStan**: Static analysis

## Deployment & Operations

### Deployment Process
1. **Pre-flight Checks**: Version bumping and file validation
2. **Maintenance Mode**: Site temporarily offline
3. **Code Sync**: Git pull from main branch
4. **Pre-deployment Backup**: Export current data
5. **Migrations**: Apply database schema changes
6. **Data Restore**: Import previous data
7. **Optimization**: Cache clearing and rebuilding
8. **Post-flight**: Version update and notifications
9. **Maintenance Off**: Site back online

### Scripts
- **deploy.sh**: Server-side deployment automation
- **publish.sh**: Local-to-server publishing with Discord notifications
- **maintenance commands**: Manual up/down switches

### Monitoring
- **Security Logs**: Dedicated security event tracking
- **Performance Metrics**: Hit counters and user engagement
- **Discord Alerts**: Real-time deployment and security notifications

## Business Logic

### Registration Flow
1. **Public Access**: Unauthenticated users can register
2. **Validation**: CPR, phone, and name validation
3. **Rate Limiting**: Per-CPR and per-IP checks
4. **Deduplication**: Updates existing records rather than creating duplicates
5. **Hit Counting**: Automatic increment on successful registration
6. **Session Tracking**: Success page with user-specific data

### Winner Selection
1. **Eligibility**: Only non-winners with valid CPR
2. **Uniqueness**: CPR-based selection to prevent multiple wins
3. **Randomization**: True random selection from eligible pool
4. **Bulk Selection**: Multiple winners with CPR uniqueness
5. **Audit Trail**: Complete logging of selections

### Hit Counting System
1. **Visitor Counter**: Total site visits
2. **User Hits**: Individual participation tracking
3. **Caching**: Redis-based caching for performance
4. **Persistence**: Data retention across deployments

## Special Considerations

### Cultural Adaptation
- **Arabic Interface**: Full RTL support with culturally appropriate design
- **Ramadan Theme**: Seasonal design elements and messaging
- **Local Customs**: Respectful interaction patterns for Bahraini audience

### Scalability Features
- **Redis Integration**: For rate limiting and caching
- **Queue System**: Background processing for heavy operations
- **WebSocket Broadcasting**: Real-time updates for live events
- **Database Indexing**: Optimized queries for high-volume periods

### Compliance & Privacy
- **Data Minimization**: Only essential fields collected
- **Secure Storage**: Encrypted sensitive information
- **Retention Policies**: Defined data lifecycle management
- **Access Controls**: Granular permissions for admin users

This comprehensive architecture ensures the Alsarya TV application can handle high-volume registration during Ramadan while maintaining security, performance, and cultural appropriateness for the Bahraini audience.