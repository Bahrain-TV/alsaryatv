# Changelog
 
 All notable changes to this project will be documented in this file.
 
 ## [Unreleased]

### Added

Add feature tests for user authentication and profile management

- Implement tests for password reset functionality including link rendering, request handling, and token validation.
- Create tests for profile information updates and ensure current data is displayed correctly.
- Add rate limiting tests to prevent duplicate registrations and bulk submissions.
- Establish registration tests to verify screen rendering and user registration flow.
- Introduce password update tests to ensure password changes are handled correctly.
- Implement profile update tests to validate user profile modifications and account deletion.
- Add two-factor authentication tests to manage enabling, disabling, and regenerating recovery codes.
- Set up initial test structure with Pest and PHPUnit integration.
- Configure Vite for asset management in the application.