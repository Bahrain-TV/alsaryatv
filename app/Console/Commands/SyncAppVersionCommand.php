<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncAppVersionCommand extends Command
{
    protected $signature = 'version:sync-app-version';

    protected $description = 'Synchronize APP_VERSION in .env files with VERSION file (includes build number)';

    public function handle()
    {
        $versionFile = base_path('VERSION');

        if (!File::exists($versionFile)) {
            $this->error('VERSION file not found at ' . $versionFile);
            return 1;
        }

        $versionFromFile = trim(File::get($versionFile));

        if (empty($versionFromFile)) {
            $this->error('VERSION file is empty');
            return 1;
        }

        $this->info("📝 Syncing APP_VERSION to: {$versionFromFile}");
        $this->line('');

        $envFiles = [
            '.env' => base_path('.env'),
            '.env.local' => base_path('.env.local'),
            '.env.production' => base_path('.env.production'),
        ];

        $updated = 0;

        foreach ($envFiles as $name => $path) {
            if (!File::exists($path)) {
                $this->comment("⊘ {$name} not found");
                continue;
            }

            $content = File::get($path);

            if (preg_match('/^APP_VERSION=.*/m', $content)) {
                $newContent = preg_replace(
                    '/^APP_VERSION=.*/m',
                    "APP_VERSION={$versionFromFile}",
                    $content
                );

                if ($newContent !== $content) {
                    File::put($path, $newContent);
                    $this->info("✅ {$name}: APP_VERSION updated to {$versionFromFile}");
                    $updated++;
                } else {
                    $this->comment("→ {$name}: Already set to {$versionFromFile}");
                }
            } else {
                $newContent = rtrim($content) . "\nAPP_VERSION={$versionFromFile}\n";
                File::put($path, $newContent);
                $this->info("✅ {$name}: APP_VERSION added as {$versionFromFile}");
                $updated++;
            }
        }

        $this->line('');

        if ($updated > 0) {
            $this->info("✅ Synchronized {$updated} file(s) with VERSION file");
            return 0;
        } else {
            $this->comment("ℹ️  All files already synchronized");
            return 0;
        }
    }
}
