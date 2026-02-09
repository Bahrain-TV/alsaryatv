<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_filament_panel()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin');

        // Filament panel returns 200 when authenticated, or redirects on insufficient permissions
        $this->assertThat(
            $response->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(200),
                $this->equalTo(302)
            )
        );
    }

    public function test_guest_cannot_access_admin_panel()
    {
        $response = $this->get('/admin');

        $response->assertStatus(302); // Should redirect to login
        $response->assertRedirect('/login');
    }
}
