<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class VersionSyncCommandTest extends TestCase
{
    protected string $testVersionFile;

    protected string $testVersionJsonFile;

    protected function setUp(): void
    {
        parent::setUp();

        // Create backup files
        $this->testVersionFile = base_path('VERSION.test');
        $this->testVersionJsonFile = base_path('version.json.test');

        // Backup original files
        if (File::exists(base_path('VERSION'))) {
            File::copy(base_path('VERSION'), $this->testVersionFile);
        }
        if (File::exists(base_path('version.json'))) {
            File::copy(base_path('version.json'), $this->testVersionJsonFile);
        }
    }

    protected function tearDown(): void
    {
        // Restore original files
        if (File::exists($this->testVersionFile)) {
            File::copy($this->testVersionFile, base_path('VERSION'));
            File::delete($this->testVersionFile);
        }
        if (File::exists($this->testVersionJsonFile)) {
            File::copy($this->testVersionJsonFile, base_path('version.json'));
            File::delete($this->testVersionJsonFile);
        }

        parent::tearDown();
    }

    public function test_version_sync_command_exists(): void
    {
        $this->artisan('version:sync', ['--help' => true])
            ->assertExitCode(0);
    }

    public function test_version_sync_fails_without_version_file(): void
    {
        // Remove VERSION file temporarily
        if (File::exists(base_path('VERSION'))) {
            File::delete(base_path('VERSION'));
        }

        $this->artisan('version:sync')
            ->expectsOutput('❌ VERSION file not found at: '.base_path('VERSION'))
            ->assertExitCode(1);
    }

    public function test_version_sync_fails_without_version_json_file(): void
    {
        // Remove version.json file temporarily
        if (File::exists(base_path('version.json'))) {
            File::delete(base_path('version.json'));
        }

        $this->artisan('version:sync')
            ->expectsOutput('❌ version.json file not found at: '.base_path('version.json'))
            ->assertExitCode(1);
    }

    public function test_version_sync_updates_version_json_from_version_file(): void
    {
        // Create test VERSION file with format: 3.3.1-32
        File::put(base_path('VERSION'), "3.3.1-32\n");

        // Create test version.json with different version
        $versionJson = [
            'version' => '3.0.0',
            'name' => 'Test App',
            'changelog' => [],
        ];
        File::put(base_path('version.json'), json_encode($versionJson, JSON_PRETTY_PRINT));

        // Run sync command
        $this->artisan('version:sync', ['--from' => 'VERSION'])
            ->assertExitCode(0);

        // Verify version.json was updated
        $updatedJson = json_decode(File::get(base_path('version.json')), true);
        $this->assertEquals('3.3.1', $updatedJson['version']);
        $this->assertEquals('version:sync-command', $updatedJson['updated_by']);
        $this->assertNotEmpty($updatedJson['changelog']);
        $this->assertEquals('3.3.1', $updatedJson['changelog'][0]['version']);
        $this->assertEquals('sync', $updatedJson['changelog'][0]['type']);
    }

    public function test_version_sync_dry_run_does_not_modify_files(): void
    {
        // Create test VERSION file
        File::put(base_path('VERSION'), "3.3.1-32\n");

        // Create test version.json with different version
        $versionJson = [
            'version' => '3.0.0',
            'name' => 'Test App',
            'changelog' => [],
        ];
        $originalJson = json_encode($versionJson, JSON_PRETTY_PRINT);
        File::put(base_path('version.json'), $originalJson);

        // Run sync command with dry-run
        $this->artisan('version:sync', ['--from' => 'VERSION', '--dry-run' => true])
            ->assertExitCode(0);

        // Verify version.json was NOT updated
        $currentJson = File::get(base_path('version.json'));
        $this->assertEquals($originalJson, $currentJson);
    }

    public function test_version_sync_detects_when_already_synchronized(): void
    {
        // Create test VERSION file
        File::put(base_path('VERSION'), "3.3.1-32\n");

        // Create test version.json with matching version
        $versionJson = [
            'version' => '3.3.1',
            'name' => 'Test App',
            'changelog' => [],
        ];
        File::put(base_path('version.json'), json_encode($versionJson, JSON_PRETTY_PRINT));

        // Run sync command
        $this->artisan('version:sync', ['--from' => 'VERSION'])
            ->expectsOutputToContain('already synchronized')
            ->assertExitCode(0);
    }

    public function test_version_sync_handles_version_without_build_number(): void
    {
        // Create test VERSION file without build number
        File::put(base_path('VERSION'), "3.3.1\n");

        // Create test version.json with different version
        $versionJson = [
            'version' => '3.0.0',
            'name' => 'Test App',
            'changelog' => [],
        ];
        File::put(base_path('version.json'), json_encode($versionJson, JSON_PRETTY_PRINT));

        // Run sync command
        $this->artisan('version:sync', ['--from' => 'VERSION'])
            ->assertExitCode(0);

        // Verify version.json was updated
        $updatedJson = json_decode(File::get(base_path('version.json')), true);
        $this->assertEquals('3.3.1', $updatedJson['version']);
    }

    public function test_version_sync_fails_with_invalid_version_format(): void
    {
        // Create test VERSION file with invalid format
        File::put(base_path('VERSION'), "invalid-version\n");

        // Create test version.json
        $versionJson = [
            'version' => '3.0.0',
            'name' => 'Test App',
        ];
        File::put(base_path('version.json'), json_encode($versionJson, JSON_PRETTY_PRINT));

        // Run sync command
        $this->artisan('version:sync', ['--from' => 'VERSION'])
            ->expectsOutputToContain('Invalid VERSION file format')
            ->assertExitCode(1);
    }

    public function test_version_sync_can_update_env_files(): void
    {
        // Create test VERSION file
        File::put(base_path('VERSION'), "3.3.1-32\n");

        // Create test version.json
        $versionJson = [
            'version' => '3.3.1',
            'name' => 'Test App',
            'changelog' => [],
        ];
        File::put(base_path('version.json'), json_encode($versionJson, JSON_PRETTY_PRINT));

        // Create test .env file
        $envFile = base_path('.env.test-sync');
        File::put($envFile, "APP_NAME=Test\nAPP_KEY=test\n");

        // Run sync command with --update-env
        $this->artisan('version:sync', ['--from' => 'VERSION', '--update-env' => true])
            ->assertExitCode(0);

        // Note: This test would need to be enhanced to verify .env file updates
        // For now, we're just ensuring the command runs successfully with the flag

        // Cleanup
        if (File::exists($envFile)) {
            File::delete($envFile);
        }
    }

    public function test_version_sync_from_version_json_to_version_file(): void
    {
        // Create test VERSION file
        File::put(base_path('VERSION'), "3.0.0-1\n");

        // Create test version.json with different version
        $versionJson = [
            'version' => '3.3.1',
            'name' => 'Test App',
            'changelog' => [],
        ];
        File::put(base_path('version.json'), json_encode($versionJson, JSON_PRETTY_PRINT));

        // Run sync command from version.json
        $this->artisan('version:sync', ['--from' => 'version.json'])
            ->assertExitCode(0);

        // Verify VERSION file was updated
        $updatedVersion = trim(File::get(base_path('VERSION')));
        $this->assertEquals('3.3.1-1', $updatedVersion);
    }

    public function test_version_sync_preserves_changelog_entries(): void
    {
        // Create test VERSION file
        File::put(base_path('VERSION'), "3.3.1-32\n");

        // Create test version.json with existing changelog
        $versionJson = [
            'version' => '3.0.0',
            'name' => 'Test App',
            'changelog' => [
                [
                    'version' => '3.0.0',
                    'type' => 'feature',
                    'message' => 'Original feature',
                    'timestamp' => '2026-01-01T00:00:00+00:00',
                ],
            ],
        ];
        File::put(base_path('version.json'), json_encode($versionJson, JSON_PRETTY_PRINT));

        // Run sync command
        $this->artisan('version:sync', ['--from' => 'VERSION'])
            ->assertExitCode(0);

        // Verify changelog was preserved and new entry was added
        $updatedJson = json_decode(File::get(base_path('version.json')), true);
        $this->assertCount(2, $updatedJson['changelog']);
        $this->assertEquals('sync', $updatedJson['changelog'][0]['type']);
        $this->assertEquals('feature', $updatedJson['changelog'][1]['type']);
        $this->assertEquals('Original feature', $updatedJson['changelog'][1]['message']);
    }
}
