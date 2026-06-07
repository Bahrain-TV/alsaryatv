<?php

namespace Tests\Feature;

use App\Models\Caller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CallerStatusControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_status_changes_caller_status(): void
    {
        $caller = Caller::factory()->create(['status' => 'active']);

        $response = $this->postJson("/api/callers/{$caller->id}/status", [
            'status' => 'APPROVED',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertEquals('APPROVED', $caller->fresh()->status);
    }

    public function test_update_status_validates_allowed_values(): void
    {
        $caller = Caller::factory()->create();

        $response = $this->postJson("/api/callers/{$caller->id}/status", [
            'status' => 'INVALID_STATUS',
        ]);

        $response->assertUnprocessable();
    }

    public function test_update_status_requires_status_field(): void
    {
        $caller = Caller::factory()->create();

        $response = $this->postJson("/api/callers/{$caller->id}/status", []);

        $response->assertUnprocessable();
    }

    public function test_send_to_live_succeeds_for_approved_caller(): void
    {
        $caller = Caller::factory()->create(['status' => 'APPROVED']);

        $response = $this->postJson("/api/callers/{$caller->id}/live");

        $response->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_send_to_live_fails_for_non_approved_caller(): void
    {
        $caller = Caller::factory()->create(['status' => 'active']);

        $response = $this->postJson("/api/callers/{$caller->id}/live");

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_toggle_winner_marks_caller_as_winner(): void
    {
        $caller = Caller::factory()->create([
            'is_winner' => false,
            'is_selected' => false,
            'status' => 'active',
        ]);

        $response = $this->postJson("/api/callers/{$caller->id}/toggle-winner");

        $response->assertOk()
            ->assertJson(['success' => true, 'is_winner' => true]);

        $fresh = $caller->fresh();
        $this->assertTrue($fresh->is_winner);
        $this->assertTrue($fresh->is_selected);
        $this->assertEquals('selected', $fresh->status);
    }

    public function test_toggle_winner_cannot_unmark_existing_winner(): void
    {
        $caller = Caller::factory()->create([
            'is_winner' => true,
            'is_selected' => true,
            'status' => 'selected',
        ]);

        $response = $this->postJson("/api/callers/{$caller->id}/toggle-winner");

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_update_status_returns_404_for_nonexistent_caller(): void
    {
        $response = $this->postJson('/api/callers/99999/status', [
            'status' => 'APPROVED',
        ]);

        $response->assertNotFound();
    }
}
