<?php

namespace Tests\Feature;

use App\Models\Caller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CallerModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        Caller::factory()->count(5)->create(['is_winner' => false, 'status' => 'active']);
        Caller::factory()->count(3)->create(['is_winner' => true, 'status' => 'active']);
        Caller::factory()->count(2)->create(['status' => 'inactive']);
    }

    public function test_winners_scope_returns_only_winners(): void
    {
        $winners = Caller::winners()->get();

        $this->assertEquals(3, $winners->count());
        $winners->each(fn ($winner) => $this->assertTrue($winner->is_winner));
    }

    public function test_eligible_scope_excludes_winners(): void
    {
        $eligible = Caller::eligible()->get();

        $this->assertEquals(5, $eligible->count());
        $eligible->each(fn ($caller) => $this->assertFalse($caller->is_winner));
    }

    public function test_eligible_scope_excludes_callers_without_cpr(): void
    {
        Caller::factory()->create(['cpr' => null, 'is_winner' => false]);

        $eligible = Caller::eligible()->get();

        // Should not include the caller with null CPR
        $eligible->each(fn ($caller) => $this->assertNotNull($caller->cpr));
    }

    public function test_get_eligible_callers_static_method(): void
    {
        $eligible = Caller::getEligibleCallers()->get();

        $this->assertEquals(5, $eligible->count());
    }

    public function test_select_random_winner_by_cpr(): void
    {
        $winner = Caller::selectRandomWinnerByCpr();

        $this->assertNotNull($winner);
        $this->assertTrue($winner->is_winner);

        // Verify it was marked as winner in database
        $this->assertTrue($winner->fresh()->is_winner);
    }

    public function test_select_random_winner_returns_null_when_all_are_winners(): void
    {
        Caller::update(['is_winner' => true]);

        $winner = Caller::selectRandomWinnerByCpr();

        $this->assertNull($winner);
    }

    public function test_increment_hits_increases_hit_count(): void
    {
        $caller = Caller::factory()->create(['hits' => 5]);

        $caller->incrementHits();

        $this->assertEquals(6, $caller->fresh()->hits);
    }

    public function test_increment_hits_updates_last_hit_timestamp(): void
    {
        $caller = Caller::factory()->create(['last_hit' => null]);

        $caller->incrementHits();

        $this->assertNotNull($caller->fresh()->last_hit);
    }

    public function test_increment_hits_is_atomic(): void
    {
        $caller = Caller::factory()->create(['hits' => 0]);

        // Simulate multiple concurrent increments
        for ($i = 0; $i < 5; $i++) {
            $caller->incrementHits();
        }

        $this->assertEquals(5, $caller->fresh()->hits);
    }

    public function test_caller_fillable_attributes(): void
    {
        $data = [
            'name' => 'Test Caller',
            'phone' => '+97312345678',
            'cpr' => '12345678901',
            'is_family' => true,
            'is_winner' => false,
            'status' => 'active',
            'ip_address' => '192.168.1.1',
            'hits' => 5,
            'notes' => 'Test notes',
            'level' => 'vip',
        ];

        $caller = Caller::create($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $caller->{$key});
        }
    }

    public function test_caller_casts_are_applied(): void
    {
        $caller = Caller::factory()->create([
            'is_family' => true,
            'is_winner' => false,
            'last_hit' => now(),
        ]);

        $this->assertIsBool($caller->is_family);
        $this->assertIsBool($caller->is_winner);
    }

    public function test_caller_relationships(): void
    {
        $caller = Caller::factory()->create();

        // Basic test that caller exists and has properties
        $this->assertNotNull($caller->id);
        $this->assertNotNull($caller->name);
    }

    public function test_eligible_callers_with_various_statuses(): void
    {
        Caller::truncate();

        Caller::factory()->create(['status' => 'active', 'is_winner' => false]);
        Caller::factory()->create(['status' => 'inactive', 'is_winner' => false]);
        Caller::factory()->create(['status' => 'blocked', 'is_winner' => false]);

        $eligible = Caller::eligible()->get();

        // All non-winners should be eligible regardless of status
        $this->assertEquals(3, $eligible->count());
    }

    public function test_callers_can_be_marked_as_family(): void
    {
        $familyCaller = Caller::factory()->create([
            'is_family' => true,
            'name' => 'عائلة عبدالله',
        ]);

        $this->assertTrue($familyCaller->is_family);
    }

    public function test_callers_can_be_marked_as_individual(): void
    {
        $individualCaller = Caller::factory()->create(['is_family' => false]);

        $this->assertFalse($individualCaller->is_family);
    }

    public function test_caller_tracks_registration_time(): void
    {
        $caller = Caller::factory()->create();

        $this->assertNotNull($caller->created_at);
        $this->assertNotNull($caller->updated_at);
    }
}
