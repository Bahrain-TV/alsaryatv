<?php

namespace App\Console\Commands;

use App\Services\EnvFileUpdater;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncAppVersionCommand extends Command
{
    protected $signature = 'version:sync-app-version';

    protected $description = 'Synchronize APP_VERSION in .env files with VERSION file (includes build number)';

    public function handle()
    {
        $versionFile = base_path('VERSION');

        if (! File::exists($versionFile)) {
            $this->error('VERSION file not found at '.$versionFile);

            return 1;
        }

        $versionFromFile = trim(File::get($versionFile));

        if (empty($versionFromFile)) {
            $this->error('VERSION file is empty');

            return 1;
        }

        $this->info("📝 Syncing APP_VERSION to: {$versionFromFile}");
        $this->line('');

        $updated = EnvFileUpdater::syncAppVersion($versionFromFile);

        $this->line('');

        if ($updated > 0) {
            $this->info("✅ Synchronized {$updated} file(s) with VERSION file");

            return 0;
        } else {
            $this->comment('ℹ️  All files already synchronized');

            return 0;
        }
    }
}
