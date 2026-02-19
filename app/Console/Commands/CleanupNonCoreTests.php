<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanupNonCoreTests extends Command
{
    protected $signature = 'tests:cleanup-non-core';
    protected $description = 'Remove non-core business tests, keeping only caller registration, security, and admin tests';

    public function handle(): int
    {
        $this->info('🗑️  Starting non-core test cleanup...');
        $this->newLine();

        $deletedCount = 0;

        // Infrastructure/Version/Production tests
        $infrastructureTests = [
            'tests/Feature/VersionManagerTest.php',
            'tests/Feature/VersionSyncCommandTest.php',
            'tests/Feature/ProductionUrlTest.php',
            'tests/Feature/ProductionUrlCurlTest.php',
            'tests/Feature/PRODUCTION_TESTS_README.md',
        ];

        // Implementation details tests
        $implementationTests = [
            'tests/Feature/CprHashingServiceTest.php',
        ];

        // Low-value tests
        $lowValueTests = [
            'tests/Feature/SplashRoutingTest.php',
            'tests/Feature/AdminPanelTest.php', // Duplicate of Admin/AdminPanelTest.php
        ];

        // Directories to delete
        $directoriesToDelete = [
            'tests/Feature/Auth',      // Jetstream user auth tests
            'tests/Feature/Settings',  // User account settings tests
            'tests/Browser',           // UI/Dusk automation tests
        ];

        // Delete individual files
        foreach (array_merge($infrastructureTests, $implementationTests, $lowValueTests) as $file) {
            $path = base_path($file);
            if (File::exists($path)) {
                File::delete($path);
                $this->line("  ✓ Deleted <fg=green>{$file}</>");
                $deletedCount++;
            }
        }

        $this->newLine();

        // Delete directories
        foreach ($directoriesToDelete as $dir) {
            $path = base_path($dir);
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);
                $this->line("  ✓ Deleted directory <fg=green>{$dir}</>");
                $deletedCount++;
            }
        }

        $this->newLine();
        $this->info("✅ Cleanup complete! Deleted {$deletedCount} test files/directories");
        $this->newLine();

        // Show remaining core tests
        $coreTests = [
            'tests/Feature/CallerRegistrationTest.php',
            'tests/Feature/CallerRegistrationSecurityTest.php',
            'tests/Feature/CallerModelTest.php',
            'tests/Feature/FormValidationTest.php',
            'tests/Feature/MainFunctionalityTest.php',
            'tests/Feature/FilamentDashboardFeatureTest.php',
            'tests/Feature/Admin/AdminPanelTest.php',
            'tests/Feature/Admin/DashboardWidgetsTest.php',
        ];

        $this->info('📋 Remaining core business tests:');
        foreach ($coreTests as $test) {
            if (File::exists(base_path($test))) {
                $this->line("  ✓ <fg=blue>{$test}</>");
            }
        }

        return self::SUCCESS;
    }
}
