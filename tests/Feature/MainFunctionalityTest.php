<?php

namespace Tests\Feature;

use App\Models\Caller;
use App\Services\CprHashingService;
use App\Providers\HitsCounter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class MainFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear all callers before each test
        Caller::truncate();
    }

    public function test_complete_registration_flow(): void
    {
        // Test the complete registration flow from form access to success page
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('سجّل الآن'); // Check for registration button
        
        // Get CSRF token from the response
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        $csrfTokenNodes = $xpath->query("//meta[@name='csrf-token']");
        
        if ($csrfTokenNodes->length > 0) {
            $csrfToken = $csrfTokenNodes->item(0)->getAttribute('content');
        } else {
            // Alternative method to get CSRF token
            preg_match('/<meta name="csrf-token" content="([^"]+)"/', $response->getContent(), $matches);
            $csrfToken = $matches[1] ?? '';
        }

        // Submit registration form
        $response = $this->post('/callers', [
            'name' => 'محمد أحمد',
            'cpr' => '12345678901',
            'phone_number' => '+97366123456',
            'registration_type' => 'individual',
            '_token' => $csrfToken,
        ]);

        // Should redirect to success page
        $response->assertRedirect(route('callers.success'));

        // Verify caller was created in the database
        $this->assertTrue(Caller::where('cpr', '12345678901')->exists());
        
        $caller = Caller::where('cpr', '12345678901')->first();
        $this->assertEquals('محمد أحمد', $caller->name);
        $this->assertEquals('+97366123456', $caller->phone);
        $this->assertEquals(1, $caller->hits);
        $this->assertEquals('active', $caller->status);
    }

    public function test_duplicate_registration_increments_hits(): void
    {
        // Create initial registration
        $initialCaller = Caller::factory()->create([
            'cpr' => '11111111111',
            'hits' => 3,
        ]);

        // Get CSRF token
        $response = $this->get('/');
        preg_match('/<meta name="csrf-token" content="([^"]+)"/', $response->getContent(), $matches);
        $csrfToken = $matches[1] ?? '';

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
        $this->assertEquals(4, $updatedCaller->hits);

        // Verify only one caller record exists
        $this->assertEquals(1, Caller::where('cpr', '11111111111')->count());
    }

    public function test_cpr_based_winner_selection(): void
    {
        // Create multiple eligible callers
        $callers = Caller::factory()->count(5)->create([
            'is_winner' => false,
            'status' => 'active',
        ]);

        // Select a random winner
        $winner = Caller::selectRandomWinnerByCpr();

        // Verify a winner was selected
        $this->assertNotNull($winner);
        $this->assertTrue($winner->is_winner);

        // Verify only one winner was selected
        $this->assertEquals(1, Caller::winners()->count());
    }

    public function test_cpr_hashing_and_verification(): void
    {
        $service = new CprHashingService();
        $cpr = '12345678901';

        // Test hashing
        $hashed = $service->hashCpr($cpr);
        $this->assertNotEquals($cpr, $hashed);
        $this->assertNotEmpty($hashed);

        // Test verification
        $isValid = $service->verifyCpr($cpr, $hashed);
        $this->assertTrue($isValid);

        // Test invalid verification
        $isInvalid = $service->verifyCpr('98765432109', $hashed);
        $this->assertFalse($isInvalid);
    }

    public function test_cpr_masking(): void
    {
        $service = new CprHashingService();
        $cpr = '12345678901';
        $masked = $service->maskCpr($cpr);

        // First 3 digits should show
        $this->assertStringStartsWith('123', $masked);

        // Rest should be masked
        $this->assertStringContainsString('*', $masked);
        $this->assertEquals(strlen($cpr), strlen($masked));
    }

    public function test_hit_counter_functionality(): void
    {
        // Test initial hit count
        $initialHits = HitsCounter::getHits();
        $this->assertIsInt($initialHits);

        // Increment user hits
        $cpr = '12345678901';
        $userHits = HitsCounter::getUserHits($cpr);
        $this->assertIsInt($userHits);

        // Test incrementing hits for a caller
        $caller = Caller::factory()->create(['cpr' => $cpr, 'hits' => 0]);
        $caller->incrementHits();

        $updatedCaller = $caller->fresh();
        $this->assertEquals(1, $updatedCaller->hits);
        $this->assertNotNull($updatedCaller->last_hit);
    }

    public function test_rate_limiting_by_cpr(): void
    {
        $cpr = '12345678901';
        $key = 'caller-registration:'.$cpr;

        // Clear any existing rate limit
        RateLimiter::clear($key);

        // Test that we can register initially
        $this->assertFalse(RateLimiter::tooManyAttempts($key, 1));

        // Simulate registration attempt
        RateLimiter::hit($key, 300); // 5 minute window

        // Now we should be limited
        $this->assertTrue(RateLimiter::tooManyAttempts($key, 1));
    }

    public function test_rate_limiting_by_ip(): void
    {
        $ip = '192.168.1.1';
        $key = 'caller-registration-ip:'.$ip;

        // Clear any existing rate limit
        RateLimiter::clear($key);

        // Test that we can register initially
        $this->assertFalse(RateLimiter::tooManyAttempts($key, 10));

        // Simulate multiple registration attempts
        for ($i = 0; $i < 10; $i++) {
            RateLimiter::hit($key, 3600); // 1 hour window
        }

        // Now we should be limited
        $this->assertTrue(RateLimiter::tooManyAttempts($key, 10));
    }

    public function test_eligible_callers_scope(): void
    {
        // Create eligible callers
        $eligibleCallers = Caller::factory()->count(3)->create([
            'is_winner' => false,
            'status' => 'active',
            'cpr' => 'valid_cpr',
        ]);

        // Create ineligible callers
        Caller::factory()->create([
            'is_winner' => true,  // Winner - not eligible
            'status' => 'active',
            'cpr' => 'valid_cpr',
        ]);

        Caller::factory()->create([
            'is_winner' => false,
            'status' => 'inactive',  // Inactive - not eligible
            'cpr' => 'valid_cpr',
        ]);

        Caller::factory()->create([
            'is_winner' => false,
            'status' => 'active',
            'cpr' => '',  // Empty CPR - not eligible
        ]);

        $eligible = Caller::eligible()->get();

        // Only the 3 eligible callers should be returned
        $this->assertEquals(3, $eligible->count());
        
        foreach ($eligible as $caller) {
            $this->assertFalse($caller->is_winner);
            $this->assertEquals('active', $caller->status);
            $this->assertNotEmpty($caller->cpr);
        }
    }

    public function test_registration_success_page_shows_correct_data(): void
    {
        // Simulate a registration flow
        $cpr = '12345678901';
        $caller = Caller::factory()->create([
            'cpr' => $cpr,
            'hits' => 5,
        ]);

        // Set session data as would happen during registration
        session([
            'cpr' => $cpr,
            'userHits' => $caller->hits,
            'totalHits' => 100,
            'seconds' => 30,
        ]);

        // Visit success page
        $response = $this->get('/callers/success');

        // Should be successful if session data exists
        if (session()->has('cpr')) {
            $response->assertStatus(200);
            $response->assertSee($cpr);
        } else {
            // If no session data, should redirect
            $response->assertRedirect('/');
        }
    }
}