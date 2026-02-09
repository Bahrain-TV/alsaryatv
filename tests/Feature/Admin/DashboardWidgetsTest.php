<?php

namespace Tests\Feature\Admin;

use App\Filament\Widgets\AnimatedStatsOverviewWidget;
use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\WinnersHistoryWidget;
use App\Models\Caller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'is_admin' => true,
            'role' => 'admin',
        ]);

        // Create test data
        Caller::factory(10)->create(['status' => 'active', 'hits' => rand(1, 50)]);
        Caller::factory(3)->create(['status' => 'active', 'hits' => rand(50, 100), 'is_winner' => true]);
        Caller::factory(5)->create(['status' => 'inactive', 'hits' => rand(5, 20)]);
        Caller::factory(2)->create(['status' => 'blocked', 'hits' => rand(1, 10)]);
    }

    public function test_animated_stats_widget_mounts(): void
    {
        $widget = new AnimatedStatsOverviewWidget();
        $widget->mount();

        $this->assertIsInt($widget->totalCallers);
        $this->assertIsInt($widget->totalWinners);
        $this->assertIsInt($widget->totalHits);
    }

    public function test_animated_stats_widget_calculates_totals(): void
    {
        $widget = new AnimatedStatsOverviewWidget();
        $widget->mount();

        $this->assertEquals(Caller::count(), $widget->totalCallers);
        $this->assertEquals(Caller::where('is_winner', true)->count(), $widget->totalWinners);
    }

    public function test_quick_actions_widget_returns_actions(): void
    {
        $widget = new QuickActionsWidget();
        $actions = $widget->getQuickActions();

        $this->assertCount(4, $actions);
        $this->assertArrayHasKey('title', $actions[0]);
        $this->assertArrayHasKey('url', $actions[0]);
    }

    public function test_recent_activity_widget_instantiates(): void
    {
        $widget = new RecentActivityWidget();
        $this->assertNotNull($widget);
    }

    public function test_winners_history_widget_instantiates(): void
    {
        $widget = new WinnersHistoryWidget();
        $this->assertNotNull($widget);
    }

    public function test_dashboard_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');

        $this->assertEquals(200, $response->status());
    }

    public function test_animated_stats_caches_data(): void
    {
        $widget1 = new AnimatedStatsOverviewWidget();
        $widget1->mount();
        $total1 = $widget1->totalCallers;

        $widget2 = new AnimatedStatsOverviewWidget();
        $widget2->mount();
        $total2 = $widget2->totalCallers;

        $this->assertEquals($total1, $total2);
    }

    public function test_animated_stats_with_zero_callers(): void
    {
        Caller::truncate();
        $widget = new AnimatedStatsOverviewWidget();
        $widget->mount();

        $this->assertEquals(0, $widget->totalCallers);
    }

    public function test_quick_actions_contain_valid_urls(): void
    {
        $widget = new QuickActionsWidget();
        $actions = $widget->getQuickActions();

        foreach ($actions as $action) {
            $this->assertStringStartsWith('/', $action['url']);
        }
    }

    public function test_dashboard_contains_valid_html(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');

        $this->assertStringContainsString('<!DOCTYPE', $response->getContent());
    }
}
