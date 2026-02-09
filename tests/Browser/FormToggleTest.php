<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class FormToggleTest extends DuskTestCase
{
    /**
     * Test the form toggle between individual and family forms on the home page.
     */
    public function test_form_toggle_animation_works(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/')
                ->waitForText('سجل الآن', 10)
                ->assertSee('أفراد')
                ->assertSee('عائلات');

            // Screenshot: Individual form (default)
            $browser->screenshot('home-individual-form');

            // Toggle to family form
            $browser->click('#toggleFamily')
                ->pause(1000)
                ->assertSee('سجل عائلتك الآن')
                ->screenshot('home-family-form');

            // Toggle back to individual form
            $browser->click('#toggleIndividual')
                ->pause(1000)
                ->assertSee('سجل الآن للمشاركة')
                ->screenshot('home-individual-form-restored');
        });
    }

    /**
     * Test the form toggle on the /register page.
     */
    public function test_form_toggle_animation_on_register_page(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/register')
                ->waitForText('اختر نوع التسجيل', 10)
                ->assertSee('أفراد')
                ->assertSee('عائلات');

            // Screenshot: Individual form (default)
            $browser->screenshot('register-individual-form');

            // Toggle to family form
            $browser->click('#toggleFamily')
                ->pause(1000)
                ->screenshot('register-family-form');

            // Toggle back to individual
            $browser->click('#toggleIndividual')
                ->pause(1000)
                ->screenshot('register-individual-form-restored');
        });
    }

    /**
     * Test dashboard screenshot (authenticated).
     */
    public function test_dashboard_screenshot(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->loginAs(\App\Models\User::first())
                ->visit('/dashboard')
                ->pause(2000)
                ->screenshot('dashboard');
        });
    }
}
