<?php

namespace App\Console\Commands;

use App\Models\Question;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class QuestionsImport extends Command
{
    protected $signature = 'questions:import {--file=PROJECT_DOCS/questions.json.enc} {--truncate}';
    protected $description = 'Import encrypted questions JSON into the database';

    public function handle(Filesystem $files)
    {
        $file = base_path($this->option('file'));
        if (! $files->exists($file)) {
            $this->error('Encrypted file not found: '.$file);
            return 1;
        }

        $this->info('Reading encrypted file...');
        $encrypted = $files->get($file);

        try {
            $json = Crypt::decryptString($encrypted);
        } catch (\Exception $e) {
            $this->error('Decryption failed: '.$e->getMessage());
            return 1;
        }

        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON after decryption: '.json_last_error_msg());
            return 1;
        }

        if ($this->option('truncate')) {
            $this->info('Truncating `questions` table...');
            DB::table('questions')->truncate();
        }

        $this->info('Inserting questions...');
        $count = 0;
        foreach ($data as $row) {
            if (! isset($row['question']) || ! isset($row['answers']) || ! isset($row['correct_answer'])) {
                $this->warn('Skipping invalid row: missing keys');
                continue;
            }

            Question::create([
                'question' => $row['question'],
                'answers' => $row['answers'],
                'correct_answer' => (int) $row['correct_answer'],
                'source' => $row['source'] ?? null,
            ]);

            $count++;
        }

        $this->info("Imported {$count} questions.");

        return 0;
    }
}
