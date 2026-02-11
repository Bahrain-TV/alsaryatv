<?php

namespace Tests\Feature;

use App\Models\Question;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionsImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_decrypts_and_inserts_questions()
    {
        $data = [
            [
                'question' => 'ما هو الاختبار؟',
                'answers' => ['أ', 'ب', 'ج', 'د'],
                'correct_answer' => 2,
            ],
            [
                'question' => 'سؤال ثانٍ',
                'answers' => ['واحد', 'اثنان'],
                'correct_answer' => 1,
            ],
        ];

        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        $enc = Crypt::encryptString($json);

        $path = base_path('PROJECT_DOCS/questions.json.enc');
        File::ensureDirectoryExists(base_path('PROJECT_DOCS'));
        File::put($path, $enc);

        $exit = Artisan::call('questions:import', ['--file' => 'PROJECT_DOCS/questions.json.enc', '--truncate' => true]);

        $this->assertEquals(0, $exit, 'artisan command exit code should be zero');
        $this->assertDatabaseCount('questions', 2);

        $q = Question::first();
        $this->assertEquals('ما هو الاختبار؟', $q->question);
        $this->assertEquals(['أ','ب','ج','د'], $q->answers);
        $this->assertEquals(2, $q->correct_answer);

        // Clean up
        File::delete($path);
    }
}
