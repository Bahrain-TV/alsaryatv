<?php

namespace Tests\Feature;

use App\Models\Caller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimpleWinnerTest extends TestCase
{
    use RefreshDatabase;

    public function test_winner_selection_works()
    {
        // Create callers manually
        $caller1 = new Caller;
        $caller1->name = 'Test Caller 1';
        $caller1->phone = '123456789';
        $caller1->cpr = '123456789';
        $caller1->is_winner = false;
        $caller1->status = 'active';
        $caller1->save();

        $caller2 = new Caller;
        $caller2->name = 'Test Caller 2';
        $caller2->phone = '987654321';
        $caller2->cpr = '987654321';
        $caller2->is_winner = false;
        $caller2->status = 'active';
        $caller2->save();

        // Test eligible callers
        $eligibleCallers = Caller::getEligibleCallers();
        $this->assertCount(2, $eligibleCallers);

        // Test winner selection
        $winner = Caller::selectRandomWinnerByCpr();
        $this->assertNotNull($winner);
        $this->assertTrue($winner->is_winner);

        // Test that only one winner was selected
        $totalWinners = Caller::where('is_winner', true)->count();
        $this->assertEquals(1, $totalWinners);
    }

    public function test_no_winner_when_no_eligible_callers()
    {
        // Create caller that's already a winner
        $caller = new Caller;
        $caller->name = 'Test Caller';
        $caller->phone = '123456789';
        $caller->cpr = '123456789';
        $caller->is_winner = true; // Already a winner
        $caller->status = 'active';
        $caller->save();

        // Test that no winner is selected
        $winner = Caller::selectRandomWinnerByCpr();
        $this->assertNull($winner);
    }
}
