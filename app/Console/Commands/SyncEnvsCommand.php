<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SyncEnvsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:sync {--s|select : Select master environment file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize environment variables across different .env files';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $basePath = base_path();
        $envPaths = collect([
            "$basePath/.env",
            "$basePath/.env.local",
            "$basePath/.env.production",
        ])->filter(fn ($path) => File::exists($path));

        $this->info(' Current Environment: '.app()->environment()."\n");
        $this->info('Available Environments:');

        $envPaths->each(function ($path, $index): void {
            $this->line("[$index]: ".basename($path));
        });

        $masterEnvPath = $envPaths->first(); // Default to .env

        if ($this->option('select')) {
            $choices = $envPaths->map(fn ($path) => basename($path))->toArray();
            $selectedIndex = $this->choice('Select the master .env file:', $choices, 0);

            $masterEnvPath = $envPaths->get($selectedIndex);
            $this->info(' Master Environment selected: '.basename($masterEnvPath));
        } else {
            $this->info(' Using current environment\'s .env file: '.basename($masterEnvPath));
        }

        try {
            $this->syncEnvVariables($masterEnvPath, $envPaths);
        } catch (\Exception $e) {
            $this->error('Error synchronizing environment variables: '.$e->getMessage());
            Log::error('Error synchronizing environment variables: '.$e->getMessage());
        }
    }

    /**
     * Synchronize environment variables from master file to other env files.
     */
    protected function syncEnvVariables(string $masterEnvPath, Collection $envPaths): void
    {
        $masterEnv = $this->parseEnvFile($masterEnvPath);
        $categorizedVars = $this->categorizeAndSortVariables($masterEnv);

        $envPaths->reject(fn ($path) => $path === $masterEnvPath)->each(function ($envPath) use ($masterEnv, $categorizedVars): void {
            $currentEnv = $this->parseEnvFile($envPath);

            // Preserve comments and existing variables
            $originalContent = File::get($envPath);
            $lines = collect(explode("\n", $originalContent));
            $newContent = $lines->filter(function ($line) {
                $trimmedLine = trim($line);

                return str_starts_with($trimmedLine, '#') || empty($trimmedLine);
            });

            // Add categorized variables
            foreach ($categorizedVars as $category => $variables) {
                // Skip adding category if it has no variables
                if (empty($variables)) {
                    continue;
                }

                $newContent->push("\n# {$category}");
                foreach ($variables as $key => $value) {
                    $existingValue = $currentEnv[$key] ?? $value; // Use master value as fallback
                    $newContent->push("{$key}={$existingValue}");
                }
            }

            // Add new variables from master that weren't categorized
            $newVars = array_diff_key($masterEnv, $currentEnv);
            if (! empty($newVars)) {
                $hasUnhandledVars = false;
                foreach ($newVars as $key => $value) {
                    // Check if this variable was already handled in a category
                    $alreadyHandled = false;
                    foreach ($categorizedVars as $categoryVars) {
                        if (array_key_exists($key, $categoryVars)) {
                            $alreadyHandled = true;
                            break;
                        }
                    }

                    if (! $alreadyHandled) {
                        if (! $hasUnhandledVars) {
                            $newContent->push("\n# Other variables");
                            $hasUnhandledVars = true;
                        }
                        $newContent->push("{$key}={$value}");
                    }
                }
            }

            File::put($envPath, $newContent->implode("\n")."\n");
        });

        $this->info('Environment files synchronized successfully!');
    }

    /**
     * Categorize and sort environment variables by their prefix.
     */
    protected function categorizeAndSortVariables(array $variables): array
    {
        $priorityOrder = [
            'APP',
            'DB',
            'MAIL',
            'LOG',
            'CACHE',
            'SESSION',
            'QUEUE',
            'AWS',
            'REDIS',
            'FILESYSTEM',
            'BROADCAST',
            'DISCORD',
            'FACEBOOK',
            'GOOGLE',
            'GITHUB',
            'USER',
            'MEMCACHED',
            'INITIAL',
            'VITE',
        ];

        $categories = [];

        foreach ($variables as $key => $value) {
            $parts = explode('_', $key);
            $category = ! empty($parts[0]) ? $parts[0] : 'UNCATEGORIZED';
            $categories[$category][$key] = $value;
        }

        foreach ($categories as $category => &$categoryVars) {
            uksort($categoryVars, function ($a, $b) use ($variables) {
                $aValue = $variables[$a];
                $bValue = $variables[$b];

                $aEmpty = empty($aValue) || $aValue === 'null';
                $bEmpty = empty($bValue) || $bValue === 'null';

                if ($aEmpty !== $bEmpty) {
                    return $aEmpty ? 1 : -1;
                }

                return strcasecmp($a, $b);
            });
        }

        $sortedCategories = [];
        foreach ($priorityOrder as $category) {
            if (isset($categories[$category])) {
                $sortedCategories[$category] = $categories[$category];
                unset($categories[$category]);
            }
        }

        ksort($categories);

        return array_merge($sortedCategories, $categories);
    }

    /**
     * Parse environment file and return variables as array.
     */
    protected function parseEnvFile(string $path): array
    {
        $contents = File::get($path);
        $vars = [];

        foreach (explode("\n", $contents) as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $vars[trim($key)] = trim($value);
        }

        return $vars;
    }
}
