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
        if (Caller::count() > 0) {
            return;
        }

        if (app()->environment('production')) {
            return;
        }

        // Keep local/dev seed small when database is empty.
        Caller::factory()->count(10)->create([
            'is_family' => false,
        ]);

        Caller::factory()->count(10)->create([
            'is_family' => true,
        ]);
    }
}
