<?php

namespace Tests\Feature;

use App\Models\Caller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompleteWinnerSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_winner_selection_workflow()
    {
        // Step 1: Create eligible callers
        $caller1 = Caller::create([
            'name' => 'Caller One',
            'phone' => '111111111',
            'cpr' => '111111111',
            'is_winner' => false,
            'status' => 'active',
        ]);

        $caller2 = Caller::create([
            'name' => 'Caller Two',
            'phone' => '222222222',
            'cpr' => '222222222',
            'is_winner' => false,
            'status' => 'active',
        ]);

        $caller3 = Caller::create([
            'name' => 'Caller Three',
            'phone' => '333333333',
            'cpr' => '333333333',
            'is_winner' => false,
            'status' => 'active',
        ]);

        // Step 2: Verify eligible callers
        $eligibleCallers = Caller::getEligibleCallers();
        $this->assertCount(3, $eligibleCallers);

        // Step 3: Select first winner
        $winner1 = Caller::selectRandomWinnerByCpr();
        $this->assertNotNull($winner1);
        $this->assertTrue($winner1->is_winner);
        $this->assertContains($winner1->cpr, ['111111111', '222222222', '333333333']);

        // Step 4: Verify only one winner exists
        $totalWinners = Caller::where('is_winner', true)->count();
        $this->assertEquals(1, $totalWinners);

        // Step 5: Select second winner - should be different CPR
        $winner2 = Caller::selectRandomWinnerByCpr();
        $this->assertNotNull($winner2);
        $this->assertTrue($winner2->is_winner);
        $this->assertNotEquals($winner1->cpr, $winner2->cpr);

        // Step 6: Verify two winners exist
        $totalWinners = Caller::where('is_winner', true)->count();
        $this->assertEquals(2, $totalWinners);

        // Step 7: Select third winner
        $winner3 = Caller::selectRandomWinnerByCpr();
        $this->assertNotNull($winner3);
        $this->assertTrue($winner3->is_winner);
        $this->assertNotEquals($winner1->cpr, $winner3->cpr);
        $this->assertNotEquals($winner2->cpr, $winner3->cpr);

        // Step 8: Verify all three are now winners
        $totalWinners = Caller::where('is_winner', true)->count();
        $this->assertEquals(3, $totalWinners);

        // Step 9: Verify no more winners can be selected
        $winner4 = Caller::selectRandomWinnerByCpr();
        $this->assertNull($winner4);
    }

    public function test_winner_selection_with_ineligible_callers()
    {
        // Create callers with various eligibility issues
        Caller::create([
            'name' => 'Already Winner',
            'phone' => '111111111',
            'cpr' => '111111111',
            'is_winner' => true, // Already a winner
            'status' => 'active',
        ]);

        // Note: We can't test null/empty CPR because database doesn't allow it
        // Instead, we test with callers who are already winners
        Caller::create([
            'name' => 'Already Winner 1',
            'phone' => '222222222',
            'cpr' => '222222222',
            'is_winner' => true, // Already a winner
            'status' => 'active',
        ]);

        Caller::create([
            'name' => 'Already Winner 2',
            'phone' => '333333333',
            'cpr' => '333333333',
            'is_winner' => true, // Already a winner
            'status' => 'active',
        ]);

        // Verify no eligible callers (all are already winners)
        $eligibleCallers = Caller::getEligibleCallers();
        $this->assertCount(0, $eligibleCallers);

        // Verify no winner can be selected
        $winner = Caller::selectRandomWinnerByCpr();
        $this->assertNull($winner);
    }

    public function test_cpr_uniqueness_guarantee()
    {
        // Create multiple callers
        for ($i = 1; $i <= 10; $i++) {
            Caller::create([
                'name' => 'Caller ' . $i,
                'phone' => '555' . str_pad($i, 7, '0', STR_PAD_LEFT),
                'cpr' => str_pad($i, 9, '0', STR_PAD_LEFT),
                'is_winner' => false,
                'status' => 'active',
            ]);
        }

        // Select multiple winners and verify CPR uniqueness
        $selectedCpRs = [];
        for ($i = 1; $i <= 5; $i++) {
            $winner = Caller::selectRandomWinnerByCpr();
            $this->assertNotNull($winner);
            $this->assertTrue($winner->is_winner);
            
            // Verify CPR is unique
            $this->assertNotContains($winner->cpr, $selectedCpRs);
            $selectedCpRs[] = $winner->cpr;
        }

        // Verify exactly 5 winners were selected
        $this->assertCount(5, $selectedCpRs);
        $this->assertCount(5, array_unique($selectedCpRs)); // All unique
        $this->assertEquals(5, Caller::where('is_winner', true)->count());
    }
}