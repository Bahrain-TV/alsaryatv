<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Crypt;

class QuestionsEncrypt extends Command
{
    protected $signature = 'questions:encrypt {--file=PROJECT_DOCS/questions.json} {--out=}';
    protected $description = 'Encrypt a questions JSON file using Laravel Crypt and write .enc file';

    public function handle(Filesystem $files)
    {
        $file = base_path($this->option('file'));
        if (! $files->exists($file)) {
            $this->error('File not found: '.$file);
            return 1;
        }

        $json = $files->get($file);
        $this->info('Validating JSON...');
        $decoded = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON: '.json_last_error_msg());
            return 1;
        }

        $encrypted = Crypt::encryptString($json);
        $out = $this->option('out') ?: $file.'.enc';
        $files->put($out, $encrypted);
        $this->info('Encrypted to: '.$out);
        $this->info('Add the .enc file to source control and remove the plaintext file from commits.');

        return 0;
    }
}
