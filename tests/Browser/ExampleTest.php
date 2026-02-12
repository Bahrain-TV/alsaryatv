<?php

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class WelcomeScreenTest extends DuskTestCase
{
    /**
     * Test that the welcome screen loads with proper content
     */
    public function test_welcome_screen_loads(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/')
                ->waitFor('body', 10)
                ->assertTitleContains('السارية');
        });
    }

    /**
     * Test that the splash screen is accessible
     */
    public function test_splash_screen_is_accessible(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/splash')
                ->assertSee('السارية');
        });
    }
}
