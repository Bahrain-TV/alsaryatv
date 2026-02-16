<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class GitHubWebhookVerifier
{
    /**
     * Verify GitHub webhook signature using HMAC-SHA256
     *
     * @param  string  $payload  Raw request body
     * @param  string  $signature  X-Hub-Signature-256 header value (sha256=...)
     * @param  string  $secret  Webhook secret from GitHub
     */
    public function verify(string $payload, string $signature, string $secret): bool
    {
        // Expected format: sha256=<hex-digest>
        if (! str_starts_with($signature, 'sha256=')) {
            Log::warning('Invalid GitHub signature format', [
                'signature_header' => substr($signature, 0, 20),
            ]);

            return false;
        }

        // Extract the hex digest from signature
        $expectedSignature = substr($signature, 7);

        // Compute HMAC-SHA256 of payload using secret
        $computedSignature = hash_hmac('sha256', $payload, $secret);

        // Use constant-time comparison to prevent timing attacks
        $isValid = hash_equals($expectedSignature, $computedSignature);

        if (! $isValid) {
            Log::warning('GitHub webhook signature mismatch', [
                'expected' => substr($expectedSignature, 0, 16).'...',
                'computed' => substr($computedSignature, 0, 16).'...',
            ]);
        }

        return $isValid;
    }

    /**
     * Check if webhook should be processed based on branch
     *
     * @param  string  $branch  Branch name from payload
     * @param  string  $allowedBranches  Comma-separated list of allowed branches
     */
    public function isBranchAllowed(string $branch, string $allowedBranches): bool
    {
        $branches = array_map('trim', explode(',', $allowedBranches));

        return in_array($branch, $branches, true);
    }

    /**
     * Check if request IP is in allowed GitHub IP ranges
     *
     * @param  string  $ip  Client IP address
     * @param  string|null  $allowedIps  Comma-separated CIDR ranges or IPs
     */
    public function isIpAllowed(string $ip, ?string $allowedIps = null): bool
    {
        // If no IP restrictions configured, allow all
        if (empty($allowedIps)) {
            return true;
        }

        $ranges = array_map('trim', explode(',', $allowedIps));

        foreach ($ranges as $range) {
            if ($this->ipInRange($ip, $range)) {
                return true;
            }
        }

        Log::warning('GitHub webhook from unauthorized IP', [
            'ip' => $ip,
        ]);

        return false;
    }

    /**
     * Check if IP is in CIDR range
     *
     * @param  string  $ip  IP address
     * @param  string  $range  CIDR range or single IP
     */
    private function ipInRange(string $ip, string $range): bool
    {
        // Single IP address
        if (! str_contains($range, '/')) {
            return $ip === $range;
        }

        // CIDR range
        [$subnet, $bits] = explode('/', $range, 2);
        $bits = (int) $bits;

        $ip = ip2long($ip);
        $subnet = ip2long($subnet);

        if ($ip === false || $subnet === false) {
            return false;
        }

        $mask = -1 << (32 - $bits);
        $subnet &= $mask;

        return ($ip & $mask) === $subnet;
    }

    /**
     * Extract branch name from webhook payload
     *
     * @param  array  $payload  Decoded JSON payload
     */
    public function extractBranch(array $payload): ?string
    {
        // Webhook payload should have "ref" like "refs/heads/main"
        $ref = $payload['ref'] ?? null;

        if (! $ref || ! str_starts_with($ref, 'refs/heads/')) {
            return null;
        }

        return substr($ref, 11); // Remove "refs/heads/" prefix
    }
}
