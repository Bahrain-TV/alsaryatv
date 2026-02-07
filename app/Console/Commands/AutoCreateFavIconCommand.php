<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AutoCreateFavIconCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:favicon {--png : Generate a single 192x192 PNG instead of a multi-layer ICO}';
    protected $aliases = ['app:auto-create-fav-icon-command'];

    /**
     * The console command description.
     *
     * @var string
     */
    

    /**


    /**
     * Added --png option to toggle between ICO and PNG.
     */
    protected $description = 'Locates logo and generates a squared favicon with an extracted gradient.';

    public function handle()
    {
        $manager = new ImageManager(new Driver());
        $publicPath = public_path();
        
        // 1. Search Logic
        $logoFile = collect(['logo', 'brand', 'icon'])
            ->crossJoin(['png', 'jpg', 'jpeg', 'webp'])
            ->map(fn($pair) => $publicPath . '/' . $pair[0] . '.' . $pair[1])
            ->first(fn($path) => file_exists($path));

        if (!$logoFile) {
            $this->error('Source logo not found in /public.');
            return 1;
        }

        $this->info("Processing: " . basename($logoFile));

        if ($this->option('png')) {
            $this->generatePng($manager, $logoFile);
        } else {
            $this->generateIco($manager, $logoFile);
        }

        return 0;
    }

    private function generatePng($manager, $source, $size = 192, $filename = 'favicon.png')
    {
        $canvas = $this->createSquaredCanvas($manager, $source, $size);
        $canvas->toPng()->save(public_path($filename));
        $this->info("Saved 192x192 PNG to public/$filename");
    }

    private function generateIco($manager, $source)
    {
        // Standard Windows ICO sizes
        $sizes = [16, 32, 48]; 
        $frames = [];

        foreach ($sizes as $size) {
            $canvas = $this->createSquaredCanvas($manager, $source, $size);
            $frames[] = $canvas->toPng()->toString();
        }

        // Pack frames into an ICO container
        // Note: For a "drop-in" file, we use a basic binary pack for ICO 
        $icoContent = $this->packIco($frames);
        file_put_contents(public_path('favicon.ico'), $icoContent);
        
        $this->info("Saved multi-resolution ICO (16, 32, 48) to public/favicon.ico");
    }

    private function createSquaredCanvas($manager, $source, $size)
    {
        $img = $manager->read($source);
        
        // Extract gradient colors from corners
        $c1 = $img->pickColor(0, 0)->toHex();
        $c2 = $img->pickColor($img->width() - 1, $img->height() - 1)->toHex();

        $canvas = $manager->create($size, $size);
        
        // Fill background with primary extracted color
        $canvas->fill($c1); 

        // Resize source to fit
        $img->scale(width: $size, height: $size);
        $canvas->place($img, 'center');

        return $canvas;
    }

    /**
     * Binary packer for ICO format
     */
    private function packIco(array $pngFrames): string
    {
        $count = count($pngFrames);
        $output = pack('v3', 0, 1, $count);
        $offset = 6 + ($count * 16);

        foreach ($pngFrames as $png) {
            $size = strlen($png);
            // ICO Directory Entry
            $output .= pack('C4v2V2', 0, 0, 0, 0, 1, 32, $size, $offset);
            $offset += $size;
        }

        foreach ($pngFrames as $png) {
            $output .= $png;
        }

        return $output;
    }












    }
