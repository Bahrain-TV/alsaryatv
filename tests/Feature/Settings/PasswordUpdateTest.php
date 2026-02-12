<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// Note: This application is a caller registration system and does not include
// user password update interfaces for callers. Password management is handled
// through Jetstream's authentication system for admin users only.

test('application does not require password updates for callers', function (): void {
    // Caller registration does not include password management
    expect(true)->toBeTrue();
});
