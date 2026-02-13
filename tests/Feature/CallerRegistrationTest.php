<?php

namespace Tests\Feature;

use App\Models\Caller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CallerRegistrationTest extends TestCase
{
    use RefreshDatabase;
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clear callers before each test
        Caller::truncate();
    }

    public function test_individual_registration_form_can_be_submitted(): void
    {
        // Get CSRF token from the welcome page first
        $welcomeResponse = $this->get('/');
        $csrfToken = $this->getCsrfToken($welcomeResponse);

        $response = $this->post('/callers', [
            'name' => 'أحمد محمد',
            'cpr' => '12345678901',
            'phone_number' => '+97366123456',
            'registration_type' => 'individual',
            '_token' => $csrfToken,
        ]);

        // Should redirect to success page
        $response->assertRedirect(route('callers.success'));

        // Verify caller was created
        $this->assertTrue(Caller::where('cpr', '12345678901')->exists());

        // Verify caller has correct attributes
        $caller = Caller::where('cpr', '12345678901')->first();
        $this->assertEquals('أحمد محمد', $caller->name);
        $this->assertEquals('+97366123456', $caller->phone);
        $this->assertEquals(1, $caller->hits);
        $this->assertEquals('active', $caller->status);
    }

    private function getCsrfToken($response): string
    {
        // Extract CSRF token from the HTML response
        preg_match('/<meta name="csrf-token" content="([^"]+)"/', $response->getContent(), $matches);

        return $matches[1] ?? '';
    }

    public function test_family_registration_form_can_be_submitted(): void
    {
        // Get CSRF token
        $welcomeResponse = $this->get('/');
        $csrfToken = $this->getCsrfToken($welcomeResponse);

        $response = $this->post('/callers', [
            'name' => 'علي عبدالله',
            'cpr' => '98765432109',
            'phone_number' => '+97365999888',
            'registration_type' => 'family',
            'family_name' => 'عائلة عبدالله',
            'family_members' => 4,
            '_token' => $csrfToken,
        ]);

        // Should redirect to success page
        $response->assertRedirect(route('callers.success'));

        // Verify caller was created
        $this->assertTrue(Caller::where('cpr', '98765432109')->exists());

        $caller = Caller::where('cpr', '98765432109')->first();
        $this->assertEquals('علي عبدالله', $caller->name);
        $this->assertEquals('+97365999888', $caller->phone);
        $this->assertEquals(1, $caller->hits);
    }

    public function test_existing_caller_can_register_again_and_increment_hits(): void
    {
        // Create initial caller
        $initialCaller = Caller::factory()->create([
            'cpr' => '11111111111',
            'hits' => 2,
        ]);

        // Get CSRF token
        $welcomeResponse = $this->get('/');
        $csrfToken = $this->getCsrfToken($welcomeResponse);

        // Submit registration again with same CPR
        $response = $this->post('/callers', [
            'name' => $initialCaller->name,
            'cpr' => '11111111111',
            'phone_number' => $initialCaller->phone,
            'registration_type' => 'individual',
            '_token' => $csrfToken,
        ]);

        $response->assertRedirect(route('callers.success'));

        // Verify hits were incremented
        $updatedCaller = Caller::where('cpr', '11111111111')->first();
        $this->assertEquals(3, $updatedCaller->hits);

        // Verify only one caller record exists
        $this->assertEquals(1, Caller::where('cpr', '11111111111')->count());
    }

    public function test_registration_stores_ip_address(): void
    {
        // Get CSRF token
        $welcomeResponse = $this->get('/');
        $csrfToken = $this->getCsrfToken($welcomeResponse);

        $response = $this->post('/callers', [
            'name' => 'فاطمة علي',
            'cpr' => '22222222222',
            'phone_number' => '+97367111222',
            'registration_type' => 'individual',
            '_token' => $csrfToken,
        ]);

        $response->assertRedirect(route('callers.success'));

        $caller = Caller::where('cpr', '22222222222')->first();
        $this->assertNotNull($caller->ip_address);
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+\.\d+$/', $caller->ip_address);
    }

    public function test_registration_with_invalid_data_fails_validation(): void
    {
        // Get CSRF token
        $welcomeResponse = $this->get('/');
        $csrfToken = $this->getCsrfToken($welcomeResponse);

        $response = $this->post('/callers', [
            'name' => '', // Missing required name
            'cpr' => '33333333333',
            'phone_number' => '+97368999111',
            'registration_type' => 'individual',
            '_token' => $csrfToken,
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertFalse(Caller::where('cpr', '33333333333')->exists());
    }

    public function test_registration_type_validation_works(): void
    {
        // Get CSRF token
        $welcomeResponse = $this->get('/');
        $csrfToken = $this->getCsrfToken($welcomeResponse);

        $response = $this->post('/callers', [
            'name' => 'محمود حسن',
            'cpr' => '44444444444',
            'phone_number' => '+97369222333',
            'registration_type' => 'invalid-type', // Invalid registration type
            '_token' => $csrfToken,
        ]);

        $response->assertSessionHasErrors('registration_type');
        $this->assertFalse(Caller::where('cpr', '44444444444')->exists());
    }

    public function test_family_registration_requires_family_members_count_between_2_and_10(): void
    {
        // Get CSRF token
        $welcomeResponse = $this->get('/');
        $csrfToken = $this->getCsrfToken($welcomeResponse);

        // Test with family_members = 1 (too few)
        $response = $this->post('/callers', [
            'name' => 'سارة محمد',
            'cpr' => '55555555555',
            'phone_number' => '+97361111222',
            'registration_type' => 'family',
            'family_members' => 1, // Invalid: minimum is 2
            '_token' => $csrfToken,
        ]);

        $response->assertSessionHasErrors('family_members');
        $this->assertFalse(Caller::where('cpr', '55555555555')->exists());

        // Get new CSRF token for second request
        $welcomeResponse2 = $this->get('/');
        $csrfToken2 = $this->getCsrfToken($welcomeResponse2);

        // Test with family_members = 11 (too many)
        $response = $this->post('/callers', [
            'name' => 'سارة محمد',
            'cpr' => '66666666666',
            'phone_number' => '+97361111222',
            'registration_type' => 'family',
            'family_members' => 11, // Invalid: maximum is 10
            '_token' => $csrfToken2,
        ]);

        $response->assertSessionHasErrors('family_members');
        $this->assertFalse(Caller::where('cpr', '66666666666')->exists());
    }

    public function test_seeded_callers_are_available_in_database(): void
    {
        // Seed sample callers
        Caller::factory()->count(5)->create();

        // Verify callers were created
        $this->assertEquals(5, Caller::count());

        // Verify each has required fields
        Caller::all()->each(function (Caller $caller): void {
            $this->assertNotNull($caller->name);
            $this->assertNotNull($caller->cpr);
            $this->assertNotNull($caller->phone);
            $this->assertGreaterThanOrEqual(0, $caller->hits);
        });
    }

    public function test_seeded_winners_are_properly_marked(): void
    {
        // Create regular callers
        Caller::factory()->count(3)->create(['is_winner' => false]);

        // Create winners
        Caller::factory()->count(2)->create(['is_winner' => true]);

        $this->assertEquals(2, Caller::winners()->count());
        $this->assertEquals(3, Caller::where('is_winner', false)->count());
    }
}
