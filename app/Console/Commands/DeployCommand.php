<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeployCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:run {--webhook : Deployment triggered by GitHub webhook}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Execute production deployment script';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $isWebhook = $this->option('webhook');

        if ($isWebhook) {
            $this->info('Deployment triggered by GitHub webhook');
            Log::info('Deployment started from GitHub webhook');
        } else {
            $this->info('Deployment triggered manually');
            Log::info('Deployment started manually');
        }

        // Get the deploy script path
        $deployScript = base_path('deploy.sh');

        if (!file_exists($deployScript)) {
            $this->error('Deploy script not found: ' . $deployScript);
            Log::error('Deploy script not found', ['script' => $deployScript]);
            return 1;
        }

        // Prepare environment for deploy.sh
        $env = $_ENV;

        // Set WEBHOOK_TRIGGER environment variable
        if ($isWebhook) {
            $env['WEBHOOK_TRIGGER'] = 'true';
        }

        // Execute deploy script with environment
        $this->info('Executing deploy.sh...');
        $this->line('');

        // Run deploy.sh with output buffering
        $process = proc_open(
            'bash ' . escapeshellarg($deployScript),
            [
                1 => STDOUT,  // stdout
                2 => STDERR,  // stderr
            ],
            $pipes,
            base_path(),
            $env
        );

        if (!is_resource($process)) {
            $this->error('Failed to start deployment script');
            Log::error('Failed to start deployment script');
            return 1;
        }

        $exitCode = proc_close($process);

        if ($exitCode === 0) {
            $this->info('');
            $this->info('✓ Deployment completed successfully!');
            Log::info('Deployment completed successfully', [
                'webhook' => $isWebhook,
            ]);
            return 0;
        } else {
            $this->error('');
            $this->error('✗ Deployment failed with exit code: ' . $exitCode);
            Log::error('Deployment failed', [
                'exit_code' => $exitCode,
                'webhook' => $isWebhook,
            ]);
            return $exitCode;
        }
    }
}
