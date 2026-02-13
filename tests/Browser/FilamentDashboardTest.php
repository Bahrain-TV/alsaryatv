<?php

namespace Tests\Browser;

use App\Models\Caller;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class FilamentDashboardTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        
        // Create test data
        Caller::factory()
            ->count(20)
            ->create(['status' => 'active']);
        
        Caller::factory()
            ->count(5)
            ->create(['status' => 'inactive']);
        
        Caller::factory()
            ->count(2)
            ->create(['is_winner' => true, 'status' => 'active']);
    }

    public function test_filament_dashboard_loads_successfully(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/admin')
                ->assertAuthenticated(guard: null)
                ->waitForText('لوحة التحكم', 10);
        });
    }

    public function test_filament_dashboard_displays_all_widgets(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/admin/login')
                ->type('email', 'admin@test.com')
                ->type('password', 'password')
                ->press('دخول')
                ->waitFor('[data-testid="dashboard"]', 10)
                ->assertPresent('.filament-widget')
                ->screenshot('dashboard-full');
        });
    }

    public function test_dashboard_displays_quick_actions(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/admin/login')
                ->type('email', 'admin@test.com')
                ->type('password', 'password')
                ->press('دخول')
                ->waitFor('[data-testid="dashboard"]', 10)
                ->assertSeeIn('body', 'اختيار فائز يدوي')
                ->assertSeeIn('body', 'إضافة متصل جديد')
                ->screenshot('dashboard-quick-actions');
        });
    }

    public function test_dashboard_displays_animated_stats(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/admin/login')
                ->type('email', 'admin@test.com')
                ->type('password', 'password')
                ->press('دخول')
                ->waitFor('[data-testid="dashboard"]', 10)
                ->waitFor('.filament-widgets-stats-overview-widget', 10)
                ->screenshot('dashboard-animated-stats');
        });
    }

    public function test_dashboard_displays_charts(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/admin/login')
                ->type('email', 'admin@test.com')
                ->type('password', 'password')
                ->press('دخول')
                ->waitFor('[data-testid="dashboard"]', 10)
                ->waitFor('canvas', 10)  // Wait for Chart.js canvases
                ->screenshot('dashboard-charts');
        });
    }

    public function test_dashboard_displays_recent_activity(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/admin/login')
                ->type('email', 'admin@test.com')
                ->type('password', 'password')
                ->press('دخول')
                ->waitFor('[data-testid="dashboard"]', 10)
                ->scrollTo('.filament-widgets-table-widget:nth-child(2)')
                ->screenshot('dashboard-recent-activity');
        });
    }

    public function test_dashboard_displays_winners(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/admin/login')
                ->type('email', 'admin@test.com')
                ->type('password', 'password')
                ->press('دخول')
                ->waitFor('[data-testid="dashboard"]', 10)
                ->scrollToBottom()
                ->screenshot('dashboard-winners');
        });
    }

    public function test_no_console_errors(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/admin/login')
                ->type('email', 'admin@test.com')
                ->type('password', 'password')
                ->press('دخول')
                ->waitFor('[data-testid="dashboard"]', 10)
                ->assertConsoleLogsMissing([
                    'error',
                    'Error',
                    'ERR',
                    'warn',
                ]);
        });
    }
}
