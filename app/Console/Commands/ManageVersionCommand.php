<?php

namespace App\Console\Commands;

use App\Services\VersionManager;
use Illuminate\Console\Command;

class ManageVersionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pkg:version {action : Action to perform (get, increment, set, changelog)}
                            {--type=patch : Version part to increment (major, minor, patch)}
                            {--v= : Specific version to set}
                            {--limit=20 : Limit for changelog display}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage application version';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $action = $this->argument('action');

        try {
            match ($action) {
                'get' => $this->showVersion(),
                'increment' => $this->incrementVersion(),
                'set' => $this->setVersion(),
                'changelog' => $this->showChangeLog(),
                default => $this->error("Unknown action: {$action}"),
            };
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
        }
    }

    private function showVersion(): void
    {
        $info = VersionManager::getVersionInfo();
        $branch = VersionManager::getBranch();
        $commit = VersionManager::getCommitHash();

        $this->info('Current Version Information:');
        $this->newLine();

        $this->table(
            ['Property', 'Value'],
            [
                ['Version', $info['version']],
                ['Name', $info['name']],
                ['Branch', $branch],
                ['Commit', $commit],
                ['Environment', app()->environment()],
                ['Updated At', $info['updated_at']],
                ['Updated By', $info['updated_by']],
            ]
        );
    }

    private function incrementVersion(): void
    {
        $type = $this->option('type');

        if (! in_array($type, ['major', 'minor', 'patch'])) {
            $this->error("Invalid type: {$type}. Use: major, minor, or patch");

            return;
        }

        $oldVersion = VersionManager::getVersion();
        $newVersion = match ($type) {
            'major' => VersionManager::incrementMajor(),
            'minor' => VersionManager::incrementMinor(),
            'patch' => VersionManager::incrementPatch(),
        };

        $this->info('✓ Version incremented successfully');
        $this->table(
            ['Property', 'Value'],
            [
                ['Type', ucfirst($type)],
                ['Previous', $oldVersion],
                ['New', $newVersion],
                ['Branch', VersionManager::getBranch()],
                ['Timestamp', now()->toDateTimeString()],
            ]
        );

        $this->line('');
        $this->comment('Don\'t forget to commit this change:');
        $this->comment('  git add version.json');
        $this->comment('  git commit -m "chore: bump version to '.$newVersion.'"');
    }

    private function setVersion(): void
    {
        $version = $this->option('v');

        if (! $version) {
            $this->error('Please provide a version with --v option');
            $this->comment('Example: php artisan pkg:version set --v=2.0.0');

            return;
        }

        $oldVersion = VersionManager::getVersion();
        VersionManager::setVersion($version);

        $this->info('✓ Version set successfully');
        $this->table(
            ['Property', 'Value'],
            [
                ['Previous', $oldVersion],
                ['New', $version],
                ['Branch', VersionManager::getBranch()],
                ['Timestamp', now()->toDateTimeString()],
            ]
        );
    }

    private function showChangeLog(): void
    {
        $changelog = VersionManager::getChangeLog();
        $limit = $this->option('limit');

        if (empty($changelog)) {
            $this->info('No changelog entries found');
            return;
        }

        $displayed = array_slice($changelog, 0, $limit);

        $this->info("Changelog (showing {count($displayed)} of {count($changelog)} entries):");
        $this->newLine();

        $this->table(
            ['Version', 'Type', 'Message', 'Timestamp'],
            array_map(fn ($entry) => [
                $entry['version'],
                strtoupper($entry['type']),
                substr($entry['message'], 0, 50).(strlen($entry['message']) > 50 ? '...' : ''),
                \Carbon\Carbon::parse($entry['timestamp'])->format('Y-m-d H:i'),
            ], $displayed)
        );
    }
}
