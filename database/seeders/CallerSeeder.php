<?php

namespace Database\Seeders;

use App\Models\Caller;
use Illuminate\Database\Seeder;

class CallerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing callers
        Caller::truncate();

        // Seed fixed counts to avoid accumulating generated data.
        Caller::factory()->count(410)->create([
            'is_family' => false,
        ]);

        Caller::factory()->count(10)->create([
            'is_family' => true,
        ]);
    }
}
