<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ValidateEnvCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'env:validate {--fix : Auto-fix missing variables} {--dry-run : Show what would be changed without making changes} {--report : Generate a detailed report} {--select-master : Choose master .env file}';

    /**
     * The console command description.
     */
    protected $description = 'Validate and synchronize all .env files - check for missing variables across environments';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Environment File Validator & Synchronizer');
        $this->line('');

        $basePath = base_path();
        $envFiles = $this->discoverEnvFiles($basePath);

        if ($envFiles->isEmpty()) {
            $this->error('No .env files found!');

            return;
        }

        $this->info('Found '.count($envFiles).' environment file(s):');
        $envFiles->each(fn ($path) => $this->line('  - '.Str::after($path, $basePath.'/')));
        $this->line('');

        // Parse all environment files
        $allEnvData = $this->parseAllEnvFiles($envFiles);

        // Determine master file
        $masterFile = $envFiles->first();
        if ($this->option('select-master')) {
            $masterFile = $this->selectMasterFile($envFiles);
        }

        $this->line('Master file: '.basename($masterFile));
        $this->line('');

        // Validate and report
        $report = $this->validateEnvFiles($allEnvData, $masterFile);

        // Display validation results
        $this->displayValidationReport($report);

        // Generate detailed report if requested
        if ($this->option('report')) {
            $this->generateDetailedReport($report);
        }

        // Fix issues if requested
        if ($this->option('fix')) {
            if ($this->option('dry-run')) {
                $this->info('DRY RUN MODE - No changes will be made');
                $this->line('');
            }

            $this->fixMissingVariables($allEnvData, $masterFile, $this->option('dry-run'));
        }
    }

    /**
     * Discover all .env files in the project.
     */
    protected function discoverEnvFiles(string $basePath): Collection
    {
        $patterns = [
            '.env',
            '.env.local',
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
     * Parse all environment files.
     */
    protected function parseAllEnvFiles(Collection $envFiles): array
    {
        $allData = [];
        foreach ($envFiles as $path) {
            $allData[$path] = $this->parseEnvFile($path);
        }

        return $allData;
    }

    /**
     * Parse a single environment file.
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
     * Select master file interactively.
     */
    protected function selectMasterFile(Collection $envFiles): string
    {
        $choices = $envFiles->map(fn ($path) => basename($path))->toArray();
        $selected = $this->choice('Select master .env file:', $choices, 0);

        return $envFiles->first(fn ($path) => basename($path) === $selected);
    }

    /**
     * Validate all environment files against master.
     */
    protected function validateEnvFiles(array $allEnvData, string $masterFile): array
    {
        $masterVars = $allEnvData[$masterFile] ?? [];
        $allKeys = collect($allEnvData)->flatMap(fn ($vars) => array_keys($vars))->unique()->sort()->toArray();

        $report = [
            'total_keys' => count($allKeys),
            'total_files' => count($allEnvData),
            'master_file' => basename($masterFile),
            'files' => [],
            'missing_variables' => [],
            'inconsistencies' => [],
        ];

        foreach ($allEnvData as $filePath => $vars) {
            $fileName = basename($filePath);
            $missing = array_diff_key(array_flip($allKeys), $vars);
            $extra = array_diff_key($vars, array_flip($allKeys));

            $report['files'][$fileName] = [
                'path' => $filePath,
                'total_vars' => count($vars),
                'missing' => array_keys($missing),
                'extra' => array_keys($extra),
            ];

            if (! empty($missing)) {
                $report['missing_variables'][$fileName] = array_keys($missing);
            }
        }

        // Find inconsistencies in values
        foreach ($allKeys as $key) {
            $values = [];
            foreach ($allEnvData as $filePath => $vars) {
                if (isset($vars[$key])) {
                    $values[basename($filePath)] = $vars[$key];
                }
            }

            if (count(array_unique($values)) > 1) {
                $report['inconsistencies'][$key] = $values;
            }
        }

        return $report;
    }

    /**
     * Display validation report to console.
     */
    protected function displayValidationReport(array $report): void
    {
        $this->line('VALIDATION REPORT');
        $this->line(str_repeat('-', 60));
        $this->line('');

        // Summary
        $this->line('Summary:');
        $this->line('  Total variables: '.$report['total_keys']);
        $this->line('  Total files: '.$report['total_files']);
        $this->line('');

        // Per-file breakdown
        $this->line('Per-File Breakdown:');
        foreach ($report['files'] as $fileName => $fileData) {
            $isMaster = $fileName === $report['master_file'];
            $badge = $isMaster ? ' [MASTER]' : '';
            $this->line("  $fileName$badge");
            $this->line('    Variables: '.$fileData['total_vars']);

            if (! empty($fileData['missing'])) {
                $count = count($fileData['missing']);
                $preview = implode(', ', array_slice($fileData['missing'], 0, 3));
                $suffix = $count > 3 ? '...' : '';
                $this->line("    MISSING: $count - $preview$suffix");
            }

            if (! empty($fileData['extra'])) {
                $count = count($fileData['extra']);
                $preview = implode(', ', array_slice($fileData['extra'], 0, 3));
                $suffix = $count > 3 ? '...' : '';
                $this->line("    EXTRA: $count - $preview$suffix");
            }
        }

        $this->line('');

        // Inconsistencies
        if (! empty($report['inconsistencies'])) {
            $this->line('Value Inconsistencies Found: '.count($report['inconsistencies']).' variables');
            foreach (array_slice($report['inconsistencies'], 0, 5) as $key => $values) {
                $this->line("  $key:");
                foreach ($values as $file => $value) {
                    $display = strlen($value) > 40 ? substr($value, 0, 40).'...' : $value;
                    $this->line("    - $file: $display");
                }
            }
            if (count($report['inconsistencies']) > 5) {
                $this->line('  ...and '.(count($report['inconsistencies']) - 5).' more');
            }
            $this->line('');
        }

        // Status
        $statusText = empty($report['missing_variables']) && empty($report['inconsistencies'])
            ? 'All environments synchronized'
            : 'Issues detected - use --fix to resolve';
        $this->line("Status: $statusText");
        $this->line('');
    }

    /**
     * Generate detailed report file.
     */
    protected function generateDetailedReport(array $report): void
    {
        $reportPath = base_path('storage/logs/env-validation-'.now()->format('Y-m-d_H-i-s').'.txt');

        $content = "Environment File Validation Report\n";
        $content .= 'Generated: '.now()->toDateTimeString()."\n";
        $content .= str_repeat('=', 80)."\n\n";

        $content .= "SUMMARY\n";
        $content .= 'Total Variables: '.$report['total_keys']."\n";
        $content .= 'Total Files: '.$report['total_files']."\n";
        $content .= 'Master File: '.$report['master_file']."\n\n";

        $content .= "DETAILED BREAKDOWN\n";
        $content .= str_repeat('-', 80)."\n";
        foreach ($report['files'] as $fileName => $fileData) {
            $content .= "\n$fileName\n";
            $content .= '  Variables: '.$fileData['total_vars']."\n";
            if (! empty($fileData['missing'])) {
                $content .= '  Missing ('.count($fileData['missing'])."):\n";
                foreach ($fileData['missing'] as $var) {
                    $content .= "    - $var\n";
                }
            }
            if (! empty($fileData['extra'])) {
                $content .= '  Extra ('.count($fileData['extra'])."):\n";
                foreach ($fileData['extra'] as $var) {
                    $content .= "    - $var\n";
                }
            }
        }

        if (! empty($report['inconsistencies'])) {
            $content .= "\n\nINCONSISTENCIES\n";
            $content .= str_repeat('-', 80)."\n";
            foreach ($report['inconsistencies'] as $key => $values) {
                $content .= "\n$key:\n";
                foreach ($values as $file => $value) {
                    $content .= "  $file: $value\n";
                }
            }
        }

        File::put($reportPath, $content);
        $this->info('Report saved: storage/logs/'.basename($reportPath));
    }

    /**
     * Fix missing variables in all files.
     */
    protected function fixMissingVariables(array $allEnvData, string $masterFile, bool $dryRun = false): void
    {
        $masterVars = $allEnvData[$masterFile] ?? [];
        $allKeys = collect($allEnvData)->flatMap(fn ($vars) => array_keys($vars))->unique()->sort()->toArray();

        $this->info('');
        $this->info('Fixing missing variables...');
        $this->line('');

        $fixedCount = 0;

        foreach ($allEnvData as $filePath => $vars) {
            $fileName = basename($filePath);

            if ($filePath === $masterFile) {
                continue;
            }

            $missing = array_diff_key(array_flip($allKeys), $vars);

            if (empty($missing)) {
                $this->line("  OK: $fileName - no missing variables");

                continue;
            }

            $this->line("  FIXING: $fileName - ".count($missing).' missing variables');

            if (! $dryRun) {
                $content = File::get($filePath);
                $newVars = [];

                foreach (array_keys($missing) as $key) {
                    $newVars[$key] = $masterVars[$key] ?? '';
                }

                $updated = $this->addMissingVariables($content, $newVars);
                File::put($filePath, $updated);
                $fixedCount += count($missing);

                foreach ($newVars as $key => $value) {
                    $display = strlen($value) > 35 ? substr($value, 0, 35).'...' : $value;
                    $this->line("      + $key = $display");
                }
            }
        }

        $this->line('');
        if ($dryRun) {
            $this->info("DRY RUN: Would add $fixedCount variables");
            $this->line('Run without --dry-run to apply changes');
        } else {
            $this->info("Fixed $fixedCount missing variables across all files");
        }
    }

    /**
     * Add missing variables to file content.
     */
    protected function addMissingVariables(string $content, array $newVars): string
    {
        $lines = explode("\n", $content);

        foreach ($newVars as $key => $value) {
            $lines[] = "$key=$value";
        }

        return implode("\n", $lines)."\n";
    }
}
