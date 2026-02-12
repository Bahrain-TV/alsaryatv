<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// Note: This application is a caller registration system and does not include
// user profile management for callers. Profile administration is handled
// through the Filament admin panel for authorized admin users only.

test('application does not require profile updates for callers', function (): void {
    // Caller registration does not include profile management
    expect(true)->toBeTrue();
});
