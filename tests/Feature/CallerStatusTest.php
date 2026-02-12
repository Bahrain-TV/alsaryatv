<?php

namespace Tests\Feature;

use App\Models\Caller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CallerStatusTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Caller $caller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->caller = Caller::factory()->create(['status' => 'pending']);
    }

    public function test_admin_can_update_caller_status(): void
    {
        $response = $this->actingAs($this->admin)
            ->post("/api/callers/{$this->caller->id}/status", [
                'status' => 'approved',
            ]);

        $response->assertJson(['success' => true]);
        $this->assertEquals('approved', $this->caller->fresh()->status);
    }

    public function test_guest_cannot_update_caller_status(): void
    {
        $response = $this->post("/api/callers/{$this->caller->id}/status", [
            'status' => 'approved',
        ]);

        $response->assertStatus(401);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $response = $this->actingAs($this->admin)
            ->post("/api/callers/{$this->caller->id}/status", [
                'status' => 'invalid-status',
            ]);

        $response->assertStatus(422);
        $this->assertEquals('pending', $this->caller->fresh()->status);
    }

    public function test_admin_can_send_approved_caller_to_live(): void
    {
        $this->caller->update(['status' => 'approved']);

        $response = $this->actingAs($this->admin)
            ->post("/api/callers/{$this->caller->id}/send-to-live");

        $response->assertJson(['success' => true]);
    }

    public function test_non_approved_caller_cannot_be_sent_to_live(): void
    {
        $this->caller->update(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->post("/api/callers/{$this->caller->id}/send-to-live");

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_admin_can_toggle_winner_status(): void
    {
        $this->assertFalse($this->caller->is_winner);

        $response = $this->actingAs($this->admin)
            ->post("/api/callers/{$this->caller->id}/toggle-winner");

        $response->assertJson(['success' => true]);
        $this->assertTrue($this->caller->fresh()->is_winner);

        // Toggle back
        $response = $this->actingAs($this->admin)
            ->post("/api/callers/{$this->caller->id}/toggle-winner");

        $response->assertJson(['success' => true]);
        $this->assertFalse($this->caller->fresh()->is_winner);
    }

    public function test_status_cannot_be_updated_to_empty_value(): void
    {
        $response = $this->actingAs($this->admin)
            ->post("/api/callers/{$this->caller->id}/status", [
                'status' => '',
            ]);

        $response->assertStatus(422);
    }

    public function test_caller_not_found_returns_404(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/api/callers/99999/status', [
                'status' => 'approved',
            ]);

        $response->assertStatus(404);
    }
}
