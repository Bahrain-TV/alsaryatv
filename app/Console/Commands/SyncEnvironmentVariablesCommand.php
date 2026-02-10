<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SyncEnvironmentVariablesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:sync-vars {--master= : Specify master .env file} {--dry-run : Preview changes without applying} {--interactive : Prompt for each variable} {--skip-validation : Skip validation checks} {--include-comments : Preserve and add section comments}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize environment variables from master .env file to all other .env files, with advanced options';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('🔄 Environment Variables Synchronizer');
        $this->line('');

        $basePath = base_path();
        $envFiles = $this->discoverEnvFiles($basePath);

        if ($envFiles->isEmpty()) {
            $this->error('❌ No .env files found!');

            return;
        }

        // Display available files
        $this->displayAvailableFiles($envFiles);

        // Select or determine master file
        $masterFile = $this->determineMasterFile($envFiles);

        if (! $masterFile) {
            $this->error('❌ Could not determine master file');

            return;
        }

        $this->line('<fg=green>Master file selected:</> <fg=cyan>'.basename($masterFile).'</>');
        $this->line('');

        // Parse master file
        $masterVars = $this->parseEnvFile($masterFile);
        $this->info('Found <fg=yellow>'.count($masterVars).'</> variables in master file');

        if (! $this->option('skip-validation')) {
            $this->validateMasterFile($masterVars);
        }

        $this->line('');

        // Sync to other files
        $targetFiles = $envFiles->reject(fn ($f) => $f === $masterFile);

        if ($targetFiles->isEmpty()) {
            $this->info('ℹ️  No target files to sync to');

            return;
        }

        $this->info('Will sync to <fg=yellow>'.count($targetFiles).'</> file(s)');
        $this->line('');

        // Perform sync
        $results = $this->syncToTargetFiles($masterVars, $targetFiles);

        // Display results
        $this->displaySyncResults($results);

        if (! $this->option('dry-run')) {
            $this->info('✅ Synchronization completed successfully!');
        } else {
            $this->info('📋 DRY RUN: No changes were applied');
            $this->line('Run without --dry-run to apply these changes');
        }
    }

    /**
     * Discover all .env files.
     */
    protected function discoverEnvFiles(string $basePath): Collection
    {
        $patterns = [
            '.env',
            '.env.local',
            '.env.*.local',
            '.env.production',
            '.env.staging',
            '.env.development',
            '.env.testing',
        ];

        $files = collect();
        foreach ($patterns as $pattern) {
            $path = "$basePath/$pattern";
            if (File::exists($path)) {
                $files->push($path);
            }
        }

        return $files->unique();
    }

    /**
     * Display available files.
     */
    protected function displayAvailableFiles(Collection $envFiles): void
    {
        $this->line('📁 Available .env files:');
        $envFiles->each(fn ($path, $i) => $this->line("  [$i] ".basename($path)));
        $this->line('');
    }

    /**
     * Determine master file.
     */
    protected function determineMasterFile(Collection $envFiles): ?string
    {
        if ($this->option('master')) {
            $masterName = $this->option('master');
            $masterFile = $envFiles->first(fn ($path) => Str::endsWith($path, $masterName));

            if ($masterFile) {
                return $masterFile;
            }

            $this->error("Master file '$masterName' not found");

            return null;
        }

        // Check in order: .env, .env.local, first found
        $priority = ['.env', '.env.local'];
        foreach ($priority as $name) {
            $file = $envFiles->first(fn ($path) => basename($path) === $name);
            if ($file) {
                return $file;
            }
        }

        return $envFiles->first();
    }

    /**
     * Parse environment file.
     */
    protected function parseEnvFile(string $path): array
    {
        $vars = [];
        $lines = File::get($path);

        foreach (explode("\n", $lines) as $line) {
            $line = trim($line);

            if (empty($line) || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $vars[trim($key)] = trim($value);
        }

        return $vars;
    }

    /**
     * Validate master file.
     */
    protected function validateMasterFile(array $masterVars): void
    {
        $emptyVars = array_filter($masterVars, fn ($v) => empty($v) || $v === 'null');

        if (! empty($emptyVars)) {
            $this->warn('⚠️  Master file has '.count($emptyVars).' empty/null variables:');
            foreach (array_keys($emptyVars) as $key) {
                $this->line("  • $key");
            }
        }
    }

    /**
     * Sync to target files.
     */
    protected function syncToTargetFiles(array $masterVars, Collection $targetFiles): array
    {
        $results = [];

        foreach ($targetFiles as $targetFile) {
            $fileName = basename($targetFile);
            $targetVars = $this->parseEnvFile($targetFile);

            $missing = array_diff_key($masterVars, $targetVars);
            $extra = array_diff_key($targetVars, $masterVars);
            $different = [];

            foreach (array_intersect_key($masterVars, $targetVars) as $key => $value) {
                if ($targetVars[$key] !== $value) {
                    $different[$key] = [
                        'master' => $value,
                        'target' => $targetVars[$key],
                    ];
                }
            }

            $results[$fileName] = [
                'path' => $targetFile,
                'missing' => $missing,
                'extra' => $extra,
                'different' => $different,
                'actions' => 0,
            ];

            // Interactive mode
            if ($this->option('interactive')) {
                $this->handleInteractiveMode($targetFile, $masterVars, $missing, $extra, $different, $results[$fileName]);

                continue;
            }

            // Auto-sync
            if (! $this->option('dry-run')) {
                $this->applySync($targetFile, $masterVars, $missing, $extra);
                $results[$fileName]['actions'] = count($missing) + count($extra);
            }
        }

        return $results;
    }

    /**
     * Handle interactive mode.
     */
    protected function handleInteractiveMode(string $targetFile, array $masterVars, array $missing, array $extra, array $different, array &$result): void
    {
        $this->line("\n<fg=cyan>Syncing ".basename($targetFile).'</>');

        $changes = [];

        // Missing variables
        if (! empty($missing)) {
            $this->line('<fg=yellow>Missing '.count($missing).' variables</>');
            foreach ($missing as $key => $value) {
                $display = strlen($value) > 40 ? substr($value, 0, 40).'...' : $value;
                if ($this->confirm("Add <fg=cyan>$key</> = <fg=gray>$display</>?")) {
                    $changes[$key] = $value;
                    $result['actions']++;
                }
            }
        }

        // Extra variables
        if (! empty($extra)) {
            $this->line('<fg=yellow>Extra '.count($extra).' variables</>');
            foreach (array_keys($extra) as $key) {
                if ($this->confirm("Remove <fg=cyan>$key</>?")) {
                    $result['actions']++;
                }
            }
        }

        if (! empty($changes) && ! $this->option('dry-run')) {
            $this->applySync($targetFile, $changes, $missing, $extra);
        }
    }

    /**
     * Apply sync to target file.
     */
    protected function applySync(string $targetFile, array $masterVars, array $missing = [], array $extra = []): void
    {
        $content = File::get($targetFile);
        $lines = explode("\n", $content);
        $updated = [];
        $processedVars = [];

        // Process existing lines
        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (empty($trimmed) || str_starts_with($trimmed, '#')) {
                $updated[] = $line;

                continue;
            }

            if (str_contains($trimmed, '=')) {
                [$key] = explode('=', $trimmed, 2);
                $key = trim($key);

                if (isset($extra[$key])) {
                    // Skip extra variables
                    continue;
                }

                if (isset($masterVars[$key])) {
                    $updated[] = "$key=".$masterVars[$key];
                    $processedVars[$key] = true;
                } else {
                    $updated[] = $line;
                }

                continue;
            }

            $updated[] = $line;
        }

        // Add missing variables
        foreach ($missing as $key => $value) {
            if (! isset($processedVars[$key])) {
                $updated[] = "$key=$value";
            }
        }

        $output = implode("\n", $updated);
        if (! str_ends_with($output, "\n")) {
            $output .= "\n";
        }

        File::put($targetFile, $output);
    }

    /**
     * Display sync results.
     */
    protected function displaySyncResults(array $results): void
    {
        $this->line('');
        $this->info('📊 Synchronization Results:');
        $this->line('');

        $totalActions = 0;

        foreach ($results as $fileName => $result) {
            $this->line("  <fg=cyan>$fileName</>");

            if (! empty($result['missing'])) {
                $count = count($result['missing']);
                $this->line("    <fg=green>✓ Added:</> $count missing variable(s)");
                $totalActions += $count;
            }

            if (! empty($result['extra'])) {
                $count = count($result['extra']);
                $this->line("    <fg=yellow>⚠ Found:</> $count extra variable(s)");
            }

            if (! empty($result['different'])) {
                $count = count($result['different']);
                $this->line("    <fg=yellow>⚠ Different:</> $count variable(s) with different values");
            }

            if ($result['actions'] === 0) {
                $this->line('    <fg=gray>No changes needed</>');
            }
        }

        $this->line('');
        $this->info("Total changes: <fg=yellow>$totalActions</>");
    }
}
