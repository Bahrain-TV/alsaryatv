<?php

namespace Tests\Unit;

use App\Services\GitHubWebhookVerifier;
use Tests\TestCase;

class GitHubWebhookVerifierTest extends TestCase
{
    private GitHubWebhookVerifier $verifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->verifier = new GitHubWebhookVerifier;
    }

    public function test_verify_accepts_valid_signature(): void
    {
        $payload = '{"action":"push"}';
        $secret = 'my-secret';
        $signature = 'sha256='.hash_hmac('sha256', $payload, $secret);

        $this->assertTrue($this->verifier->verify($payload, $signature, $secret));
    }

    public function test_verify_rejects_invalid_signature(): void
    {
        $payload = '{"action":"push"}';
        $secret = 'my-secret';
        $signature = 'sha256='.hash_hmac('sha256', $payload, 'wrong-secret');

        $this->assertFalse($this->verifier->verify($payload, $signature, $secret));
    }

    public function test_verify_rejects_missing_sha256_prefix(): void
    {
        $payload = '{"action":"push"}';
        $secret = 'my-secret';
        $signature = hash_hmac('sha256', $payload, $secret);

        $this->assertFalse($this->verifier->verify($payload, $signature, $secret));
    }

    public function test_is_branch_allowed_returns_true_for_listed_branch(): void
    {
        $this->assertTrue($this->verifier->isBranchAllowed('main', 'main,develop'));
        $this->assertTrue($this->verifier->isBranchAllowed('develop', 'main,develop'));
    }

    public function test_is_branch_allowed_returns_false_for_unlisted_branch(): void
    {
        $this->assertFalse($this->verifier->isBranchAllowed('feature/x', 'main,develop'));
    }

    public function test_is_branch_allowed_trims_whitespace(): void
    {
        $this->assertTrue($this->verifier->isBranchAllowed('develop', 'main, develop'));
    }

    public function test_is_ip_allowed_returns_true_when_no_restrictions(): void
    {
        $this->assertTrue($this->verifier->isIpAllowed('1.2.3.4', null));
        $this->assertTrue($this->verifier->isIpAllowed('1.2.3.4', ''));
    }

    public function test_is_ip_allowed_matches_exact_ip(): void
    {
        $this->assertTrue($this->verifier->isIpAllowed('192.168.1.1', '192.168.1.1'));
        $this->assertFalse($this->verifier->isIpAllowed('192.168.1.2', '192.168.1.1'));
    }

    public function test_is_ip_allowed_matches_cidr_range(): void
    {
        $this->assertTrue($this->verifier->isIpAllowed('192.168.1.50', '192.168.1.0/24'));
        $this->assertFalse($this->verifier->isIpAllowed('192.168.2.1', '192.168.1.0/24'));
    }

    public function test_extract_branch_from_valid_ref(): void
    {
        $payload = ['ref' => 'refs/heads/main'];
        $this->assertEquals('main', $this->verifier->extractBranch($payload));
    }

    public function test_extract_branch_from_feature_branch(): void
    {
        $payload = ['ref' => 'refs/heads/feature/my-feature'];
        $this->assertEquals('feature/my-feature', $this->verifier->extractBranch($payload));
    }

    public function test_extract_branch_returns_null_for_tag_ref(): void
    {
        $payload = ['ref' => 'refs/tags/v1.0.0'];
        $this->assertNull($this->verifier->extractBranch($payload));
    }

    public function test_extract_branch_returns_null_when_ref_missing(): void
    {
        $this->assertNull($this->verifier->extractBranch([]));
    }
}
