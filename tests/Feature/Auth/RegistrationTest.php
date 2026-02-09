<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// Main registration screen
test('splash screen can be rendered', function (): void {
    $response = $this->get('/splash');

    $response->assertStatus(200);
    $response->assertViewIs('splash');
});

test('home registration page can be rendered', function (): void {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertViewIs('welcome');
});

test('family registration page can be rendered', function (): void {
    $response = $this->get('/family');

    $response->assertStatus(200);
    $response->assertViewIs('welcome');
});
