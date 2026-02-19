<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SplashRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_route_renders_splash_by_default(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('splash');
    }

    public function test_skip_splash_query_renders_welcome(): void
    {
        $response = $this->get('/?skip-splash=true');

        $response->assertStatus(200);
        $response->assertViewIs('welcome');
    }
}
