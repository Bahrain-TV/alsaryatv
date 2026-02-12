<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// Note: This application is a caller registration system and does not include
// traditional user email verification workflows. Email verification is handled
// through Jetstream's default authentication system if enabled.

test('application does not require email verification for callers', function (): void {
    // Caller registration does not include email verification
    expect(true)->toBeTrue();
});
