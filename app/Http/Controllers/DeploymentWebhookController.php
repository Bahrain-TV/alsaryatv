<?php

namespace App\Http\Controllers;

use App\Services\GitHubWebhookVerifier;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class DeploymentWebhookController extends Controller
{
    public function __construct(private GitHubWebhookVerifier $verifier) {}

    /**
     * Handle incoming GitHub webhook payload
     */
    public function handle(Request $request): Response
    {
        $webhookSecret = config('deployment.github_webhook_secret');
        $allowedBranches = config('deployment.webhook_allowed_branches', 'main,production');
        $allowedIps = config('deployment.webhook_allowed_ips');

        // Get webhook signature from headers
        $signature = $request->header('X-Hub-Signature-256', '');

        // Get raw request body for signature verification
        $payload = $request->getContent();

        // Log webhook attempt
        Log::info('GitHub webhook received', [
            'ip' => $request->ip(),
            'event' => $request->header('X-GitHub-Event', 'unknown'),
        ]);

        // Verify webhook secret
        if (! $this->verifier->verify($payload, $signature, $webhookSecret)) {
            Log::warning('GitHub webhook signature verification failed');

            return response()->json([
                'error' => 'Signature verification failed',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Verify request IP (if configured)
        if (! $this->verifier->isIpAllowed($request->ip(), $allowedIps)) {
            Log::warning('GitHub webhook from unauthorized IP', [
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Unauthorized IP address',
            ], Response::HTTP_FORBIDDEN);
        }

        // Only process push events
        if ($request->header('X-GitHub-Event') !== 'push') {
            return response()->json([
                'message' => 'Webhook received but not a push event',
            ], Response::HTTP_OK);
        }

        // Parse JSON payload
        $data = $request->json()->all();

        // Extract branch from payload
        $branch = $this->verifier->extractBranch($data);

        if (! $branch) {
            Log::warning('GitHub webhook: could not extract branch from payload');

            return response()->json([
                'error' => 'Could not determine branch from webhook payload',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Check if branch is allowed
        if (! $this->verifier->isBranchAllowed($branch, $allowedBranches)) {
            Log::info('GitHub webhook: branch not in allowed list', [
                'branch' => $branch,
            ]);

            return response()->json([
                'message' => "Branch '$branch' not configured for auto-deployment",
            ], Response::HTTP_OK);
        }

        // Check if deployment is already running
        if ($this->isDeploymentRunning()) {
            Log::warning('GitHub webhook received while deployment already running', [
                'branch' => $branch,
            ]);

            return response()->json([
                'error' => 'Deployment already in progress',
            ], Response::HTTP_CONFLICT);
        }

        // Trigger deployment
        return $this->triggerDeployment($branch);
    }

    /**
     * Check if deployment is already running
     */
    private function isDeploymentRunning(): bool
    {
        $lockFile = '/tmp/deploy.lock';

        if (! file_exists($lockFile)) {
            return false;
        }

        // Check if PID in lock file is still running
        $pid = (int) trim(file_get_contents($lockFile));

        if ($pid <= 0) {
            return false;
        }

        // Use kill -0 to check if process exists (POSIX)
        // Returns 0 if process exists, non-zero if not
        $result = shell_exec("kill -0 $pid 2>&1");

        return $result === null || $result === '';
    }

    /**
     * Trigger deployment via Artisan command
     */
    private function triggerDeployment(string $branch): Response
    {
        try {
            Log::info('Triggering deployment from GitHub webhook', [
                'branch' => $branch,
            ]);

            // Call the deploy command with webhook flag
            // This executes in background to return quickly
            $exitCode = Artisan::call('deploy:run', [
                '--webhook' => true,
            ]);

            if ($exitCode === 0) {
                Log::info('Deployment command queued successfully', [
                    'branch' => $branch,
                ]);

                return response()->json([
                    'message' => 'Deployment triggered successfully',
                    'branch' => $branch,
                ], Response::HTTP_ACCEPTED);
            } else {
                Log::error('Deployment command failed', [
                    'exit_code' => $exitCode,
                    'branch' => $branch,
                ]);

                return response()->json([
                    'error' => 'Failed to start deployment',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            Log::error('Exception during webhook deployment', [
                'message' => $e->getMessage(),
                'branch' => $branch,
            ]);

            return response()->json([
                'error' => 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
