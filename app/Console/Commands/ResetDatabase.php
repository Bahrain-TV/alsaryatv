<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Migrations\ResetCommand;

class ResetDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:reset-fresh
                            {--seed : Seed the database after reset}
                            {--force : Force the operation without confirmation}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Reset the entire database, deleting all tables and data without backup, then optionally seed fresh data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // Display warning
        $this->newLine();
        $this->warn('⚠️  WARNING: This will DELETE ALL DATABASE TABLES AND DATA permanently!');
        $this->warn('No backup will be created. This action CANNOT be undone.');
        $this->newLine();

        // Ask for confirmation unless --force flag is used
        if (!$this->option('force')) {
            if (!$this->confirm('Do you really want to reset the entire database? (This cannot be undone)', false)) {
                $this->info('Database reset cancelled.');
                return 0;
            }
        }

        $this->newLine();
        $this->info('🗑️  Resetting database...');

        try {
            // Get the database connection
            $connection = DB::connection();
            $driver = $connection->getDriverName();

            if ($driver === 'sqlite') {
                // For SQLite, we need to drop all tables manually
                $this->resetSqliteDatabase();
            } else {
                // For MySQL/PostgreSQL, use foreign_key_checks
                $this->resetMysqlLikeDatabase($driver);
            }

            $this->info('✅ Database reset complete! All tables dropped.');

            // Run migrations
            $this->newLine();
            $this->info('🔄 Running migrations...');
            $this->call('migrate', ['--force' => true]);
            $this->info('✅ Migrations completed.');

            // Seed if requested
            if ($this->option('seed')) {
                $this->newLine();
                $this->info('🌱 Seeding database with fresh data...');
                $this->call('db:seed', ['--force' => true]);
                $this->info('✅ Database seeding completed.');
            }

            $this->newLine();
            $this->info('🎉 Database reset successfully!');
            if ($this->option('seed')) {
                $this->info('Fresh data has been seeded.');
            } else {
                $this->line('Use <fg=cyan>php artisan db:seed</> to seed sample data.');
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error resetting database: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Reset SQLite database by dropping all tables.
     *
     * @return void
     */
    protected function resetSqliteDatabase(): void
    {
        $connection = DB::connection();
        $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

        foreach ($tables as $table) {
            $connection->statement('DROP TABLE IF EXISTS ' . $table->name);
        }

        // Clear sqlite_sequence if it exists
        try {
            $connection->statement('DELETE FROM sqlite_sequence');
        } catch (\Exception $e) {
            // Table might not exist, that's fine
        }
    }

    /**
     * Reset MySQL-like databases by disabling foreign key checks.
     *
     * @param string $driver
     * @return void
     */
    protected function resetMysqlLikeDatabase(string $driver): void
    {
        $connection = DB::connection();

        // Disable foreign key checks
        if ($driver === 'mysql') {
            $connection->statement('SET FOREIGN_KEY_CHECKS = 0');
        } elseif ($driver === 'pgsql') {
            $connection->statement('SET CONSTRAINTS ALL DEFERRED');
        }

        // Get all tables
        if ($driver === 'mysql') {
            $tables = DB::select('SHOW TABLES');
            $tableKey = 'Tables_in_' . env('DB_DATABASE');
        } else {
            // PostgreSQL
            $tables = DB::select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public'");
            $tableKey = 'tablename';
        }

        // Drop all tables
        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            if ($driver === 'mysql') {
                $connection->statement('DROP TABLE IF EXISTS `' . $tableName . '`');
            } else {
                $connection->statement('DROP TABLE IF EXISTS "' . $tableName . '" CASCADE');
            }
        }

        // Re-enable foreign key checks
        if ($driver === 'mysql') {
            $connection->statement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }
}
