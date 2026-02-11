<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuestionsEncryptTest extends TestCase
{
    use RefreshDatabase;

    public function test_encrypt_command_creates_encrypted_file_and_is_decryptable()
    {
        File::ensureDirectoryExists(base_path('PROJECT_DOCS'));

        $data = [
            [
                'question' => 'نموذج سؤال',
                'answers' => ['1', '2', '3', '4'],
                'correct_answer' => 1,
            ],
        ];

        $plainPath = base_path('PROJECT_DOCS/questions.json');
        $encPath = base_path('PROJECT_DOCS/questions.json.enc');

        File::put($plainPath, json_encode($data, JSON_UNESCAPED_UNICODE));

        // Ensure no leftover enc file
        if (File::exists($encPath)) {
            File::delete($encPath);
        }

        $exit = Artisan::call('questions:encrypt', ['--file' => 'PROJECT_DOCS/questions.json', '--out' => 'PROJECT_DOCS/questions.json.enc']);
        $this->assertEquals(0, $exit);
        $this->assertTrue(File::exists($encPath), 'Encrypted file should exist');

        $encrypted = File::get($encPath);
        $decrypted = Crypt::decryptString($encrypted);

        $this->assertJson($decrypted);
        $this->assertEquals(json_encode($data, JSON_UNESCAPED_UNICODE), $decrypted);

        // Cleanup
        File::delete($plainPath);
        File::delete($encPath);
    }
}
