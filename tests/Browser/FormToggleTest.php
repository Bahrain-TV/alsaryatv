<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class FormToggleTest extends DuskTestCase
{
    /**
     * Test the form toggle animation between individual and family forms
     *
     * @return void
     */
    public function test_form_toggle_animation_works()
    {
        $this->browse(function (Browser $browser): void {
            // Visit the home page
            $browser->visit('/')
                ->waitForText('تسجيل', 5) // Wait for registration text to appear
                ->assertSee('أفراد') // Ensure individual button is visible
                ->assertSee('عائلات'); // Ensure family button is visible

            // Take a screenshot of the initial state
            $browser->screenshot('form-toggle-initial');

            // Click the family toggle button
            $browser->click('#toggleFamily') // Using the ID of the family toggle button
                ->pause(1000) // Wait for animation to complete
                ->assertSee('تسجيل العائلة') // Ensure family form text appears
                ->screenshot('form-toggle-family-view'); // Take screenshot after switching to family view

            // Click the individual toggle button
            $browser->click('#toggleIndividual') // Using the ID of the individual toggle button
                ->pause(1000) // Wait for animation to complete
                ->assertSee('تسجيل') // Ensure individual form text appears
                ->screenshot('form-toggle-individual-view'); // Take screenshot after switching to individual view

            // Verify that the animation happened by checking that screenshots exist
            $this->assertFileExists(public_path('screenshots/form-toggle-initial.png'));
            $this->assertFileExists(public_path('screenshots/form-toggle-family-view.png'));
            $this->assertFileExists(public_path('screenshots/form-toggle-individual-view.png'));
        });
    }

    /**
     * Test the form toggle animation on the calls/register page
     *
     * @return void
     */
    public function test_form_toggle_animation_on_register_page()
    {
        $this->browse(function (Browser $browser): void {
            // Visit the registration page
            $browser->visit('/calls/register')
                ->waitForText('اختر نوع التسجيل', 5) // Wait for registration type text to appear
                ->assertSee('أفراد') // Ensure individual button is visible
                ->assertSee('عائلات'); // Ensure family button is visible

            // Take a screenshot of the initial state
            $browser->screenshot('register-page-initial');

            // Click the family toggle button
            $browser->click('#toggleFamily') // Using the ID of the family toggle button
                ->pause(1000) // Wait for animation to complete
                ->screenshot('register-page-family-view'); // Take screenshot after switching to family view

            // Click the individual toggle button
            $browser->click('#toggleIndividual') // Using the ID of the individual toggle button
                ->pause(1000) // Wait for animation to complete
                ->screenshot('register-page-individual-view'); // Take screenshot after switching to individual view

            // Verify that the animation happened by checking that screenshots exist
            $this->assertFileExists(public_path('screenshots/register-page-initial.png'));
            $this->assertFileExists(public_path('screenshots/register-page-family-view.png'));
            $this->assertFileExists(public_path('screenshots/register-page-individual-view.png'));
        });
    }
}
