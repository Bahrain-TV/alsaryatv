<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FavIconMakerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'favicon:make {image? : Path to image file or URL (optional)}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Generate favicon from an image. Searches base URL for images if no path provided.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $imagePath = $this->argument('image');

        // If no image provided, search base URL for images
        if (! $imagePath) {
            $this->info('🔍 Searching base URL for images...');
            $imagePath = $this->searchForImages();

            if (! $imagePath) {
                $this->error('❌ No images found. Please provide an image path.');

                return self::FAILURE;
            }

            $this->info("✅ Found image: {$imagePath}");
        }

        // Process the image
        return $this->processFavicon($imagePath);
    }

    /**
     * Search for images in the public directory and base URL
     */
    private function searchForImages(): ?string
    {
        // Search in public directory first
        $publicPath = public_path();
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];

        // Priority: logo files, then any image
        $priority = ['logo', 'icon', 'brand', 'alsarya'];

        foreach ($priority as $keyword) {
            foreach ($imageExtensions as $ext) {
                $files = glob("{$publicPath}/**/{$keyword}*.{$ext}", GLOB_BRACE);
                if (! empty($files)) {
                    return $files[0];
                }
            }
        }

        // If no priority matches, find any image
        foreach ($imageExtensions as $ext) {
            $files = glob("{$publicPath}/**/*.{$ext}", GLOB_BRACE);
            if (! empty($files)) {
                return $files[0];
            }
        }

        return null;
    }

    /**
     * Process and create favicon from image
     */
    private function processFavicon(string $imagePath): int
    {
        // Validate image exists
        if (! file_exists($imagePath) && ! filter_var($imagePath, FILTER_VALIDATE_URL)) {
            $this->error("❌ Image not found: {$imagePath}");

            return self::FAILURE;
        }

        try {
            // Download if URL
            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                $this->info('📥 Downloading image from URL...');
                $imageData = file_get_contents($imagePath);
                if (! $imageData) {
                    throw new \Exception('Failed to download image');
                }
                $tmpFile = tempnam(sys_get_temp_dir(), 'favicon');
                file_put_contents($tmpFile, $imageData);
                $imagePath = $tmpFile;
            }

            // Load image
            $imageInfo = @getimagesize($imagePath);
            if (! $imageInfo) {
                throw new \Exception('Invalid image file');
            }

            $imageType = $imageInfo[2];
            $supportedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];

            if (! in_array($imageType, $supportedTypes)) {
                throw new \Exception('Unsupported image type. Supported: JPG, PNG, GIF, WEBP');
            }

            // Load image based on type
            $image = match ($imageType) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($imagePath),
                IMAGETYPE_PNG => imagecreatefrompng($imagePath),
                IMAGETYPE_GIF => imagecreatefromgif($imagePath),
                IMAGETYPE_WEBP => imagecreatefromwebp($imagePath),
                default => throw new \Exception('Unsupported image type'),
            };

            if (! $image) {
                throw new \Exception('Failed to load image');
            }

            // Create favicon sizes
            $this->info('🎨 Creating favicon variants...');

            $faviconDir = public_path('favicon');
            if (! is_dir($faviconDir)) {
                mkdir($faviconDir, 0755, true);
            }

            // Standard favicon sizes
            $sizes = [
                'favicon-16x16.png' => 16,
                'favicon-32x32.png' => 32,
                'favicon-196x196.png' => 196,
                'apple-touch-icon.png' => 180,
                'android-chrome-192x192.png' => 192,
                'android-chrome-512x512.png' => 512,
            ];

            foreach ($sizes as $filename => $size) {
                $this->createResizedIcon($image, $size, "{$faviconDir}/{$filename}");
                $this->line("  ✓ Created {$filename}");
            }

            // Create ICO format (classic favicon)
            $this->createIcoFavicon($image, "{$faviconDir}/favicon.ico");
            $this->line('  ✓ Created favicon.ico');

            // Create WebP versions
            $webpSizes = [
                'favicon-192.webp' => 192,
                'favicon-512.webp' => 512,
            ];

            foreach ($webpSizes as $filename => $size) {
                $this->createResizedIcon($image, $size, "{$faviconDir}/{$filename}", 'webp');
                $this->line("  ✓ Created {$filename}");
            }

            // Create webmanifest.json
            $this->createManifest($faviconDir);
            $this->line('  ✓ Created site.webmanifest');

            imagedestroy($image);
            if (isset($tmpFile)) {
                unlink($tmpFile);
            }

            $this->info('✅ Favicon created successfully!');
            $this->info('📝 Add this to your HTML head:');
            $this->line('  <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">');
            $this->line('  <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">');
            $this->line('  <link rel="apple-touch-icon" href="/favicon/apple-touch-icon.png">');
            $this->line('  <link rel="manifest" href="/favicon/site.webmanifest">');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("❌ Error: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    /**
     * Create resized icon image
     */
    private function createResizedIcon($sourceImage, int $size, string $outputPath, string $format = 'png'): void
    {
        $resized = imagecreatetruecolor($size, $size);

        // Preserve transparency
        if ($format === 'png' || $format === 'webp') {
            imagecolortransparent($resized, imagecolorallocatealpha($resized, 0, 0, 0, 127));
            imagesavealpha($resized, true);
        }

        // Get original dimensions
        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);

        // Copy and resize
        imagecopyresampled(
            $resized, $sourceImage,
            0, 0, 0, 0,
            $size, $size,
            $width, $height
        );

        // Save
        match ($format) {
            'png' => imagepng($resized, $outputPath),
            'webp' => imagewebp($resized, $outputPath, 80),
            default => imagepng($resized, $outputPath),
        };

        imagedestroy($resized);
    }

    /**
     * Create ICO format favicon with multiple resolutions.
     * Produces a real ICO file containing 16, 32, 48, and 196 px layers.
     */
    private function createIcoFavicon($sourceImage, string $outputPath): void
    {
        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);
        $layerSizes = [16, 32, 48, 196];
        $pngImages = [];

        foreach ($layerSizes as $size) {
            $layer = imagecreatetruecolor($size, $size);
            imagealphablending($layer, false);
            imagesavealpha($layer, true);
            $transparent = imagecolorallocatealpha($layer, 0, 0, 0, 127);
            imagefill($layer, 0, 0, $transparent);
            imagecopyresampled($layer, $sourceImage, 0, 0, 0, 0, $size, $size, $width, $height);

            // Capture PNG data in-memory
            ob_start();
            imagepng($layer);
            $pngImages[] = ['size' => $size, 'data' => ob_get_clean()];
            imagedestroy($layer);
        }

        // Build ICO file structure
        $imageCount = count($pngImages);
        // ICONDIR header: 6 bytes
        $header = pack('vvv', 0, 1, $imageCount);
        $entries = '';
        $dataOffset = 6 + ($imageCount * 16); // header + entries
        $imageData = '';

        foreach ($pngImages as $img) {
            $data = $img['data'];
            $size = $img['size'];
            $w = $size >= 256 ? 0 : $size;
            $h = $size >= 256 ? 0 : $size;

            // ICONDIRENTRY: 16 bytes each
            $entries .= pack('CCCCvvVV',
                $w,              // width  (0 = 256+)
                $h,              // height (0 = 256+)
                0,               // color count
                0,               // reserved
                1,               // color planes
                32,              // bits per pixel
                strlen($data),   // image data size
                $dataOffset      // offset to image data
            );

            $imageData .= $data;
            $dataOffset += strlen($data);
        }

        file_put_contents($outputPath, $header . $entries . $imageData);
    }

    /**
     * Create web manifest file
     */
    private function createManifest(string $faviconDir): void
    {
        $manifest = [
            'name' => config('app.name', 'AlSarya'),
            'short_name' => 'AlSarya',
            'icons' => [
                [
                    'src' => '/favicon/android-chrome-192x192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ],
                [
                    'src' => '/favicon/android-chrome-512x512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ],
                [
                    'src' => '/favicon/favicon-192.webp',
                    'sizes' => '192x192',
                    'type' => 'image/webp',
                ],
                [
                    'src' => '/favicon/favicon-512.webp',
                    'sizes' => '512x512',
                    'type' => 'image/webp',
                ],
            ],
            'theme_color' => '#0f172a',
            'background_color' => '#ffffff',
            'display' => 'standalone',
        ];

        file_put_contents(
            "{$faviconDir}/site.webmanifest",
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }
}
