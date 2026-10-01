<?php

namespace Tests\Feature;

use App\Models\Caller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CallerStatsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_stats_returns_correct_structure(): void
    {
        $response = $this->getJson('/api/caller-stats');

        $response->assertOk()
            ->assertJsonStructure([
                'total_callers',
                'total_hits',
                'today_callers',
                'total_winners',
            ]);
    }

    public function test_get_stats_returns_zero_when_empty(): void
    {
        $response = $this->getJson('/api/caller-stats');

        $response->assertOk()
            ->assertJson([
                'total_callers' => 0,
                'total_hits' => 0,
                'today_callers' => 0,
                'total_winners' => 0,
            ]);
    }

    public function test_get_stats_reflects_caller_data(): void
    {
        Caller::factory()->count(3)->create(['hits' => 5, 'is_winner' => false]);
        Caller::factory()->count(2)->create(['hits' => 10, 'is_winner' => true]);

        $response = $this->getJson('/api/caller-stats');

        $response->assertOk()
            ->assertJson([
                'total_callers' => 5,
                'total_hits' => 35,
                'total_winners' => 2,
            ]);
    }

    public function test_get_stats_counts_today_callers(): void
    {
        // Created today
        Caller::factory()->count(2)->create(['created_at' => now()]);

        // Created yesterday
        Caller::factory()->create(['created_at' => now()->subDay()]);

        $response = $this->getJson('/api/caller-stats');

        $response->assertOk()
            ->assertJson(['today_callers' => 2]);
    }
}
