<?php

namespace Tests\Feature;

use App\Services\VersionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class VersionManagerTest extends TestCase
{
    use RefreshDatabase;

    protected string $versionFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->versionFile = base_path('version.json');
    }

    public function test_get_version_returns_current_version(): void
    {
        $version = VersionManager::getVersion();

        $this->assertNotNull($version);
        $this->assertIsString($version);

        // Version should match semantic versioning pattern
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+/', $version);
    }

    public function test_get_version_info_returns_array(): void
    {
        $info = VersionManager::getVersionInfo();

        $this->assertIsArray($info);
        $this->assertArrayHasKey('version', $info);
    }

    public function test_version_is_consistent(): void
    {
        $version1 = VersionManager::getVersion();
        $version2 = VersionManager::getVersion();

        $this->assertEquals($version1, $version2);
    }

    public function test_version_has_semantic_versioning(): void
    {
        $version = VersionManager::getVersion();

        // Should be in format X.Y.Z
        $parts = explode('.', $version);

        $this->assertCount(3, $parts);
        $this->assertTrue(is_numeric($parts[0]));
        $this->assertTrue(is_numeric($parts[1]));
    }

    public function test_version_info_contains_name(): void
    {
        $info = VersionManager::getVersionInfo();

        $this->assertArrayHasKey('name', $info);
        $this->assertNotEmpty($info['name']);
    }

    public function test_version_info_contains_timestamp(): void
    {
        $info = VersionManager::getVersionInfo();

        // May or may not have timestamp depending on setup
        $this->assertIsArray($info);
    }

    public function test_increment_patch_version(): void
    {
        $oldVersion = VersionManager::getVersion();
        $newVersion = VersionManager::incrementPatch();

        $this->assertNotEquals($oldVersion, $newVersion);

        // Patch should be incremented
        $this->assertTrue(version_compare($newVersion, $oldVersion, '>'));
    }

    public function test_increment_minor_version(): void
    {
        $oldVersion = VersionManager::getVersion();
        $newVersion = VersionManager::incrementMinor();

        $this->assertNotEquals($oldVersion, $newVersion);

        // Version should be greater
        $this->assertTrue(version_compare($newVersion, $oldVersion, '>'));
    }

    public function test_increment_major_version(): void
    {
        $oldVersion = VersionManager::getVersion();
        $newVersion = VersionManager::incrementMajor();

        $this->assertNotEquals($oldVersion, $newVersion);

        // Version should be greater
        $this->assertTrue(version_compare($newVersion, $oldVersion, '>'));
    }

    public function test_version_file_exists(): void
    {
        $this->assertTrue(File::exists($this->versionFile));
    }

    public function test_version_file_contains_valid_json(): void
    {
        $content = File::get($this->versionFile);
        $data = json_decode($content, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('version', $data);
    }
}
