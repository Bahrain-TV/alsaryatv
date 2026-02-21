<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ThankYouScreenCounterTest extends TestCase
{
    /**
     * Test that thank-you-screen.js file exists and contains counter functionality.
     */
    #[Test]
    public function thank_you_screen_js_file_exists_with_counter(): void
    {
        $jsPath = resource_path('js/thank-you-screen.js');
        
        $this->assertFileExists($jsPath, 'thank-you-screen.js should exist');
        
        $content = file_get_contents($jsPath);
        
        // Verify counter-related code exists
        $this->assertStringContainsString('userHits', $content, 
            'Should have userHits property');
        $this->assertStringContainsString('totalHits', $content, 
            'Should have totalHits property');
        $this->assertStringContainsString('animateHitsCounter', $content, 
            'Should have animateHitsCounter method');
        $this->assertStringContainsString('thank-you-hits-counter', $content, 
            'Should have counter element ID');
    }

    /**
     * Test that thank-you-screen.css file exists with counter styles.
     */
    #[Test]
    public function thank_you_screen_css_file_exists_with_styles(): void
    {
        $cssPath = resource_path('css/thank-you-screen.css');
        
        $this->assertFileExists($cssPath, 'thank-you-screen.css should exist');
        
        $content = file_get_contents($cssPath);
        
        // Verify counter-related styles exist
        $this->assertStringContainsString('.thank-you-stats', $content, 
            'Should have .thank-you-stats class');
        $this->assertStringContainsString('.stat-value', $content, 
            'Should have .stat-value class');
        $this->assertStringContainsString('.stat-label', $content, 
            'Should have .stat-label class');
    }

    /**
     * Test that ThankYouScreen is imported in app.js.
     */
    #[Test]
    public function thank_you_screen_is_imported_in_app_js(): void
    {
        $appJsPath = resource_path('js/app.js');
        
        $this->assertFileExists($appJsPath, 'app.js should exist');
        
        $content = file_get_contents($appJsPath);
        
        $this->assertStringContainsString(
            "import ThankYouScreen from './thank-you-screen'", 
            $content,
            'app.js should import ThankYouScreen'
        );
        
        $this->assertStringContainsString(
            'window.ThankYouScreen = ThankYouScreen',
            $content,
            'app.js should expose ThankYouScreen globally'
        );
    }

    /**
     * Test that thank-you-screen.js is in vite.config.js.
     */
    #[Test]
    public function thank_you_screen_is_in_vite_config(): void
    {
        $viteConfigPath = base_path('vite.config.js');
        
        $this->assertFileExists($viteConfigPath, 'vite.config.js should exist');
        
        $content = file_get_contents($viteConfigPath);
        
        $this->assertStringContainsString(
            "'resources/js/thank-you-screen.js'",
            $content,
            'vite.config.js should include thank-you-screen.js as entry point'
        );
    }

    /**
     * Test that the counter animation logic is correct.
     */
    #[Test]
    public function counter_animation_logic_is_correct(): void
    {
        $jsPath = resource_path('js/thank-you-screen.js');
        $content = file_get_contents($jsPath);
        
        // Verify the counter starts at 10% of final value
        $this->assertStringContainsString(
            'Math.max(1, Math.floor(this.userHits * 0.1))',
            $content,
            'Counter should start at 10% of final value'
        );
        
        // Verify animation duration is 1.5 seconds
        $this->assertStringContainsString(
            'const duration = 1500',
            $content,
            'Animation duration should be 1500ms (1.5 seconds)'
        );
        
        // Verify counter interval is 30ms
        $this->assertStringContainsString(
            'const interval = 30',
            $content,
            'Counter interval should be 30ms'
        );
        
        // Verify counter stops at final value
        $this->assertStringContainsString(
            'if (currentCount >= this.userHits)',
            $content,
            'Counter should stop when reaching userHits'
        );
    }

    /**
     * Test that stats are only shown when userHits > 0.
     */
    #[Test]
    public function stats_only_show_when_user_hits_positive(): void
    {
        $jsPath = resource_path('js/thank-you-screen.js');
        $content = file_get_contents($jsPath);
        
        // Verify conditional display
        $this->assertStringContainsString(
            'if (this.userHits > 0)',
            $content,
            'Stats should only show when userHits > 0'
        );
        
        // Verify animateHitsCounter guard clause
        $this->assertStringContainsString(
            'if (!hitsCounter || this.userHits <= 0) return',
            $content,
            'Counter animation should not run when userHits <= 0'
        );
    }

    /**
     * Test that the built assets include thank-you-screen.
     */
    #[Test]
    public function built_assets_include_thank_you_screen(): void
    {
        $manifestPath = public_path('build/manifest.json');
        
        if (! file_exists($manifestPath)) {
            $this->markTestSkipped('Build manifest not found. Run `npm run build` first.');
        }
        
        $manifest = json_decode(file_get_contents($manifestPath), true);
        
        $this->assertArrayHasKey('resources/js/thank-you-screen.js', $manifest,
            'Build manifest should include thank-you-screen.js');
        
        $this->assertArrayHasKey('resources/css/thank-you-screen.css', $manifest,
            'Build manifest should include thank-you-screen.css');
    }

    /**
     * Test that success.blade.php has inline counter as fallback.
     */
    #[Test]
    public function success_blade_has_inline_counter_fallback(): void
    {
        $bladePath = resource_path('views/callers/success.blade.php');
        
        $this->assertFileExists($bladePath, 'success.blade.php should exist');
        
        $content = file_get_contents($bladePath);
        
        // Verify inline counter animation exists
        $this->assertStringContainsString(
            'const userHits = {{ session(\'userHits\', 1) }}',
            $content,
            'Should get userHits from session'
        );
        
        $this->assertStringContainsString(
            'hitsCounter.textContent = Math.floor(currentCount)',
            $content,
            'Should animate counter value'
        );
    }
}
