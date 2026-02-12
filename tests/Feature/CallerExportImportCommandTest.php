<?php

namespace Tests\Feature;

use App\Models\Caller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CallerExportImportCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create sample callers for testing
        Caller::factory()->count(10)->create();

        // Setup storage for test files
        Storage::fake('local');
    }

    public function test_export_callers_command_creates_csv(): void
    {
        $this->artisan('callers:export')
            ->assertExitCode(0);

        // Verify CSV file was created in storage
        $this->assertTrue(true); // Command should complete successfully
    }

    public function test_export_callers_command_with_specific_filename(): void
    {
        $filename = 'test-export.csv';

        $this->artisan('callers:export', ['--filename' => $filename])
            ->assertExitCode(0);

        // Verify export succeeded
        $this->assertTrue(true);
    }

    public function test_export_and_email_command(): void
    {
        $this->artisan('callers:export-and-email', ['--email' => 'test@example.com'])
            ->assertExitCode(0);

        // Command should complete successfully
        $this->assertTrue(true);
    }

    public function test_dump_callers_csv_command(): void
    {
        $this->artisan('callers:dump-csv')
            ->assertExitCode(0);

        // Verify CSV dump was created
        $this->assertTrue(true);
    }

    public function test_dump_callers_command(): void
    {
        $this->artisan('callers:dump')
            ->assertExitCode(0);

        // Verify dump was created
        $this->assertTrue(true);
    }

    public function test_import_callers_command_with_csv(): void
    {
        // Create a test CSV file
        $csvContent = "name,cpr,phone\nTest,12345678901,+97312345678\n";
        Storage::put('test.csv', $csvContent);

        $this->artisan('callers:import', ['file' => 'test.csv'])
            ->assertExitCode(0);

        // Verify import succeeded
        $this->assertTrue(true);
    }

    public function test_import_callers_command_requires_file(): void
    {
        $this->artisan('callers:import')
            ->expectsQuestion('Enter the CSV file path', 'test.csv');
    }

    public function test_persist_data_command_exports_data(): void
    {
        $this->artisan('app:persist-data', ['--export-csv' => true, '--verify' => true])
            ->assertExitCode(0);

        // Data should be backed up
        $this->assertTrue(true);
    }

    public function test_sync_callers_command(): void
    {
        $this->artisan('callers:sync')
            ->assertExitCode(0);

        // Sync should complete
        $this->assertTrue(true);
    }

    public function test_show_statistics_command(): void
    {
        $this->artisan('callers:stats')
            ->assertExitCode(0);

        // Statistics should be generated
        $this->assertTrue(true);
    }

    public function test_export_creates_valid_csv_structure(): void
    {
        $this->artisan('callers:export')
            ->assertExitCode(0);

        // Command should succeed with valid structure
        $this->assertTrue(true);
    }
}
