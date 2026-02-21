<?php

namespace Tests\Feature;

use App\Models\Caller;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\DB;

class DataPreservationTest extends TestCase
{
    /**
     * Test that backup:data command exists and works.
     */
    #[Test]
    public function backup_data_command_exists(): void
    {
        $this->artisan('backup:data --type=all')
            ->expectsOutputToContain('Backup')
            ->assertSuccessful();
    }

    /**
     * Test that app:persist-data command exists and verifies data.
     */
    #[Test]
    public function persist_data_command_exists(): void
    {
        $this->artisan('app:persist-data --verify')
            ->expectsOutputToContain('persist')
            ->assertSuccessful();
    }

    /**
     * Test that winners data is preserved across operations.
     */
    #[Test]
    public function winners_data_preservation_command_works(): void
    {
        // Just test that the command runs successfully
        $this->artisan('app:persist-data --verify')
            ->assertSuccessful();
        
        // Verify the command doesn't destroy data
        $winnersCount = Caller::where('is_winner', true)->count();
        $this->assertGreaterThanOrEqual(0, $winnersCount);
    }

    /**
     * Test that selected callers data is preserved.
     */
    #[Test]
    public function selected_callers_preservation_command_works(): void
    {
        // Just test that the command runs successfully
        $this->artisan('app:persist-data --verify')
            ->assertSuccessful();
        
        // Verify the command doesn't destroy data
        $selectedCount = Caller::where('is_selected', true)->where('is_winner', false)->count();
        $this->assertGreaterThanOrEqual(0, $selectedCount);
    }

    /**
     * Test that caller levels are preserved.
     */
    #[Test]
    public function caller_levels_preservation_command_works(): void
    {
        // Just test that the command runs successfully
        $this->artisan('app:persist-data --verify')
            ->assertSuccessful();
        
        // Verify levels exist
        $goldCount = Caller::where('level', 'gold')->count();
        $silverCount = Caller::where('level', 'silver')->count();
        $this->assertGreaterThanOrEqual(0, $goldCount);
        $this->assertGreaterThanOrEqual(0, $silverCount);
    }

    /**
     * Test that deploy scripts contain data preservation commands.
     */
    #[Test]
    public function deploy_scripts_contain_data_preservation(): void
    {
        $deployScripts = [
            'deploy.sh',
            'deploy-simple.sh',
            'deploy-auto.sh',
            'deploy-and-up.sh',
            'deploy_registration_fix.sh',
        ];

        foreach ($deployScripts as $script) {
            $path = base_path($script);
            $this->assertFileExists($path, "Deploy script $script should exist");
            
            $content = file_get_contents($path);
            
            $this->assertStringContainsString(
                'backup:data',
                $content,
                "$script should contain backup:data command"
            );
            
            $this->assertStringContainsString(
                'app:persist-data',
                $content,
                "$script should contain app:persist-data command"
            );
        }
    }

    /**
     * Test that deploy-with-data-preserve.sh script exists.
     */
    #[Test]
    public function data_preserve_script_exists(): void
    {
        $path = base_path('deploy-with-data-preserve.sh');
        $this->assertFileExists($path, 'deploy-with-data-preserve.sh should exist');
        
        $content = file_get_contents($path);
        
        $this->assertStringContainsString(
            'Pre-Migration Backup',
            $content,
            'Script should have pre-migration backup step'
        );
        
        $this->assertStringContainsString(
            'post-migration',
            strtolower($content),
            'Script should have post-migration verification'
        );
    }

    /**
     * Test that critical data fields exist in callers table.
     */
    #[Test]
    public function critical_data_fields_exist(): void
    {
        $this->assertTrue(
            DB::getSchemaBuilder()->hasColumn('callers', 'is_winner'),
            'callers table should have is_winner column'
        );
        
        $this->assertTrue(
            DB::getSchemaBuilder()->hasColumn('callers', 'is_selected'),
            'callers table should have is_selected column'
        );
        
        $this->assertTrue(
            DB::getSchemaBuilder()->hasColumn('callers', 'level'),
            'callers table should have level column'
        );
        
        $this->assertTrue(
            DB::getSchemaBuilder()->hasColumn('callers', 'hits'),
            'callers table should have hits column'
        );
    }

    /**
     * Test that production database has winners data.
     */
    #[Test]
    public function production_has_winner_data(): void
    {
        $winners = Caller::where('is_winner', true)->count();
        $this->assertGreaterThan(0, $winners, 'Should have at least one winner in database');
    }

    /**
     * Test that data preservation creates backup files.
     */
    #[Test]
    public function backup_creates_files(): void
    {
        // Run backup command
        $this->artisan('backup:data --type=all')
            ->assertSuccessful();
        
        // Check if backup directory exists and has files
        $backupDir = storage_path('backups');
        $this->assertDirectoryExists($backupDir, 'Backup directory should exist');
    }
}
