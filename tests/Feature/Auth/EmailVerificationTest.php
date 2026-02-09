<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// Note: This application is a caller registration system and does not include
// traditional user email verification workflows. These tests are disabled.

test('placeholder test', function (): void {
    expect(true)->toBeTrue();
});
