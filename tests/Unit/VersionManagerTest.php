<?php

namespace Tests\Unit;

use App\Services\VersionManager;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class VersionManagerTest extends TestCase
{
    private string $versionFilePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->versionFilePath = base_path('version.json');

        // Backup original if exists
        if (File::exists($this->versionFilePath)) {
            File::copy($this->versionFilePath, $this->versionFilePath.'.bak');
        }
    }

    protected function tearDown(): void
    {
        // Restore original
        if (File::exists($this->versionFilePath.'.bak')) {
            File::move($this->versionFilePath.'.bak', $this->versionFilePath);
        }

        parent::tearDown();
    }

    public function test_get_version_returns_string(): void
    {
        $version = VersionManager::getVersion();

        $this->assertIsString($version);
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', $version);
    }

    public function test_get_version_info_returns_array_with_version_key(): void
    {
        $info = VersionManager::getVersionInfo();

        $this->assertIsArray($info);
        $this->assertArrayHasKey('version', $info);
    }

    public function test_set_version_updates_version(): void
    {
        VersionManager::setVersion('9.8.7');
        $version = VersionManager::getVersion();

        $this->assertEquals('9.8.7', $version);
    }

    public function test_set_version_rejects_invalid_format(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        VersionManager::setVersion('invalid-version');
    }

    public function test_set_version_rejects_partial_version(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        VersionManager::setVersion('1.2');
    }

    public function test_increment_patch(): void
    {
        VersionManager::setVersion('1.2.3');
        $newVersion = VersionManager::incrementPatch();

        $this->assertEquals('1.2.4', $newVersion);
    }

    public function test_increment_minor_resets_patch(): void
    {
        VersionManager::setVersion('1.2.3');
        $newVersion = VersionManager::incrementMinor();

        $this->assertEquals('1.3.0', $newVersion);
    }

    public function test_increment_major_resets_minor_and_patch(): void
    {
        VersionManager::setVersion('1.2.3');
        $newVersion = VersionManager::incrementMajor();

        $this->assertEquals('2.0.0', $newVersion);
    }

    public function test_compare_versions(): void
    {
        $this->assertEquals(1, VersionManager::compareVersions('2.0.0', '1.0.0'));
        $this->assertEquals(-1, VersionManager::compareVersions('1.0.0', '2.0.0'));
        $this->assertEquals(0, VersionManager::compareVersions('1.0.0', '1.0.0'));
    }

    public function test_is_outdated_returns_true_when_remote_is_newer(): void
    {
        VersionManager::setVersion('1.0.0');

        $this->assertTrue(VersionManager::isOutdated('2.0.0'));
    }

    public function test_is_outdated_returns_false_when_current_is_newer(): void
    {
        VersionManager::setVersion('3.0.0');

        $this->assertFalse(VersionManager::isOutdated('2.0.0'));
    }

    public function test_get_change_log_returns_array(): void
    {
        $changelog = VersionManager::getChangeLog();

        $this->assertIsArray($changelog);
    }

    public function test_add_change_log_appends_entry(): void
    {
        $initialCount = count(VersionManager::getChangeLog());

        VersionManager::addChangeLog('feature', 'Added new test');

        $changelog = VersionManager::getChangeLog();
        $this->assertCount($initialCount + 1, $changelog);
        $this->assertEquals('feature', $changelog[0]['type']);
        $this->assertEquals('Added new test', $changelog[0]['message']);
    }

    public function test_get_branch_returns_non_empty_string(): void
    {
        $branch = VersionManager::getBranch();

        $this->assertIsString($branch);
        $this->assertNotEmpty($branch);
    }

    public function test_get_commit_hash_returns_non_empty_string(): void
    {
        $hash = VersionManager::getCommitHash();

        $this->assertIsString($hash);
        $this->assertNotEmpty($hash);
    }
}
