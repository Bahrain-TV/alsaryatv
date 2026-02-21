<?php

namespace App\Console\Commands;

use App\Models\ObsOverlayVideo;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class RecordObsOverlayCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'obs:record {--url=} {--environment=} {--seconds=65} {--fps=50} {--timeout=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record the OBS overlay animation and store in database (50i interlaced)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🎬 AlSarya TV OBS Overlay Recorder - 50i Interlaced');
        $this->line('');

        // Detect server port if URL not explicitly provided
        $url = $this->option('url');
        $environment = $this->option('environment');

        if (empty($url)) {
            $this->info('🔍 Detecting server environment...');

            // If environment is explicitly specified
            if ($environment === 'production') {
                $detectedUrl = $this->getProductionUrl();
                if ($detectedUrl === null) {
                    $this->error('❌ Production URL not configured. Please set APP_URL in .env');
                    return self::FAILURE;
                }
                $this->info("✅ Production mode: {$detectedUrl}");
            } elseif ($environment === 'local') {
                $detectedUrl = $this->detectLocalServer();
                if ($detectedUrl === null) {
                    $this->error('❌ No local server detected. Please start your Laravel server first.');
                    $this->error('   Run: php artisan serve');
                    return self::FAILURE;
                }
                $this->info("✅ Local server detected: {$detectedUrl}");
            } else {
                // Auto-detect: try LOCAL first (most common), then production
                $detectedUrl = $this->detectLocalServer();
                if ($detectedUrl !== null) {
                    $this->info("✅ Local server detected: {$detectedUrl}");
                } else {
                    $detectedUrl = $this->getProductionUrl();
                    if ($detectedUrl !== null) {
                        $this->info("✅ Production URL detected: {$detectedUrl}");
                    }
                }
            }

            if ($detectedUrl === null) {
                $this->error('❌ No running server detected.');
                $this->error('');
                $this->error('   The OBS overlay route is: /obs-overlay');
                $this->error('');
                $this->error('   To fix this, start your Laravel server:');
                $this->error('   ');
                $this->error('   php artisan serve --host=127.0.0.1 --port=8122');
                $this->error('   ');
                $this->error('   Or use a custom URL:');
                $this->error('   ');
                $this->error('   php artisan obs:record --url=http://127.0.0.1:8122/obs-overlay');
                $this->error('');
                return self::FAILURE;
            }

            $url = $detectedUrl.'/obs-overlay';
        }

        // Verify the URL is accessible before recording
        $this->info("🔗 Validating URL: {$url}");
        try {
            $urlCheck = Process::timeout(10)->run("curl -s -o /dev/null -w '%{{http_code}}' '{$url}'");
            $httpCode = trim($urlCheck->output());
            if ($httpCode === '404') {
                $this->error('❌ Route /obs-overlay not found on the server.');
                $this->error('');
                $this->error('   This means the production server does not have the latest code.');
                $this->error('   Please deploy the latest version first:');
                $this->error('');
                $this->error('   ./deploy-obs-route.sh');
                $this->error('');
                $this->error('   Or manually run on production:');
                $this->error('   git pull && php artisan optimize:clear && php artisan route:cache');
                return self::FAILURE;
            } elseif ($httpCode === '403') {
                $this->error('❌ Access forbidden (403). Check server permissions.');
                return self::FAILURE;
            } elseif (! in_array($httpCode, ['200', '301', '302'])) {
                $this->warn("⚠️  URL returned HTTP {$httpCode}, attempting to continue...");
            } else {
                $this->info("✅ URL is accessible (HTTP {$httpCode})");
            }
        } catch (\Exception $e) {
            $this->warn("⚠️  Could not verify URL: {$e->getMessage()}");
        }

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
        $seconds = (int) $this->option('seconds');
        $fps = (int) $this->option('fps');

        // 50i interlaced = 50 FPS (25 frames per second, each frame has 2 fields)
        // Compute timeout: each frame can take up to ~0.5s in Playwright
        // plus overhead for browser launch, pre-warm, and FFmpeg encoding.
        $userTimeout = (int) $this->option('timeout');
        $timeout = $userTimeout > 0
            ? $userTimeout
            : (int) ($seconds * $fps * 0.6) + 180;

        $this->info("⏱️  Recording {$seconds}s at {$fps} FPS (50i interlaced, process timeout: {$timeout}s)...");
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

    /**
     * Get production URL from .env file.
     */
    protected function getProductionUrl(): ?string
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            return null;
        }
        
        $envContent = file_get_contents($envPath);
        
        // Look for APP_URL with production-like domain
        if (preg_match('/APP_URL=(https?:\/\/[^\s]+)/', $envContent, $matches)) {
            $url = rtrim($matches[1], '/');
            // Verify it's not a local URL
            if (! preg_match('/https?:\/\/(localhost|127\.0\.0\.1|0\.0\.0\.0)/', $url)) {
                return $url;
            }
        }
        
        return null;
    }

    /**
     * Detect if a local server is running and return the base URL.
     */
    protected function detectLocalServer(): ?string
    {
        // First, check if APP_URL in .env has a local port we should try
        $envPath = base_path('.env');
        $appPort = null;
        
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            // Check for APP_PORT in .env
            if (preg_match('/APP_PORT=(\d+)/', $envContent, $matches)) {
                $appPort = $matches[1];
            }
            // Also check APP_URL for localhost with port
            if (preg_match('/APP_URL=https?:\/\/(?:localhost|127\.0\.0\.1):(\d+)/', $envContent, $matches)) {
                $appPort = $matches[1];
            }
        }
        
        // Build list of ports to check - APP_PORT first, then common ones
        $portsToCheck = [8122, 8000, 8080, 8001, 9000, 3000];
        if ($appPort && ! in_array($appPort, $portsToCheck)) {
            array_unshift($portsToCheck, (int) $appPort);
        }
        
        // Check both localhost and 127.0.0.1 variants
        $commonHosts = ['127.0.0.1', 'localhost'];

        foreach ($commonHosts as $host) {
            foreach ($portsToCheck as $port) {
                $url = "http://{$host}:{$port}";
                try {
                    $result = Process::timeout(2)->run("curl -s -o /dev/null -w '%{{http_code}}' {$url}");
                    $httpCode = trim($result->output());
                    if (in_array($httpCode, ['200', '301', '302', '404'])) {
                        $this->info("  Found server at {$url} (HTTP {$httpCode})");
                        return $url;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return null;
    }
}
