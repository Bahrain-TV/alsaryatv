<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncVersionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'version:sync 
                            {--from=VERSION : Source file to sync from (VERSION or version.json)}
                            {--update-env : Also update APP_VERSION in .env files}
                            {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize version information between VERSION file, version.json, and optionally .env files';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $basePath = base_path();
        $versionFile = "$basePath/VERSION";
        $versionJsonFile = "$basePath/version.json";

        // Check if files exist
        if (! File::exists($versionFile)) {
            $this->error("❌ VERSION file not found at: $versionFile");

            return self::FAILURE;
        }

        if (! File::exists($versionJsonFile)) {
            $this->error("❌ version.json file not found at: $versionJsonFile");

            return self::FAILURE;
        }

        // Read current versions
        $versionContent = trim(File::get($versionFile));
        $versionJsonContent = json_decode(File::get($versionJsonFile), true);

        if (! $versionJsonContent || ! isset($versionJsonContent['version'])) {
            $this->error('❌ Invalid version.json format - missing version field');

            return self::FAILURE;
        }

        $this->info('📋 Current Version State:');
        $this->line("   VERSION file: $versionContent");
        $this->line('   version.json: '.$versionJsonContent['version']);
        $this->newLine();

        // Parse VERSION file (format: "3.3.1-32" => base: "3.3.1", build: "32")
        if (! preg_match('/^(\d+\.\d+\.\d+)(-(\d+))?$/', $versionContent, $matches)) {
            $this->error("❌ Invalid VERSION file format: $versionContent (expected: X.Y.Z or X.Y.Z-BUILD)");

            return self::FAILURE;
        }

        $baseVersion = $matches[1]; // e.g., "3.3.1"
        $buildNumber = $matches[3] ?? '0'; // e.g., "32" or "0"

        $source = $this->option('from');
        $isDryRun = $this->option('dry-run');

        if ($source === 'VERSION') {
            // Sync version.json to match VERSION file's base version
            $sourceVersion = $baseVersion;
            $targetFile = 'version.json';

            if ($versionJsonContent['version'] === $sourceVersion) {
                $this->info("✅ version.json is already synchronized with VERSION file ($sourceVersion)");
            } else {
                $this->warn("⚠️  Version mismatch detected:");
                $this->line("   version.json: {$versionJsonContent['version']} → $sourceVersion");

                if (! $isDryRun) {
                    // Update version.json
                    $versionJsonContent['version'] = $sourceVersion;
                    $versionJsonContent['updated_at'] = now()->toIso8601String();
                    $versionJsonContent['updated_by'] = 'version:sync-command';

                    // Add changelog entry
                    if (! isset($versionJsonContent['changelog'])) {
                        $versionJsonContent['changelog'] = [];
                    }

                    array_unshift($versionJsonContent['changelog'], [
                        'version' => $sourceVersion,
                        'type' => 'sync',
                        'message' => "Synchronized version.json with VERSION file (base: $sourceVersion, build: $buildNumber)",
                        'timestamp' => now()->toIso8601String(),
                    ]);

                    File::put($versionJsonFile, json_encode($versionJsonContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n");
                    $this->info("✅ Successfully updated version.json to version $sourceVersion");
                } else {
                    $this->info('🔍 [DRY RUN] Would update version.json to version '.$sourceVersion);
                }
            }
        } elseif ($source === 'version.json') {
            // Sync VERSION file to match version.json (less common, but supported)
            $sourceVersion = $versionJsonContent['version'];
            $targetFile = 'VERSION';

            if ($baseVersion === $sourceVersion) {
                $this->info("✅ VERSION file is already synchronized with version.json ($sourceVersion)");
            } else {
                $this->warn("⚠️  Version mismatch detected:");
                $this->line("   VERSION file: $versionContent → $sourceVersion-$buildNumber");

                if (! $isDryRun) {
                    File::put($versionFile, "$sourceVersion-$buildNumber\n");
                    $this->info("✅ Successfully updated VERSION file to $sourceVersion-$buildNumber");
                } else {
                    $this->info("🔍 [DRY RUN] Would update VERSION file to $sourceVersion-$buildNumber");
                }
            }
        } else {
            $this->error("❌ Invalid source option: $source (must be 'VERSION' or 'version.json')");

            return self::FAILURE;
        }

        // Optionally update .env files
        if ($this->option('update-env')) {
            $this->newLine();
            $this->info('🔄 Updating APP_VERSION in .env files...');

            $envFiles = collect([
                "$basePath/.env",
                "$basePath/.env.production",
                "$basePath/.env.preview",
                "$basePath/.env.local",
            ])->filter(fn ($path) => File::exists($path));

            if ($envFiles->isEmpty()) {
                $this->warn('⚠️  No .env files found to update');
            } else {
                $appVersion = "$baseVersion-$buildNumber";

                foreach ($envFiles as $envFile) {
                    $envName = basename($envFile);
                    $content = File::get($envFile);

                    if (str_contains($content, 'APP_VERSION=')) {
                        if (! $isDryRun) {
                            $newContent = preg_replace('/^APP_VERSION=.*/m', "APP_VERSION=\"$appVersion\"", $content);
                            File::put($envFile, $newContent);
                            $this->line("   ✅ Updated $envName: APP_VERSION=\"$appVersion\"");
                        } else {
                            $this->line("   🔍 [DRY RUN] Would update $envName: APP_VERSION=\"$appVersion\"");
                        }
                    } else {
                        if (! $isDryRun) {
                            File::append($envFile, "\nAPP_VERSION=\"$appVersion\"\n");
                            $this->line("   ✅ Added to $envName: APP_VERSION=\"$appVersion\"");
                        } else {
                            $this->line("   🔍 [DRY RUN] Would add to $envName: APP_VERSION=\"$appVersion\"");
                        }
                    }
                }
            }
        }

        $this->newLine();
        if ($isDryRun) {
            $this->info('🔍 Dry run completed - no files were modified');
        } else {
            $this->info('✅ Version synchronization completed successfully!');
        }

        return self::SUCCESS;
    }
}
