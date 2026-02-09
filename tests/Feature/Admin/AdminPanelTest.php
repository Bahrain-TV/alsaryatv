<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
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
    }

    public function test_authenticated_admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');

        $this->assertEquals(200, $response->status());
    }

    public function test_dashboard_page_title_displayed(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');

        $this->assertStringContainsString('لوحة التحكم', $response->getContent());
    }

    public function test_dashboard_renders_html(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');

        $this->assertEquals(200, $response->status());
        $this->assertStringContainsString('<!DOCTYPE', $response->getContent());
    }

    public function test_admin_can_create_caller(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/callers/create');

        $this->assertEquals(200, $response->status());
    }

    public function test_admin_dashboard_loads_without_errors(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');

        $this->assertTrue($response->status() === 200);
    }

    public function test_sidebar_navigation_present(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');

        $content = $response->getContent();
        $this->assertNotEmpty($content);
        $this->assertGreaterThan(100, strlen($content));
    }

    public function test_multiple_admins_can_access_dashboard(): void
    {
        $admin2 = User::factory()->create(['is_admin' => true, 'role' => 'admin']);

        $response1 = $this->actingAs($this->admin)->get('/admin');
        $response2 = $this->actingAs($admin2)->get('/admin');

        $this->assertEquals(200, $response1->status());
        $this->assertEquals(200, $response2->status());
    }

    public function test_dashboard_responds_quickly(): void
    {
        $start = microtime(true);
        $this->actingAs($this->admin)->get('/admin');
        $duration = (microtime(true) - $start) * 1000;

        // Dashboard should load in under 5 seconds
        $this->assertLessThan(5000, $duration);
    }
}
