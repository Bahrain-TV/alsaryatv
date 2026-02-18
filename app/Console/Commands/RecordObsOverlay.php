<?php

namespace App\Console\Commands;

use App\Models\ObsOverlayVideo;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class RecordObsOverlay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'obs:record {--url=http://localhost:8000/obs-overlay} {--seconds=65} {--fps=30} {--timeout=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record the OBS overlay animation and store in database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🎬 AlSarya TV OBS Overlay Recorder - Scheduled Task');
        $this->line('');

        // Generate filename with timestamp
        $now = Carbon::now();
        $filename = 'obs-overlay-'.$now->format('Y-m-d-H-i-s').'.mov';
        $directory = 'obs-overlays';
        $relPath = $directory.'/'.$filename;
        $fullPath = storage_path('app/public/'.$relPath);

        // Ensure directory exists
        if (! is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $this->info("📁 Output: public/storage/{$relPath}");

        // Run the recording script
        $url = $this->option('url');
        $seconds = (int) $this->option('seconds');
        $fps = (int) $this->option('fps');

        // Compute a generous timeout: each frame can take up to ~0.5s in Playwright
        // plus 120s overhead for browser launch, pre-warm, and FFmpeg encoding.
        $userTimeout = (int) $this->option('timeout');
        $timeout = $userTimeout > 0
            ? $userTimeout
            : (int) ($seconds * $fps * 0.5) + 120;

        $this->info("⏱️  Recording {$seconds}s at {$fps} FPS (process timeout: {$timeout}s)...");
        $this->line('');

        try {
            $result = Process::path(base_path())
                ->timeout($timeout)
                ->run(
                    "npm run record:obs-overlay -- --url '{$url}' --out '{$fullPath}' --seconds {$seconds} --fps {$fps}"
                );

            if ($result->failed()) {
                $this->error('❌ Recording failed');
                $this->error($result->errorOutput());

                return self::FAILURE;
            }

            // Check if file exists and get size
            if (! file_exists($fullPath)) {
                $this->error('❌ Output file not created');

                return self::FAILURE;
            }

            $fileSize = filesize($fullPath);
            $mimeType = 'video/quicktime';

            // Store in database
            $video = ObsOverlayVideo::create([
                'filename' => $filename,
                'path' => $relPath,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
                'recorded_at' => $now,
                'status' => 'ready',
                'notes' => "Scheduled recording at {$now->format('H:i:s')}",
            ]);

            $this->info('');
            $this->info('✅ Recording completed successfully!');
            $this->info('📊 File size: '.$this->formatBytes($fileSize));
            $this->info("🆔 Video ID: {$video->id}");
            $this->line('');

            // Prune old videos (keep last 30 days)
            $this->pruneOldVideos();

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Prune videos older than 30 days.
     */
    protected function pruneOldVideos(): void
    {
        $cutoffDate = Carbon::now()->subDays(30);
        $oldVideos = ObsOverlayVideo::where('recorded_at', '<', $cutoffDate)->get();

        if ($oldVideos->isEmpty()) {
            $this->info('🧹 No videos to prune');

            return;
        }

        $this->info('🧹 Pruning '.$oldVideos->count().' old videos...');

        foreach ($oldVideos as $video) {
            try {
                // Delete file
                $fullPath = $video->getFullPath();
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                    $this->line("   ✓ Deleted: {$video->filename}");
                }

                // Mark as deleted in database
                $video->update(['status' => 'deleted']);
            } catch (\Exception $e) {
                $this->warn("   ⚠ Failed to delete {$video->filename}: {$e->getMessage()}");
            }
        }

        $this->info('✓ Pruning complete');
    }

    /**
     * Format bytes to human readable.
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }
}
