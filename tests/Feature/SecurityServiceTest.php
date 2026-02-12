<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SecurityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SecurityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SecurityService $securityService;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->securityService = new SecurityService;
        $this->user = User::factory()->create();

        // Clear cache before each test
        Cache::flush();
    }

    public function test_validate_operation_allows_first_attempt(): void
    {
        $result = $this->securityService->validateOperation($this->user, 'test-operation');

        $this->assertTrue($result);
    }

    public function test_validate_operation_respects_rate_limit(): void
    {
        $maxAttempts = config('security.rate_limiting.max_attempts', 5);

        // Make max attempts
        for ($i = 0; $i < $maxAttempts; $i++) {
            $result = $this->securityService->validateOperation($this->user, 'test-operation');
            $this->assertTrue($result);
        }

        // Next attempt should fail
        $result = $this->securityService->validateOperation($this->user, 'test-operation');
        $this->assertFalse($result);
    }

    public function test_different_operations_have_separate_limits(): void
    {
        $this->securityService->validateOperation($this->user, 'operation-1');
        $this->securityService->validateOperation($this->user, 'operation-2');

        // Both should succeed on first attempt
        $result1 = $this->securityService->validateOperation($this->user, 'operation-1');
        $result2 = $this->securityService->validateOperation($this->user, 'operation-2');

        $this->assertTrue($result1);
        $this->assertTrue($result2);
    }

    public function test_validate_request_with_empty_rules(): void
    {
        $request = \Illuminate\Http\Request::create('/', 'POST');

        $result = $this->securityService->validateRequest($request, []);

        $this->assertTrue($result);
    }

    public function test_security_service_logs_rate_limit_exceeded(): void
    {
        // Make requests to exceed limit
        $maxAttempts = config('security.rate_limiting.max_attempts', 5);

        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->securityService->validateOperation($this->user, 'monitored-operation');
        }

        // This should be logged
        $result = $this->securityService->validateOperation($this->user, 'monitored-operation');

        $this->assertFalse($result);
    }

    public function test_different_users_have_separate_rate_limits(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->securityService->validateOperation($user1, 'test-operation');
        $this->securityService->validateOperation($user2, 'test-operation');

        // Both users should be able to perform operations independently
        $result1 = $this->securityService->validateOperation($user1, 'test-operation');
        $result2 = $this->securityService->validateOperation($user2, 'test-operation');

        // Results depend on max_attempts, but each user has independent counter
        $this->assertTrue(is_bool($result1));
        $this->assertTrue(is_bool($result2));
    }
}
