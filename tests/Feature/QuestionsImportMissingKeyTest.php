<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuestionsImportMissingKeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_fails_when_app_key_missing()
    {
        File::ensureDirectoryExists(base_path('PROJECT_DOCS'));

        $data = [
            [
                'question' => 'سؤال للاختبار',
                'answers' => ['أ','ب'],
                'correct_answer' => 1,
            ],
        ];

        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        $enc = Crypt::encryptString($json);
        $encPath = base_path('PROJECT_DOCS/questions.json.enc');
        File::put($encPath, $enc);

        // Simulate missing APP_KEY
        Config::set('app.key', '');

        $exit = Artisan::call('questions:import', ['--file' => 'PROJECT_DOCS/questions.json.enc']);
        $this->assertNotEquals(0, $exit, 'Import should fail when APP_KEY is missing');

        // Clean up
        File::delete($encPath);
    }
}
