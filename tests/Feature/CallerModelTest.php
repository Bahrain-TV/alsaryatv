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

        // Cleanup database before each test
        Caller::truncate();
    }

    public function test_winners_scope_returns_only_winners(): void
    {
        // 3 Winners (Active)
        Caller::factory()->count(3)->create([
            'is_winner' => true,
            'status' => 'active'
        ]);
        
        // 5 Non-Winners (Active)
        Caller::factory()->count(5)->create([
            'is_winner' => false,
            'status' => 'active'
        ]);

        $winners = Caller::winners()->get();

        $this->assertEquals(3, $winners->count());
        $winners->each(fn ($winner) => $this->assertTrue((bool)$winner->is_winner));
    }

    public function test_eligible_scope_logic(): void
    {
        // 1. Valid Eligible Caller (Active, Non-Winner, Has CPR)
        Caller::factory()->create([
            'is_winner' => false,
            'status' => 'active',
            'cpr' => '123456789'
        ]);

        // 2. Inactive Caller (Should be excluded)
        Caller::factory()->create([
            'is_winner' => false,
            'status' => 'inactive',
            'cpr' => '987654321'
        ]);

        // 3. Winner (Should be excluded)
        Caller::factory()->create([
            'is_winner' => true,
            'status' => 'active',
            'cpr' => '1122334455'
        ]);


        
        // 5. Empty CPR (Should be excluded)
        Caller::factory()->create([
            'is_winner' => false,
            'status' => 'active',
            'cpr' => ''
        ]);

        $eligible = Caller::eligible()->get();

        // Only case 1 should be returned
        $this->assertEquals(1, $eligible->count());
        $this->assertEquals('123456789', $eligible->first()->cpr);
    }

    public function test_select_random_winner_by_cpr(): void
    {
        Caller::factory()->create([
            'is_winner' => false,
            'status' => 'active',
            'cpr' => '123456789'
        ]);

        $winner = Caller::selectRandomWinnerByCpr();

        $this->assertNotNull($winner);
        $this->assertTrue((bool)$winner->is_winner); // Should be marked winner now
        $this->assertEquals('123456789', $winner->cpr);
        
        // Verify persists in DB
        $this->assertTrue((bool)$winner->fresh()->is_winner);
    }

    public function test_select_random_winner_returns_null_when_no_eligible_callers(): void
    {
        // Create only winners or inactive users
        Caller::factory()->create(['is_winner' => true, 'status' => 'active']);
        Caller::factory()->create(['is_winner' => false, 'status' => 'inactive']);

        $winner = Caller::selectRandomWinnerByCpr();

        $this->assertNull($winner);
    }

    public function test_increment_hits_increases_hit_count_atomically(): void
    {
        $caller = Caller::factory()->create(['hits' => 5]);

        $caller->incrementHits();

        $freshCaller = $caller->fresh();
        $this->assertEquals(6, $freshCaller->hits);
        $this->assertNotNull($freshCaller->last_hit);
    }
}
