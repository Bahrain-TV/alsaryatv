<?php

namespace Tests\Feature;

use App\Models\Caller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CallerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Caller $caller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->caller = Caller::factory()->create();
    }

    public function test_guests_can_view_caller_registration_form(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('welcome');
    }

    public function test_authenticated_user_can_view_callers_list(): void
    {
        Caller::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)->get('/callers');

        $response->assertStatus(200);
        $response->assertViewHas('callers');
    }

    public function test_admin_can_edit_caller(): void
    {
        $response = $this->actingAs($this->admin)->get("/callers/{$this->caller->id}/edit");

        $response->assertStatus(200);
        $response->assertViewHas('caller');
    }

    public function test_admin_can_update_caller(): void
    {
        $response = $this->actingAs($this->admin)->put("/callers/{$this->caller->id}", [
            'name' => 'Updated Name',
            'phone_number' => '+97312345678',
            'cpr' => '12345678901',
            'is_winner' => true,
            'notes' => 'Test notes',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertEquals('Updated Name', $this->caller->fresh()->name);
    }

    public function test_guest_cannot_edit_caller(): void
    {
        $response = $this->get("/callers/{$this->caller->id}/edit");

        $response->assertRedirect('/login');
    }

    public function test_admin_can_delete_caller(): void
    {
        $callerId = $this->caller->id;

        $response = $this->actingAs($this->admin)->delete("/callers/{$this->caller->id}");

        $response->assertRedirect(route('dashboard'));
        $this->assertNull(Caller::find($callerId));
    }

    public function test_guest_cannot_delete_caller(): void
    {
        $response = $this->delete("/callers/{$this->caller->id}");

        $response->assertRedirect('/login');
        $this->assertNotNull(Caller::find($this->caller->id));
    }

    public function test_admin_can_view_winners(): void
    {
        Caller::factory()->count(3)->create(['is_winner' => true]);
        Caller::factory()->count(2)->create(['is_winner' => false]);

        $response = $this->actingAs($this->admin)->get('/callers/winners');

        $response->assertStatus(200);
        $response->assertViewHas('winners');
    }

    public function test_admin_can_view_families(): void
    {
        Caller::factory()->count(5)->create(['is_family' => true]);

        $response = $this->actingAs($this->admin)->get('/callers/families');

        $response->assertStatus(200);
        $response->assertViewHas('families');
    }

    public function test_cpr_existence_can_be_checked(): void
    {
        $response = $this->post('/api/callers/check-cpr', [
            'cpr' => $this->caller->cpr,
        ]);

        $response->assertJson(['exists' => true]);
    }

    public function test_non_existent_cpr_returns_false(): void
    {
        $response = $this->post('/api/callers/check-cpr', [
            'cpr' => 'nonexistent',
        ]);

        $response->assertJson(['exists' => false]);
    }

    public function test_admin_can_toggle_winner_status(): void
    {
        $this->assertFalse($this->caller->is_winner);

        $response = $this->actingAs($this->admin)
            ->post("/callers/{$this->caller->id}/toggle-winner");

        $response->assertJson(['success' => true, 'is_winner' => true]);
        $this->assertTrue($this->caller->fresh()->is_winner);
    }

    public function test_guest_cannot_toggle_winner_status(): void
    {
        $response = $this->post("/callers/{$this->caller->id}/toggle-winner");

        $response->assertStatus(401);
    }

    public function test_admin_can_select_random_winner(): void
    {
        Caller::factory()->count(10)->create(['is_winner' => false]);

        $response = $this->actingAs($this->admin)->post('/callers/random-winner');

        $response->assertJson(['success' => true]);
        $this->assertArrayHasKey('winner', $response->json());

        $winner = Caller::find($response->json('winner.id'));
        $this->assertTrue($winner->is_winner);
    }

    public function test_random_winner_fails_when_no_eligible_callers(): void
    {
        Caller::truncate();

        $response = $this->actingAs($this->admin)->post('/callers/random-winner');

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_update_caller_validates_input(): void
    {
        $response = $this->actingAs($this->admin)->put("/callers/{$this->caller->id}", [
            'name' => '', // Invalid: empty
            'phone_number' => '+97312345678',
            'cpr' => '12345678901',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_caller_index_is_paginated(): void
    {
        Caller::factory()->count(30)->create();

        $response = $this->actingAs($this->admin)->get('/callers');

        $response->assertViewHas('callers');
    }
}
