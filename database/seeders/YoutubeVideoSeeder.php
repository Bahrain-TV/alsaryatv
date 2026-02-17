<?php

namespace Database\Seeders;

use App\Models\YoutubeVideo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class YoutubeVideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        YoutubeVideo::create([
            'title' => 'بث مباشر من برنامج السارية',
            'description' => 'شاهد البث المباشر لبرنامج السارية على تلفزيون البحرين',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'is_live_stream' => true,
            'is_enabled' => true,
            'sort_order' => 1,
        ]);

        YoutubeVideo::create([
            'title' => 'مقدمة برنامج السارية',
            'description' => 'تعرف على برنامج السارية وكيفية المشاركة',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'is_live_stream' => false,
            'is_enabled' => true,
            'sort_order' => 2,
        ]);
    }
}
