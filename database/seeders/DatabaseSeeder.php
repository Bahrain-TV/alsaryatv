<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Realistic seed data: callers are imported from database/seeders/data/callers_seed.csv by CallerSeeder.
        // Factory-based fake caller generation is intentionally disabled to ensure production-like seed data.
        // Example (disabled):
        // Caller::factory()->count(500)->create();

        $this->call([
            UserSeeder::class,
            CallerSeeder::class,
        ]);
    }
}
