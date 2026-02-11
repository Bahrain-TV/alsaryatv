<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class QuestionsSeeder extends Seeder
{
    public function run()
    {
        // Decrypt and import during seeding if .enc file is present
        $this->command->info('Attempting to import encrypted questions (if present)...');

        $exit = Artisan::call('questions:import', ['--file' => base_path('PROJECT_DOCS/questions.json.enc'), '--truncate' => true]);

        if ($exit !== 0) {
            $this->command->warn('questions:import returned non-zero exit code. Check file presence or APP_KEY.');
        }
    }
}
