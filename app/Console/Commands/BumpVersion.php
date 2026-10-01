<?php

namespace App\Console\Commands;

use App\Services\EnvFileUpdater;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BumpVersion extends Command
{
    protected $signature = 'version:bump
                            {type=build : The type of version bump (major, minor, patch, build)}
                            {--show : Show current version without bumping}';

    protected $description = 'Bump the application version number';

    private string $versionFile;

    public function __construct()
    {
        parent::__construct();
        $this->versionFile = base_path('VERSION');
    }

    public function handle(): int
    {
        $version = $this->getCurrentVersion();

        if ($this->option('show')) {
            $this->displayVersion($version);

            return Command::SUCCESS;
        }

        $type = $this->argument('type');
        $newVersion = $this->bumpVersion($version, $type);

        $this->saveVersion($newVersion);
        $this->updateEnvFile($newVersion);

        $this->info("Version bumped: {$version['full']} → {$newVersion['full']}");
        $this->displayVersion($newVersion);

        return Command::SUCCESS;
    }

    private function getCurrentVersion(): array
    {
        if (File::exists($this->versionFile)) {
            $content = trim(File::get($this->versionFile));

            return $this->parseVersion($content);
        }

        // Default version
        return [
            'major' => 1,
            'minor' => 0,
            'patch' => 0,
            'build' => 1,
            'full' => '1.0.0-1',
        ];
    }

    private function parseVersion(string $version): array
    {
        // Parse version like "1.2.3-456" or "1.2.3"
        if (preg_match('/^(\d+)\.(\d+)\.(\d+)(?:-(\d+))?$/', $version, $matches)) {
            return [
                'major' => (int) $matches[1],
                'minor' => (int) $matches[2],
                'patch' => (int) $matches[3],
                'build' => isset($matches[4]) ? (int) $matches[4] : 1,
                'full' => $version,
            ];
        }

        // Fallback
        return [
            'major' => 1,
            'minor' => 0,
            'patch' => 0,
            'build' => 1,
            'full' => '1.0.0-1',
        ];
    }

    private function bumpVersion(array $version, string $type): array
    {
        switch ($type) {
            case 'major':
                $version['major']++;
                $version['minor'] = 0;
                $version['patch'] = 0;
                $version['build'] = 1;
                break;

            case 'minor':
                $version['minor']++;
                $version['patch'] = 0;
                $version['build'] = 1;
                break;

            case 'patch':
                $version['patch']++;
                $version['build'] = 1;
                break;

            case 'build':
            default:
                $version['build']++;
                break;
        }

        $version['full'] = sprintf(
            '%d.%d.%d-%d',
            $version['major'],
            $version['minor'],
            $version['patch'],
            $version['build']
        );

        return $version;
    }

    private function saveVersion(array $version): void
    {
        File::put($this->versionFile, $version['full']);
        $this->info('Updated VERSION file');
    }

    private function updateEnvFile(array $version): void
    {
        $envPath = base_path('.env');

        $versionStr = "{$version['major']}.{$version['minor']}.{$version['patch']}";
        EnvFileUpdater::set($envPath, 'APP_VERSION', $versionStr);
        EnvFileUpdater::set($envPath, 'APP_BUILD', (string) $version['build']);

        $this->info('Updated .env file');
    }

    private function displayVersion(array $version): void
    {
        $this->newLine();
        $this->table(
            ['Component', 'Value'],
            [
                ['Full Version', $version['full']],
                ['Major', $version['major']],
                ['Minor', $version['minor']],
                ['Patch', $version['patch']],
                ['Build', $version['build']],
            ]
        );
    }
}
