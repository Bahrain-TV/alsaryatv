<?php

namespace Tests\Unit;

use Tests\TestCase;

class FormAnimationTest extends TestCase
{
    /**
     * Test that the form toggle animation code is properly implemented
     * This verifies the GSAP animation code exists in the blade templates
     */
    public function test_form_toggle_animation_code_exists()
    {
        // Check that the calls/form-toggle.blade.php contains the spinning animation code
        $formTogglePath = resource_path('views/calls/form-toggle.blade.php');
        $this->assertFileExists($formTogglePath);

        $content = file_get_contents($formTogglePath);

        // Check for GSAP timeline usage
        $this->assertStringContainsString('gsap.timeline', $content);
        $this->assertStringContainsString('rotationY', $content);
        $this->assertStringContainsString('duration', $content);

        // Check for the toggle button IDs
        $this->assertStringContainsString('toggleIndividual', $content);
        $this->assertStringContainsString('toggleFamily', $content);

        // Check for animation properties
        $this->assertStringContainsString('rotationY: -90', $content);
        $this->assertStringContainsString('x: -100', $content);
        $this->assertStringNotContainsString('stagger: 0.05', $content); // No longer using stagger on individual inputs

        echo "✓ Form toggle animation code verified in calls/form-toggle.blade.php\n";
    }

    /**
     * Test that the welcome page animation code is properly implemented
     */
    public function test_welcome_page_animation_code_exists()
    {
        // Check that the welcome.blade.php contains the spinning animation code
        $welcomePath = resource_path('views/welcome.blade.php');
        $this->assertFileExists($welcomePath);

        $content = file_get_contents($welcomePath);

        // Check for GSAP timeline usage in the registration toggle section
        $this->assertStringContainsString('gsap.timeline', $content);
        $this->assertStringContainsString('rotationY', $content);

        // Check for the toggle button IDs
        $this->assertStringContainsString('individual-toggle', $content);
        $this->assertStringContainsString('family-toggle', $content);

        // Check for animation properties
        $this->assertStringContainsString('rotationY: -90', $content);
        $this->assertStringContainsString('x: -100', $content);
        $this->assertStringNotContainsString('stagger: 0.05', $content); // No longer using stagger on individual inputs

        echo "✓ Welcome page animation code verified in welcome.blade.php\n";
    }

    /**
     * Test that the tornado effect has been removed
     */
    public function test_tornado_effect_removed()
    {
        // Check that the calls/form-toggle.blade.php does not contain tornado code
        $formTogglePath = resource_path('views/calls/form-toggle.blade.php');
        $this->assertFileExists($formTogglePath);

        $content = file_get_contents($formTogglePath);

        // Check that tornado-related code is removed
        $this->assertStringNotContainsString('TornadoEffect', $content);
        $this->assertStringNotContainsString('tornado-active', $content);
        $this->assertStringNotContainsString('initialize', $content);
        $this->assertStringNotContainsString('particle', $content);

        echo "✓ Tornado effect successfully removed from calls/form-toggle.blade.php\n";

        // Also check welcome.blade.php
        $welcomePath = resource_path('views/welcome.blade.php');
        $this->assertFileExists($welcomePath);

        $welcomeContent = file_get_contents($welcomePath);

        // Check that tornado-related code is removed
        $this->assertStringNotContainsString('TornadoEffect', $welcomeContent);
        $this->assertStringNotContainsString('switchFormWithTornado', $welcomeContent);

        echo "✓ Tornado effect successfully removed from welcome.blade.php\n";
    }
}
