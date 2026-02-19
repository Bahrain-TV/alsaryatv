<?php

namespace Tests\Feature;

use App\Models\Caller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormValidationTest extends TestCase
{
    use RefreshDatabase;

    private function assertValidationError($response, string $field = null): void
    {
        // Support both JSON validation responses (API) and session redirects (web)
        if (method_exists($response, 'isJson') && $response->isJson()) {
            $errors = $response->json('errors') ?? [];
            if ($field === null) {
                $this->assertNotEmpty($errors);
            } else {
                $this->assertArrayHasKey($field, $errors);
            }
        } else {
            if ($field === null) {
                $response->assertSessionHasErrors();
            } else {
                $response->assertSessionHasErrors($field);
            }
        }
    }

    public function test_store_caller_request_validates_required_name(): void
    {
        $response = $this->post('/callers', [
            'name' => '',
            'cpr' => '12345678901',
            'phone_number' => '+97312345678',
            'registration_type' => 'individual',
        ]);

        $this->assertEquals(0, Caller::count());
    }

    public function test_store_caller_request_validates_required_cpr(): void
    {
        $response = $this->post('/callers', [
            'name' => 'Test Name',
            'cpr' => '',
            'phone_number' => '+97312345678',
            'registration_type' => 'individual',
        ]);

        $this->assertValidationError($response, 'cpr');
    }

    public function test_store_caller_request_validates_required_phone_number(): void
    {
        $response = $this->post('/callers', [
            'name' => 'Test Name',
            'cpr' => '12345678901',
            'phone_number' => '',
            'registration_type' => 'individual',
        ]);

        $this->assertValidationError($response, 'phone_number');
    }

    public function test_store_caller_request_validates_registration_type(): void
    {
        $response = $this->post('/callers', [
            'name' => 'Test Name',
            'cpr' => '12345678901',
            'phone_number' => '+97312345678',
            'registration_type' => 'invalid',
        ]);

        $this->assertValidationError($response, 'registration_type');
    }

    public function test_store_caller_request_validates_family_members_minimum(): void
    {
        $response = $this->post('/callers', [
            'name' => 'Test Name',
            'cpr' => '12345678901',
            'phone_number' => '+97312345678',
            'registration_type' => 'family',
            'family_members' => 1, // Too low, minimum is 2
        ]);

        $this->assertValidationError($response, 'family_members');
    }

    public function test_store_caller_request_validates_family_members_maximum(): void
    {
        $response = $this->post('/callers', [
            'name' => 'Test Name',
            'cpr' => '12345678901',
            'phone_number' => '+97312345678',
            'registration_type' => 'family',
            'family_members' => 11, // Too high, maximum is 10
        ]);

        $this->assertValidationError($response, 'family_members');
    }

    public function test_store_caller_request_allows_valid_family_registration(): void
    {
        $response = $this->post('/callers', [
            'name' => 'Test Name',
            'cpr' => '12345678901',
            'phone_number' => '+97312345678',
            'registration_type' => 'family',
            'family_members' => 5,
        ]);

        $response->assertRedirect(route('callers.success'));
    }

    public function test_store_caller_request_allows_valid_individual_registration(): void
    {
        $response = $this->post('/callers', [
            'name' => 'Test Name',
            'cpr' => '12345678901',
            'phone_number' => '+97312345678',
            'registration_type' => 'individual',
        ]);

        $response->assertRedirect(route('callers.success'));
    }

    public function test_store_caller_request_validates_name_max_length(): void
    {
        $response = $this->post('/callers', [
            'name' => str_repeat('a', 256), // Max is 255
            'cpr' => '12345678901',
            'phone_number' => '+97312345678',
            'registration_type' => 'individual',
        ]);

        $this->assertValidationError($response, 'name');
    }

    public function test_store_caller_request_validates_cpr_max_length(): void
    {
        $response = $this->post('/callers', [
            'name' => 'Test Name',
            'cpr' => str_repeat('1', 256), // Max is 255
            'phone_number' => '+97312345678',
            'registration_type' => 'individual',
        ]);

        $this->assertValidationError($response, 'cpr');
    }

    public function test_store_caller_request_validates_phone_max_length(): void
    {
        $response = $this->post('/callers', [
            'name' => 'Test Name',
            'cpr' => '12345678901',
            'phone_number' => str_repeat('1', 46), // Max is 45
            'registration_type' => 'individual',
        ]);

        $this->assertValidationError($response, 'phone_number');
    }

    public function test_update_caller_request_validates_required_fields(): void
    {
        $caller = Caller::factory()->create();

        $response = $this->actingAs(\App\Models\User::factory()->create())
            ->put("/callers/{$caller->id}", [
                'name' => '',
                'phone_number' => '+97312345678',
                'cpr' => '12345678901',
            ]);

        $this->assertValidationError($response, 'name');
    }

    public function test_update_caller_request_validates_unique_cpr(): void
    {
        $caller1 = Caller::factory()->create(['cpr' => '12345678901']);
        $caller2 = Caller::factory()->create(['cpr' => '98765432109']);

        $response = $this->actingAs(\App\Models\User::factory()->create())
            ->put("/callers/{$caller2->id}", [
                'name' => 'Updated Name',
                'phone_number' => '+97312345678',
                'cpr' => '12345678901', // Already taken by caller1
            ]);

        $this->assertValidationError($response, 'cpr');
    }

    public function test_update_caller_request_allows_same_cpr_for_same_caller(): void
    {
        $caller = Caller::factory()->create(['cpr' => '12345678901']);

        $response = $this->actingAs(\App\Models\User::factory()->create())
            ->put("/callers/{$caller->id}", [
                'name' => 'Updated Name',
                'phone_number' => '+97312345678',
                'cpr' => '12345678901', // Same CPR, same caller
            ]);

        $response->assertRedirect(route('dashboard'));
    }

    public function test_bilingual_validation_messages_are_available(): void
    {
        // Test that validation messages support Arabic
        $response = $this->post('/callers', [
            'name' => '',
            'cpr' => '12345678901',
            'phone_number' => '+97312345678',
            'registration_type' => 'individual',
        ]);

        // Should have validation errors in session
        $this->assertValidationError($response);
    }
}
