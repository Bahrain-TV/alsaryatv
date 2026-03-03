<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TinkeretteTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get('/tinkerette');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_tinkerette(): void
    {
        $response = $this->actingAs($this->user)->get('/tinkerette');

        $response->assertOk();
        $response->assertSee('Tinkerette');
        $response->assertSee('php artisan');
    }

    public function test_tinkerette_runs_list_by_default(): void
    {
        $response = $this->actingAs($this->user)->get('/tinkerette');

        $response->assertOk();
        $response->assertSee('artisan list');
    }

    public function test_tinkerette_runs_about_command(): void
    {
        $response = $this->actingAs($this->user)->get('/tinkerette?cmd=about');

        $response->assertOk();
        $response->assertSee('artisan about');
    }

    public function test_blocked_commands_return_403(): void
    {
        $blockedCommands = ['down', 'up', 'env', 'tinker', 'migrate:fresh', 'db:wipe'];

        foreach ($blockedCommands as $cmd) {
            $response = $this->actingAs($this->user)->get('/tinkerette?cmd=' . $cmd);

            $response->assertForbidden();
        }
    }

    public function test_invalid_characters_return_400(): void
    {
        $response = $this->actingAs($this->user)->get('/tinkerette?cmd=' . urlencode('list; rm -rf /'));

        $response->assertStatus(400);
    }

    public function test_shows_authenticated_user_email(): void
    {
        $response = $this->actingAs($this->user)->get('/tinkerette');

        $response->assertOk();
        $response->assertSee($this->user->email);
    }

    public function test_quick_commands_are_displayed(): void
    {
        $response = $this->actingAs($this->user)->get('/tinkerette');

        $response->assertOk();
        $response->assertSee('route:list');
        $response->assertSee('migrate:status');
    }

    public function test_tinkerette_blocked_in_production(): void
    {
        app()->detectEnvironment(fn () => 'production');

        $response = $this->actingAs($this->user)->get('/tinkerette');

        $response->assertForbidden();
    }
}
